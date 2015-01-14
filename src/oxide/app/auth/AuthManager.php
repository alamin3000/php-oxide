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
use oxide\base\Dictionary;
use oxide\util\EventNotifier;
use oxide\http\Route;

class AuthManager  {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   const
      EVENT_ACCESS_DENIED = 'AuthAccessDenied',
      EVENT_ACCESS_GRANTED = 'AuthAcessGranted';
   
   protected
      $_authenticator = null,
      $_config = null,
      $_storage = null;
   
   public function __construct(Dictionary $config, Authenticator $auth) {
      $this->_config = $config;
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
      return $this->_config->get('roles', NULL, TRUE);
   }
   
   /**
    * Get the authentication rules
    * @return array
    */
   public function getRules() {
      return $this->_config->get('rules', NULL, TRUE);
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
      $identity = $this->getAuth()->getIdentity();
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
      
      return $validator;
   }
   
}