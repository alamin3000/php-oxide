<?php

namespace oxide\app;
use oxide\base\AbstractClass;
use oxide\http\Command;
use oxide\http\CommandFactory;
use oxide\http\Route;
use oxide\http\Context;
use oxide\util\EventNotifier;

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
   extends AbstractClass 
   implements Command {
   
	protected  
      /**
       * @var Route
       */
      $_route = null,
      
      /**
       * @var string
       */
      $_configFile = 'config.json',
      
      /**
       * _config
       * 
       * @var Dictionary
       * @access private
       */
      $_config = null,
           
      /**
       * Http Context object
       * @var Context
       */
      $_context = null,
           
      /**
       * Default action.  If no action provided, this action name will be used.
       * @var string 
       */
      $_defaultActionName = 'index',
           
      /**
       * Indicates if all actions should be forwarded to $_defaultActionName
       * @var bool 
       */
      $_catchAll = false,
           
      /**
       * @var View 
       */
      $_view = null,
           
      /**
       * Data container for view.
       * 
       * @var ViewData 
       */
      $_viewData = null,
           
      /**
       * View manager responsible for rendering view and layout
       * 
       * @var ViewManager
       */
      $_viewManager = null,
           
      /**
       * @var bool Indicates if rendering should be performed after action execution
       */     
      $_autoRender = true;
      
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
      $action_name = $route->action;
      if($this->_catchAll) {
         if(!empty($action_name)) {
            array_unshift($route->params, $action_name); // the action name part of the params
            $route->params = array_filter($route->params); // remove any empty
         }
         
         $route->action = $this->_defaultActionName;
      } else {
         // for to appropriate action method
         if(empty($action_name)) {
            $route->action = $this->_defaultActionName;
         }
      }
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
    * Get the data container
    * 
    * All data set to the container will be available to the view
    * @return ArrayContainer
    */
   public function getViewData() {
      if($this->_viewData == null) {
         $this->_viewData = new ViewData(null, $this->getContext());
      }
      return $this->_viewData;
   }
   
   /**
    * 
    * @param \oxide\app\ViewData $data
    */
   public function setViewData(ViewData $data) {
      $this->_viewData = $data;
   }
   
   /**
    * Get Params passed to the route
    * 
    * If $index is int, it will use simple array access
    * If $index is string, it will use paired path to get value
    *    /module/controller/action/key/value
    * @return array
    */
   public function getParams($index = null, $default = null) {
      $params = $this->_route->params;
      if($index === null) return $params;
      if(is_int($index)) {
         return (isset($params[$index])) ? $params[$index] : $default;
      } else {
         if(($keypos = array_search($index, $params)) !== FALSE) {
            if(isset($params[$keypos+1])) {
               return $params[$keypos+1];
            } 
         }
      }
      
      return $default;
   }
   
   /**
    * 
    * @param type $cardinality
    * @param type $names
    */
   public function getAcceptedParams($cardinality, $names = null) {
      
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
         $config     = $cmanager->openConfigByDirectory($namespace, $this->_configFile);
         $this->_config = $config;
      }
      
      return $this->_config;
   }
   
   /**
    * 
    * @param Context $context
    */
   private function validateAccess(Context $context) {
      $config = ConfigManager::sharedInstance()->getConfig();
      $roles = $config->get('roles', null, true);
      $rules = $config->get('rules', null, true);
      $route = $this->getRoute();
      
      // perform access validation
      $auth = $context->get('auth');
      $authManager = new auth\AuthManager($roles, $rules, $auth);
      $authManager->validateAccess($route, EventNotifier::sharedInstance(), true);
   }
   
   /**
	 * forward to given $action immediately
	 * 
    * @access public
	 * @param string $action
    * @throws Exception
	 */
	public function forward($action, ViewData $data) {
      if(empty($action)) throw new Exception('Action name can not be empty.', 500);
      
      $this->_route->action = $action;
      $context = $this->getContext();
      $request = $context->getRequest();
      $httpmethod = strtoupper($request->getMethod());

      // generate and validate method name
      $action_filtered = CommandFactory::stringToName($action);
      if(!is_callable($action_filtered, true)) {
         throw new \BadMethodCallException("Invalid method: ".htmlentities($action_filtered));
      }
      
      // various methods that will be called
		$method = $this->generateActionMethod($action_filtered);
      $method_http = "{$method}__{$httpmethod}";

      if(method_exists($this, $method_http)) { // calling specialized HTTP method
         return $this->{$method_http}($context, $data);
      } else if(method_exists($this, $method)) { // calling generic method
         return $this->{$method}($context, $data);
      } else { // no method is provided
         return $this->onUndefinedAction($context, $action);
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
      
      try {
         $data = $this->getViewData();
         $this->validateAccess($context);
         $this->onInit($context); // call initializer
         $view = $this->onExecute($context, $data);
         
         // rendering the view, if enabled (default)
         if($this->_autoRender) {
            $this->onRender($context, $view);
         }
         
         $this->onExit($context);
      } 
      
      catch(\Exception $e) {
         $this->onException($context, $e);
      }
	}
   
   /**
    * Subclassing controller must/should call parent::onInit if overriding
    * 
    * @param Context $context
    */
   protected function onInit(Context $context) {
      
   }
   
   /**
    * 
    * @param Context $context
    * @param ViewData $data
    * @return View
    */
   protected function onExecute(Context $context, ViewData $data) {
      $view =  $this->forward($this->_route->action, $data);
      if($view === null) {
         $viewManager = $this->getViewManager();
         $view = $viewManager->createView($data);
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
      $viewManager = $this->getViewManager();
      $response = $context->getResponse();

      $prepview = $viewManager->prepareView($view);
      $response->setContentType($prepview->getContentType(), $prepview->getEncoding());
      $response->addBody($prepview->render());
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
   protected function onUndefinedAction(Context $context, $action) {
      throw new \Exception("Action: [$action] is not found defined in: [" . get_called_class() . "] controller.");
   }  
}