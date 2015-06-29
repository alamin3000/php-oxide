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
   /**
    * Create http context
    * 
    * @param \oxide\http\Request $request
    * @param \oxide\http\Response $response
    * @param \oxide\http\Session $session
    */
	public function __construct(Request $request, Response $response, Session $session) {
		parent::__construct();
      $this->set('request', $request); 
      $this->set('response', $response);
      $this->set('session', $session);
      
      // setup the authentication based on the session
      $this->addResolver('auth', function() {
         return new Auth(new AuthStorage($this->get('session')));
      });
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
    * 
    * @return Session
    */
   public function getSession() {
      return $this->get('session');
   }
   
   /**
    * Get user auth
    * 
    * @return Auth
    */
   public function getAuth() {
      return $this->get('auth');
   }
}