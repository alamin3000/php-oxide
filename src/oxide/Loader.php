<?php
/**
 * Oxide Framework
 *
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name
 */

namespace Oxide;

use Oxide\Common\Dictionary;
use Oxide\Common\Pattern\SingletonTrait;
use Oxide\Data\Connection;
use Oxide\Http\Context;
use Oxide\Http\FrontController;
use Oxide\Http\Server;
use Oxide\Util\ConfigManager;
use Oxide\Util\EventNotifier;
use Oxide\Util\Mailer;
use Oxide\Util\PSR4Autoloader;

/**
 * Print variable info
 *
 * @param type $var
 */
function dump($var)
{
    util\Debug::dump($var, false, 1);
}

/**
 * Function throws exception.
 *
 * Useful when can not use throw syntax (such as ternary operation)
 * @param type $str
 * @param type $code
 * @throws \Exception
 */
function exception($str = null, $code = null)
{
    throw new \Exception($str, $code);
}

/**
 * Throws exception if given $value is null, else given $value is returned
 *
 * @param $value
 * @return mixed
 * @throws NotFoundException
 */
function required($value)
{
    if ($value === null || empty($value)) {
        throw new NotFoundException();
    }

    return $value;
}

function value($container, $key, $default = null)
{
    if (is_array($container)) {

    }
}


/**
 * Oxide Loader
 *
 * Manages namespaces
 * Provides bootstrap functionality
 *
 * In general, all Oxide framework applications should use Loader's bootstrap
 * method to start the application
 *
 * For applications not using Composer, register_autoload() must be called before
 * making any reference to any oxide framework classes.
 *
 * Typical application initialized and started by calling the bootstrap() method.
 */
class Loader
{
    use SingletonTrait;

    protected
        $modules = [],
        $autoloader = null;

    const
        EVENT_BOOTSTRAP_START = 'LoaderBootstrapStart',
        EVENT_BOOTSTRAP_END = 'LoaderBootstrapEnd';

    /**
     * Get the PSR4 Autoloader class
     * @return PSR4Autoloader
     */
    public function getAutoloader()
    {
        if ($this->autoloader === null) {
            $this->autoloader = new PSR4Autoloader();
        }

        return $this->autoloader;
    }

    /**
     * Load the oxide framework
     */
    public function load()
    {
        // turn all errors into exception
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new \Exception("Error type: {$errno} raised: \"{$errstr}\" at {$errfile}:{$errline}");
        });

        // register auto loader
//        $autoloader = $this->getAutoloader();
//        $autoloader->register();
//        $dir = dirname(__FILE__);
//        $autoloader->addNamespace('oxide', $dir);
    }

    /**
     * Initializes the FrontController and returns the instance.
     *
     * Performs various bootstrap processes for oxide application.
     * @param string $config_dir Configuration directory
     * @param boolean $autorun Whether or not application should start
     * @return http\FrontController
     */
    public static function bootstrap($config_dir)
    {

        // get singleton loader and register it.
        $loader = self::getInstance();
        $loader->load();

        // create the config manager and share it
        $configManager = new ConfigManager($config_dir);
        $config = $configManager->getConfig();
        ConfigManager::setSharedInstance($configManager);

        // create and initialize the global services
        $loader->initializeSharedServices($config);


        // create and configure the request object
        $request = Server::currentRequest();
        if (isset($config['base'])) {
            $request->setBase($config['base']);
        }


        // creating the http context with the current server request
        $context = new Context($request);



        // create the front controller and share it
        $fc = new FrontController($context);
        FrontController::setSharedInstance($fc);

        // load modules
        $modules = isset($config['modules']) ? $config['modules'] : exception("Modules are required.");
        $loader->loadModules($modules, $fc->getRouter(), $context);

        return $fc;
    }

    /**
     * Set shared/global services
     *
     * @param \oxide\base\Dictionary $config
     */
    protected function initializeSharedServices(Dictionary $config)
    {
        // shared notifier
        EventNotifier::setSharedInstance(new EventNotifier());

        // shared connection
        Connection::bindSharedInstance(function () use ($config) {
            return new Connection($config->get('database', null, true), [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                'FETCH_MODE' => \PDO::FETCH_ASSOC
            ]);
        });

        // shared mailer
        Mailer::bindSharedInstance(function () use ($config) {
            return new Mailer(true, new Dictionary($config->get('email')));
        });
    }


    /**
     * @param array $modules
     * @param \oxide\http\Router $router
     */
    protected function loadModules(array $modules, http\Router $router, http\Context $context)
    {
        $autoloader = $this->getAutoloader();
        if ($modules) {
            foreach ($modules as $module) {
                $name = isset($module['name']) ? $module['name'] : exception('Module name is required.');
                $dir = isset($module['dir']) ? ltrim($module['dir'], '/') : exception('Module dir is required.');
                $namespace = isset($module['namespace']) ? $module['namespace'] : exception('Module namespace is required.');
                if ($namespace) {
                    $autoloader->addNamespace($namespace, $dir);
                }

                if ($name) {
                    $router->register($name, $dir, $namespace);
                }

                $pluginClass = "{$namespace}\\Plugin";
                if (class_exists($pluginClass)) {
                    $plugin = $context->instantiate($pluginClass);
                    if (!$plugin instanceof app\Pluggable) {
                        throw new \Exception("Plugin ($pluginClass) must be an instance of Pluggable interface.");
                    }

                    $plugin->plug();
                }
            }
        }
    }
}