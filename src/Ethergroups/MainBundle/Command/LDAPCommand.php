<?php
namespace Ethergroups\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Entities\Users;

class LDAPCommand extends ContainerAwareCommand {
    
    protected function configure()
    {
        $this
        ->setName('Ethergroups:ldap')
        ->setDescription('Removes users (and Groups if necessary), when a user is deleted in LDAP')
        //->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
        //->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get all neccessary services
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $ldap = $this->getContainer()->get('ldap.data.provider');
        $grouphandler = $this->getContainer()->get('grouphandler');
        
        // Get all local users
        $localusers = $em->getRepository('EthergroupsMainBundle:Users')->findAll();

        $count = 0;
        foreach ($localusers as $localuser) {
            // Is the user in ldap yet?
            $ldapuser = $ldap->getUserRecord($localuser->getUid());
            // User is not in ldap
            if($ldapuser === false) {
                $groups = $localuser->getGroups();
                // Go through all groups and remove him (if necessary delete the group)
                foreach ($groups as $group) {
                    $grouphandler->removeUser($group, $localuser);
                }
                $output->writeln('Removed user: '.$localuser->getUid());
                $em->remove($localuser);
                $count++;
            }
        }
        $em->flush();
        
        $output->writeln('successfuly removed '.$count.' users');
    }
}