<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app\auth;
use oxide\validation\Validator;
use oxide\validation\ValidationResult;

class AuthManager implements Validator {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   protected
      $_authenticator = null,
      $_roles = null,
      $_rules = null;
   
   public function __construct(array $roles = null, array $rules = null) {
      $this->_roles = $roles;
      $this->_rules = $rules;
   }
   
   
   /**
    * Get the authenticator
    * 
    * @return Authenticator
    */
   public function getAuthenticator() {
      if($this->_authenticator === null) {
         $this->_authenticator = new Authenticator();
      }
      
      return $this->_authenticator;
   }
   
   /**
    * Get the current user identity
    * 
    * @return type
    */
   public function getIdentity() {
      return $this->getAuthenticator()->getIdentity();
   }
   
   /**
    * 
    * @param Route $route
    * @param ValidationResult $result
    * @throws \Exception
    */
   public function validate($route, ValidationResult &$result = null) {
      $auth = $this->getAuthenticator();
      $identity = $auth->getIdentity();
      $roles = $this->_roles;
      $rules = $this->_rules;
      if(!$roles || !$rules) {
         throw new \Exception('Both roles and rules must be set in configuration.');
      }
      $validator = new AccessValidator($route, $roles, $rules);
      if($validator->validate($identity, $result)) {
         return $route;
      } else {
         return null;
      }
   }
   
}