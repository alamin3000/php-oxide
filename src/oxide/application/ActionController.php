<?php
namespace oxide\application;
use oxide\util\EventNotifier;
use oxide\http\CommandController;
use oxide\http\Route;
use oxide\http\Context;
use oxide\application\View;
use oxide\application\ViewController;
use oxide\helper\App;
use oxide\helper\Template;
use oxide\helper\Auth;
use oxide\data\model\Cingle;
use oxide\helper\Util;
use Exception;
use oxide\util\ConfigFile;

/**
 * Action Controller
 *
 * Action controller is the primary controller for MVC design pattern
 * Module controller will usually extends this controller
 *
 * Privides full set of functionalities for implementing MVC pattern
 * Supports view and view controller
 * 
 * @package oxide
 * @subpackage application
 */
abstract class ActionController extends CommandController {
	protected            
      /**
       * @var View holds the view for the action current action
       */
		$_view = null,
      $_viewDir = null,
		$_viewScript = null,
		$_viewScriptExt = 'phtml',
		$_viewDirSuffix = '',
      $_actionName,
      $_actionMethodPrefix = 'execute',
      $_actionMethodSuffix = '',
           
      /**
       * @var string Default action.  If no action provided, this action name will be used.
       */
      $_defaultActionName = 'index',
           
      /**
       * @var bool Indicates if all actions should be forwarded to $_defaultActionName
       */
      $_catchAll = false,
           
      /**
       * @var boolean Indicates if auto action view rendering should be performed by the controller
       */
		$_autoRender = true,
           
      /**
       * @var boolean Indicates if auto layout view rendering should be performed
       */
      $_autoRenderLayout = true,
      $_config = null,
      $_configFile = 'config.ini',
           
      /**
       * @var array Holds variables for the view
       */
		$_vars = [];
      
	/**
	 * initialize the controller
	 *
    * finalizes the construction.  can NOT be overriden.
    * @access protected
    * @final
	 * @param Route $route
	 */
	public final function  __construct(Route $route) {
		parent::__construct($route);
      
      // check if has access
	   Auth::access($route);
      $db = App::database();
      Cingle::connection($db);
      
 		// setup view directory
		$this->_viewDir			= dirname($this->_controllerDir) . '/view';
		$this->_viewDirSuffix	= $this->_controllerName . '/';
		$this->_actionName		= $route->action;
	}
   
   /**
	 * this method determine which action method to call and the attempts to call
	 *
	 * override this to provide one action per controller design pattern
    * @access protected
	 * @param Context $context
    * @notifies {Module}{Controller}{Action}Start
    * @notifies {Module}{Controller}{Action}End
	 */
	protected function onExecute(Context $context) {
		$action_name = $this->getActionName();		
      if($this->_catchAll) {
         array_unshift($this->_route->params, $action_name);
         $this->_route->params = array_filter($this->_route->params);
         $action_name = $this->_defaultActionName;
      } else {
         // for to appropriate action method
         if(empty($action_name)) {
            $action_name = $this->_defaultActionName;
         }
      }
      
      $this->forward($action_name);
	}
	
	/**
	 * renders the action controller
	 *
	 * if auto render is set to false, then this method won't do anything
	 *
	 * first method obtains the view and assigns all variables to it
	 * then it objtains view controller and renders the view
	 * finally it adds completely rendered view into the response object
    * @access protected
	 * @param Context $context
	 */
	protected function onRender(Context $context) {
		// check to see if auto rendering is set
      // if not, rendering will not be done.
		if(!$this->_autoRender) return;
      
		// get the action view
		$view = $this->getView();
      $view->setPreRender(true);	// we want to pre render the content view
      
		// assign all action variables to the view
      foreach($this->_vars as $key => $val) { $view->$key = $val; }
      
      $notifier = EventNotifier::defaultInstance();
      $view->setRenderCallback(function($view) use ($notifier) {
         $notifier->notify('ActionControllerViewRenderStart', $this, ['view' => $view]);
      }, function($view) use ($notifier) {
         $notifier->notify('ActionControllerViewRenderEnd', $this, ['view' => $view]);
      });
      
      // assing the view to the templet's content view
		Template::content($view);
      if($this->_autoRenderLayout) {
         // render and add to the respnose object
         $viewController = $this->getViewController();
         $context->response->addBody($viewController->render($view));
      } else {
         $context->response->addBody($view->render());
      }
	}
   
   /**
	 * returns current action name
	 * @return string
	 */
	public function getActionName() {
		return $this->_actionName;
	}
	
	/**
	 * sets the action to execute
	 * 
	 * this method must be used before action execution start.
	 * @param string $name
	 */
	public function setActionName($name) {
		$this->_actionName = $name;
      // also need to update context
      if($this->_context) $this->_context->route->action = $name;
	}

   /**
    * returns all url params
    * 
    * @return array
    */
   public function getParams($index = null, $default = null) {
      $params = $this->_route->params;
      if($index === null) return $params;
      if(is_int($index)) {
         return Util::value($params, $index, $default);
      } else {
         $keypos = array_search($index, $params);
         if($keypos !== FALSE) {
            if(isset($params[$keypos+1])) {
               return $params[$keypos+1];
            }
         }
      }
      
      return $default;
   }
	
