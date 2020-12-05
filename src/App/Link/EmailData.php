<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Link;

use WC\{
    WConfig,
    Valid
};

/**
 * Description of EmailData
 *
 * @author michael
 */
trait EmailData {

    protected function listPageNum(int $numberPage, int $pageRows, string $orderby) {
        $start = ($numberPage - 1) * $pageRows;
        $sql = <<<EOD
select b.*, count(*) over() as full_count
    from email_group b
EOD;

        $qry = $this->dbq;

        $qry->bindLimit($pageRows, $start);
        $qry->order($orderby);

        $results = $qry->queryOA($sql);

        $maxrows = !empty($results) ? $results[0]->full_count : 0;

        return new PageInfo($numberPage, $pageRows, $results, $maxrows);
    }

    protected function getWebDir(): string {
        if (empty($this->webdir)) {
            $app = $this->app;
            $path = Valid::noFrontSlash($app->email_sets);
            $this->webdir = $app->temp_dir . "/" . Valid::endSlash($path);
            ;
        }
        return $this->webdir;
    }

    protected function getLeafPath($tp) {
        return DIRECTORY_SEPARATOR . $tp->name . ".phtml";
    }

    protected function makeSetsDir($galpath): bool {
        $imgdir = $this->getWebDir() . $galpath;
        if (!file_exists($imgdir)) {
            if (!mkdir($imgdir, 0775, true)) {
                $this->flash("Cannot make path : " . $imgdir);
                return false;
            }
        }
        return true;
    }
    protected function delete_regid($rid) : bool {
        $worked = $this->db->execute("delete from reg_mail where reg_id = $rid");
        if ($worked) {
            $worked = $this->db->execute("delete from register where id = $rid");
        }
        return $worked;
    }
    protected function valid_email_domain($email) : bool {
        $email_parts = explode('@', $email);
        if (count($email_parts) === 2) {
            $email_host = $email_parts[1];
            if (!empty($email_host)) {
                $ip = gethostbyname($email_host);
                if ($ip !== $email_host) {
                    return true;
                }
            }
        }
        return false;
    }

    /** encode a record id number, and email address */
    protected function encode_map(): array {
        return ['0' => 'a', '1' => 'e', '2' => 't', '3' => 's',
            '4' => 'i', '5' => 'o', '6' => 'u',
            '7' => 'n', '8' => 'm', '9' => 'y', '/' => 'b'];
    }

    protected function decode_map(): array {
        return ['a' => '0', 'e' => '1', 't' => '2', 's' => '3',
            'i' => '4', 'o' => '5', 'u' => '6',
            'n' => '7', 'm' => '8', 'y' => '9', 'b' => '/'];
    }

    protected function link_encode(string $id1, string $id2): string {
        $data = str_split($id1 . '/' . $id2);
        $map = $this->encode_map();
        $s = '';
        foreach ($data as $c) {
            $s .= $map[$c];
        }
        return bin2hex(shoco_compress($s));
    }

    protected function link_decode(string $input): ?array {
        if (strlen($input) % 2 !== 0) {
            return null;
        }
        $check = str_split($input);
        foreach ($check as $hex) {
            if (!ctype_xdigit($hex)) {
                return null;
            }
        }
        $sc = hex2bin($input);
        $d = shoco_decompress($sc);
        $data = str_split($d);
        $map = $this->decode_map();
        $s = '';
        foreach ($data as $c) {
            if (!isset($map[$c])) {
                return null;
            }
            $s .= $map[$c];
        }
        if (!empty($s)) {
            $two = explode('/', $s, 2);
            if (count($two) == 2 && (strlen($two[0]) > 0) && (strlen($two[1] > 0))) {
                if (is_numeric($two[0]) && is_numeric($two[1]))
                    return $two;
            }
        }
        return null;
    }

    protected function setupPlates($tp, $group) {

        $email_path = $this->getWebDir();

        if (!file_exists($email_path)) {
            mkdir($email_path, 0777, true);
        }
        $group_path = $email_path . $group->name;
        if (!file_exists($group_path)) {
            mkdir($group_path, 0777, true);
        }
        $content_path = $group_path . '/' . $tp->name . ".phtml";
        file_put_contents($content_path, $tp->html);

        $plates = new WConfig();
        $plates->UI = [$email_path];
        $plates->ext = 'phtml';
        $plates->view = $group->name . '/' . $tp->name;
        return $plates;
    }

    protected function getDirPath($gal) {
        return $this->getWebDir() . $gal->name;
    }

    protected function indexPage($m) {
        $numberPage = Valid::toInt($_REQUEST, 'page', 1);
        $orderby = 'name';
        $order_field = 'b.name desc';

        $m->orderby = $orderby;
        $m->page = $this->listPageNum($numberPage, 12, $order_field);
    }

}
