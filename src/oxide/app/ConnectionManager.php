<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app;
use oxide\data\Connection;

class ConnectionManager {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   protected
      $_connection = null,
      $_config = null,
      $_options = null;
   
   /**
    * 
    * @param array $config
    * @param array $options
    */
   public function __construct(array $config = null, array $options = null) {
      $this->_config = $config;
      $this->_options = $options;
   }
   
   /**
    * Get the managed connection
    * 
    * @param string $id
    * @return Connection
    * @throws \Exception
    */
   public function getConnection() {
      if($this->_connection === null) {
         $config = $this->_config;
         if(!$config) {
            throw new \Exception("Database configuration not found.");
         }
         $options = $this->_options;
         if(!$options) {
            $options = [
               \PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION,
               'FETCH_MODE'			=> \PDO::FETCH_ASSOC
               ];
         }
         $this->_connection = new Connection($config, $options);
      }

      return $this->_connection;
   }
     
   /**
    * Get the shared connection using the sharedInstance
    * 
    * @return Connection
    */
   public static function sharedConnection() {
      return self::sharedInstance()->getConnection();
   }
}