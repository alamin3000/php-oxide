<?php
namespace oxide\ui\html;
use oxide\ui\Renderer;
use oxide\ui\ArrayString;
use oxide\base\pattern\ArrayFunctionsTrait;
use oxide\base\pattern\ArrayAccessTrait;
/**
 * Html Element
 *
 * Represents a html tag.
 * @package oxide
 * @subpackage ui
 */
class Element 
   extends Tag 
   implements \ArrayAccess, \Countable , \IteratorAggregate {   
   use  ArrayAccessTrait, ArrayFunctionsTrait; 
     
   public 
      /**
       * @var array Tag objects to be wrapped
       */
      $wrappers = [],
      $before = [],
      $after = [];
   
	protected
      $_parent = null,
      $_rendererCallback = null;
         
   /**
	 * construct the element
	 * 
	 * @param string $tag default tag is SPAN
	 * @param string $inner
	 * @param array $attributes
	 */
   public function __construct($tag = null, $inner = null, array $attributes = null) {
      parent::__construct($tag, $attributes);
      if($inner !== null) {
         $this->_t_array_storage[] = $inner;
      }
   }
         
   /**
    * Set the content for the element
    * 
    * Existing content will be removed.
    * @param mixed $html
    */
   public function setHtml($html) {
      $this->_t_array_storage = [$html];
   }
   
   /**
    * Get the html string of the inner tag
    * 
    * @see renderInner()
    * @return string
    */
   public function getHtml() {
      return $this->renderInner();
   }
   
   /**
    * Sets the parent for the element
    * 
    * This method is called when added to any element.
    * @param Element $element
    */
   public function setParent(Element $element = null) {
      $this->_parent = $element;
   }
   
   /**
    * Get the parent element if available
    * 
    * @return Element
    */
   public function getParent() {
      return $this->_parent;
   }
      
   /**
	 * resets the element
	 *
	 * This is useful to reuse the object for different element
	 * All information about current element will be removed (tag name, attributes, renderer..)
	 */
	public function reset() {
      $this->_tag = null;
		$this->_t_array_storage = [];
		$this->_wrappers = [];
		$this->_before = [];
		$this->_after = [];
		$this->_attributes = [];
	}
	
	
	/**
	 * getIterator function.
	 * 
	 * @access public
	 * @return void
	 */
	public function getIterator() {
		return new \ArrayIterator($this->_t_array_storage);
	}

   /**
    * Renderer callable
    * 
    * Callback signature $callable($this)
    * The callback has few options
    *		return void/NULL: rendering will continue to be performed.
    								this is useful for additional customization before rendering
    		return string		further rending will NOT be continued.
    								return will be assumed to be performed in the callback.
    								and return back the returned string from the callback.
    * @param callable $callable
    */
   public function setRendererCallback(callable $callback = null) {
      $this->_rendererCallback = $callback;
   }
   
   
   /**
    * getRendererCallback function.
    * 
    * @access public
    * @return void
    */
   public function getRendererCallback() {
      return $this->_rendererCallback;
   }
   
   /**
    * renders the html tag
    *
    * render is further divided into three parts
    * - renderStartTag, -renderInnerHtml and -renderEndTag
    * subclasses may only need to override the renderInnerHtml.
    * @access public
    * @return string
    */
   public function render() {
      try {
         $this->onRender();
         
         // call renderer callback
         if($this->_rendererCallback) {
            $return = call_user_func($this->_rendererCallback, $this);
            if($return !== null) {
	            return $return;
            }
         }
         
         // start rendering process
         // all rendering partial will be stored in $buffer
         $buffer = new ArrayString();
         // render before 
         if($this->before) {
	         foreach($this->before as $before) {
		         $buffer->append($before);
	         }
         }
         // render element
         $buffer[] = $this->renderOpen();
         $buffer[] = $this->renderInner();
         $buffer[] = $this->renderClose(); 
         // render after
         if($this->after) {
	         foreach($this->after as $after) {
		         $buffer->append($after);
	         }
         }        
         
         // now perform the wrappers
         if(!empty($this->wrappers)) {
            reset($this->wrappers);
            foreach($this->wrappers as $wrapper) {
               $buffer->prepend($wrapper->renderOpen());
               $buffer->append($wrapper->renderClose());
            }
         }
         
         return (string) $buffer;
      }
      catch (\Exception $e) {
         $msg = 'Error in Element rendering: ' . get_called_class().
               '. ('.
               $e->getMessage().
               ') ';
         trigger_error($msg);
      }
   }
   
   /**
    * renders the content of the html element
	 * 
    * @param Element $element
    * @param ArrayString $buffer Current rendering string buffer 
	 * @return void
    */
   public function renderInner() {      
      $buffer = '';
      foreach($this->_t_array_storage as $inner) {
         if($inner instanceof Renderer) {
            $buffer .= $inner->render();
         } else {
            $buffer .= $inner;
         }
      }
      
      return $buffer;
   }
   
   /**
    * Renders a HTML element based on given $tag and $content
    * 
    * @param \oxide\ui\html\Tag $tag
    * @param type $content
    */
   public static function renderTag($tag, $content = null, array $attributes = null, $void = false) {
      if($tag instanceof self) {
         return $tag->renderOpen() .
            $content.
            $tag->renderClose();    
      } else {
         $tag = new Tag($tag, $attributes, $void);
         return $tag->renderContent($content);
      }
   }
   
   protected function _t_array_access_set($key, $value) {
      if($value instanceof Element) {
         $value->setParent($this);
      }
   }
   protected function _t_array_access_unset($key, $value) {
      if($value instanceof Element) {
         $value->setParent(null);
      }
   }

	
   /**
    * Internal rendering event.
    *
    * This method will be called when rendering is requested, before performing
    * any rendering 
    * @return void
    */
   protected function onRender() {}
}