<?php

/**
 * Oxide Framework
 *
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name
 */

namespace Oxide\Support;

use Oxide\Support\Pattern\SharedInstanceTrait;

class Mailer extends \PHPMailer
{
    use SharedInstanceTrait;

    /**
     *
     * @param bool $exceptions
     * @param Dictionary $config
     * @throws \Exception
     */
    public function __construct($exceptions = false, Dictionary $config = null)
    {
        parent::__construct($exceptions);

        if ($config) {
            $this->configure($config);
        }
    }

    /**
     * Configure the internal mail agent
     *
     * @param Dictionary $config
     * @throws \Exception
     */
    public function configure(Dictionary $config)
    {
        $type = $config->get('transport', null, true);
        if ($type == 'smtp') {
            // getting smpt related configurations
            $host = $config->getUsingKeyPath('options.host', null, true);
            $port = $config->getUsingKeyPath('options.port', 25);
            $encrypt = $config->getUsingKeyPath('options.encrypt', null);
            $username = $config->getUsingKeyPath('options.username', null, true);
            $password = $config->getUsingKeyPath('options.password', null, true);

            // setting up the
            $this->isSMTP();
            $this->Host = $host;
            $this->Port = $port;
            $this->SMTPAuth = true;
            if ($encrypt) {
                $this->SMTPSecure = $encrypt;
            }
            $this->Username = $username;
            $this->Password = $password;

        } else {
            if ($type == 'sendmail') {
                $this->isSendmail();
            } else {
                if ($type == 'mail') {
                    // nothing to do
                } else {
                    throw new \Exception('Email transport is not recognized.');
                }
            }
        }
    }

    /**
     * Reset all information for
     */
    public function resetAll()
    {
        $this->clearAddresses();
        $this->clearAllRecipients();
        $this->clearAttachments();
        $this->clearBCCs();
        $this->clearCCs();
        $this->clearReplyTos();
        $this->clearCustomHeaders();
    }
}