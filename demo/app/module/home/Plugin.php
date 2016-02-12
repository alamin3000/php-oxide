<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace app\module\home;
use oxide\app\Pluggable;
/**
 * Description of Loader
 *
 * @author alaminahmed
 */
class Plugin implements Pluggable {
   
   public function __construct(\oxide\http\Session $session) {
      
   }
   
   public function plug() {
//      echo "here';';'";
   }
}
