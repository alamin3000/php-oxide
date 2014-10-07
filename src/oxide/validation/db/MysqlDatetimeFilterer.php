<?php
namespace oxide\validation;
use oxide\data\Connection;
/**
 * filters value into MySQL DateTime format
 *
 * @package oxide
 * @subpackage validation
 */
class MysqlDatetimeFilterer implements Filterer
{
	public function filter($value)
	{
      $time = Connection::toDateTime($value);
      return $time;
	}
}
