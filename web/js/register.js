
function post_regajax(responseText, statusText, xhr, $form) {
    var status = $('#regform');
    status.html(responseText);
    status.show();
}
function prep_regajax() {
    try {
        $('#regevt').ajaxForm(
            {
            success: post_regajax
            }
        );
    }
    catch(error) {
        alert(error);
    }    
}

$(document).ready(function() { 
        prep_regajax();
});

    



