// scripting for file.htm, to order by Asset
// For GalleryAdmController

// scripting for file.htm, to order by Asset
function image_click(e) {
    // set current session imageid
    var imageid =  $(e.currentTarget).data('imgid');
    var url="/admin/gallery/setid/" + imageid
    $.post(url, {id:imageid},
    function(data, status) {
        $("div.img-row").each(function( index, elem) { 
            var rowimg = elem.getAttribute('data-imgid');
            var color = (rowimg === data) ? "yellow" : "#eee";
            $(elem).css("background-color", color);   
        } );
    }); 
}

function post_imagelist(responseText, statusText, xhr, $form) {
    var status = $('#image_status');
    status.html(responseText);
    status.show();
    /* status.style.display = 'block'; */
    //alert('done');
    cfg_dtclass();
    prep_imagelist();
    $('#image_op').selectpicker('refresh');
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
  $("div.img-row").on("click", image_click);
}
$(document).ready(function() { 
        prep_imagelist();
});
