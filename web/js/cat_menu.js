var selLink2;
var selColor2;

function fetch(aref)
{
    var gid = $(aref).attr('id');
    var link = $(aref).attr('href');

    if (gid === "first_link") {
        gid = 'f' + link;
        aref = document.getElementById(gid);
        link = $(aref).attr('href');
    }
    
    if (selLink2)
    {
        if (selLink2 == gid)
            return;
    }
    else {
        selColor2 = $(aref).css('background-color');
    }
    if (link.includes('?')) {
        link += "&sub=1";
    }
    else {
        link += "?sub=1";
    }
    $.get(link,function(data){
            $('#article').html(data);
            if (link.includes('show=grid')) {
                laygrid('mgrid');
            }
    });
    $(aref.parentNode).css('background-color','palegoldenrod');
    if (selLink2)
    {
        var old = document.getElementById(selLink2).parentNode;
        $(old).css('background-color',selColor2);

    }
    selLink2 = gid;
}

$(document).ready(function () {
   $("#first_link").click();
});