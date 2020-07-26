<?php

namespace App\Link;

use WC\Valid;
use WC\Db\Server;
use WC\Db\DbQuery;
use App\Models\MemberEmail;
use App\Models\Member;

/**
 * Description of member
 *
 * @author michael
 */
trait MemberData
{

    static public function getOrderBy($m, $orderby)
    {
        if (is_null($orderby)) {
            $orderby = 'name-alt';
        }
        $alt_list = array(
            'mdate' => 'mdate',
            'cdate' => 'cdate',
            'name' => 'name',
            'surname' => 'surname',
            'email' => 'email',
            'phone' => 'phone',
            'city' => 'city',
            'status' => 'status'
        );
        $col_arrow = array(
            'mdate' => '',
            'cdate' => '',
            'name' => '',
            'surname' => '',
            'email' => '',
            'phone' => '',
            'city' => '',
            'status' => ''
        );
        switch ($orderby) {
            default:
            case 'name':
                $alt_list['name'] = 'name-alt';
                $col_arrow['name'] = '&#8595;';
                $order_field = 'M.fname asc';
                break;
            case 'mdate':
                $alt_list['mdate'] = 'mdate-alt';
                $col_arrow['mdate'] = '&#8595;';
                $order_field = 'M.last_update asc';
                break;
            case 'cdate':
                $alt_list['cdate'] = 'cdate-alt';
                $col_arrow['cdate'] = '&#8595;';
                $order_field = 'M.create_date asc';
                break;
            case 'surname':
                $alt_list['surname'] = 'surname-alt';
                $col_arrow['surname'] = '&#8595;';
                $order_field = 'M.lname asc';
                break;
            case 'email':
                $alt_list['email'] = 'email-alt';
                $col_arrow['email'] = '&#8595;';
                $order_field = 'ME.email_address asc';
                break;
            case 'phone':
                $alt_list['phone'] = 'phone-alt';
                $col_arrow['phone'] = '&#8595;';
                $order_field = 'M.phone asc';
                break;
            case 'city':
                $alt_list['city'] = 'city-alt';
                $col_arrow['city'] = '&#8595;';
                $order_field = 'M.city asc';
                break;
            case 'status':
                $alt_list['status'] = 'status-alt';
                $col_arrow['status'] = '&#8595;';
                $order_field = 'email_status asc';
                break;
            case 'name-alt':
                $col_arrow['name'] = '&#8593;';
                $order_field = 'M.fname desc';
                break;
            case 'surname-alt':
                $col_arrow['surname'] = '&#8593;';
                $order_field = 'M.lname desc';
                break;
            case 'mdate-alt':
                $col_arrow['mdate'] = '&#8593;';
                $order_field = 'M.last_update desc';
                break;
            case 'cdate-alt':
                $col_arrow['mdate'] = '&#8593;';
                $order_field = 'M.last_update desc';
                break;
            case 'email-alt':
                $col_arrow['email'] = '&#8593;';
                $order_field = 'ME.email_address desc';
                break;
            case 'phone-alt':
                $col_arrow['phone'] = '&#8593;';
                $order_field = 'M.phone desc';
                break;
            case 'city-alt':
                $col_arrow['city'] = '&#8593;';
                $order_field = 'M.city desc';
                break;
            case 'status-alt':
                $col_arrow['status'] = '&#8593;';
                $order_field = 'email_status desc';
                break;
        }
        $m->orderalt = $alt_list;
        $m->orderby = $orderby;
        $m->col_arrow = $col_arrow;
        return $order_field;
    }

