{{ content() }}
<link href="/assets/css/jquery.datetimepicker.css" rel="stylesheet">
{{ javascript_include("assets/js/jquery.datetimepicker.js") }}

<?php
     $form = $this->view->form;
     $memid = $this->view->memid;
?>

<?php if (is_object($form)) { ?>
<div class='container' style="background-color:lightgray;">
    <a href="/mailchimp/list/{{memid}}">Members Donation List</a>
</div>
<form method="post">
<div class='container' style="background-color:lightpink;">
    <p>Edit Donation</p>
    <div class="form-group">
        <label for="purpose"><?= $form->label("purpose") ?></label>
        <?= $form->render("purpose"); ?>
    </div>
    <div class="form-group">
        <label for="amount"><?= $form->label("amount") ?></label>
        <?= $form->render("amount"); ?>
    </div>    
    <div class="form-group">
        <label for="member_date"><?= $form->label("member_date") ?></label>
        <?= $form->render("member_date"); ?>
    </div>    
    <div>
        <?= $form->render("mcid"); ?>
        {{ submit_button("Update Donation") }}
        <p></p>
    </div>
</div>
</form>

<?php } ?>


<script type="text/javascript">
$('#member_date').datetimepicker({
	format:'Y-m-d',
        timepicker:false
});

</script>