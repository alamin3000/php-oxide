<?php
namespace oxide\data;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



interface Store extends \Countable, \ArrayAccess
{
   public function write($key, $value);
   public function read($key, $default = null);
   public function delete($key);
}