var AboutToSubmit = false;
var TextAreaID;
var Editor;

function smotePost() {
    if ($(TextAreaID).summernote('codeview.isActivated')) {
        $(TextAreaID).summernote('codeview.deactivate');
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
};

function codeOptions() {
    return { // codemirror options
            lineNumbers: true,
            mode: "application/x-httpd-php",
            matchBrackets : true,
            theme: "ambiance",
            lineWiseCopyCut : true,
            undoDepth : 200
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
        },
        height:300,
        codemirror: codeOptions()
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

function mce_setup(area_id) {
    tinymce.init({selector: area_id});
}
function codearea_setup(area_id) {
     TextAreaID=area_id;
     var ctl = document.getElementById("article");
     
     Editor = CodeMirror.fromTextArea(ctl, codeOptions());
}
function textarea_setup(area_id) {
    TextAreaID=area_id;
    try {
        var airMode = getUrlParameter('airmode');
        var isAirMode = (airMode == '1');

        $(TextAreaID).summernote(smoteOptions(isAirMode));

    } catch (error) {
        alert(error);
    }
}
