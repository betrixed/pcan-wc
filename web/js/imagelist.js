// scripting for file.htm, to order by Asset

function post_imagelist(responseText, statusText, xhr, $form) {
    var status = $('#image_status');
    status.html(responseText);
    status.show();
    /* status.style.display = 'block'; */
    //alert('done');
    cfg_dtclass();
    prep_imagelist();
}
function prep_imagelist() {
    try {
        $('#imageList').ajaxForm(
            {
            success: post_imagelist
            }
        );
    }
    catch(error) {
        alert(error);
    }    
    
    $( ".imageDesc" ).click(function(event) {
         var isReadOnly = $(event.target).prop('readonly');
         if (isReadOnly)
         {
            var id = '#chk' + event.target.id.substring(4);
            $(id).prop('checked',true); 
            $(event.target).prop('readonly',false);
         }
    });

    $( ".imageDesc" ).change(function(event) {
         var id = '#chk' + event.target.id.substring(4);
         $(id).prop('checked',true); 
    });
    
}

$(document).ready(function() { 
        prep_imagelist();
});

    



