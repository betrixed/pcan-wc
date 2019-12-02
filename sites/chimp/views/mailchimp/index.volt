{{ content() }}
<div class='container'>
<?php

    $ubase = "mailchimp/index?orderby=";
      
    $link = $ubase . $this->view->orderby;  
    $page = $this->view->page;
    
    echo " " . $this->tag->linkTo($link, 'First');
    echo " | " . $this->tag->linkTo($link.'&page=' . $page->before, 'Previous');
    echo " | " .  $this->tag->linkTo($link.'&page=' . $page->next, 'Next');
    echo " | " .  $this->tag->linkTo($link. '&page=' . $page->last, 'Last');
    echo " | " .  $page->current, "/", $page->last;
    
?>
</div>
<div class='container' style="background-color:white;">
<table class='table-striped'>
    <thead>
        <tr>
            <th></th>
            <th><?php echo $this->tag->linkTo($ubase.$orderalt['name'], 'Name') . $col_arrow['name']  ?>
            / <?php echo $this->tag->linkTo($ubase.$orderalt['surname'], 'Surname') . $col_arrow['surname']  ?></th>
            <th><?php echo $this->tag->linkTo($ubase.$orderalt['email'], 'Email') . $col_arrow['email'] ?></th>
            <th><?php echo $this->tag->linkTo($ubase.$orderalt['date'], 'Date') . $col_arrow['date']  ?></th>
            <th><?php echo $this->tag->linkTo($ubase.$orderalt['status'], 'Status') . $col_arrow['status']  ?></th>
            <th>Edit</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($page->items as $mc) { ?>
        <tr data-toggle="collapse" data-target="#r{{mc.id}}" class="accordian-toggle"> 
            <td><button class="btn btn-default btn-xs"><span class="glyphicon glyphicon-eye-open"/></button></td>
            <td class='leftCell'>{{ mc.name ~ ' ' ~ mc.surname }}</td>
            <td class='centerCell'>{{ mc.email }}</td>
            
            <td><?php echo substr($mc->created_at,0,10); ?></td>
            <td class='centerCell'>{{ mc.status }}</td> 
            <td>{{ link_to("mailchimp/edit/" ~ mc.id, "Edit") }}</td>
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