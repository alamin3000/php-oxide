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

   
	public function __construct(Request $request, Response $response) {
		parent::__construct();
      $this->setRequest($request); 
      $this->setResponse($response);
	}
  
   /**
    * Current application request object
    * @return Request
    */
	public function getRequest() {
		return $this->get('Request');
	}

   /**
    * Current application response object
    * @return Response
    */
	public function getResponse() {
      return $this->get('Response');
	}
}