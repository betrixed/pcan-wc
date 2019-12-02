{{ content() }}

<div class='container'>
<?php
    $person = $this->view->person;
    $member = $this->view->member;
?>
</div>

<?php if (is_object($member)) { ?>
<form method="post">
    <input type="hidden" name="formid" value="member" />
 <div class='panel-primary'>
    <div class="panel-heading">Member info</div>
    <div class="panel-body">
        {% include 'partials/member.volt' %}
        <div class="form-group">
            <button type='submit' name='edit-member' value='edit' >Save</button>
            <!--<button type='submit' name='edit-member' value='delete' >Delete</button>-->
        </div>
    </div>
</div>
   
</form>
<?php } ?>
