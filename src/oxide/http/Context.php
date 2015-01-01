<?php
namespace oxide\http;
use oxide\base\Container;

/**
 * Context class
 * 
 * Context a container of storing data and services for the current http session.
 * Provides lazy loading functionality
 * @package oxide
 * @subpackage http
 */
class Context extends Container {
   use \oxide\base\pattern\SharedInstanceTrait;
   protected
      $_request = null,
      $_response = null,
      $_session = null;


   
	public function __construct(Request $request) {
		parent::__construct();
      $this->_request = $request;
	}
  
   /**
    * Current application request object
    * @return Request
    */
	public function getRequest() {
		return $this->_request;
	}

   /**
    * Current application response object
    * @return Response
    */
	public function getResponse() {
      if($this->_response == null) {
         $this->_response = new Response();
      }
      
		return $this->_response;
	}
   
   /**
    * @return Session
    */
   public function getSession() {
      if($this->_session === null) {
         $this->_session = Session::getInstance();
      }
      
      return $this->_session;
   }
}