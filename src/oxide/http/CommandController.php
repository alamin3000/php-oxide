<?php
namespace oxide\http;
use oxide\validation\misc\VariableNameValidator;
use oxide\util\EventNotifier;
use Exception;
use ReflectionClass;


/**
 * Command Controller
 * 
 * implements Command to comply with front controller engines specification
 * provides command execution life cycle events
 * 
 * @package oxide
 * @subpackage http
 */
abstract class CommandController implements Command {
	protected 
      /**
       * @var Route routing information for this controller
       */
      $_route = null,
      
      /**
       * @var Context context for the controller
       */
      $_context = null,
           
      $_controllerDir = null,
           
      $_moduleName,
      $_moduleDir;

   /**
    * initialize the command controller based on given route
    * 
    * direct creation of command is not allowed.  should use create method instead.
    * 
    * all sub classes MUST call this parent method when overriding.
    * @param Context $context
    */
   public function  __construct(Route $route) {
      $this->__command_notification_prefix = 'Command';
      $this->_route = $route;
      
 		// controller class will find out about its own information
		// this important when forwarding occures.
      $reflector = new ReflectionClass(get_called_class());
     	$controllerdir = dirname($reflector->getFileName());
      $moduledir = dirname($controllerdir);
     	
		// setup controller information variables
		$this->_moduleName 		= $route->module;
		$this->_moduleDir 		= $moduledir;
		$this->_controllerName	= $route->controller;
		$this->_controllerDir	= $controllerdir;
   }
   
   /**
    * 
    * @param \oxide\http\Route $route
    * @return null
    */
   public static function generateClassName(Route $route)
   {
      $validator = new VariableNameValidator();
      
      // validate module name
      if(!$validator->validate($route->module)) {
         return null;
      }
      
      // validate controller name if given
      if(!empty($route->controller) && !$validator->validate($route->controller)) {
         return null;
      }
      
      
      
		$module = $route->module;
      $controller = ucwords($route->controller);
      $class = "{$module}\controller\\{$controller}Controller";
      return $class;
   }
   
   /**
    * 
    * @param \oxide\http\Route $route
    * @return null|\oxide\http\class
    * @throws Exception
    */
   public static function createWithRoute(Route $route) {
      $class = self::generateClassName($route);
      if($class) {
         $instance = new $class($route);
      } else {
         $instance = null;
      }

		return $instance;
   }

   /**
    * @access public
    * @notifies EngineInit
    * @param void
    * @return void
    */
	public function execute(Context $context)
	{
      $this->_context = $context; // store the context locally
      
      $notifier = EventNotifier::defaultInstance();
      
      try {
         // controller initialization
         $notifier->notify("CommandControllerInit", $this, ['context' => $context]);
         $this->onInit($context);

         // controller main execution
         $notifier->notify("CommandControllerExecute", $this, ['context' => $context]);         
         $this->onExecute($context); // start processing
         
         // controller rendering
         $notifier->notify("CommandControllerRender", $this, ['context' => $context]);
         $this->onRender($context);

         $notifier->notify("CommandControllerExit", $this, ['context' => $context]);      
         $this->onExit($context);
      } 
      
      catch(Exception $e) {
         $context->exception = $e;
         $notifier->notify("CommandControllerException", $this, ['context' => $context, 'exception' => $e]);         
         $this->onException($context, $e);
      }
	}

   /**
	 * sanitize given $str string
	 *
	 * filters out all characters except: alpha, number, -, _
    * @access public
	 * @param string $str
	 * @return string
	 */
	public function sanitize($str) {
		$regex = '/[^a-z0-9\-_]+/i';
		$replace = '';
		return preg_replace($regex, $replace, $str);
	}   
   
   /**
    * Current controller's context object
    * 
    * @return Context
    */
   public function getContext()
   {
      return $this->_context;
   }
   
   /**
    * 
    * @return Route
    */
   public function getRoute()
   {
      return $this->_route;
   }
   
   /**
	 * current module name
	 * 
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->_moduleName;
	}
	
	/**
	 * directory for the current module
	 * 
	 * @return string
	 */
	public function getModuleDir()
	{
		return $this->_moduleDir;
	}

   /**
    * get controller name
    * 
    * @return string
    */
	public function getControllerName()
	{
		return $this->_controllerName;
	}
   
   /**
    * Returns the current controller's directory 
    * 
    * @return string
    */
   public function getControllerDir() {
      return $this->_controllerDir;
   }
   
   protected function onInit(Context $context) {}
   protected function onExecute(Context $context) {}
   protected function onRender(Context $context) {}
   protected function onExit(Context $context) {}
   protected function onException(Context $context, Exception $exeption) {
      if($exeption) throw $exeption;
      else if($context->exception)
         throw $context->exception;
      else
         throw new Exception('Unknown Error');      
   }   
}