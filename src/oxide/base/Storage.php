<?php
namespace oxide\base;

/**
 * Storage interface
 * 
 * Provides basic interface for simple persistent storage machanism
 */
interface Storage {
   public function read($key, $default = null);
   public function write($key, $value);
   public function has($key);
   public function delete($key);
}