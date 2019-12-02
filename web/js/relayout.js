
function laygrid(id) {
    var grid = document.getElementById(id);
    salvattore.recreateColumns(grid);
}

function showAs(s) {
    $("#showid").html(s);
}
function relayout(aref,vid) {
    var link = $(aref).attr('get');
    //alert('lay ' + vid + ' ' + link );
    $.get(link,function(data){
        try {
            $("#" + vid).html(data);
            if (link.includes('show=grid')) {
                laygrid('mgrid');
            }
        }
        catch(e) {
            alert(e);
        }
    });  
}
