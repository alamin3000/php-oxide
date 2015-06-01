<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app\auth;
use oxide\validation\Result;
use oxide\util\EventNotifier;
use oxide\http\Route;

class AuthManager  {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   const
      EVENT_ACCESS_DENIED = 'AuthAccessDenied',
      EVENT_ACCESS_GRANTED = 'AuthAcessGranted';
   
   protected
      $_authenticator = null,
      $_roles = null,
      $_rules = null,
      $_config = null,
      $_storage = null;
   
   /**
    * 
    * @param array $roles
    * @param array $rules
    * @param \oxide\app\auth\Authenticator $auth
    */
   public function __construct(array $roles, array $rules, Authenticator $auth) {
      $this->_roles = $roles;
      $this->_rules = $rules;
      $this->_authenticator = $auth;
      $this->_storage = $auth->getStorage();
   }
   
   /**
    * Get the authenticator for this manager
    * 
    * @return Authenticator
    */
   public function getAuth() {
      return $this->_authenticator;
   }
   
   /**
    * Get the authentication roles
    * @return array
    */
   public function getRoles() {
      return $this->_roles;
   }
   
   /**
    * Get the authentication rules
    * @return array
    */
   public function getRules() {
      return $this->_rules;
   }
   
   /**
    * Validate access for given $route using current $identity
    * 
    * If validation passes, it will return the $identity, else null
    * It will also notify events to default event notifier
    * @param Route $route
    * @throws \Exception
    * @return Result Description
    */
   public function validateAccess(Route $route, EventNotifier $notifier = null, $throwException = false) {
      $result = new Result();
      if($this->getAuth()->hasIdentity()) {
	      $identity = $this->getAuth()->getIdentity();
      } else {
	      $identity = null;
      }
      
      $validator = new AccessValidator($route, $this->getRoles(), $this->getRules());
      $args = ['route' => $route, 'identity' => $identity, 'result' => $result];
      
      $validator->validate($identity, $result);
      if($notifier) {
         if($result->isValid()) {
            $notifier->notify(self::EVENT_ACCESS_GRANTED, $this, $args);
         } else {
            $notifier->notify(self::EVENT_ACCESS_DENIED, $this, $args);
            if($throwException) {
               throw new AccessException(implode('. ', $result->getErrors()));
            }
         }
      }
      
      return $result;
   }
   
}