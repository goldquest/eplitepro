/**
 * Created by timowelde on 06.12.13.
 */

function initIndex(pathRemovePic) {
    // Collapse other groups
    $('.group-content').hide();

    // Hide actions
    $('.actions').hide();

    // Hide usernames and enable showUsernamesHandler
    usernamesHandler();
    // Add Submithandler to the newuser form
    newUserHandler();

    // Hide flash messages with a click
    flashmessages.clickHandler();

    // Add Handler for "remove group"
    removeGroupHandler($('.group_delete'));

    // initialise group picture upload
    var uploadGroupPicture = new UploadGroupPicture({
        pathRemovePic: pathRemovePic,
        pathOrigPic: $('#headerpic').attr('src')
    });

    // Prepare newpadforms
    var newpadform = $('.pads .newpadform');
    // Add submit handler for the newpadforms
    newPadFormSubmitHandler(newpadform, uploadGroupPicture);
    // Ajax call for expanding group, if clicked
    expandGroupHandler($('#groups .group-name'), newpadform, uploadGroupPicture);

    // show action icons when mouseover groupname
    showActionsHandler($('#groups .group-name'))

    // Trigger click for first group
    $('.group:first .group-link').click();

    // Show group_edit button (This functionality is hidden, when js is disabled)
    var editgroup = $('.group .editgroup').show();
    // Edit groupname click handler
    renameGroupClickHandler(editgroup);

    // groupname form submit handler
    renameGroupFormHandler($('.group .editform'));

    // show/hide groups
    $('#togglegroups').click(function(e){
        e.preventDefault();
        $('#groups-menu').toggle();
        $('#pad').toggleClass('fullwidth');
        $(this).find('img').toggle();
    });

    // renew cookie every x seconds
    renewCookieHandler()
}

function renewCookieHandler() {
    var renewObj = $('#renewCookie')
    var url = renewObj.attr('href')

    var delay = parseInt(renewObj.data('expires'))*1000

    timeoutFunc = function(delay) {
        renewCookieTimeout = setTimeout(function(){
            $.get(url, function(data, textStatus, jqXHR) {
                var delay = parseInt(data)*1000
                timeoutFunc(delay)
            })
        }, delay)
    }

    timeoutFunc(delay)

}