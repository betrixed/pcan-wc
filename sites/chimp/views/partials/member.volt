<div class="form-group">
    <label for="name"><?= $member->label("name") ?></label>
    <?= $member->render("name"); ?>
    <label for="surname"><?= $member->label("surname") ?></label>
    <?= $member->render("surname"); ?>
</div>
<div class="form-group">
    <label for="email"><?= $member->label("email") ?></label>
    <?= $member->render("email"); ?>
</div>
<div class="form-group">
    <label for="phone1"><?= $member->label("phone1") ?></label>
    <?= $member->render("phone1"); ?>
    <label for="phone2"><?= $member->label("phone2") ?></label>
    <?= $member->render("phone2"); ?>
</div>
<div class="form-group">
    <label for="info"><?= $member->label("info") ?></label>
    <?= $member->render("info"); ?>
</div>
<div class="form-group">
    <label for="status"><?= $member->label("status") ?></label>
    <?= $member->render("status"); ?>
    <?= $member->render("mcid"); ?>
</div>
<div class="form-group">
    <label for="memberType"><?= $member->label("memberType") ?></label>
    <?= $member->render("memberType"); ?>
</div>
<div class="form-group">
    <label for="financial"><?= $member->label("financial") ?></label>
    <?= $member->render("financial"); ?>
</div>        
<div class="form-group">
    <label for="interests"><?= $member->label("interests") ?></label>
    <?= $member->render("interests"); ?>
</div> 
<div class="form-group">
    <label for="volunteer"><?= $member->label("volunteer") ?></label>
    <?= $member->render("volunteer"); ?>
</div>    
<div class="form-group">
    <label for="position"><?= $member->label("position") ?></label>
    <?= $member->render("position"); ?>
</div>    
<div class="form-group">
    <label for="organisation"><?= $member->label("organisation") ?></label>
    <?= $member->render("organisation"); ?>
</div>  