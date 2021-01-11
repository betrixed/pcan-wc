<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Link;

use WC\Db\DbQuery;
use WC\Link\PageInfo;
use WC\Models\Linkery;
use Phiz\Db\Column;
use WC\Valid;
/**
 * Description of LinkeryData
 *
 * @author michael
 */
trait LinkeryData {

    protected function getAllLinks($id) {

        $sql = <<<EOD
select i.* from links i
    join linktogallery k on i.id = k.linkid and k.gallid = :id    
EOD;
        $results = (new DbQuery($this->db))->objectSet($sql,
                ['id' => $id], ['id' => Column::BIND_PARAM_INT]);
        return $results;
    }

    protected function listPageNum($numberPage, $pageRows, $orderby) {
        $start = ($numberPage - 1) * $pageRows;
        $sql = <<<EOD
select b.*, count(*) over() as full_count
    from link_gallery b
    order by  $orderby
    LIMIT  :pgsize OFFSET :pgstart
EOD;


        $results = (new DbQuery($this->db))->objectSet($sql,
                ['pgsize' => $pageRows,
                    'pgstart' => $start],
                ['pgsize' => Column::BIND_PARAM_INT,
                    'pgstart' => Column::BIND_PARAM_INT]);

        $maxrows = !empty($results) ? $results[0]->full_count : 0;

        return new PageInfo($numberPage, $pageRows, $results, $maxrows);
    }

    protected function linkeryPage($m) {
        $numberPage = Valid::toInt($_REQUEST, 'page', 1);
        $orderby = 'name';
        $order_field = 'b.name desc';

        $m->orderby = $orderby;
        $m->page = $this->listPageNum($numberPage, 12, $order_field);
    }

}
