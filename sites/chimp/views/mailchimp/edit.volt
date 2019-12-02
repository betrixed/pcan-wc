{{ content() }}

<div class='container'>
<?php

    $ubase = "mailchimp/edit";
    
    $person = $this->view->person;
    
    $form = $this->view->form;
    $donate = $this->view->donate;
    
?>
</div>
<div class='container' style="background-color:white;">
    <?php if (count($donate) > 0) { ?>    
        <p>Donations from {{ person.name }} {{ person.surname}} </p>
        <table>
            <thead>
                <tr><th>Purpose</th><th>Date</th><th>Amount</th>
                </tr>
            </thead>
        <?php
        $row = 0;
        foreach ($donate as $mcd) { 
            $row += 1;
        ?>
            <tr>
                <td>{{ mcd.purpose }} </td>
                <td>{{ mcd.member_date }} </td>
                <td>{{ mcd.amount }} </td>
            </tr>
        <?php } ?>
        </table>
    <?php } else { ?>
        <p>No donations from {{ person.name }} {{ person.surname}} </p>
     <?php }  ?>
</div>

<?php if (is_object($form)) { ?>
<form method="post">
<div class='col-md-4' style="background-color:lightgreen;">
    <p>New Donation</p>
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
        {{ submit_button("Add Donation") }}
        <p></p>
    </div>
</div>
</form>

<?php } ?>


<script type="text/javascript">
$('#member_date').datetimepicker({
        //formatTime:'H:i',
	formatDate:'d.m.Y',
        timepicker:false
});

</script>