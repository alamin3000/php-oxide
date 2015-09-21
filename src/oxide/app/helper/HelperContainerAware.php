<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app\helper;

interface HelperContainerAware {
   public function getHelperContainer();
   public function setHelperContainer(HelperContainer $helper);
}