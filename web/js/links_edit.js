var AboutToSubmit = false;

function doDelete(myform)
{
    myform.action = '/admin/link/delete';
    myform.method = 'post';

    myform.submit();
    return false;
}

function smotePost(f) {
    if ($('#summary').summernote('codeview.isActivated')) {
        $('#summary').summernote('codeview.deactivate');
    }
    AboutToSubmit = true;
    return true;
}

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
}
function smoteOptions(isAirMode) {
    smote = {
        callbacks: {
            onChange: function (contents) {
                if (contents) {
                    var winEvent = window.attachEvent || window.addEventListener;
                    var chkEvent = window.attachEvent ? 'onbeforeunload' : 'beforeunload';

                    winEvent(chkEvent, function (e) {
                        if (AboutToSubmit)
                            return true;
                        var confirmationMessage = 'This page is asking you to confirm that you want to leave - data you have entered may not be saved';
                        (e || window.event).returnValue = confirmationMessage;
                        return confirmationMessage;
                    });
                }
            }
        }
    };
    if (isAirMode)
        smote['airMode'] = true;
    return smote;
}

function codeSwitch()
{
    var airMode = getUrlParameter('airmode');
    var isAirMode = (airMode == '1');
    var loc = window.location;
    var newloc = loc.protocol + '//' + loc.host + loc.pathname;
    if (!isAirMode)
    {
        newloc = newloc + '?airmode=1';
    }
    // force submit
    $('#linkform').trigger('submit');
    window.location = newloc;
}

$(document).ready(function () {
    try {
        var airMode = getUrlParameter('airmode');
        var isAirMode = (airMode == '1');

        $("#summary").summernote(smoteOptions(isAirMode));

        if (isAirMode)
        {
            $('#airbtn').html('Switch to Editor');
        }

    } catch (error) {
        alert(error);
    }
});