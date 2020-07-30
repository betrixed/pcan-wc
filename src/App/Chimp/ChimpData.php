<?php

namespace App\Chimp;

use WC\Db\Server;
use WC\Db\DbQuery;
use Mailchimp\MailchimpLists;
use Mailchimp\MailchimpAPIException;
use App\Chimp\Api;
use App\Models\ChimpLists;
use App\Models\ChimpEntry;
use App\Models\MemberEmail;
use App\Models\Member;
use WC\App;
use Phalcon\Db\Column;
/**
 * Description of chimplists
 *
 * @author michael
 */
trait ChimpData {
    protected $chimp_api = null;
    
    public function getApi() {
        if ($this->chimp_api === null) {
            $app = $this->app;
            require_once $app->php_dir . '/chimpv3/vendor/autoload.php';
            $s = $app->getSecrets();
            if( isset($s['chimp'])) {      
                $this->chimp_api = new Api($s['chimp']);
            }
            else {
                throw new \Exception("Mail Chimp settings not found");
            }
        }
        return $this->chimp_api;
    }

    public function defaultList() : ?ChimpLists 
    {
        $api = $this->getApi();
        if ($api) {
            return ChimpLists::findFirstById($api->getDefaultListId());
        }
        return null;   
    }

    static function compare_dates($date1, $date2) {
        $d1 = new \DateTime($date1);
        $d2 = new \DateTime($date2);
        if ($d1 == $d2) {
            return 0;
        }
        if ($d1 < $d2) {
            return -1;
        }
        return 1;
    }

    static function memberNamePhone($fname, $lname, $phone) {
        return Member::findFirst([
            'conditions' =>
             'fname = :fn: and lname = :ln: and phone = :ph:',
            'bind' => 
            ['fn' => $fname, 'ln' => $lname, 'ph' => $phone], 
            
            'bindTypes' => 
            ['fn' => Column::BIND_PARAM_STR, 'ln' >= Column::BIND_PARAM_STR, 'ph' => Column::BIND_PARAM_STR]
            ]);
    }

    /** return a ChimpLists model object, from a JSON record
     * 
     * @param type $list
     * @return ChimpLists
     */
    static function createChimpList($list): ChimpLists {
        $chimplist = new ChimpLists();
        $chimplist->listid = $list->id;
        $chimplist->listName = $list->name;
        $stats = $list->stats;
        $chimplist->last_send = $stats->campaign_last_sent;
        $chimplist->last_sub = $stats->last_sub_date;
        $chimplist->last_unsub = $stats->last_unsub_date;
        $json = new \stdClass();
        $json->campaign_defaults = $list->campaign_defaults;
        $json->contact = $list->contact;

        $chimplist->phpjson = json_encode($json);

        $chimplist->save();
        return $chimplist;
    }

    public function allLists() {
        $qry = new DbQuery($this->db);
        $allLists = $qry->objectSet("select * from chimp_lists");
        return $allLists;
    }

    static public function byUniqueId($id, $uid) {
        return ChimpEntry::findFirst(
                        ["listid = ?0 and uniqueid = ?1",
                            'bind' => [$id, $uid]
        ]);
    }

    /**
     * For default list, get array of [ChimpList record, mail chimp member info, ChimpEntry record]
     * MailChimpMember info, and ChimpEntry record if it exists
     * @param type $email
     * @return type
     */
    public function getEmailStatus($email) {
        $list = $this->defaultList();
        $info = $this->getMemberInfo($list, $email);
        $id = $list->id;
        $entry = ($info !== false) ? static::byUniqueId($id, $info->unique_email_id) : false;
        return [$list, $info, $entry];
    }

    /**
     * email is an email address, not a record id 
     */
     public function getMemberInfo($list, $email) {
        $list_api = $this->getApi()->listApi();

        try {
            $result = $list_api->getMemberInfo($list->listid, $email);
            return $result;
        } catch (MailchimpAPIException $me) {
            return false;
        }
    }

