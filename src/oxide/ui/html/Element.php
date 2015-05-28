<?php
namespace oxide\ui\html;
use oxide\base\String;
use oxide\ui\Renderer;
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
   use ArrayAccessTrait, ArrayFunctionsTrait; 
   
	protected
      /**
       * @var Element
       */
      $_parent = null,
           
      /**
       * @var Renderer
       */
      $_renderer = null;
         
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
      
      return $this;
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
	 * resets the element
	 *
	 * This is useful to reuse the object for different element
	 * All information about current element will be removed (tag name, attributes, renderer..)
	 */
	public function reset() {
      $this->clearAttributes();
		$this->_t_array_storage = [];
	}
	
   /**
    * Sets the parent for the element
    * 
    * This method is called when added to any element.
    * @param Element $element
    */
   public function setParent(Element $element = null) {
      $this->_parent = $element;
      
      return $this;
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
	 * getIterator function.
	 * 
	 * @access public
	 * @return void
	 */
	public function getIterator() {
		foreach ($this->_t_array_storage as $item) {
         yield $item;
      }
	}
   
   /**
    * Removes the element from the parent tree, 
    * @return self
    */
   public function remove() {
      if(($parent = $this->getParent())) {
         if(($pos = $parent->search($this))) {
            $parent->offsetUnset($pos);
         }
      }
      
      return $this;
   }

   /**
    * Renderer callable
    * 
    * Callback signature $callable(Element $this, ArrayString $buffer)
    * If any of the callback functions returns anything other than null,
    * Futher rendering will be terminated
    * @param Renderer $renderer
    */
   public function setRendererDelegate(Renderer $renderer) {
      $this->_renderer = $renderer;
   }
   
   /**
    * getRendererCallback function.
    * 
    * @access public
    * @return Renderer
    */
   public function getRendererDelegate() {
      return $this->_renderer;
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
   final public function render() {
      try {
         $this->onRender();
         
         // start rendering process
         // all rendering partial will be stored in $buffer
         $buffer = new String();
         
         // if renderer delegate is assigned, it will be pased
         if(($renderer = $this->getRendererDelegate())) {
            return $renderer->render();
         }
         
         $this->onRenderOpen($buffer);
         $this->onRenderInner($buffer);
         $this->onRenderClose($buffer);    
         
         return $buffer->__toString();
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
   
   protected function onRender() {}
   
   /**
    * Internal event to Render the opening tag
    * @param ArrayString $buffer
    */
   protected function onRenderOpen(String $buffer) {
      $buffer->append($this->renderOpen());
   }
   
   /**
    * Internal event to render the inner tag
    * @param ArrayString $buffer
    */
   protected function onRenderInner(String $buffer) {
      $buffer->append($this->renderInner());
   }
   
   /**
    * Internal event to render the close tag
    * @param ArrayString $buffer
    */
   protected function onRenderClose(String $buffer) {
      $buffer->append($this->renderClose());
   }
   
   /**
    * 
    * @param type $key
    * @param \oxide\ui\html\Element $value
    */
   protected function _t_array_access_set($key, $value) {
      if($value instanceof Element) {
         $value->setParent($this);
      }
   }
   
   /**
    * 
    * @param type $key
    * @param \oxide\ui\html\Element $value
    */
   protected function _t_array_access_unset($key, $value) {
      if($value instanceof Element) {
         $value->setParent(null);
      }
   }
}