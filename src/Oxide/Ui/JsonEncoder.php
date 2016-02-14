<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui;

/**
 * Json View
 * 
 * Renders current data using json_encode function
 */
class JsonEncoder implements Renderer {
   public 
      $options = 0;
   
   public function __construct() {
      parent::__construct();
      $this->contentType = 'application/json';
   }
   
   protected function render() {
      return json_encode($this->getData(), $this->options);
   }
}