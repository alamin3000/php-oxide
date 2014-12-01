<?php
namespace oxide\http;
use oxide\std\Container;

/**
 * Context class
 * 
 * Context a container of storing data and services for the current http session.
 * Provides lazy loading functionality
 * @package oxide
 * @subpackage http
 */
class Context extends Container {
   use \oxide\util\pattern\DefaultInstanceTrait;
   
   /**
    */
	public function __construct() {
		parent::__construct();
		$this->set('request', function() { return Request::currentServerRequest(); });
		$this->set('response', function() { return Response::defaultInstance(); });
      $this->set('session', function() { return Session::getInstance(); });
	}
  
   /**
    * Current application request object
    * @return Request
    */
	public function getRequest() {
		return $this->get('request');
	}

   /**
    * Current application response object
    * @return Response
    */
	public function getResponse() {
		return $this->get('response');
	}
   
   /**
    * @return Session
    */
   public function getSession() {
      return $this->get('session');
   }
}