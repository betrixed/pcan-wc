<?php

namespace Chimp\DB;

use WC\DB\Server;
use Mailchimp\MailchimpLists;
use Mailchimp\MailchimpAPIException;
use Chimp\Api;
use WC\DB\MemberEmail;
use WC\DB\Member;

/**
 * Description of chimplists
 *
 * @author michael
 */
class ChimpLists extends \DB\SQL\Mapper {

    const TABLE = 'chimp_lists';

    public static $LOCAL_ZONE = null;

    //put your code here
    public function __construct($db = null) {
        if (is_null($db)) {
            $db = Server::db();
        }
        parent::__construct($db, static::TABLE, NULL, 1.0e8); // 100 second
    }

    static function classInit() {
        if (is_null(static::$LOCAL_ZONE)) {
            static::$LOCAL_ZONE = new \DateTimeZone('Australia/Sydney');
        }
    }

    static function getByListId($listid) {
        $result = new ChimpLists();
        return $result->load(['listId = ?', $listid]);
    }

    static function defaultList() {
        // $f3 = \Base::instance();
        $listrecid = \Base::instance()->get('secrets.chimp.default-list');
        return static::getById($listrecid);
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

    static function cnvDateTime($chimpTime) {
        $d1 = new \DateTime($chimpTime);
        $d1->setTimezone(static::$LOCAL_ZONE);
        return $d1->format('Y-m-d H:i:s');
    }

    static function createChimpList($list) {
        $chimplist = new ChimpLists();
        $chimplist['listid'] = $list->id;
        $chimplist['listName'] = $list->name;
        $stats = $list->stats;
        $chimplist['last_send'] = $stats->campaign_last_sent;
        $chimplist['last_sub'] = $stats->last_sub_date;
        $chimplist['last_unsub'] = $stats->last_unsub_date;
        $json = new \stdClass();
        $json->campaign_defaults = $list->campaign_defaults;
        $json->contact = $list->contact;

        $chimplist['phpjson'] = json_encode($json);

        return $chimplist->save();
    }

    static function allLists() {
        $allLists = [];
        static::classInit();
        $db = Server::db();
        $allLists = $db->exec("select * from " . static::TABLE);
        return $allLists;
    }

    /**
     * For default list, get array of [ChimpList record, mail chimp member info, ChimpEntry record]
     * MailChimpMember info, and ChimpEntry record if it exists
     * @param type $email
     * @return type
     */
    static function getEmailStatus($email) {
        $list = static::defaultList();
        $info = $list->getMemberInfo($email);
        $id = $list['id'];
        $entry = ($info !== false) ? ChimpEntry::byUniqueId($id, $info->unique_email_id) : false;
        return [$list, $info, $entry];
    }

    public function getMemberInfo($email) {
        $api = Api::instance()->listApi();

        try {
            $result = $api->getMemberInfo($this['listid'], $email);

            return $result;
        } catch (MailchimpAPIException $me) {
            return false;
        }
    }

    public function addMemberEmail($eid) {
        $email = MemberEmail::byId($eid);
        if ($email === false) {
            return;
        }
        $member = Member::byId($email['memberid']);

        // ensure this email doesn't exist already in list
        $result = $this->getMemberInfo($email['email_address']);

        if ($result === false) {
            $api = Api::instance()->listApi();
            $fname = $member['fname'];
            $lname = $member['lname'];
            $phone = $member['phone'];
            $source = $member['source'];
            $city = $member['city'];
            $state = $member['state'];
            $zip = $member['postcode'];
            $country = $member['country_code'];
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

            $result = $api->addMember($this['listid'], $email['email_address'],
                    ['merge_fields' => $merge, 'status' => 'subscribed']);
        }
        if ($result === false) {
            return false;
        } else {
            $entry_rec = new ChimpEntry();
            $entry_rec['listid'] = $this['id'];
            $entry_rec['uniqueid'] = $result->unique_email_id;
            $entry_rec['emailid'] = $eid;
            $entry_rec['chimpid'] = $result->id;
            $entry_rec['status'] = $result->status;
            $entry_rec->save();
            return $entry_rec;
        }
    }

    /**
     * 
     * @return array of ChimpList records
     */
    static function sync() {
        $allLists = [];
        static::classInit();

        $db = Server::db();
        $db->exec("LOCK TABLES " . static::TABLE . " WRITE");
        try {
            $db->exec("SET autocommit=0");
            $json = Api::instance()->getLists();

            foreach ($json->lists as $list) {
                $stats = $list->stats;
                $stats->last_sub_date = static::cnvDateTime($stats->last_sub_date);
                $stats->last_unsub_date = static::cnvDateTime($stats->last_unsub_date);
                $stats->campaign_last_sent = static::cnvDateTime($stats->campaign_last_sent);
                $chimplist = static::getByListId($list->id);
                if ($chimplist === false) {
                    $chimplist = static::createChimpList($list);
                    $isNew = true;
                } else {
                    $isNew = false;
                }
                if (!$isNew) {


                    $newSub = static::compare_dates($stats->last_sub_date, $chimplist['last_sub']);
                    $newUnsub = static::compare_dates($stats->last_unsub_date, $chimplist['last_unsub']);
                    $newSend = static::compare_dates($stats->campaign_last_sent, $chimplist['last_send']);
                    if ($newSub || $newUnsub || $newSend) {
                        $chimplist['last_sub'] = $stats->last_sub_date;
                        $chimplist['last_unsub'] = $stats->last_unsub_date;
                        $chimplist['last_send'] = $stats->campaign_last_sent;

                        $chimplist->update();
                    }
                }

                $allLists[] = $chimplist;
            }
            $db->exec("COMMIT");
        } finally {
            $db->exec("UNLOCK TABLES");
        }

        return $allLists;
    }

    static function mergeValue($rec, $field, $value) {
        switch ($field) {
            case 'last_update':
                if (empty($rec[$field]) || $value > $rec[$field]) {
                    $rec[$field] = $value;
                }
                break;
            case 'create_date':
                if (empty($rec[$field]) || $value < $rec[$field]) {
                    $rec[$field] = $value;
                }
                break;
            case 'phpjson': {
                    if (!empty($rec[$field])) {
                        $data = json_decode($rec[$field]);
                    } else {
                        $data = [];
                    }
                    if (!empty($value) && !in_array($value, $data)) {
                        $data[] = $value;
                        $rec[$field] = json_encode($data);
                    }
                }
            default:
                if (empty($rec[$field]) && !empty($value)) {
                    $rec[$field] = $value;
                }
                break;
        }
    }

    public function syncMembers() {
        $listid = $this['listid'];
        $recid = $this['id'];
        $offset = 0;
        $params = [
            'offset' => $offset,
            'count' => 10
        ];

        ChimpLists::classInit();
        $db = Server::db();
        $total = 0;

        while (true) {
            //$response = Api::instance()->doCurl('GET', "lists/$listid/members", $params);
            $json = Api::instance()->getMembers($listid, $params);
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
                $last_update = ChimpLists::cnvDateTime($memberClass->last_changed);
                $uniqueid = $memberClass->unique_email_id;
                $mcid = $memberClass->id;
                $email_address = $memberClass->email_address;
                $create_date = ChimpLists::cnvDateTime($memberClass->timestamp_opt);
                // see if this has been done, and has lastupdate
                $entry_rec = ChimpEntry::byUniqueId($recid, $uniqueid);

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
                $email_rec = MemberEmail::byEmail($email_address);
                if ($email_rec !== false) {
                    $member_rec = Member::byId($email_rec['memberid']);
                    $newMember = false;
                } else {
                    $member_rec = Member::byPhone($fname, $lname, $phone);
                    if ($member_rec === false) {
                        $member_rec = new Member();
                        $newMember = true;
                    } else {
                        $newMember = false;
                    }
                }
                $test = $member_rec['create_date'];
                if ($newMember || ($member_rec['last_update'] < $last_update) || is_null($test)) {
                    static::mergeValue($member_rec, 'fname', $fname);
                    static::mergeValue($member_rec, 'lname', $lname);
                    static::mergeValue($member_rec, 'addr1', $address->addr1);
                    static::mergeValue($member_rec, 'addr2', $address->addr2);
                    static::mergeValue($member_rec, 'city', $address->city);
                    static::mergeValue($member_rec, 'state', $address->state);
                    static::mergeValue($member_rec, 'postcode', $address->zip);
                    static::mergeValue($member_rec, 'country_code', $address->country);
                    static::mergeValue($member_rec, 'phone', $phone);
                    static::mergeValue($member_rec, 'source', $source);
                    static::mergeValue($member_rec, 'status', $status);
                    static::mergeValue($member_rec, 'last_update', $last_update);
                    static::mergeValue($member_rec, 'create_date', $create_date);
                    static::mergeValue($member_rec, 'phpjson', $email_address);
                    if ($newMember)
                        $member_rec->save();
                    else
                        $member_rec->update();
                }

                if ($email_rec === false) {
                    $email_rec = new MemberEmail();
                    $email_rec['email_address'] = $email_address;
                    $email_rec['memberid'] = $member_rec['id'];
                    $email_rec->save();
                }

                if ($entry_rec === false) {
                    $entry_rec = new ChimpEntry();
                    $entry_rec['listid'] = $recid;
                    $entry_rec['uniqueid'] = $uniqueid;
                    $entry_rec['emailid'] = $email_rec['id'];
                    $entry_rec['chimpid'] = $mcid;
                    $entry_rec['status'] = $status;
                    $entry_rec->save();
                }
            }
            $db->commit();
        }
    }

    static function getById($id) {
        $result = new ChimpLists();
        return $result->load(['id = ?', $id]);
    }

}
