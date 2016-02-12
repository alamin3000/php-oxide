<?php
namespace Oxide\Exception;

use Exception;

class NotFoundException extends Exception
{
    public function __construct($whatNotFound = null, $scope = null)
    {

    }
}