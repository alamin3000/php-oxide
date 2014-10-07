<?php
namespace oxide\ui\misc;
use oxide\ui\html\Element;

/**
 * 
 */
class Breadcrumb extends Element implements \IteratorAggregate
{   
	public
		/*
		 * item separator symbol
		 * @var string
		 */
		$symbol = "&#187;",
		$title = null;

   
	public function __construct(array $items = null)
	{
		parent::__construct('div');
      
      if($items) {
         foreach ($items as $key => $value) {
            $this->push($key, $value);
         }
      }
	}

	/**
	 * Add a link at the begining of the breadcrumb
	 * 
	 * @param string $str
	 * @param string $link
	 */
	public function first($str, $link)
	{
      $this->prepend([$str, $link]);
	}

	/**
	 * adds a link to the end of the breadcrumb
	 * 
	 * @param string $str
	 * @param string $link
	 */
	public function push($str, $link = null) {
		$this->append([$str, $link]);
	}
	
   /**
    * Alias of push()
    * @deprecated 
    * @see push
    * @param type $str
    * @param type $link
    */
	public function add($str, $link)
	{
		$this->append([$str, $link]);
	}
	
   /**
    * returns the current (last) link's title
	 * 
    * @return string
    */
   public function title()
   {
      $count = count($this) - 1;
      if($count < 0) return '';
      
      $last = $this[$count];
      return $last[0];
   }


   /**
    * renders
    * 
    * @param Element $el
    * @return string
    */
//	public function onRenderInnerTag(Element $el, \oxide\util\ArrayString $buffer)
//	{
//		$count = count($this-stack);
//      \oxide\util\Debug::dump($this);
//		if($count > 0) {
//			for($i=0;$i < ($count - 1); $i++) {
//				$item = $this->_stack[$i];
//
//				if($item[1] == null) {
//					$buffer->append("<a>{$item[0]}</a> {$this->symbol} ");
//				} else {
//					$buffer->append("<a href=\"{$item[1]}\">{$item[0]}</a> {$this->symbol} ");
//				}
//			}
//
//			// add the last one as title
//			$item = $this->_stack[$count -1];
//			if($item[1] == null) {
//				$buffer->append("<b>{$item[0]}</b>");
//			} else {
//				$buffer->append("<a href=\"{$item[1]}\"><strong>{$item[0]}</strong></a>");
//			}
//		}
//	}
//
   public function getIterator() 
   {
      return new \ArrayIterator($this->_t_array_storage);
   }
   
}