    static function addMemberEmail($list, $eid) {
        $email = MemberEmail::findFirstById($eid);
        if (empty($email)) {
            return;
        }
        $member = Member::findFirstById($email->memberid);

        // ensure this email doesn't exist already in list
        $result = static::getMemberInfo($list, $email->getEmailAddress());

        if ($result === false) {
            $api = Api::instance()->listApi();
            $fname = $member->fname;
            $lname = $member->lname;
            $phone = $member->phone;
            $source = $member->source;
            $city = $member->city;
            $state = $member->state;
            $zip = $member->postcode;
            $country = $member->country_code;
            if (is_null($phone)) {
                $phone = '';
            }
            if (is_null($city)) {
                $city = '';
            }
            if (is_null($state)) {
                $state = '';
            }
            if (is_null($zip)) {
                $zip = '';
            }
            if (is_null($country)) {
                $country = '';
            }
            if (is_null($source)) {
                $source = '';
            }
            $merge = [
                'FNAME' => $fname,
                'LNAME' => $lname,
                'PHONE' => $phone,
                'MMERGE6' => $source,
                'MMERGE7' => $city,
                'MMERGE8' => $state,
                'MMERGE9' => $zip
            ];
            /* if (!empty($country)) {
              $address = new \stdClass();
              $address->country = $country;
              $merge['ADDRESS'] = $address;
              } */

            $result = $api->addMember($list->listid, $email->email_address,
                    ['merge_fields' => $merge, 'status' => 'subscribed']);
        }
        if ($result === false) {
            return false;
        } else {
            $entry_rec = new ChimpEntry();
            $entry_rec->listid = $list->id;
            $entry_rec->uniqueid = $result->unique_email_id;
            $entry_rec->emailid = $eid;
            $entry_rec->chimpid = $result->id;
            $entry_rec->status = $result->status;
            $entry_rec->save();
            return $entry_rec;
        }
    }

    /**
     * 
     * @return array of ChimpList records
     */
    public function chimp_sync() {
        $allLists = [];
        $api = $this->getApi();
        
        $db = $this->db;
        $db->execute("LOCK TABLES chimp_lists WRITE");
        try {
            $db->execute("SET autocommit=0");
            $json = $api->getLists();

            foreach ($json->lists as $list) {
                $stats = $list->stats;
                $stats->last_sub_date = $api->cnvDateTime($stats->last_sub_date);
                $stats->last_unsub_date = $api->cnvDateTime($stats->last_unsub_date);
                $stats->campaign_last_sent = $api->cnvDateTime($stats->campaign_last_sent);
                
                $chimplist = ChimpLists::findFirstByListid($list->id);
                if ($chimplist === false) {
                    $chimplist = static::createChimpList($list);
                    $isNew = true;
                } else {
                    $isNew = false;
                }
                if (!$isNew) {
                    $newSub = static::compare_dates($stats->last_sub_date, $chimplist->last_sub);
                    $newUnsub = static::compare_dates($stats->last_unsub_date, $chimplist->last_unsub);
                    $newSend = static::compare_dates($stats->campaign_last_sent, $chimplist->last_send);
                    if ($newSub || $newUnsub || $newSend) {
                        $chimplist->last_sub = $stats->last_sub_date;
                        $chimplist->last_unsub = $stats->last_unsub_date;
                        $chimplist->last_send = $stats->campaign_last_sent;

                        $chimplist->update();
                    }
                }

                $allLists[] = $chimplist;
            }
            $db->execute("COMMIT");
        } finally {
            $db->execute("UNLOCK TABLES");
        }

        return $allLists;
    }

    static function mergeValue($rec, $field, $value) {
        if (empty($value)) 
            return;
        $method = 'get' . ucfirst($field);
        $current = $rec->$method();
        switch ($field) {
            case 'lastUpdate':
                if (empty($current) || $value > $current) {
                    $rec->$field = $value;
                }
                break;
            case 'createDate':
                if (empty($current) || $value < $current) {
                    $rec->$field = $value;
                }
                break;
            case 'phpjson': {
                    if (!empty($current)) {
                        $data = json_decode($current);
                    } else {
                        $data = [];
                    }
                    if (!empty($value) && !in_array($value, $data)) {
                        $data[] = $value;
                        $rec->$field = json_encode($data);
                    }
                }
            default:
                if (empty($current) && !empty($value)) {
                    $rec->$field = $value;
                }
                break;
        }
    }

