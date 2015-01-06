<?php

namespace oxide\app;
use oxide\base\AbstractClass;
use oxide\http\Command;
use oxide\http\CommandFactory;
use oxide\http\Route;
use oxide\http\Context;
use oxide\util\EventNotifier;
use oxide\helper\_util;
use oxide\data\Connection;

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
      $_viewManager = null;
      
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
         $viewManager = new ViewManager($config->getRequired('template'));
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

      // generate and validate method name
      $action_filtered = CommandFactory::stringToName($action);
      if(!is_callable($action_filtered, true)) {
         throw new \BadMethodCallException("Invalid method: ".htmlentities($action_filtered));
      }
      
		$method = $this->generateActionMethod($action_filtered);
      
      // attempt to exectue http method action if avalable
      // store view if avalable
      $method_http = "{$method}__{$httpmethod}";
      if(method_exists($this, $method_http)) { // calling specialized HTTP method
         return $this->{$method_http}($context, $data);
      } else if(method_exists($this, $method)) { // calling generic method
         return $this->{$method}($context, $data);
      } else { // no method is provided
         return $this->onUndefinedAction($context, $action);
      }
	}   
   
   private function init(Context $context) {
      $config = $context->getConfig();
      $route = $this->getRoute();
      
      // perform access validation
      $authManager = new auth\AuthManager($config, $context->getAuth());
      $authManager->validateAccess($this->getRoute(), 
              EventNotifier::sharedInstance(), true); // throws exception if denied
      
      $viewData = $this->_viewData;
      
      // setup helpers
      $viewData->setHelper('Flash', function() {
         return new helper\FlashHelper();
      });
      
      $viewData->setHelper('Url', function() use ($context, $route) {
         return new helper\UrlHelper($context->getRequest(), $route);
      });
      
      $viewData->setHelper('Html', function() {
         return new helper\HtmlHelper();
      });
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
    * @return type
    */
   protected function onExecute(Context $context, Route $route) {
      $action_name = $route->action;		
      if($this->_catchAll) {
         array_unshift($route->params, $action_name);
         $route->params = array_filter($route->params);
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
      $viewManager = $this->getViewManager();
      $viewManager->setRoute($this->getRoute());
      if($view === NULL) {
         // no view is provided
         // we will use default view
         $data = $this->getViewData();
         $view = $viewManager->createView($data);
      }
      
      $response = $context->getResponse();
      $viewManager->renderViewToResponse($view, $response);
   }
   
   /**
    * 
    * @param Context $context
    */
   protected function onExit(Context $context) {
      
   }
   
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