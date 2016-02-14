<?php

/**
 * Oxide Framework
 *
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name
 */

namespace Oxide\Http;

use Oxide\Support\Dictionary;

class Session extends Dictionary
{
    protected
        $_options = [
        'session_id' => null,
        'cookie_path' => '/',
        'cookie_timeout' => 21600,
        'garbage_timeout' => 216600,
        'session_dir' => 'oxide_session',
        'cookie_secure' => false,
        'cookie_domain' => null
    ];


    /**
     * Construct a new session
     *
     * @param type $options
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->_options = array_merge($this->_options, $options);
        }

        $this->start();
        parent::__construct($_SESSION);
    }

    /**
     * Start the session, if already hasn't been done so
     *
     * @throws exception\HeaderAlreadySentException
     */
    protected function start()
    {
        if (session_status() == PHP_SESSION_NONE) {

            // throw error if header is already sent
            if (headers_sent()) {
//           throw new exception\HeaderAlreadySentException(heade);
            }

            // set the options and configure the session for the first time
            $options = $this->_options;
            session_set_cookie_params(
                $options['cookie_timeout'],
                $options['cookie_path'],
                $options['cookie_domain'],
                $options['cookie_secure'],
                true);
            ini_set('session.gc_maxlifetime', $options['garbage_timeout']);
            session_cache_limiter("must-revalidate");

            // start the session
            session_start();
        }
    }

    /**
     * get session id
     * @access public
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * sets session id.
     *
     * must be call prior to starting the session.
     * @access public
     * @param $id string
     * @throws Oxide_Session_Exception_AlreadyStarted
     */
    public function setId($id)
    {
        session_id($id);
    }

    /**
     * regenerate a session id.
     *
     * this should be done after session is started.
     * @access public
     */
    public function regenerateId($deleteold = true)
    {
        session_regenerate_id($deleteold);
        $this->_id = session_id();
    }
}