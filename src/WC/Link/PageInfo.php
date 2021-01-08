<?php

namespace WC\Link;

/**
 * A page of results from a query
 *
 * @author Michael Rynn
 */
class PageInfo {

    public $before;
    public $next;
    public $last;
    public $current;
    public $dataRows;
    public $items;
    public $pageRows;

    /**
     * $pagenum current page
     * $limit   number of rows in a page
     * $results reference to the page rows as object array
     * $maxrows is number of rows in query without the LIMIT
     */
    public function __construct($pageNum, $pageSize, &$results, $maxrows) {
        $this->items = &$results;      //  reference to results array
        if ($pageSize < 0) {
            $pageSize = 0;
        }
        $this->pageRows = $pageSize;
        $this->dataRows = $maxrows;
        $this->before = ($pageNum > 1) ? $pageNum - 1 : 1;
        $this->current = $pageNum;
        $this->last = (int) (($maxrows - 1) / $pageSize + 1);
        $this->next = ($pageNum < $this->last) ? $pageNum + 1 : $this->last;
    }

}
