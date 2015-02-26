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
      $this->_viewData = new ViewData(); // create data dictionary for the view
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
      return $this->_viewData;
   }
   
   /**
    * Assign a data to the data dictionary
    * 
    * @param type $key
    * @param type $value
    */
   public function assign($key, $value) {
      $this->_viewData[$key] = $value;
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
         $keypos = array_search($index, $params);
         if($keypos !== FALSE) {
            if(isset($params[$keypos+1])) {
               return $params[$keypos+1];
            } 
         }
      }
      
      return $default;
   }
   
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
         $config     = $cmanager->openConfigByDirectory($namespace);
         $this->_config = $config;
      }
      
      return $this->_config;
   }
   
   /**
    * Initializes the controllers
    * - set the controller route to the context
    * - performs controller access check
    * - setup the hepers
    * @param Context $context
    */
   private function init(Context $context) {
      $config = $context->getConfig();
      $route = $this->getRoute();
      $context->setRoute($route);
      
      // perform access validation
      $auth = $context->getAuth();
      $authManager = new auth\AuthManager($config, $auth);
      $authManager->validateAccess($route, EventNotifier::sharedInstance(), true);
      
      $conn = $context->getConnection();
      \oxide\data\Connection::setSharedInstance($conn);
      \oxide\data\model\ActiveRecord::sharedConnection($conn);
      
      // setup helpers
      if(!helper\HelperContainer::hasSharedInstance()) {
         $helpers = new helper\HelperContainer($context);
         helper\HelperContainer::setSharedInstance($helpers);
      } else {
         $helpers = helper\HelperContainer::sharedInstance();
      }
      $helpers->set('auth', $auth);
      
      $viewData = $this->_viewData;
      $viewData->setHelperContainer($helpers);
      
      // update context
      $context->setHelperContainer($helpers);
   }
   
   /**
	 * forward to given $action immediately
	 * 
    * @access public
	 * @param string $action
    * @throws Exception
	 */
	public function forward($action) {
      if(empty($action)) throw new Exception('Action name can not be empty.', 500);
      $data = $this->getViewData();
      $this->_route->action = $action;
      $context = $this->getContext();
      $request = $context->getRequest();
      $httpmethod = strtoupper($request->getMethod());
      $handled = false;

      // generate and validate method name
      $action_filtered = CommandFactory::stringToName($action);
      if(!is_callable($action_filtered, true)) {
         throw new \BadMethodCallException("Invalid method: ".htmlentities($action_filtered));
      }
      
      // various methods that will be called
		$method = $this->generateActionMethod($action_filtered);
      $method_init = "{$method}__init";
      $method_view = "{$method}__view";
      $method_http = "{$method}__{$httpmethod}";

      // method executer
      $executer = function($method, $context, $data) use (&$handled) {
         if(method_exists($this, $method)) {
            $handled = true;
            return $this->{$method}($context, $data);
         }
      };
      
      // calling methods in particular order
      $executer($method_init, $context, $data); // initialize method
      $executer($method_http, $context, $data); // http version method
      $view = $executer($method, $context, $data); // standard method
      $view1 = $executer($method_view, $context, $data); // view method
      
      if($view1) {
	      $view = $view1;
      }
      
      if(!$handled) {
         return $this->onUndefinedAction($context, $action);
      }
      
      return $view;
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
         $this->init($context);
         $this->onInit($context); // call initializer
         $route = $this->_route;
         $view = $this->onExecute($context, $route);
         $this->onRender($context, $view);
         $this->onExit($context);
      } catch(\Exception $e) {
         $this->onException($context, $e);
      }
	}
   
   final public function render() {
      $context = $this->getContext();
      $this->onRender($context);
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
    * @param Route $route
    * @return View
    */
   protected function onExecute(Context $context, Route $route) {
      $action_name = $route->action;
      if($this->_catchAll) {
         if(!empty($action_name)) {
            array_unshift($route->params, $action_name);
            $route->params = array_filter($route->params);
            $context->getRequest()->setParams($route->params); // update the request params
         }
         
         $action_name = $this->_defaultActionName;
      } else {
         // for to appropriate action method
         if(empty($action_name)) {
            $action_name = $this->_defaultActionName;
         }
      }
      
      // forward to the action
      return $this->forward($action_name);
   }
   
   /**
    * Perform rendering to the response object
    * 
    * @param Context $context
    * @param \oxide\app\View $view
    * @throws Exception
    */
   protected function onRender(Context $context, View $view = null) {
      if($this->_autoRender) {
         $viewManager = $this->getViewManager();
         $response = $context->getResponse();
         $data = $this->getViewData();

         $prepview = $viewManager->prepareViewWithData($view, $data);
         $response->setContentType($prepview->getContentType(), $prepview->getEncoding());
         $response->addBody($prepview->render());
      }
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