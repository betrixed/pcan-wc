function cfg_datetime(ix, elem)
{
    /* alert('d-cfg ' + elem.name + ' ' + elem.value); */
   $(elem).flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i:s"
    });
}

function cfg_dtclass() {
    $(".datetimepicker-input").each(cfg_datetime);
}

$(document).ready(cfg_dtclass);