<?php
namespace Oxide\Http;

/**
 * Command interface
 *
 * @package oxide
 * @subpackage http
 */
interface Command
{
    public function execute(Context $context);
}