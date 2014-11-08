<?php
namespace oxide\ui\html;
use oxide\ui\Renderer;
use oxide\util\ArrayString;
use oxide\helper\_html;
use oxide\util\pattern\ArrayFunctionsTrait;

/**
 * Html Element
 *
 * Represents a html tag.
 * @package oxide
 * @subpackage ui
 */
class Element extends Tag implements \ArrayAccess, \Countable {   
   use ArrayFunctionsTrait;
   
	protected
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
      if($inner !== null) $this->inner($inner);
   }
      
   /**
    * Access to the inner contents as array.
    * 
    * Element stores all inner contents in as an array
    * 
    * @param mixed $content
    * @return array
    */
   public function inner($content = null, $identifier = null) {
      if ($content) {
         if (is_array($content)) {
            foreach ($content as $acontent) {
               $this->_t_array_storage = $acontent;
            }
         } else {
            if ($identifier)
               $this->_t_array_storage[$identifier] = $content;
            else
               $this->_t_array_storage[] = $content;
         }
      } else {
         if ($identifier !== null)
            return $this->_t_array_storage[$identifier];
         else
            return $this->_t_array_storage;
      }
   }
   
	/**
	 * sets the inner HTML content
	 *
	 * It replaces all content if exits.
	 * @see append()
	 * @see prepend()
	 * @param string $html
	 * @return string
	 */
	public function html($html = null) {
      if($html) { $this->_t_array_storage = [$html]; }
      else {
         return $this->renderInnerTag();
      }
	}

	/**
	 * Sets the innner HTML content with simple text.
	 *
	 * It function will remove all HTML tags and only store texts for bot set/get functionalties
	 *
	 * @param string $text
	 * @return string
	 */
	public function text($text = null) {
      if($text) $this->_t_array_storage = [\strip_tags((string)$text)];
      else {
         return \strip_tags($this->renderInnerTag ());
      }
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
         
         if($renderer) {  $buffer[] =  $renderer->render($this->inner()); } 
         else {
            $buffer[] = $this->renderOpenTag();
            $this->onInnerRender($buffer);
            $buffer[] = $this->renderInnerTag();
            $buffer[] = $this->renderCloseTag();         
         }

         $this->onPostRender($buffer); // notifying internal post render event
         if($this->_callback_post_render) {
            foreach($this->_callback_post_render as $callback) {$callback($this, $buffer); }
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
   public function renderInnerTag() {      
      $buffer = '';
      foreach($this->inner() as $inner) {
         if($this->_callback_inner_render) {
            foreach($this->_callback_inner_render as $callback) {
               $callback($this, $inner, $buffer);
            }
         }
         
         $buffer .= _html::toString($inner);
      }
      
      return $buffer;
   }
   
   /**
    * Allows to render an element based on given $tag and $content
    * 
    * @param \oxide\ui\html\Tag $tag
    * @param type $content
    */
   public static function renderTag(Tag $tag, $content = null) {
      return $tag->renderOpenTag() .
         _html::toString($content).
         $tag->renderCloseTag();     
   }
   
   protected function onInnerRender(ArrayString $buffer) {}
   protected function onPreRender(ArrayString $buffer) { }
   protected function onPostRender(ArrayString $buffer) {}
   public function __toString() {
      return $this->render();
   }
}