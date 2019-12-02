{{ content() }}
<?php
    $member = $this->view->form;
?>

<form id="fnew" method="post">
<div class='container' style="background-color:white;">
    <p>Create new member record</p>
    {% include 'partials/member.volt' %}
    <div>
        {{ submit_button("Create New Member") }}
    </div>
    <?= $form->render("mcid"); ?>
</div>
</form>