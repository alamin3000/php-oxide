<?php

/**
 * Oxide Framework
 *
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name
 */

namespace Oxide\Util;

abstract class Debug
{
    public
        $enabled = false;

    const
        ERR_CRITICAL = 90000,
        ERR_NOTIFY = 90001,
        ERR_UNKNOWN = 99999;


    /**
     * breakpoint function.
     *
     * @access public
     * @static
     * @return void
     */
    public static function breakpoint()
    {
        $trace = debug_backtrace();
        if (isset($trace[1]['class'])) {
            $caller = $trace[1]['class'];
            $line = $trace[1]['line'];
            $file = $trace[1]['file'];
        } else {
            $caller = $trace[0]['class'];
            $line = $trace[0]['line'];
            $file = $trace[0]['file'];
        }

        return ['caller' => $caller, 'file' => $file, 'line' => $line];
    }


    /**
     * Log a string
     *
     * @param string $string
     */
    public static function log($string)
    {
        $trace = debug_backtrace();
        if (isset($trace[1]['class'])) {
            $caller = $trace[1]['class'];
            $line = $trace[1]['line'];
        } else {
            $caller = $trace[0]['file'];
            $line = $trace[0]['line'];
        }

        echo "<pre>[{$caller}:{$line}] {$string}</pre>";
    }


    /**
     * Dumb a variable
     *
     * @param mixed $var
     * @param bool $returnString
     */
    public static function dump($var, $returnString = false, $index = 0)
    {
        $trace = debug_backtrace();
//      echo "<div style='position: fixed; bottom:0px; left:0px;'>";
        echo "<pre>";
        ob_start();
        print_r($var);
//      echo var_dump($var);
        echo htmlentities(ob_get_clean());
        echo "</pre>";
        echo "<p><strong>File:</strong> {$trace[$index]["file"]} <strong>Line:</strong> {$trace[$index]["line"]}</p>";
//      echo "</div>";
    }
}