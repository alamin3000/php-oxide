<?php

namespace oxide\mvc;
use oxide\base\AbstractClass;
use oxide\http\Command;
use oxide\http\CommandFactory;
use oxide\http\Route;
use oxide\http\Context;
use oxide\mvc\auth\Authentication;
use oxide\mvc\auth\AccessValidator;
use oxide\mvc\ViewManager;
use oxide\util\EventNotifier;
use oxide\helper\_util;

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
      $_viewData = null;
      
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
         return _util::value($params, $index, $default);
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
	 * this method determine which action method to call and the attempts to call
	 *
	 * override this to provide one action per controller design pattern
	 * @param Context $context
	 */
	final public function execute(Context $context) {
      $this->_context = $context;
      try {
         $this->onInit($context); // call initializer
         $route = $this->_route;
         $view = $this->onExecute($context, $route);
         $this->onRender($context, $view);
         $this->onExit($context);
      } catch(\Exception $e) {
         $this->onException($context, $e);
      }
	}
   
   public function getAcceptedParams($cardinality, $names = null) {
      
   }
	
	/**
	 * get the view manager for the action controller
	 *
	 * @return ViewManager
	 */
	public function getViewManager() {
      if(!ViewManager::hasDefaultInstance()) {
         $config = $this->_context->get('config');
         $route = $this->_route;
         $templates = _util::value($config, 'templates', null, true);
         $viewController = new ViewManager($route, $templates);
         ViewManager::setDefaultInstance($viewController);
      }
      
      return ViewManager::defaultInstance();
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
   
   /**
    * Subclassing controller must/should call parent::onInit if overriding
    * 
    * @param Context $context
    */
   protected function onInit(Context $context) {
      $config = $context->get('config');
      $context->set('database', function() use ($config) {
         if(!Connection::hasDefaultInstance()) {
            $dbconfig = (array) $config['database'];
            $dboptions = array(
                  \PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION,
                  'FETCH_MODE'			=> \PDO::FETCH_ASSOC
                  );
            $conn = new Connection($dbconfig, $dboptions);
            Connection::setDefaultInstance($conn);
         }

         return Connection::defaultInstance();
      });
      
      
      $auth = Authentication::defaultInstance();
      $route = $this->_route;
      $identity = $auth->getIdentity();
      $roles = _util::value($config, 'roles', null, true);
      $rules = _util::value($config, 'rules', null, true);
      $validator = new AccessValidator($route, $roles, $rules);
      $notifier = EventNotifier::defaultInstance();
      $result = null;
      $validator->validate($identity, $result);      
      if(!$result->isValid()) {
         $notifier->notify('ControllerAccessDenied', $this, ['route' => $route, 'identity' => $identity, 'result' => $result]);
         $error_string = implode('. ', $result->getErrors());
         throw new AuthAccessException($error_string);
      } else {
         $notifier->notify('ControllerAccessGranted', $this, ['route' => $route, 'identity' => $identity, 'result' => $result]);
      }
      
      $context->set('auth', $auth);
   }
   
   /**
    * 
    * @param Context $context
    * @param Route $route
    * @return type
    */
   protected function onExecute(Context $context) {
      $route = $this->getRoute();
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
    * @param \oxide\mvc\View $view
    * @throws Exception
    */
   protected function onRender(Context $context, View $view = null) {
      $viewManager = $this->getViewManager();
      if($view === NULL) {
         // no view is provided
         // we will use default view
         $data = $this->getViewData();
         $data->share('context', $context);
         $view = $viewManager->createView($data);
         $data->setView($view);
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