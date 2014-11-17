<?php
namespace oxide\ui\view;
use oxide\ui\view\ViewAbstract;
/**
 * View Object
 * 
 * represent a physical html page.
 * holds variables
 * renders the represented html page
 *
 * @package oxide
 * @subpackage application
 */
class ScriptView extends ViewAbstract {
	private
      $_cached = null,
      $_isRendering = false;
	
	protected
      $_prerender_callback = null,
      $_postrender_callback = null,
      $_title = null,
      $_script = null,
      $_subViews = array(),
      $_containerView = null,
      $_identifier = null,
      $_preRender = false;
   
   /**
    * @param string $script
    * @param string $title
    * @param string $identifier
    */
   public function __construct($script = null, $title = null, $identifier = null) {
      parent::__construct();
   	$this->_script	 	= $script;
      $this->_title = $title;
      $this->_identifier = $identifier;
      $this->contentType = 'text/html';
   }
   		
	/**
	 * sets view identifier
	 * 
	 * this string is used to retrive view from the container script
	 * you would pass $identifier in $this->getView($identifier) method3
    * @access public
	 * @return 
	 * @param object $str
	 */
	public function setIdentifier($str) {
		$this->_identifier = $str;
	}
	
	/**
    * 
	 * get the view indentifier
    * 
    * @access public
	 * @return string
	 */
	public function getIdentifier() {
		return $this->_identifier;
	}

	/**
	 * checks to see if the view has been rendered before
    * 
    * @access public
	 * @return bool
	 */
	public function isRendered() {
		return ($this->_cached !== null);
	}

	/**
	 * add a subview to the current view
	 * 
    * @access public
	 * @return 
	 * @param object $view
	 */
	public function addSubView(ScriptView $view) {
		$view->setContainerView($this);	// sets the parent view
		$this->_subViews[] = $view;
	}

	/**
	 * returns a sub view by the given identifier
	 * 
	 * identifier can be unique string
	 * @return View|array|null
	 * @param string|int $identifier
	 */
	public function getSubView($identifier) {
		foreach($this->_subViews as $view) {
			if($view->getIdentifier() == $identifier) return $view;
		}
		
		return null;
	}
	
	/**
	 * sets parent view
	 * 
	 * DO NOT call this function manullay.  
    * This will be automatically set when adding as subview
    * @access public
	 * @return void
	 * @param \oxide\application\View $view
	 */
	public function setContainerView(ScriptView $view) {
		$this->_containerView = $view;
	}

	/**
	 * returns parent view if any, else returns null
    * 
    * @access public
	 * @return \oxide\application\View | null
	 */
	public function getContainerView() {
		return $this->_containerView;
	}

	/**
	 * sets the view script to render
	 *
	 * this function maybe used to change script for rendering
	 * this function must be called prior to outputting the content.
	 * @access public
	 * @param $script string
	 */
	public function setScript($script) {
		$this->_script = $script;
	}
	
	/**
	 * get the current view script
	 * @access public
	 * @return string
	 */
	public function getScript() {
		return $this->_script;
	}
	
	/**
	 * set the pre redering value
	 * 
	 * if view is set to pre render, 
    * then it will rendered first by parent view (if any) before itself is rendered
    * @access public
    * @param object $bool
	 */
	public function setPreRender($bool) {
		$this->_preRender = $bool;
	}
	
	/**
	 * checks to see if the view is set to pre render
    * 
    * @access public
	 * @return bool
	 * @see setPreRender()
	 * @see getPreRender()
	 */
	public function isPreRender() {
		return ($this->_preRender == true);
	}
	
   /**
    * Executes the script in private scope
    * @param type $script
    * @param type $args
    */
   public function executeScript($script, array $args = null) {
      if($args) {
         extract($args);
      }
      
      include $script;
   }
   
   /**
    * @param type $cache
    */
   public function setCacheData($cache) {
      $this->_cached = $cache;
   }
   
   /**
    * Set rendering callbacks
    * @param \Closure $prerenderer
    * @param \Closure $postrenderer
    */
   public function setRenderCallback(\Closure $prerenderer, \Closure $postrenderer) {
      $this->_prerender_callback = $prerenderer;
      $this->_postrender_callback = $postrenderer;
   }

   /**
    * renders and returns the view script contents as string.
 	 * 
 	 * this function will render the view script, buffer it and then return as string.
 	 * all public variables will be available as within local scope of the view script it's rendering.
	 * @access public
	 * @param $args
	 * @return string
    */
	public function render() {
		if($this->_cached !== null) { // return cached if available
			return $this->_cached;
		}
		
		if($this->_isRendering) { // rendering within rendering :/
         trigger_error("View ({$this->_identifier}, {$this->_script}) is already rendering.", E_USER_ERROR);
		}
      
		$this->_isRendering = true;

		$script = $this->_script;
		if(!file_exists($script)) {
         trigger_error("View script '{$script}' not found", E_USER_ERROR);
		}

		/*
		 * load the code file for the view script, if exists
		 * view code file will be executed BEFORE pre render views
		 */
		$base = basename($script, '.phtml');
		$dir = dirname($this->getScript());
		$codefile = $dir . '/' . $base . '.php';
		if(file_exists($codefile)) {
			$this->executeScript($codefile);
		}

		foreach($this->_subViews as $view) { // render all subviews with pre-render
			if($view->isPreRender()) {
				$view->render(); // render the view and cache it
			}
		}
		
		ob_start();
      
      if($this->_prerender_callback) {
         $callback = $this->_prerender_callback;
         $callback($this);
      }
      
      $this->executeScript($script);
      
      if($this->_postrender_callback) {
         $callback = $this->_postrender_callback;
         $callback($this);
      }
      
      $this->_cached = ob_get_clean();
		$this->_isRendering = false;
      
		return $this->_cached;
	}
}