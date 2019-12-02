function ready_eventList() {
    $('#eventList').ajaxForm({
      complete: function (xhr) {
        var evtlist = $('#event_dates');
        evtlist.html(xhr.responseText);
        evtlist.style.display = 'block';
        document.refresh;
    }
    });
}
$(document).ready(function () {
    ready_eventList();
    $('#eventForm').ajaxForm({
        complete: function (xhr) {
            var evtlist = $('#event_dates');
            evtlist.html(xhr.responseText);
            evtlist.style.display = 'block';
            document.refresh;
        }
    });
});