$(document).ready(function () {
    alert('ready');
    var opt = {
        format: 'YYYY-MM-DD HH:mm'
    };
    var ct = $('#created_on');
    ct.flatpickr(opt);
});