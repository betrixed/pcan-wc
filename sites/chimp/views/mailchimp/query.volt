{{ content() }}
<?php
    $form = $this->view->form;
?>
<div class="container">
<div class="panel-group">
    
<div class='panel panel-primary'>
    <div class="panel-heading">Links</div>
    <div class="panel-body">
    <a href="{{ '/' ~ myController }}new" class="btn btn-default">Create new member record</a>
    <a href="/admin/api/index" class="btn btn-default">Synchronize from Mail Chimp</a>
    </div>
</div>
<form id="fquery" method="post" action='memberlist'>
<div class='panel panel-default panel-primary' style="background-color:white;">
    <div class="panel-heading">Member Search - One or more conditions combined with 'AND'</div>
    <div class="panel-body">

    <div class="form-group">
        <label for="name"><?= $form->label("name") ?></label>
        <?= $form->render("name"); ?>
        <?= $form->render("name_sel"); ?>
    </div>
    <div class="form-group">
        <label for="surname"><?= $form->label("surname") ?></label>
        <?= $form->render("surname"); ?>
        <?= $form->render("surname_sel"); ?>
    </div>
    <div class="form-group">
        <label for="statustype"><?= $form->label("statustype") ?></label>
    <?= $form->render("statustype"); ?>
    </div>
    <div class="form-group">
        <label for="membertype"><?= $form->label("membertype") ?></label>
    <?= $form->render("membertype"); ?>
    </div>
    <div class="form-group">
        <label for="hasPhone"><?= $form->label("hasPhone") ?></label>
    <?= $form->render("hasPhone"); ?>
    </div>
    <div>
        <?= $form->render("orderby"); ?>
        <nav>

            <button class='btn btn-success' id='submit_find' type='submit' name='submit' value ='query'>List</button>
        
            <button class='btn btn-warning pull-right' id='submit_download' type='submit' name='submit' value ='download'>Download</button>

        </nav>
        <p></p>
    </div>
    </div>
</div>
</form>

</div>
</div>
<div id='member_list'>
</div>

<script type="text/javascript">
$(function() {
    var mlist = $('#member_list');

    $('#fquery').ajaxForm({
        complete: function(xhr) {
            mlist.html(xhr.responseText);
            mlist.style.display = 'block';
            document.refresh; 
        }
    });

})();
</script>
<script type="text/javascript">
    function tsort( torder)
    {
        var form = document.getElementById("fquery");
        $('#orderby').val(torder);   
        $('#submit_find').click();
    }
</script>
    
    

