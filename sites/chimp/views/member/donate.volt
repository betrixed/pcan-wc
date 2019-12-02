{{ content() }}

<div class='container'>
<?php
    //$person = $this->view->person;
    //$member = $this->view->member;
    $donate = $this->view->donate;
?>
</div>
<div class='panel panel-primary'>
    <div class="panel-heading">Member Donations</div>
    <div class='panel-body'>
    <?php if (count($donate) > 0) { ?>    
        <p>All Donations</p>
        <table>
            <thead>
                <tr><th class="centerCell">Purpose</th>
                    <th class="centerCell">Date</th>
                    <th class="centerCell">Amount</th>
                    <th class="centerCell">from</th>
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
                <td><a href="/admin/mailchimp/list/{{mcd.mcid}}">{{mcd.name ~ ' ' ~ mcd.surname}}</a></td>
            </tr>
        <?php } ?>
        </table>
    <?php } else { ?>
        <p>No donations found</p>
     <?php }  ?>
    </div>
</div>