	/**
	 * get current view script
	 * 
	 * if view script is not set at the time of calling,
	 * it will generate and set view script based on view dir, view dir suffix, action name and view scirpt extension
    * @access public
    * @return string
	 */
	public function getViewScript() {
		if($this->_viewScript == null) {
			$this->_viewScript = $this->generateFullViewScriptPath($this->getActionName());
		}
		
		return $this->_viewScript;
	}

	/**
	 * generate full script name based on given script name
	 *
	 * DO NOT provide file extension.  extension will be added.
    * @access public
	 * @param string $scriptname script name without the extension
	 * @return string
	 */
	public function generateFullViewScriptPath($scriptname) {
		return 	$this->_viewDir . '/' .
               $this->_viewDirSuffix .
               $scriptname .
               '.' . $this->_viewScriptExt;
	}
	
	/**
	 * set view controller for the action
	 * 
	 * if you simply need to change view script, use setViewScript() instead
    * NOTE: this will override all variables assigned to the view.
    * @access public
	 * @return 
	 * @param object $view
	 */
	public function setView(View $view) {
		$this->_view = $view;
		$this->_viewScript = $view->getScript();
	}
	
	/**
	 * get the view object for the action
    * 
    * NOTE: this will create view if not already created
    * DO NOT use this to check if view is loaded or not.  Use the protected variable $_view instead
    * @access public
	 * @return View
	 */
	public function getView() {
		if($this->_view === null) {
	      $this->_view = new View($this->getViewScript());
      }
      
      return $this->_view;
	}
	
	/**
	 * get the view controller for the action controller
	 *
	 * If view controller does not exists for the controller, then it will first check
	 * for it in the context, if not then create a new one.
    * @access public
	 * @return ViewController
	 */
	public function getViewController() {
      if(!ViewController::hasDefaultInstance()) {
         $config = App::config();
         $viewController = new ViewController($config->get('templates'), $this->_route->module);
         ViewController::setDefaultInstance($viewController);
      }
      
      return ViewController::defaultInstance();
	}

   /**
    * assign variable to the view script
    *
    * these variables will be available to the action's view script. 
	 * To assign to ALL view scripts, and template, use share() instead
    * @access public
    * @param string $key
    * @param mixed $value
	 * @see share()
    */
	public function assign($key, $value = '') {
      $this->_vars[$key] = $value;
      if(is_null($value) && isset($this->_vars[$key]))  {
         unset($this->_vars[$key]);
      }
	}
	
	/**
	 * get the assigned value to the view script
	 * 
	 * @param string $key
	 * @return mixed 
	 */
	public function assigned($key, $default = null) {
		if(isset($this->_vars[$key])) return $this->_vars[$key];
      else return $default;
	}
   
   /**
	 * read module config + merge with user pref
    * @access public
    * @throws Exception
	 */
	public function getConfig() {
		if($this->_config === null) {
			$module = $this->getModuleName();
			$dir = $this->getModuleDir();
			$config_file = $dir . '/config/' . $this->_configFile;
			if(file_exists($config_file)) {
				$objConfig = new ConfigFile($config_file);            
			} else {
				throw new Exception('No Configuration is defined for: ' . $module);
			}
			$pref = App::pref($module);
         if($pref) {
            $objConfig->merge($pref);
         }
         
			$this->_config = $objConfig;
		}
		
		return $this->_config;			
	}
   
	/**
	 * forward to given $action immediately
	 * 
    * @access public
	 * @param string $action
    * @throws Exception
	 */
	public function forward($action) {
      if(empty($action)) {
			throw new Exception('Action name can not be empty.', 500);
		}
      $handled = false;
      $context = $this->getContext();
      $args = func_get_args();
      $args[0] = $context;    // replaces the first param (which is $action) with context
                              // rest params will be sent to the method
      $request = $context->getRequest();
      $httpmethod = strtoupper($request->getMethod());

      $this->setActionName($action);
      
      // generate method name
		$action = $this->sanitize($action);
      $action_filtered = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
		$method = $this->_actionMethodPrefix . $action_filtered . $this->_actionMethodSuffix;
      
      // we need to update the view script if view is already loaded
      if($this->_view) {
         $this->_view->setScript($this->generateFullViewScriptPath($this->getActionName()));
      }
      
      $caller = function($method) use (&$args, &$handled) {
         if(method_exists($this, $method)) {
            call_user_func_array(array($this, $method), $args);
            $handled = true;
         }
      };
      
      $caller("{$method}_start");
      $caller("{$method}_{$httpmethod}");
      $caller($method);
      $caller( "{$method}_end");
      if(!$handled) {
         // action doesn't exists
         $this->onUndefinedAction($context, $action);
		}
	}

   /**
    * Calls when action provided is not defined in the class
    * @param Context $context
    * @param type $action
    * @throws Exception
    */
   protected function onUndefinedAction(Context $context, $action) {
      throw new Exception("Action: [$action] is not found defined in: [" . $this->getControllerName() . "] controller.");
   }  
}