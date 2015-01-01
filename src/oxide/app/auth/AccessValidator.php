<?php
namespace oxide\app\auth;
use oxide\validation\ValidatorAbstract;
use oxide\http\Route;
use oxide\http\Router;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Role\GenericRole;
use oxide\validation\ValidationResult;

/**
 * access validates
 * 
 * uses Zend framework to handle all authentication and authorization
 * @package oxide
 */
class AccessValidator extends ValidatorAbstract
{
   protected
      $_route = null,
      $_roles = null,
      $_rules = null;

   const
      ROLE_GUEST = 'guest';
   
   /**
    * constructs the access validator object
    * 
    * @param object $identity
    * @param array $roles
    * @param array $rules
    * @throws \Exception
    */
   public function __construct($route, $roles, $rules)
   {
      $this->_route = $route;
      $this->_roles = (array)$roles;
      $this->_rules = (array)$rules;

      if(!$roles) {
         // roles must be exists
			throw new \Exception('No Roles in Admin config file');
		}

      if(!$rules) {
      	throw new \Exception('No Rules defined for Administration');
      }
   }

   public function hasRoleAccessToModule($role, $module)
   {
      
      
   }

   protected function _addRolesToAcl($roles, $acl)
   {
      // add roles
		foreach($roles as $role => $parent) {
			if(!$parent) {
				$parent = null;
			}

		   $acl->addRole(new GenericRole($role), $parent);
		}
   }

   protected function _addRulesToAcl($rules, Acl $acl)
   {
      $router = new Router();
      $router->defaultAction = null;
      $router->defaultController = null;
      $router->defaultModule = null;
      
      // add rules
		foreach($rules as $role => $rule) {
			$rule_parts = explode(',', $rule); // break the rules into array (rules are separated by comman)

			foreach($rule_parts as $rule_part) {	// for each rule for the role
				$role_type = substr(trim($rule_part), 0, 1);	// first char must be access symbol + or -
				if(!in_array($role_type, array('+', '-'))) {
					throw new \Exception('Rules must supply access/deny symbol (+/-)');
				}

				// map access method based on symbol
				if($role_type == '-') {
					$method = 'deny';
				} else {
					$method = 'allow';
				}

            $rule_string = trim(substr(trim($rule_part), 1)); // rest of the string is resource.previlege

            if(empty($rule_string)) {
            	// no resource or previlege specified
					// this role has either access or no access to all everything
               $role_resource = null;
					$role_privilege = null;
            } else {
               $route = $router->urlToRoute($rule_string);
               $role_resource = $this->generateResourceName($route); // first part is resource
              	$role_privilege = $route->action;
            }
            
            $resource = new GenericResource($role_resource);
				if(!$acl->hasResource($resource)) {
					$acl->addResource($resource);
      		}

				// add the access info for the role
//            print "<p>($method) $role = $role_resource / $role_privilege</p>";
            $acl->$method($role, $role_resource, $role_privilege);
			}
		}
   }
   
   public function generateResourceName(Route $route)
   {
      $resource = null;
      if($route->module) {
         $resource = $route->module;
         if($route->controller) {
            $resource .= ':' . $route->controller;
         }
      }
      
      return $resource;
   }
   
   public function registerResource(Acl $acl, $resource = null, $parent = null)
   {
      $resource = new GenericResource($resource);
      $parent = new GenericResource($parent);
      
      if(!$acl->hasResource($parent)) {
         $acl->addResource($parent);
      }
      
      if(!$acl->hasResource($resource)) {
         $acl->addResource($resource, $parent);
      }
   }

   /**
    * implements Validator interface
    *  
    * @param Route $route
    * @param ValidatorResult $result
    * @return ValidatorResult
    */
   public function validate($identity, ValidationResult &$result = null)
   {
      if(!$result) {
         $result = new ValidationResult();
      }

      // create new Zend Access Control
		$acl = new Acl();
		$roles = $this->_roles;
      $rules = $this->_rules;
      
      $router = new Router();
      $router->defaultAction = null;
      $router->defaultController = null;
//      $router->defaultModule = null;
      $route = $router->urlToRoute($this->_route->path);
      
		// get the requested resource and previlige info setup
	
//		if(strtolower($route->action) == 'module') {
//         // if current action is module
//         // the
//         $resource = (isset($route->params[0])) ? $route->params[0] : null;
//         $privilege = (isset($route->params[1])) ? $route->params[1] : null;
//		} else {
      $privilege = $route->action;
//		}
         
      // generate the resource name based on the route
      $resource = $this->generateResourceName($route);
      $this->registerResource($acl, $resource, $route->module);

      // add the roles
      $this->_addRolesToAcl($roles, $acl);

      // add the rules
      $this->_addRulesToAcl($rules, $acl);

      // check for current identity/member info
      if($identity) {
			// make sure identity is not hacked
			// we will load member based on the given identity
			// @todo do it!
         $role = $identity->role;
      } else {
      	$identity = null;
         $role = self::ROLE_GUEST;
      }
      // finally check for the access and return the result
      $allowed = true;
      //print "checking access for role: {$role}, resource: {$resource} with privilege: {$privilege}";

//      print "<p>checking $role = $resource / $privilege</p>";
      if(!$acl->isAllowed($role, $resource, $privilege)) {
         $allowed = false;
			$result->addError("$resource/$privilege is not allowed for $role");
      }

      return $this->_returnResult($allowed, $result, $route);
   }
}