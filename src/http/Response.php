<?php
namespace oxide\http;

/**
 * Response class
 *
 * Manages complete HTTP(S) response, including headers and body information.
 * @package oxide
 * @subpackage http
 */
class Response
{
   use \oxide\util\pattern\DefaultInstanceTrait;
   
   private 
		$_headers = array(),
		$_responseCode = 200,
		$_charset = 'utf-8',
		$_options = array(
	     'Content-Type' => null
			),
		$_body = array(),
		$_allowCaching,
		$_cacheTime,
		$_lastModified;
	
	
	/**
	 * request to set caching header
	 * 
	 * if $bool is true, then caching headers will be sent to encourage browse to cache
	 * else headers will be sent to disable caching all together
	 * if null value is send, then no caching will be send
	 * 
	 * @return 
	 * @param bool $bool
	 * @param int $time[optional] if caching is set, provide number of seconds to keep cache alive
	 */
	public function setCachingHeaders($bool, $time = 86400, $lastModified = null)
	{
		$this->_allowCaching = $bool;
		$this->_cacheTime = $time;
		$this->_lastModified = $lastModified;
	}

   
	/**
	 * sets the content type of the response
	 * 
	 * use this to output other then html content, such as dynamic image, pdf etc
	 * @access public
	 * @return 
	 * @param string $type
	 * @param string $charset[optional] charset is default to utf-8
	 */
   public function setContentType($type, $charset = 'utf-8')
   {
   	if($charset) {
   		$type = "$type; charset=$charset";
   	}
      $this->_options['Content-Type'] = $type;
   }
   
   /**
    * set redirect header information
    * 
    * use this to redirect to different page
    * @access public
    * @param string $url
    */
   public function setRedirect($url)
	{
      // if header is already sent, this will not work
		if(headers_sent())
         trigger_error ('Cannot send response.  Headers already sent.', E_USER_WARNING);
      $header = "Location: $url";
      $this->addHeader($header);
   }
   
   /**
    * set encoding for the page
    */
   public function setEncoding($encoding)
	{
      $this->_options['encoding'] = $encoding;
   }

	/**
	 * add header header info
	 * 
	 * @param string $name
	 * @param string $value
	 * @param bool $replace
	 */
   public function addHeader($name, $value = null, $replace = true)
	{
		$this->_headers[] = array('name' => $name, 'value' => $value, 'replace' => $replace);
	}

	/**
	 * Sends the header response immidaitely
	 *
	 * if $exit is set to true, then execution stops immidiately
	 * @param bool $exit
	 */
   public function sendHeaders($exit = false)
	{
      // main header
      header('HTTP/1.1 '. $this->_responseCode);
   
      // first setup the options
      foreach($this->_options as $key => $value) {
         if($value == null) continue;
         header("{$key}: {$value}");
      }
      
      // now add if any additional header information added
      if(count($this->_headers)) {
         foreach($this->_headers as $header) {
            if($header['value'] != null) {
            	header("{$header['name']}: {$header['value']}", $header['replace']);
            } else {
            	header($header['name'], $header['replace']);
            }
         }
      }

		// caching
//		if($this->_allowCaching === true) {
//			// encourage browser caching
//         $lastMod = filemtime(__FILE__) + date("Z");
//			header("Last-Modified: ". gmdate("D, d M Y H:i:s", $lastMod) . " GMT");
//			$expires = time() + $this->_cacheTime;
//			header("Expires: " .gmdate("D, d M Y H:i:s", $expires) . " GMT");
//			header("Cache-Control: max-age=". $this->_cacheTime);
//		} else if($this->_allowCaching === false) {
//			// disable browser caching
//			header("Last-Modified: ". gmdate("D, d M Y H:i:s") . " GMT");
//         header("Expires: " .gmdate("D, d M Y H:i:s") . " GMT");
//         header("Cache-Control: no-store, no-cache, must-revalidate ");
//         header("Cache-Control: post-check=0, pre-check=0", false);
//         header("Pragma: no-cache");
//		}
   }
	
	
   /**
    * add content to body
    * 
    * content will be appended to the body array
    * if optional $index is provided then content will added to that key location
    * this may be useful to later get the specific content or replace it's content
    * 
    * @return 
    * @param mixed $content
    * @param int|string $index[optional]
    */
   public function addBody($content, $index = null)
	{
		if($index === null) { 
         $this->_body[] = $content;
		} else {
			$this->_body[$index] = $content;
		}
   }
   
   /**
    * replace current body with given content
    * 
    * @return 
    * @param object $content
    */
   public function setBody($content)
	{
      $this->_body = array($content);
   }

	/**
	 * returns current body array
	 * 
	 * @param int|string $index[optional]
	 * @return array|string
	 */
   public function getBody($index = null)
	{
   	if($index === null) {
         return $this->_body;
		} else {
			return $this->_body[$index];
		}
   }
	
	/**
	 * returns entire body as string
	 * 
	 * @return string
	 */
	public function getBodyString()
	{
		$body = '';
		foreach($this->_body as $content) {
			$body .= (string) $content;
		}
		
		return $body;
	}

	/**
	 * reset complete response
	 *
	 * it will remove all header and body information
	 */
	public function resetAll()
	{
		$this->resetHeader();
		$this->resetBody();
	}

	/**
	 * reset all current header information
	 *
	 * This will not alter caching and response code
	 */
	public function resetHeader()
	{
		$this->_options = array();
		$this->_headers = array();
	}

	/**
	 * reset all current body content
	 */
	public function resetBody()
	{
		$this->setBody(null);
	}

	/**
	 * send current request back to requested browser
	 *
	 * this method will send both headers and body contents
	 */
	public function send($exit = true)
	{
		print $this;
		if($exit) exit();
	}

	/**
	 * send both header and body
	 * 
	 * @return string
	 */
   public function __toString()
	{
		if(!headers_sent()) $this->sendHeaders();
//      else         trigger_error ('Cannot send response.  Headers already sent.', E_USER_ERROR);
      return $this->getBodyString();
   }
}