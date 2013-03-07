<?php

namespace HUBerlin\EPLiteProBundle\Security;

class LdapDataSource
{
	private $ldapHost;
	private $ldapPort;
	private $ldapUserDN;
	private $ldapUserAttribute;

    public function __construct($host, $port, $dn, $uidAttribute) {
        // TODO - Make this configurable
		$this->ldapHost = $host;
		$this->ldapPort = $port;
		$this->ldapUserDN = $dn;
		$this->ldapUserAttribute = $uidAttribute;
    }
	
	public function authenticateUser($user, $password) {
		// Connect
		$resourceLink = $this->connect();
		
		// Check if the connection succeeded 
		if (!$resourceLink) {
			return false;
		}
		
		// Try to bind to ldap
		$result = false;
		$bind = false;
		try {
			$bind = @ldap_bind($resourceLink, $this->ldapUserAttribute . "=" . $user . "," . $this->ldapUserDN, $password);
		} catch(ErrorException $e) {
			$result = false;
		}
		
		// Did the bind succeed
		if ($bind) {
			// Try to retrieve the user record
			$result = $this->getUserRecord($user, $resourceLink);
		} else {
			$result = false;
		}
		
		// Shut down the ldap connection
		$this->disconnect($resourceLink);
		
		return $result;
	}
	
	public function getUserRecord($user, $resourceLink = NULL) {
		// Try to retrieve the user attributes 
		$result = $this->searchRecords($this->ldapUserAttribute . "=" . $user, array("uid", "sn", "cn", "mail", "givenName"), $resourceLink);
		
		// Check if only one result was fetched
		if (!$result || !array_key_exists("count", $result) || $result["count"] != 1) {
			// Appearantly the user was not found or multiple records were returned
			return false;
		} else {
			return $result[0];
		}
	}
	
	public function getUserRecordExtended($user, $resourceLink = NULL) {
	    // Try to retrieve the user attributes
	    $result = $this->searchRecords('(|('.$this->ldapUserAttribute . "=" . $user.')(sn='.$user.')(cn='.$user.')(givenName='.$user.'))', array("uid", "sn", "cn", "mail", "givenName"), $resourceLink);
	    
	    // Check if only one result was fetched
	    if (!$result || !array_key_exists("count", $result) || $result["count"] != 1) {
	        // Appearantly the user was not found or multiple records were returned
	        return false;
	    } else {
	        return $result[0];
	    }
	}
	
	public function searchRecords($filter, $attributes, $resourceLink = NULL) {
		// Try to connect if no established connection was given
		$connect = false;
		if (!$resourceLink) {
			$resourceLink = $this->connect();
			$connect = true;
		}
		
		// Check if the connection succeeded 
		if (!$resourceLink) {
			return false;
		}
		
		// Everything was good, retrieve the requested attributes
		$result = false;
		try {
			// Grap the attributes and records from ldap
			$resultLink = @ldap_search($resourceLink, $this->ldapUserDN, $filter, $attributes);
			$result = ldap_get_entries($resourceLink, $resultLink);
			
			// Free the result
			ldap_free_result($resultLink);
		} catch(ErrorException $e) {
			$result = false; 
		}
		
		// Close the connection (if it was opened by ourself)
		if ($connect) {
			$this->disconnect($resourceLink);
		}
		
		return $result;
	}
	
	public function connect() {
		try {
			// Try to connect to our host
			$resourceLink = @ldap_connect($this->ldapHost, $this->ldapPort);
			if (!$resourceLink) {
				return false;
			}
			
			// Setup usage of Protocol version 3
			ldap_set_option($resourceLink, LDAP_OPT_PROTOCOL_VERSION, 3);
			return $resourceLink;
		} catch(ErrorException $e) {
			return false; 
		}
	}
	
	public function disconnect($resourceLink) {
		// If no link exists we do nothing
		if (!$resourceLink) { 
			return;
		}
		
		return ldap_unbind($resourceLink);
	}	
}

?>