{{ content() }}

<div class='container'>
<?php

    $ubase = "mailchimp/edit";
    $person = $this->view->person;
    
    $form = $this->view->form;
    $donate = $this->view->donate;
    
    $data = $this->view->data;
?>
</div>

<?php if (is_string($data)) { ?>
<pre>
    {{ data }}
</pre>
<?php } ?>
<?php if (is_object($person)) { 
    
    $address = $person->address1;
    if (strlen($address) > 0)
        $address .= ', ';
    $address .= $person->suburb;
    if (strlen($address) > 0)
        $address .= ', ';
    $address .= $person->state;
    if (strlen($address) > 0)
        $address .= ' ';           
    $address .= $person->postcode;
?>
 <div class='panel-primary'>
    <div class="panel-heading">Member info</div>
    <div class="panel-body">
        <div class="row">
        <div class="col-md-1">
            <span class="label label-default">Name</span><p>{{ person.name }}</p>
        </div>
        <div class="col-md-1">
        <span class="label label-default">Surname</span><p>{{ person.surname }}</p>
        </div>
        <div class="col-md-3">
            <span class="label label-default">Email</span><p>{{ person.email }}</p>
        </div>
        <div class="col-md-2">
            <span class="label label-default">Phone</span><p>{{ person.phone1 }}</p><p>{{ person.phone2 }}</p>
        </div>
        <div class="col-md-1">
        <span class="label label-default">Status</span><p>{{ person.status }}</p>
        </div>
         <div class="col-md-3">
        <span class="label label-default">Extra</span><p>{{ person.info }}</p>
        </div>
        </div>
        <div class="row">
            
        </div>
        <div class="row">
            <div class="col-md-5">
            <table><caption>Optional fields</caption>
                <tr><td>Member-type</td><td>{{ person.memberType }}</td></tr>
                <tr><td>Financial</td><td>{{ person.financial }}</tr>
                <tr><td>Interests</td><td>{{ person.interests }}</tr>
                <tr><td>Volunteer</td><td>{{ person.volunteer }}</tr>
                <tr><td>Position</td><td>{{ person.position }}</tr>
                <tr><td>Organisation</td><td>{{ person.organisation }}</tr>
                <tr><td>Address</td><td>{{ address }}</tr>
            </table>
            </div>
        </div>
        
    </div>
    <div class="panel-footer ">
            <a class="button btn-lg" href="/admin/member/edit/{{ person.mcid }}" >Edit This Record</a>
            <a class="button btn-lg" href="/admin/mailchimp/query" >Search Member List</a>
    </div>
    
</div>

<?php } ?>

<div class='panel panel-primary'>
    <div class="panel-heading">Member Donations</div>
    <div class='panel-body'>
    <?php if (count($donate) > 0) { ?>    
        <p>Donations from {{ person.name }} {{ person.surname}} </p>
        <table>
            <thead>
                <tr><th>Purpose</th><th>Date</th><th>Amount</th><th>Edit</th>
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
                <td><a href="/admin/mailchimp/donate/{{mcd.donateId}}">Edit</a></td>
            </tr>
        <?php } ?>
        </table>
    <?php } else { ?>
        <p>No donations from {{ person.name }} {{ person.surname}} </p>
     <?php }  ?>
    </div>
</div>


<?php if (is_object($form)) { ?>
<form method="post" >
    <input type="hidden" name="formid" value="donation" />
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
        <div class='input-group date' id='member_date' >
            <label for="member_date"><?= $form->label("member_date") ?></label>
            <input type='text' class="form-control" name="member_date" />
            <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar"></span>
            </span>
        </div>    
    </div> 
    <div>
        <?= $form->render("mcid"); ?>
        {{ submit_button("Add Donation") }}
        <p></p>
    </div>
</div>
</form>

<?php } ?>
<div class="clear"></div>


<script type='text/javascript'>
$(function() {
    var opt = { 
        format : 'YYYY-MM-DD'
    };
    
    var ct = $('#member_date');
    
    ct.datetimepicker(opt);

});
</script>