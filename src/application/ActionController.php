<?php
namespace oxide\application;
use oxide\util\EventNotifier;
use oxide\http\CommandController;
use oxide\http\Route;
use oxide\http\Context;
use oxide\application\View;
use oxide\application\ViewController;
use oxide\helper\App;
use oxide\helper\template;
use oxide\helper\Auth;
use oxide\data\model\Cingle;
use oxide\helper\Util;
use Exception;
use oxide\util\ConfigFile;
use oxide\helper\Notifier;

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
	protected final function  __construct(Route $route) {
		parent::__construct($route);
      
      // check if has access
	   Auth::access($route);
      $db = App::database();
      Cingle::connection($db);
      
 		// setup view directory
		$this->_viewDir			= dirname($this->_controllerDir) . '/view';
		$this->_viewDirSuffix	= $this->_controllerName . '/';
		$this->_actionName		= (empty($route->action)) ? $this->_defaultActionName : $route->action;
	}
   
   /**
    * Initializes the database
    * 
    * @param \oxide\http\Context $context
    */
   protected function onInit(Context $context) {
      parent::onInit($context);
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
		// get the action name
		$notifier = EventNotifier::defaultInstance();
		$action_name = $this->getActionName();
		
      if($this->_catchAll) {
         array_unshift($this->_route->params, $this->getActionName());
         $this->_route->params = array_filter($this->_route->params);
         $this->forward($this->_defaultActionName);
      } else {
         // for to appropriate action method
         $this->forward($action_name);
      }
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
      $view->setIdentifier(template::CONTENT_VIEW);
		template::content($view);
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
      if($index === null) return $this->_route->params;
      if(is_int($index)) {
         $params = $this->_route->params;
         return Util::value($params, $index, $default);
      } else {
         $params = $this->getParamsPairedBySlash();
         if(isset($params[$index])) return $params[$index];
         else return $default;
      }
   }
   
   /**
    * 
    * @return string
    */
   public function getParamPath() {
      $params = $this->getParams();
      if($params) {
         return implode('/', $params);
      } else {
         return '';
      }
   }
   /**
    *
    * @access public
    * @return array 
    */
   public function getParamsPairedBySlash() {
      static $pairs = null;
      if($pairs == null) {
         $params = $this->_route->params;
         
         for ($i = 0; $i < count($params);$i+=2) {
            $key = $this->sanitize($params[$i]);
            if(empty($key)) continue;
            
            // check if value exists
            if(isset($params[$i+1])) { 
               $value = $params[$i+1];
            } else {
               $value = null;
            }

            $pairs[$key] = $value;
         }
      }
      
      return $pairs;
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
         $viewController = new ViewController($config->get('template'), $this->getModuleName());
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
    * redirect to different page
    * @param type $url
    */
   public function redirect($url) {
      $context = $this->getContext();
      $response = $context->getResponse();
      $response->setRedirect($url);
      $response->send(true);
   }
   
   /**
    * Redirect to a module
    * 
    * Current controller's rendering will be disabled
    * Current controller's context will be used
    * @param Route $route
    */
   public function dispatch(Route $route) {
      // first we disable rendering on this action controller
      $this->_autoRender = false;
      
      // get the default default dispatcher
      $context = $this->getContext();
      $dispatcher = $context->dispatcher;
      if(!$dispatcher) {
         throw new Exception('Dispatcher not found in the context.');
      }
      
      $dispatcher->dispatch($route, $context);
   }
   
	/**
	 * forward to given $action immediately
	 * 
    * @access public
	 * @param string $action
    * @throws Exception
	 */
	public function forward($action) {
      $context = $this->getContext();
      if(empty($action)) {
			throw new Exception('Action name can not be empty.', 500);
		}
      
      $args = func_get_args();
      $this->setActionName($action);
      
      // generate method name
		$action = $this->sanitize($action);
      $method = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
		$method = $this->_actionMethodPrefix . $method . $this->_actionMethodSuffix;
      
      // we need to update the view script if view is already loaded
      if($this->_view) {
         $this->_view->setScript($this->generateFullViewScriptPath($this->getActionName()));
      }
      
      $this->onActionStart($context, $action);
		// attempt to call action method
		if(method_exists($this, $method)) {
         
			$args[0] = $context;    // replaces the first param (which is $action) with context
                                 // rest params will be sent to the method
         call_user_func_array(array($this, $method), $args);
		} else {
         // action doesn't exists
         $this->onUndefinedAction($context, $action);
		}
      $this->onActionEnd($context, $action);
	}

   
   protected function _generateFullRouteClassName($prefix = '', $suffix = '') {
      $module = ucfirst($this->getModuleName());
		$controller = ucfirst($this->getControllerName());
		$action = ucfirst($this->getActionName());
      return "{$prefix}{$module}{$controller}{$action}{$suffix}";
   }

   protected function onActionStart(Context $context, $action) {}
   protected function onActionEnd(Context $context, $action) {}

   /**
    * Calls when action provided is not defined in the class
    * @param Context $context
    * @param type $action
    * @throws Exception
    */
   protected function onUndefinedAction(Context $context, $action) {
      throw new Exception("Action: [$action] is not found defined in: [" . $this->getControllerName() . "] controller.");
   }  
   
   protected function onExit(Context $context) {
      parent::onExit($context);
   }
}