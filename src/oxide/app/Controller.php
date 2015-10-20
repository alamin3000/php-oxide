<?php

namespace oxide\app;
use oxide\base\ReflectingClass;
use oxide\http\Command;
use oxide\http\CommandFactory;
use oxide\http\Route;
use oxide\http\Context;
use oxide\base\Dictionary;
use oxide\util\ConfigManager;

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
abstract class Controller 
   extends ReflectingClass 
   implements Command {
   
   protected
      /**
       * If set to true, the Controller will forward all actions to the default action (index)
       * 
       * Action name will be treated as param and  added to the params array
       * Must be set during initialization (onInit method)
       * @var bool
       */
      $forwardAllToIndex = false,
           
      /**
       * @var bool
       */
      $autoRender = true,
           
      /**
       * @var string
       */
      $configFile = 'config.json',
      
      /**
       * @var ViewData Data container for the view
       */
      $viewData = null,
           
      /**
       * @var HelperContainer Helper container class
       */
      $helperContainer = null;
   
	private  
      /**
       * @var string
       */
      $_defaultActionName = 'index',
           
           
      /**
       * @var Route
       */
      $_route = null,
           
      /**
       * @var Context
       */
      $_context = null,
           
      
      /**
       * _config
       * 
       * @var Dictionary
       * @access private
       */
      $_config = null,
           
      /**
       * View manager responsible for rendering view and layout
       * 
       * @var ViewManager
       */
      $_viewManager = null,
           
      /**
       * 
       */
      $_aclManager = null;
      
	/**
	 * initialize the controller
	 *
    * finalizes the construction.  can NOT be overriden.
    * @access protected
    * @final
	 * @param Route $route
	 */
	public final function  __construct(Route $route) {
      $this->_route = $route;
	}
      
   /**
    * Get the route object for the controller
    * 
    * @return Route
    */
   public function getRoute() {
      return $this->_route;
   }
   
   /**
    * 
    * @return Context
    */
   public function getContext() {
      return $this->_context;
   }
   
   /**
    * 
    * @return AclManager
    */
   public function getAclManager() {
      if($this->_aclManager == null) {
         $config = ConfigManager::sharedInstance()->getConfig();
         $roles = $config->get('roles', null, true);
         $rules = $config->get('rules', null, true);
         $this->_aclManager = new AclManager($this->getRoute(), $roles, $rules);
      }
      
      return $this->_aclManager;
   }
         
	/**
	 * get the view manager for the action controller
	 *
	 * @return ViewManager
	 */
	public function getViewManager() {
      if(!$this->_viewManager) {
         $config = ConfigManager::sharedInstance()->getConfig();
         $viewManager = new ViewManager($config->get('template', NULL, TRUE), $this->_route);
         $this->_viewManager = $viewManager;
      }
      
      return $this->_viewManager;
	}
   
   /**
    * Set the view manager for this controller
    * 
    * @param ViewManager $viewManager
    */
   public function setViewManager(ViewManager $viewManager) {
      $this->_viewManager = $viewManager;
   }
   
   /**
    * Get the shared Helper
    * 
    * @return helper\Helper
    */
   public function getHelper() {
      if(!helper\Helper::hasSharedInstance()) {
         helper\Helper::setSharedInstance(new helper\Helper($this->getContext()));
      }
      
      return helper\Helper::sharedInstance();
   }

   /**
    * Generate action method name
    * 
    * Override this for different name
    * @param string $action
    * @return string
    */
   protected function generateActionMethod($action) {
      return 'execute' . $action;
   }

   /**
    * Get controller configuration
    * 
    * @access public
    * @return void
    */
   public function getConfig() {
      if($this->_config === null) {
         $cmanager = ConfigManager::sharedInstance();
         $namespace = $this->classBaseNamespace();
         $config     = $cmanager->openConfigByDirectory($namespace, $this->configFile);
         $this->_config = $config;
      }
      
      return $this->_config;
   }
   
   /**
	 * forward to given $action immediately
	 * 
    * Route object will be updated accordinly
    * @access public
	 * @param string $action
    * @throws Exception
	 */
	private function forward($action, array $params = null) {
      if(empty($action)) throw new Exception('Action name can not be empty.', 500);
      
      // generate and validate method name
      $action_filtered = CommandFactory::stringToName($action);
      if(!is_callable($action_filtered, true)) {
         throw new \BadMethodCallException("Invalid method: ".htmlentities($action_filtered));
      }
      
      // get some data handy
      $route = $this->getRoute();
      $route->action = $action; // update the action in route
      $route->params = $params;
      
      // we should perform access validation
      $this->onAccessValidation();
      
      // start forwarding
      $httpmethod = strtoupper($route->method);
      
      // various methods that will be called
		$method = $this->generateActionMethod($action_filtered);
      $method_http = "{$method}__{$httpmethod}";
      
      if(method_exists($this, $method_http)) { // calling specialized HTTP method
         $action_method = $method_http;
      } else if(method_exists($this, $method)) { // calling generic method
         $action_method = $method;
      } else { // no method is provided
         $action_method = null;
      }
      
      if($action_method) {
         return $this->invokeMethod($method, $params);
      } else {
         return $this->onUndefinedAction($action, $params);
      }
	}
   
   /**
	 * this method determine which action method to call and the attempts to call
	 *
	 * override this to provide one action per controller design pattern
	 * @param Context $context
	 */
	final public function execute(Context $context) {
      $this->_context = $context;
      $route = $this->getRoute();
      
      try {
         $this->onInit($context); // call initializer
         
         $view = $this->onExecute($context, $route);
         
         // rendering the view, if enabled (default)
         if($this->autoRender) {
            if($view === null || !($view instanceof View)) {
               throw new \Exception("onExecute must return a view.");
            }
            
            $this->onRender($context, $view);
         }
         
         $this->onExit($context);
      } 
      
      catch(\Exception $e) {
         $this->onException($context, $e);
      }
	}
   
   /**
    * Performs access validations
    * 
    * @param Context $context
    */
   protected function onAccessValidation() {
      $this->getAclManager()->performValidation($this->getContext()->getAuth());
   }

   /**
    * Subclassing controller must/should call parent::onInit if overriding
    * 
    * @param Context $context
    */
   protected function onInit(Context $context) {
      $this->viewData = new ViewData(null, $this->getHelper());
   }
   
   /**
    * 
    * @param Context $context
    * @param ViewData $data
    * @return View
    */
   protected function onExecute(Context $context, Route $route) {
      $params = $route->params;
      $action = null;
      if($this->forwardAllToIndex) {
         array_unshift($params, $route->action);
         $action = $this->_defaultActionName;
      } else {
         $params = $route->params;
         if($route->action === null) {
            $action = $this->_defaultActionName;
         } else {
            $action = $route->action;
         }
      }
      
      $view =  $this->forward($action, $params);
      if($view === null) {
         $viewManager = $this->getViewManager();
         $view = $viewManager->createView($route->action, $this->viewData);
      }
      
      return $view;
   }
   
   /**
    * Perform rendering to the response object
    * 
    * @param Context $context
    * @param \oxide\app\View $view
    * @throws Exception
    */
   protected function onRender(Context $context, View $view) {
      $response = $context->getResponse();
      $response->setContentType($view->getContentType(), $view->getEncoding());
      $response->addBody($view->render());
   }
   
   /**
    * 
    * @param Context $context
    */
   protected function onExit(Context $context) { }
   
   /**
    * 
    * @param Context $context
    * @param Exception $e
    * @throws Exception
    */
   protected function onException(Context $context, \Exception $e) {
      throw $e;
   }
   
   /**
    * Calls when action provided is not defined in the class
    * @param Context $context
    * @param type $action
    * @throws Exception
    */
   protected function onUndefinedAction($action, array $params) {
      throw new \Exception("Action: [$action] is not defined in: [" . get_called_class() . "] controller.");
   }
}