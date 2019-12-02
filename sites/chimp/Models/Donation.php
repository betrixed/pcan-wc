<?php

/*
See the "licence.txt" file at the root "private" folder of this site
*/
namespace Mod\Chimp\Models;

use Phalcon\Mvc\Model;

class Donation extends Model {
    
    public $donateId;
    
    public $mcid;
    
    public $amount;
    
    public $purpose;
    
    public $created_at;
    
    public $member_date;
};
