function enable_datemember(ix, elem)
{
   $(elem).flatpickr({
        enableTime: false,
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d"
    });
}

function post_donation(responseText, statusText, xhr, $form) {
    var status = $('#donateList');
    status.html(responseText);
    status.show();
}

function prep_member() {
    $(".datetimepicker-input").each(enable_datemember);
    
    try {
        $('#donateForm').ajaxForm(
            {
            success: post_donation
            }
        );
    }
    catch(error) {
        alert(error);
    }    
}

$(document).ready(function() { 
        prep_member();
});
