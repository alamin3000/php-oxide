<?php
namespace oxide\mvc\auth;
use Zend\Authentication\AuthenticationService;

class Authentication extends AuthenticationService {
   use \oxide\base\pattern\DefaultInstanceTrait;
   
   /**
    * Check if authentication storage has been set or not
    * 
    * @return boolean
    */
   public function hasStorage() {
      return ($this->storage == null);
   }
   
   /**
    * 
    * @return Storage\StorageInterface
    */
   public function getStorage() {
       if (null === $this->storage) {
           $this->setStorage(new SessionStorage());
       }

       return $this->storage;
   }
}