    static function assignPost($post, $m)
    {
        $default = '';
        $m->id = Valid::toInt($post, 'id', 0);
        $m->fname = Valid::toStr($post, 'fname', $default);
        $m->lname = Valid::toStr($post, 'lname', $default);
        $m->phone = Valid::toPhone($post, 'phone', $default);
        $m->addr1 = Valid::toStr($post, 'addr1', $default);
        $m->addr2 = Valid::toStr($post, 'addr2', $default);
        $m->postcode = Valid::toStr($post, 'postcode', $default);
        $m->city = Valid::toStr($post, 'city', $default);
        $m->state = Valid::toStr($post, 'state', $default);
        $m->country_code = Valid::toStr($post, 'country_code', $default);
        $m->ref_source = Valid::toStr($post, 'source', $default);
        $m->status = Valid::toStr($post, 'status', 'no-email');
    }

    /**
     * Return member record, and flag if new
     * @param array $post
     * @param WC\WConfig $m - model object for form re-entry
     * @return array[ record, bool (isNew) ]
     * This does not write back to database.
     */
    static function assignMember($m)
    {
        $errorList = [];
        $default = "";

        if (!empty($m->phone)) {
            if (!Valid::has_GEnDigits($m->phone, 8)) {
                $errorList[] = ['phone', 'Phone no. must have at least 8 digits'];
            }
        }

        if ($m->id > 0) {
            $rec = Member::findFirstById($m->id);
            $isNew = false;
        } else {
            // see if record exists by phone
            $rec = self::findFirst($m->fname, $m->lname, $m->phone);

            if (empty($rec)) {
                $rec = new Member();
                $isNew = true;
            } else {
                $isNew = false;
            }
        }


        $rec->phone = $m->phone;
        $rec->fname = $m->fname;
        $rec->lname = $m->lname;

        $rec->addr1 = $m->addr1;
        $rec->addr2 = $m->addr2;
        $rec->postcode = $m->postcode;
        $rec->city = $m->city;
        $rec->state = $m->state;
        $rec->country_code = $m->country_code;
        $rec->ref_source = $m->ref_source;
        $rec->status = $m->status;
        $today = Valid::now();
        if ($isNew) {
            $rec->create_date = $today;
            $rec->phpjson = json_encode([]);
        }

        $rec->last_update = $today;
        return [$rec, $isNew];
    }

    /**
     * 
     * @param string $email
     * @return array [ member, member_email]
     */
    static function byEmail($email)
    {
        $me = MemberEmail::byEmail($email);
        if ($me !== false) {
            $mbr = static::byId($me['memberid']);
            return [$mbr, $me];
        }
        return [false, false];
    }

    /** update status field on record id
     * 
     * @param type $id
     * @param type $status
     */
    public function setEmailStatus($id, $status)
    {
        $sql = "update member_email set status = :stat where id = :id";
        $db = $this->db;
        $db->execute($sql, ['id' => $id, 'stat' => $status]);
    }

    public function getMemberEmails($id)
    {
        $sql = "select e.* from member_email e where e.memberid = :mid";
        $qry = new DbQuery($this->db);
        $results = $qry->arraySet($sql, ['mid' => $id]);
        if ($results) {
            return $results;
        } else {
            return [];
        }
    }

    /**
     * 
     * @param string $email
     * @return array [ member, member_email]
     */
    static function EmailWithMember($email): array
    {
        $me = MemberEmail::findFirstByEmailAddress($email);

        if (!empty($me)) {
            $mbr = Member::findFirstById($me->memberid);
            return [$mbr, $me];
        }
        return [false, false];
    }

    public function getMemberDonations($id)
    {
        $sql = "select d.* from donation d where d.memberid = :mid order by d.member_date";

        $results = (new DbQuery($this->db))->arraySet($sql, ['mid' => intval($id)]);
        if ($results) {
            return $results;
        } else {
            return [];
        }
    }
    
    static function memberNamePhone($fname, $lname, $phone) {
        return Member::findFirst(
            ['fname = ? and lname = ? and phone = ?', 
            [$fname, $lname, $phone], 
            [Column::BIND_PARAM_STR, Column::BIND_PARAM_STR, Column::BIND_PARAM_STR]
            ]);
    }

}
