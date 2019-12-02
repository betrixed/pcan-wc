<?php
namespace Chimp\Models;

use Phalcon\Mvc\Model;

class Mclist extends Model {
    public $id;
    
    public $listId;
    
    public $listName;
    
    
    public $members;
    
    public $unsubscribed;
    
    public $cleaned;
}