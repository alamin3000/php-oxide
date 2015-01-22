<?php
namespace oxide\ui\html;
use oxide\ui\Renderer;
use oxide\util\ArrayString;
use oxide\base\pattern\ArrayAccessTrait;
use oxide\base\pattern\ArrayFunctionsTrait;

/**
 * Html Element
 *
 * Represents a html tag.
 * @package oxide
 * @subpackage ui
 */
class Element 
   extends Tag 
   implements \ArrayAccess, \Countable {   
   use ArrayAccessTrait, ArrayFunctionsTrait;   
	protected
      $_rendererCallback = null;
         
   /**
	 * construct the element
	 * 
	 * @param string $tag default tag is SPAN
	 * @param string $inner
	 * @param array $attributes
	 */
   public function __construct($tag = 'span', $inner = null, array $attributes = null) {
      parent::__construct($tag, $attributes);
      if($inner !== null) {
         $this->_t_array_storage[] = $inner;
      }
   }
   
   public function &getInners() {
      return $this->_t_array_storage;
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
	 * resets the element
	 *
	 * This is useful to reuse the object for different element
	 * All information about current element will be removed (tag name, attributes, renderer..)
	 */
	public function reset() {
		$this->_t_array_storage = [];
		$this->_t_property_storage = [];
	}

   /**
    * Renderer callable
    * 
    * Callback signature $callable($this, $buffer)
    * If method returns TRUE, then internal rendering will be performed
    * @param callable $callable
    * @return callable
    */
   public function rendererCallback(callable $callable = null) {
      if($callable) $this->_rendererCallback = $callable;
      else return $this->_rendererCallback;
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
         if($this->_rendererCallback) {
            $renderer = $this->_rendererCallback;
            return $renderer($this);
         }
         
         $buffer = new ArrayString();
         $this->onPreRender($buffer);
         $buffer[] = $this->renderOpen();
         $this->onInnerRender($buffer);
         $buffer[] = $this->renderInner();
         $buffer[] = $this->renderClose();         

         $this->onPostRender($buffer); // notifying internal post render event
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
         $tag = new Tag($tag, $attributes);
         return $tag->renderWithContent($content);
      }
   }   
   
   protected function onRender() {}
   protected function onInnerRender(ArrayString $buffer) {}
   protected function onPreRender(ArrayString $buffer) { }
   protected function onPostRender(ArrayString $buffer) {}
}