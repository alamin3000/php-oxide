<?php

namespace Oxide\Http;

class Server
{
    /**
     * Getting server variable
     *
     * Using explicit checking because filter_input may not work with INPUT_SERVER in some servers
     * Code take from https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=730094
     * @param type $key
     * @param type $default
     * @return type
     */
    public static function vars($var = null, $default = null, $filter = null, $opt = null)
    {
        $key = ucwords($var);
        if (filter_has_var(INPUT_SERVER, $key)) {
            return filter_input(INPUT_SERVER, $key, FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);
        } else {
            if (isset($_SERVER[$key])) {
                return filter_var($_SERVER[$key], FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);
            } else {
                return $default;
            }
        }
    }

    /**
     * Get the current server Request object
     *
     * @staticvar Request $request
     * @return Request
     */
    public static function currentRequest()
    {
        static $request = null;
        if ($request === null) {
            $host = self::vars('HTTP_HOST');
            $uri = self::vars('REQUEST_URI');
            $scheme = null;
            $https = self::vars('HTTPS');
            if ($https && $https == 1) /* Apache */ {
                $scheme = 'https';
            } elseif ($https && $https == 'on') /* IIS */ {
                $scheme = 'https';
            } elseif (self::vars('SERVER_PORT') == 443) /* others */ {
                $scheme = 'https';
            } else {
                $scheme = 'http';
            }

            $url = "{$scheme}://{$host}{$uri}";

            // create request from the url and setup the additional information
            $method = self::vars('REQUEST_METHOD');
            $posts = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);
            $headers = getallheaders();

            $request = new Request($url, $method, null, $posts, $headers);
        }

        return $request;
    }
}