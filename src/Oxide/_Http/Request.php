<?php
namespace Oxide\Http;

/**
 * Http Request object
 *
 * Wraps incoming http request information
 * This object is not meant for general Request object for communicating with oter server/services
 * In fact, you can't create a new Request object.  You must use static method getCurrentRequest()
 * to obtain current HTTP request
 *
 * @package oxide
 * @subpackage http
 * @todo change _routeComponents to _moduleCompnonent
 * @todo better path components
 * @todo utilize base
 */
class Request
{
    protected
        /**
         * @var string Holds the current request url
         */
        $_url = null,
        $_base = '/',

        $_relativeUrl = null,

        /**
         * @var boolean Indicates if the request is secured or not
         */
        $_secured = false;


    protected
        $_cookie_identifier = "__REQUEST_ID__",
        $_uriComponents = array(),
        $_routeComponents = array(),
        $_pathParams = array(),
        $_method = null,
        $_queries = [],
        $_posts = [],
        $_headers = [],
        $_vars = [];

    const
        URI_SCHEME = 'scheme',
        URI_HOST = 'host',
        URI_PATH = 'path',
        URI_BASE = 'base',
        URI_SCRIPT = 'script',
        URI_PORT = 'port',
        URI_PASS = 'pass',
        URI_USER = 'user',
        URI_FRAGMENT = 'fragment',
        URI_QUERY = 'query';

    /**
     * constructor
     *
     * Cannot instanciate. Must use create methods instead.
     * @access private
     */
    public function __construct(
        $url = null,
        $method = 'get',
        array $query = null,
        array $post = null,
        array $headers = null
    ) {
        // set the url and it's components
        if ($url) {
            $this->setUrl($url);
        }

        // set the request method
        $this->setMethod($method);

        // set the query path
        if ($query) {
            $this->_queries = $query;
        }

        if ($post) {
            $this->_posts = $post;
        }

        if ($headers) {
            $this->_headers = $headers;
        }
    }

    /**
     * Set the url for the request
     *
     * This will re-evaluate the url and will adjust
     * @param string $url
     */
    public function setUrl($url)
    {
        // parse the uri
        $uris = parse_url($url);
        $this->_uriComponents = $uris;

        // parse the path params
        $params = array_filter(explode('/', $uris[self::URI_PATH]));
        $this->_pathParams = array_values($params);

        // if url has query, merge it in
        if (isset($uris[self::URI_QUERY])) {
            $query = $uris[self::URI_QUERY];
            parse_str(html_entity_decode($query), $this->_queries);
        }

        // detect if secured or not
        if (isset($uris[self::URI_SCHEME])) {
            if (strtolower($uris[self::URI_SCHEME]) == 'https') {
                $this->_secured = true;
            } else {
                $this->_secured = false;
            }
        }

        // relative
        $relatives = [$uris[self::URI_PATH]];
        if (isset($uris[self::URI_QUERY])) {
            $relatives[] = '?' . $uris[self::URI_QUERY];
        }
        if (isset($uris[self::URI_FRAGMENT])) {
            $relatives[] = '#' . $uris[self::URI_FRAGMENT];
        }
        $this->_relativeUrl = implode('', $relatives);

        // store the url
        $this->_url = $url;
    }

    /**
     * Gets the full URL for the request.
     * This may or may not have scheme and host information, depending on whether
     * those information was provided when creating the request or not.
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Set base path for the request.
     *
     * Must be set before routing process begins
     * @access public
     * @param mixed $base
     * @return void
     */
    public function setBase($base)
    {
        $this->_base = '/' . trim($base, '/');
    }

    /**
     * Get the current base for the request.
     *
     * @access public
     * @return void
     */
    public function getBase()
    {
        return $this->_base;
    }

    /**
     *
     * @return bool
     */
    public function isSecured()
    {
        return $this->_secured;
    }

    /**
     * Set HTTP method for the request
     *
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * Get the HTTP method
     *
     * @return null|string
     */
    public function getMethod()
    {
        return $this->_method;
    }


