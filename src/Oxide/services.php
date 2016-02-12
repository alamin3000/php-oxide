<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

return [
   'oxide\data\Connection' => [
      'alias' => ['connection', 'db'],
      'bind' => function(base\Dictionary $config) {
         return new data\Connection($config->get('database', null, TRUE), [
            \PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION,
            'FETCH_MODE'			=> \PDO::FETCH_ASSOC
         ]);
   ]
];