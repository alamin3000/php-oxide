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
   use ArrayAccessTrait, ArrayFunctionsTrait { offsetSet as protected _offsetSet;}   
	protected
      $_parent = null,
      $_cache = null,
      $_renderer = null,
      $_callback_pre_render = null,
      $_callback_post_render = null,
      $_callback_inner_render = null;
         
   /**
	 * construct the element
	 * 
	 * @param string $tag default tag is SPAN
	 * @param string $inner
	 * @param array $attributes
	 */
   public function __construct($tag = 'span', $inner = null, array $attributes = null) {
      parent::__construct($tag, $attributes);
      if($inner !== null) $this->addInner($inner);
   }
   
   /**
    * Set the parent
    * 
    * This will be 
    * @param Element $parent
    */
   public function setParent(Element $parent) {
      $this->_parent = $parent;
   }
   
   /**
    * @return Element 
    */
   public function getParent() {
      return $this->_parent;
   }
   
   /**
    * Add inner content for the element
    * 
    * @param type $content
    * @param type $identifier
    */
   public function addInner($content, $identifier = null) {
      if (is_array($content)) {
         foreach ($content as $acontent) {
            $this[] = $acontent;
         }
      } else {
         if ($identifier)
            $this[$identifier] = $content;
         else
            $this[] = $content;
      }
   }
   
   /**
    * Return all inner elements
    * 
    * @return array
    */
   public function getInners() {
      return $this->toArray();
   }
   
   /**
    * Return inner element/content by $identifier if found
    * 
    * @param string $identifier
    * @param mixed $default
    * @return \oxide\ui\html\Element
    */
   public function getInnerByIdentifier($identifier, $default = null) {
      if(isset($this[$identifier]))
         return $this[$identifier];
      else return $default;
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
      $this->renderInner();
   }
   
   /**
    * Set plain text for the element
    * 
    * Will perform strip_tag
    * @param mixed $text
    */
   public function setText($text) {
      $this->_t_array_storage = [\strip_tags((string)$text)];
   }
   
   /**
    * Get plain text of the inner text
    * @return string
    */
   public function getText() {
      \strip_tags($this->renderInner());
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
   
   public function offsetSet($offset, $value) {
      if($value instanceof Element) {
         $value->setParent($this);
      }
      $this->_offsetSet($offset, $value);
   }
   
   /**
    * Get the current renderer for the element, if any
    * 
    * If no renderer is found, Element will be rendered by itself.
    * @return \oxide\ui\Renderer
    */
   public function getRenderer() {
      return $this->_renderer;
   }
   
   /**
    * Set renderer for the element
    * 
    * @param \oxide\ui\Renderer $renderer
    */
   public function setRenderer(Renderer $renderer) {
      $this->_renderer = $renderer;
   }
   
   /**
    * Register callbacks for different part of the rendering process
    * All callbacks will have following signature callback($this, ArrayString $buffer, $item = null)
    * $item argument is only available in the inner callback
    * @param \Closure $prerender
    * @param \Closure $innerrender
    * @param \Closure $postrender
    */
   public function registerRenderCallbacks(\Closure $prerender = null, \Closure $innerrender = null, \Closure $postrender = null ) {
      if($prerender) 
         if($this->_callback_pre_render) $this->_callback_pre_render[] = $prerender;
         else $this->_callback_pre_render = [$prerender];
         
      if($postrender) 
         if($this->_callback_post_render) $this->_callback_post_render[] = $prerender;
         else $this->_callback_post_render = [$prerender];
         
      if($innerrender) 
         if($this->_callback_inner_render) $this->_callback_inner_render[] = $prerender;
         else $this->_callback_inner_render = [$prerender];
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
         $renderer = $this->getRenderer();
         $buffer = new ArrayString();
         
         $this->onPreRender($buffer);
         if($this->_callback_pre_render) { // notify pre render callbacks
            foreach($this->_callback_pre_render as $callback) { $callback($this, $buffer); }
         }
         
         if($renderer) {  $buffer[] =  $renderer->render(); } 
         else {
            $buffer[] = $this->renderOpen();
            $this->onInnerRender($buffer);
            $buffer[] = $this->renderInner();
            $buffer[] = $this->renderClose();         
         }

         $this->onPostRender($buffer); // notifying internal post render event
         if($this->_callback_post_render) {
            foreach($this->_callback_post_render as $callback) {$callback($this, $buffer); }
         }
         
         if($this->wrapperTag) {
            $buffer->prepend($this->wrapperTag->renderOpen());
            $buffer->append($this->wrapperTag->renderClose());
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
      foreach($this->getInners() as $inner) {
         if($this->_callback_inner_render) {
            foreach($this->_callback_inner_render as $callback) {
               $callback($this, $inner, $buffer);
            }
         }
         
         if($inner instanceof Renderer) {
            $buffer .= $inner->render();
         } else {
            $buffer .= $inner;
         }
      }
      
      return $buffer;
   }
   
   protected function onInnerRender(ArrayString $buffer) {}
   protected function onPreRender(ArrayString $buffer) { }
   protected function onPostRender(ArrayString $buffer) {}
}