    /**
     * Get HTTP Request Headers
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getHeaders($key = null, $default = null)
    {
        if (!$key) {
            return $this->_headers;
        }
        if ($key) {
            return (isset($this->_headers[$key])) ? $this->_headers[$key] : $default;
        } else {
            return $this->_headers;
        }
    }

    /**
     * Returns given value for requested $key from Uri component array.
     *
     * @access public
     * @param string $key
     * @return string|null
     **/
    public function getUriComponents($key = null, $default = null)
    {
        if ($key === null) {
            return $this->_uriComponents;
        }
        if (!isset($this->_uriComponents[$key])) {
            return $default;
        }

        return $this->_uriComponents[$key];
    }

    /**
     * get a path from given index
     *
     * Example
     * from given path  /path/string
     * Accessing to index 1 in the above example will return 'string'
     *
     * @access public
     * @param int $index
     * @param mixed $default value to return if given index is not found
     * @return string
     */
    public function getPaths($index = null, $default = '')
    {
        if ($index === null) {
            return $this->_pathParams;
        }
        if (isset($this->_pathParams[$index])) {
            return $this->_pathParams[$index];
        } else {
            return $default;
        }
    }

    /**
     * gets a value post value
     *
     * returns raw value without applying any kind of filtering.
     * @access public
     * @param string $key
     * @param mixed $default value to return if given $key is not found
     * @return mixed
     */
    public function getPost($key = null, $default = null)
    {
        if ($key === null) {
            return $this->_posts;
        }
        if (isset($this->_posts[$key])) {
            return $this->_posts[$key];
        }
        return $default;
    }

    /**
     * returns a value from the query string
     *
     * returns raw value without any kind of filtering applied.
     * @access public
     * @param string $key
     * @param mixed $default value to return if $key not found
     * @return string
     */
    public function getQuery($key = null, $default = null)
    {
        if ($key === null) {
            return $this->_queries;
        }
        if (isset($this->_queries[$key])) {
            return $this->_queries[$key];
        }
        return $default;
    }

    /**
     * Set any additional params for the request
     *
     * This is usually called by the Router object once the request has been routed.
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->_vars = $params;
    }

    /**
     * Get
     * @param type $index
     * @param type $default
     * @return type
     */
    public function getParams($index = null, $default = null)
    {
        if ($index === null) {
            return $this->_vars;
        }
        if (isset($this->_vars[$index])) {
            return $this->_vars[$index];
        }
        return $default;
    }

    /**
     * Get input value from given $type
     *
     * This will always return from user Input
     * Simply used filter_input method
     * @param type $type [INPUT_GET | INPUT_POST | INPUT_SERVER, etc]
     * @param string $key
     * @param mixed $default
     * @param type $filter
     * @param mixed $opitons
     * @return type
     */
    public static function getInput(
        $type,
        $key = null,
        $default = null,
        $filter = FILTER_UNSAFE_RAW,
        $opitons = null
    ) {
        if ($key === null) {
            $vals = filter_input_array($type);
            if (!$vals) {
                return [];
            } else {
                return $vals;
            }
        }

        $val = filter_input($type, $key, $filter, $opitons);
        if ($val === false || $val === null) {
            return $default;
        }

        return $val;
    }

    /**
     * get query string
     *
     * allows to add and remove from query before retriving the string
     * @access public
     * @param mixed $add key value to the query string, if exists, it will be overridden
     * @param mixed $remove remove the key and it's value from the string
     * @return string
     */
    public function getQueryString(array $add = null, $remove = null)
    {
        // get the query string array
        $qparams = $this->getQuery();

        // add to query string, if requested
        if ($add) {
            if (is_array($add)) {
                foreach ($add as $key => $value) {
                    $qparams[$key] = $value;
                }
            }
        }

        // remove from query if requested
        if ($remove) {
            if (is_array($remove)) {
                foreach ($remove as $key) {
                    unset($qparams[$key]);
                }
            } else {
                unset($qparams[$remove]);
            }
        }

        return http_build_query($qparams);
    }


    /**
     * get current client IP address
     *
     * @return string
     */
    public static function getIpAddress()
    {
        $client_ip = Server::vars('HTTP_CLIENT_IP');
        if ($client_ip) {
            return $client_ip;
        }

        $forward_ip = Server::vars('HTTP_X_FORWARDED_FOR');
        if ($forward_ip) {
            return $forward_ip;
        } else {
            return Server::vars('REMOTE_ADDR');
        }
    }
}