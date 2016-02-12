<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

use oxide;

return array(
   'EventNotifer' => oxide\util\EventNotifier::class,
   'Connection' => function(oxide\base\Container $c) {
      
   },
           
   'Mailer' => function() {
      
   },
           
   'Router' => oxide\http\Router::class,
           
   'ViewManager'
   
   
);