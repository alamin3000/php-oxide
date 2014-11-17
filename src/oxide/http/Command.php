<?php
namespace oxide\http;

/**
 * Command interface
 *
 * @package oxide
 * @subpackage http
 */
interface Command {
	public function execute(Context $context);
}