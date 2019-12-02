<div id='side_div'></div>
<script type="text/javascript">
    $(document).ready()
    {
        $.get('/index/side',function(data){
            $('#side_div').html(data);
        });
    }
</script>