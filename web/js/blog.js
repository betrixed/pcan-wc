var AboutToSubmit = false;

function smotePost() {
    if ($('#article').summernote('codeview.isActivated')) {
        $('#article').summernote('codeview.deactivate');
    }
    AboutToSubmit = true;
    return true;
}

function smoteVerify() {
    if ($('#article').summernote('codeview.isActivated')) {
        $('#article').summernote('codeview.deactivate');
    }
    var htmldata = $('#article').summernote('code');
    $.ajax({
        url: "/admin/blog/verify",
        method: "POST",
        data: {article: htmldata}
    }).done(function (ret) {
        $("#analysis").html(ret);
    });
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
};

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
    $('#postForm').trigger('submit');
    window.location = newloc;
}

function wrapStyle()
{
    var sel = $('#style').val();
    $('#wrap_style').attr('class', sel);
}


function blog_setup() {
    try {
        var airMode = getUrlParameter('airmode');
        var isAirMode = (airMode == '1');

        $("#article").summernote(smoteOptions(isAirMode));
        var options = {
            target: '#category_status'
        };
        $('#categoryList').ajaxForm(options);

    } catch (error) {
        alert(error);
    }
}

$(document).ready(blog_setup);