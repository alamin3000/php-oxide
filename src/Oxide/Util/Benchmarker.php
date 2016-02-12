<?php

/**
 * Oxide Framework
 *
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name
 */

namespace Oxide\Util;
use Closure;

class Benchmarker
{
    public function benchmark(Closure $script, $iteration)
    {
        $time_start = microtime(true);
        for ($i = 0; $i < $iteration; $i++) {
            $script();
        }
        $time_end = microtime(true);

        $diff = ($time_end - $time_start);

        return $diff;
    }
}