// scripting for file.htm, to order by Asset
$(document).ready(function() {
        var options = {
            beforeSubmit: uploadBegin,
            success: uploadOK
        };
        $('#upfile').ajaxForm(options);
    });
   
 function uploadBegin(formData, jqForm, options) { 
    var bar = $('.up_bar');
    var percent = $('.up_percent');
    var status = $('#image_status');
    
    $('#up_div').show();
    status.empty();
    percent.show();
    bar.show();
    var percentVal = '0%';
    bar.width(percentVal)
    percent.html(percentVal);
    return true; 
} 

function uploadOK(responseText, statusText, xhr, $form)  { 
}



