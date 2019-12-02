
$(document).ready(function () {
    var cartdiv = $('#cartdiv');
    var fromdiv = $('#fromdiv');
    try {

        $('#cartsub').ajaxForm({
            complete: function (xhr) {
                cartdiv.html(xhr.responseText);
                cartdiv.style.display = 'block';
                document.refresh;
            }
        });
        $('#fromsub').ajaxForm({
            complete: function (xhr) {
                fromdiv.html(xhr.responseText);
                fromdiv.style.display = 'block';
                document.refresh;
            }
        });    
    }
    catch(error) {
     alert(error);
    }
});
