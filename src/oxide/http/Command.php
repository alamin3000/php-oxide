<?php
namespace oxide\http;

/**
 * Command interface
 *
 * Interface to implement command design pattern for oxide engine
 * @package oxide
 * @subpackage http
 */
interface Command {
	public function execute(Context $context);
}