    public function syncMembers(ChimpLists $list) {
        $listid = $list->listid;
        $recid = $list->id;
        $offset = 0;
        $params = [
            'offset' => $offset,
            'count' => 10
        ];

        $api = $this->getApi();
        $db = $this->db;
        $total = 0;

        while (true) {
            //$response = Api::instance()->doCurl('GET', "lists/$listid/members", $params);
            $json = $api->getMembers($listid, $params);
            //$json = json_decode($response->body);
            $ct = count($json->members);
            $total += $ct;
            if ($ct === 0)
                break;
            $params['offset'] += $ct;
            $db->begin();
            foreach ($json->members as $memberClass) {
                // id, email_address, unique_email_id, email_type, status
                // merge_fields - 
                // ADDRESS - addr1, addr2, city, state, zip, country
                // PHONE
                // BIRTHDAY
                // MMERGE6 - source
                // MMERGE7 - suburb
                // MMERGE8 - state
                // MMERGE9 - postcode
                // MMERGE10 - phone
                // tags -- []

                $status = $memberClass->status;
                $last_update = $api->cnvDateTime($memberClass->last_changed);
                $uniqueid = $memberClass->unique_email_id;
                $mcid = $memberClass->id;
                $email_address = $memberClass->email_address;
                $create_date = $api->cnvDateTime($memberClass->timestamp_opt);
                // see if this has been done, and has lastupdate
                $entry_rec = static::byUniqueId($recid, $uniqueid);

                /* if ($entry_rec !== false) {
                  continue;
                  }
                 */
                $merge_fields = $memberClass->merge_fields;
                $source = $merge_fields->MMERGE6;
                $fname = $merge_fields->FNAME;
                $lname = $merge_fields->LNAME;

                if (!empty($merge_fields->ADDRESS)) {
                    $address = $merge_fields->ADDRESS;
                } else {
                    $address = new \stdClass();
                    $address->country = 'AU';
                }
                if (empty($address->city)) {
                    $address->city = $merge_fields->MMERGE7;
                }
                if (empty($address->state)) {
                    $address->state = $merge_fields->MMERGE8;
                }
                if (empty($address->zip)) {
                    $address->zip = $merge_fields->MMERGE9;
                }
                if (empty($merge_fields->PHONE)) {
                    $merge_fields->PHONE = $merge_fields->MMERGE10;
                }

                if (!empty($merge_fields->PHONE)) {
                    $phone = preg_replace('/\s+/', ' ', $merge_fields->PHONE);
                    if (strlen($phone) === 9 && is_numeric($phone) && substr($phone, 0, 1) !== '0') {
                        $phone = '0' . $phone;
                    }
                } else {
                    $phone = '';
                }
                $email_rec = MemberEmail::findFirstByEmailAddress($email_address);
                if ( !empty($email_rec)) {
                    $member_rec = Member::findFirstById($email_rec->memberid);
                    $newMember = false;
                } else {
                    
                    $member_rec = self::memberNamePhone($fname, $lname, $phone);
                    if (empty($member_rec)) {
                        $member_rec = new Member();
                        $newMember = true;
                    } else {
                        $newMember = false;
                    }
                }
                $test = $member_rec->create_date;
                if ($newMember || ($member_rec->last_update < $last_update) || is_null($test)) {
                    static::mergeValue($member_rec, 'fname', $fname);
                    static::mergeValue($member_rec, 'lname', $lname);
                    if (isset($address->addr1)) {
                        static::mergeValue($member_rec, 'addr1', $address->addr1);
                    }
                    if (isset($address->addr2)) {
                        static::mergeValue($member_rec, 'addr2', $address->addr2);
                    }
                    if (isset($address->city)) {
                        static::mergeValue($member_rec, 'city', $address->city);
                    }
                    if (isset($address->state)) {
                        static::mergeValue($member_rec, 'state', $address->state);
                    }
                    if (isset($address->zip)) {
                        static::mergeValue($member_rec, 'postcode', $address->zip);
                    }
                    if (isset($address->country)) {
                        static::mergeValue($member_rec, 'countryCode', $address->country);
                    }
                    static::mergeValue($member_rec, 'phone', $phone);
                    static::mergeValue($member_rec, 'source', $source);
                    static::mergeValue($member_rec, 'status', $status);
                    static::mergeValue($member_rec, 'lastUpdate', $last_update);
                    static::mergeValue($member_rec, 'createDate', $create_date);
                    static::mergeValue($member_rec, 'phpjson', $email_address);
                    if ($newMember)
                        $member_rec->save();
                    else
                        $member_rec->update();
                }

                if (empty($email_rec)) {
                    $email_rec = new MemberEmail();
                    $email_rec->email_address = $email_address;
                    $email_rec->memberid = $member_rec->id;
                    $email_rec->save();
                }

                if (empty($entry_rec)) {
                    $entry_rec = new ChimpEntry();
                    $entry_rec->listid = $recid;
                    $entry_rec->uniqueid = $uniqueid;
                    $entry_rec->emailid = $email_rec->id;
                    $entry_rec->chimpid = $mcid;
                    $entry_rec->status = $status;
                    $entry_rec->save();
                }
            }
            $db->commit();
        }
    }

}
