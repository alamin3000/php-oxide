<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui\view;


class JsonView extends ViewAbstract {
   public function render() {
      return json_encode($this->getData());
   }
}