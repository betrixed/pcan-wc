var selectedId;
var origcolor = 'white';

function fetchid(aref)
{
    
    var gid = $(aref).attr('id');
    var href =  $(aref).attr('href');
   
    if (gid === "first_link") {
        gid = 'f' + href;
        aref = document.getElementById(gid);
    }
    var link = "/cat/fetch/" + href;

    if (selectedId)
    {
        if (selectedId == gid)
            return;
    }
    else {
        var obj = document.getElementById(gid);
        origcolor = $(obj).css('backgroundColor');
    }

    $.get(link,function(data){
            $('#article').html(data);
    });
    if (selectedId)
    {
        var old = document.getElementById(selectedId).parentNode;
        $(old).css('backgroundColor',origcolor);
        $(aref.parentNode).css('backgroundColor','palegoldenrod');
    }
    else {
         $(aref.parentNode).css('backgroundColor','palegoldenrod');
    }
    selectedId = gid;

    var editlink = $('#edit_link');
    if (editlink)
    {
        editlink.attr('href','/admin/blog/edit/' + gid);
    }
}

$(document).ready(function () {
            $("#first_link").click();
});