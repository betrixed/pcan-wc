<?php
    $page = $this->view->page;
?>

    <?php 
        if (is_object($page) && count($page->items) > 0) {
    ?>
    <div id='mt1'  class='container' style="background-color:lightblue;">
        <p><?= count($page->items) . " rows have been returned" ?></p>
    </div>
    <div class='container' style="background-color:white;">
    <table class='table-striped'>
    <thead>
        <tr>
            <th></th>
            <th>
                <button class='btn btn-info' onclick ='tsort("<?= $orderalt["name"]?>")'>Name</button><?= $col_arrow['name'] ?>&nbsp;/&nbsp;
                <button class='btn btn-info' onclick ='tsort("<?= $orderalt["surname"]?>")'>Surname</button><?= $col_arrow['surname'] ?>
            </th>
            <th>&nbsp;<button class='btn btn-info' onclick ='tsort("<?= $orderalt["email"]?>")'>Email</button><?= $col_arrow['email'] ?></th>
            <th>&nbsp;<button class='btn btn-info' onclick ='tsort("<?= $orderalt["date"]?>")'>Date</button><?= $col_arrow['date'] ?></th>
            <th>&nbsp;<button class='btn btn-info' onclick ='tsort("<?= $orderalt["status"]?>")'>Status</button><?= $col_arrow['status'] ?></th>
            <th>&nbsp;&nbsp;link</th>
        </tr>
    </thead>
    <tbody>
    <?php 
        $row = 0;
        foreach ($page->items as $mc) { 
            $row += 1;
    ?>
        <tr data-toggle="collapse" data-target="#r{{mc.id}}" class="accordian-toggle"> 
            <td><?= $row ?><button class="btn btn-default btn-xs"><span class="glyphicon glyphicon-eye-open"/></button></td>
            <td class='leftCell'>{{ mc.name ~ ' ' ~ mc.surname }}</td>
            <td class='centerCell'>{{ mc.email }}</td>
            
            <td><?php echo substr($mc->created_at,0,10); ?></td>
            <td class='centerCell'>{{ mc.status }}</td> 
            <td>{{ link_to(myController ~ "list/" ~ mc.mcid, "List") }}</td>
        </tr>
        <tr>
            <td colspan="5" class="hiddenRow"><div 
                class="accordian-body-collapse collapse row" 
                id="r{{mc.id}}" >
                <div class="text-left bg-white">
                    <div class="row">
                        <p> 
<pre><label>Info</label> {{ mc.info }}
<br/><label>Phone1</label>  {{ mc.phone1 }} <label>Phone2</label>  {{ mc.phone2 }}</pre>
</p>
                    </div>
                </div>
                </div></td>
        </tr>
    <?php } ?>
    </tbody>
    </table>
    </div>
<?php
}
?>

    
    

