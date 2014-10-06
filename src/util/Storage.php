<?php
namespace oxide\util;

/**
 * Storage interface
 * 
 * Provides basic interface for simple storage machanism
 */
interface Storage {
   public function read($key, $default = null);
   public function write($key, $value);
   public function has($key);
   public function delete($key);
}