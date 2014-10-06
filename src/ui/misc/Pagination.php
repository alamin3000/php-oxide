<?php
namespace oxide\ui\misc;
use oxide\ui\html\Element;

class Pagination extends Element
{
   public $pageCount       = 1;
   public $currentPage     = 1;
   public $recordCount     = 1;
   public $printCount      = 5;
   
   public $url             = '';
   public $queryString     = '?';
   public $queryStringKey  = '';
   
   public $labelPrevious   = '&laquo; previous';
   public $labelNext       = 'next &raquo;';
   public $labelDots       = '&hellip;';
   
   public $css             = '';
   public $cssCurrent      = '';
   public $cssNumber       = '';
   public $cssFirstLast    = '';
   public $cssPreviousNext = '';
   
   
   /**
    * construction
    *
    * requires pagecount.
    * @access public
    */
   public function __construct($pagecount, $querykey = 'p', $printcount = 5) {
   	parent::__construct('span');
      // initialize required local variables
      $this->pageCount = $pagecount;
      $this->queryStringKey = $querykey;
      $this->printCount = $printcount;
      
      
      // get current page number from the query string.
      // and store it locally.
      $page = (isset($_GET[$querykey])) ? (int) $_GET[$querykey] : 1;
      if($page < 1) {
         $page = 1;
      }
      $this->currentPage = $page;
      
      // get and build the query string.
      // if querykey already exists in the query, then remove it.
      $querystring = '?' . $_SERVER['QUERY_STRING'];
      $this->queryString = str_ireplace(array("&$querykey=$page", "$querykey=$page"), '', $querystring);
   }
   
   
   /**
    * prints the pagination components
    *
    * prints complete pagintaion based on 
    * @access public
    * @return string
    */
   public function onRenderInnerTag(Element $el, \oxide\util\ArrayString $buffer) {
   	/*
   	 * if only one page, then don't do anything.
   	 */
   	if($this->pageCount < 2) return '1';
   	
   	
      $start = -1;
      $end = -1;
		$mean = floor($this->printCount/2);
		
		if($this->currentPage <= $this->printCount) {
			$start = 1;
			if($this->pageCount < $this->printCount) {
				// total pages is less then requested printcount.
				// so only print upto total pages
				$end = $this->pageCount;
			} else {
				// total pages is more then printcount, so print upto
				// total printcount
				$end = $this->printCount;
			}
		}
		
		elseif($this->currentPage > $this->printCount) {
			$start = $this->currentPage - $mean;
			if($this->pageCount < ($this->currentPage + $this->printCount)) {
				// not enough pages to print
				// so print upto total pages
				$end = $this->pageCount;
			} else {
				$end = $this->currentPage + $mean;
			}
		}	
		
		// start building the pagination components
		$html = ' ';
		
		// previous link.
		if($this->currentPage > 1) {
         $html .= $this->_wrap($this->_link($this->labelPrevious, $this->currentPage - 1), $this->cssPreviousNext);
		}
		
		// first link
		if($start > 1) {
         $html .= $this->_wrap($this->_link('1', 1), $this->cssFirstLast) . $this->labelDots;
		}
		
		// page number links
		for($i = $start; $i <= $end; $i++) {
         if($i == $this->currentPage) {
            $html .= $this->_wrap('<strong>'. $i . '</strong>', $this->cssNumber);
         } else {
            $html .= $this->_wrap($this->_link($i, $i), $this->cssNumber);
         }
		}
		
		// last link
		if($end < $this->pageCount) {
         $html .= $this->labelDots . $this->_wrap($this->_link($this->pageCount, $this->pageCount), $this->cssFirstLast);
		}
		
		// next link
		if($this->currentPage < $this->pageCount) {
         $html .= $this->_wrap($this->_link($this->labelNext, $this->currentPage + 1), $this->cssPreviousNext);
		}
      
      // return the html string
      $buffer->append($html);
   }
   
   private function _link($label, $pageto) {
      return '<a href="'. $this->url . $this->queryString . '&' . $this->queryStringKey . '=' . $pageto . '">' . $label . '</a>';
   }
   
   private function _wrap($str, $class = '') {
      return ' <span class="'.$class.'">'.$str.'</span> ';
   }
}