<?php
namespace oxide\app;
use oxide\validation\Validator;
use oxide\http\Auth;
use oxide\http\Route;
use oxide\http\Router;
use Zend\Permissions\Acl\Acl;
use oxide\validation\Result;
use oxide\util\EventNotifier;


/**
 * access validates
 * 
 * uses Zend framework to handle all authentication and authorization
 * @package oxide
 */
class AclManager implements Validator {
   protected
      $_acl = null,
      $_route = null;

   const
      EVENT_ACCESS_DENIED = 'AuthAccessDenied',
      EVENT_ACCESS_GRANTED = 'AuthAcessGranted',
      ACCESS_ALLOW = 'allow',
      ACCESS_DENY = 'deny',
      ROLE_GUEST = 'guest',
      ROLE_ADMIN = 'admin';
   
   /**
    * constructs the access validator object
    * 
    * @param Route $route
    * @param array $roles
    * @param array $rules
    */
   public function __construct(Route $route, array $roles = null, array $rules = null) {
      $this->_route = $route;
      
      // add the current resource
      $this->addResource($route);
      
      $this->addRoles([
         self::ROLE_GUEST => null,
         self::ROLE_ADMIN => null
      ]);
      
      $this->allow(self::ROLE_GUEST, null); // give guest access to all routes
      
      if($roles) {
         $this->addRoles($roles);
      }
      
      if($rules) {
         $this->addRules($rules);
      }
   }
   
   /**
    * Get the internal Acl instance
    * 
    * @return Acl
    */
   public function getAcl() {
      if($this->_acl === null) {
         $this->_acl = new Acl();
      }
      
      return $this->_acl;
   }
   
   /**
    * Restrict guest access to the given $action on the current route
    * 
    * Current $route will be used for resource
    * @param string $action
    */
   public function restrict($action = null) {
      $this->deny(self::ROLE_GUEST, $this->_route, $action);
   }
   
   /**
    * Add a role
    * 
    * @param string $role
    * @param string $parent
    */
   public function addRole($role, $parent = null) {
      $acl = $this->getAcl();
      
      // recursively add the parent, if doen't exist
      if($parent && !$acl->hasRole($parent)) {
         $this->addRole($parent);
      }
      
      if(!$acl->hasRole($role)) {
         $acl->addRole($role, $parent);
      }
   }

   /**
    * Add multiple roles at onces
    * 
    * @param array $roles
    */
   public function addRoles(array $roles) {
		foreach($roles as $role => $parent) {
         if(!$parent) { $parent = null; }
		   $this->addRole($role, $parent);
		}
   }
   
   /**
    * Add resource
    * @param Route $route
    */
   public function addResource(Route $route) {
      $acl = $this->getAcl();
      
      $resource = $this->getResourceForRoute($route);
      $parent = $route->module;
      
      if($parent !== null) {
         if(!$acl->hasResource($parent)) {
            $acl->addResource($parent);
         }
      }
      
      if(!$acl->hasResource($resource)) {
         $acl->addResource($resource, $parent);
      }
      
//      print "<p>registering resource: {$resource} \ {$parent}</p>";
   }
   
   /**
    * Allows given $role access to the $route with given $action
    * 
    * @param type $role
    * @param Route $route
    * @param type $action
    */
   public function allow($role, Route $route = null) {
      $resource = ($route) ? $this->getResourceForRoute($route) : null;
      $privilege = ($route) ? $this->getPrivilegeForRoute($route) : null;
//      print "<p>(allow) $role = $resource / $privilege</p>";
      $this->getAcl()->allow($role, $resource, $privilege);
   }
   
   /**
    * Deny $role access to the given $route / $action
    * 
    * @param type $role
    * @param Route $route
    * @param type $action
    */
   public function deny($role, Route $route = null) {
      $resource = ($route) ? $this->getResourceForRoute($route) : null;
      $privilege = ($route) ? $this->getPrivilegeForRoute($route) : null;
//      print "<p>(deny) $role = $resource / $privilege</p>";
      $this->getAcl()->deny($role, $resource, $privilege);
   }

   /**
    * 
    * @param type $rules
    * @throws \Exception
    */
   public function addRules(array $rules) {
      $router = new Router();
      $router->defaultAction = null;
      $router->defaultController = null;
      $router->defaultModule = null;
      
      // add rules
		foreach($rules as $rule) {
         $role = isset($rule['role']) ? $rule['role'] : \oxide\exception('Rule must contain role.');
         $access = isset($rule['access']) ? strtolower($rule['access']) : \oxide\exception('Rule must contain access.');
         if(isset($rule['route'])) {
            $route = $router->arrayToRoute($rule['route']);
         } else if(isset($rule['path'])) {
            $route = $router->urlToRoute(trim($rule['path'], '/'));
         } else {
            $route = new Route();
         }
         
         $this->addResource($route);
         
         if($access == self::ACCESS_ALLOW) {
            $this->allow($role, $route);
         } else if($access == self::ACCESS_DENY) {
            $this->deny($role, $route);
         } else {
            throw new \Exception("Access ({$access}) is not recognized.");
         }
		}
   }
   
   /**
    * Get the privilege from the given $route
    * 
    * @param Route $route
    * @return type
    * @throws \Exception
    */
   public function getPrivilegeForRoute(Route $route) {
      return $route->action;
   }
   
   /**
    * Get the rosource for the given $route
    * 
    * Resource is extrated using resource closure.  If closure is not set, 
    * an exception will be thrown.
    * @param Route $route
    * @return type
    * @throws \Exception
    */
   public function getResourceForRoute(Route $route) {
      $resource = '';
      if($route->module) {
         $resource = $route->module;
         if($route->controller) {
            $resource .= ':' . $route->controller;
         }
      }
      
      return $resource;
   }
   
   /**
    * 
    * @param \oxide\app\auth\Authenticator $auth
    */
   public function performValidation(Auth $auth) {
      $route = $this->_route;
      $result = new Result();
      $notifier = EventNotifier::sharedInstance();
      $args = ['route' => $route, 'identity' => $auth->getIdentity(), 'result' => $result];
      
      $this->validate($auth, $result);
      if($result->isValid()) {
         $notifier->notify(self::EVENT_ACCESS_GRANTED, $this, $args);
      } else {
         $notifier->notify(self::EVENT_ACCESS_DENIED, $this, $args);
         throw new AccessException(implode('. ', $result->getErrors()));
      }
   }   

   /**
    * implements Validator interface
    *  
    * @param Autenticator $auth
    * @param ValidatorResult $result
    */
   public function validate($auth, Result &$result = null) {
      if(!$result) {
         $result = new Result();
      }
      
      // create new Zend Access Control
      $acl = $this->getAcl();      
      $route = $this->_route;
      $privilege = $this->getPrivilegeForRoute($route);
      $resource = $this->getResourceForRoute($route);
      
      // check for current identity/member info
      if($auth->hasIdentity()) {
         $identity = $auth->getIdentity();
         $role = $identity->role;
      } else {
      	$identity = null;
         $role = self::ROLE_GUEST;
      }
      
//      print "checking access for role: {$role}, resource: {$resource} with privilege: {$privilege}";
//      print "<p>checking $role = $resource / $privilege</p>";
      
      // give admin access to all route
      // we do here to override any other admin rules.
      // admin must have access to all resources
      $this->allow(self::ROLE_ADMIN, null); 
      
      // perform check
      if(!$acl->isAllowed($role, $resource, $privilege)) {
         $result->addError("$resource/$privilege is not allowed for $role");
      }
   }
}