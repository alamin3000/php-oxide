<?php
namespace Oxide\Http;

/**
 * Command Controller
 *
 * implements Command to comply with front controller engines specification
 * provides command execution life cycle events
 *
 * @package oxide
 * @subpackage http
 */
class CommandFactory
{
    public
        $classNamespace = 'controller',
        $classSuffix = 'Controller',
        $classPrefix = null;

    protected
        $context = null;

    /**
     *
     * @param \oxide\http\Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Generate a full resolvable class name based on route
     *
     * @param \oxide\http\Route $route
     * @return null
     */
    public function generateClassName(Route $route)
    {
        $namespace = $route->namespace;
        $controller = self::stringToName($route->controller);

        // make sure module and controller names are provided and are valid
        if (empty($controller) || !$this->validateClassname($controller)) {
            return null;
        }

        $classnamespace = $this->classNamespace;
        $classsuffix = $this->classSuffix;
        $classprefix = $this->classPrefix;
        $class = "{$namespace}\\{$classnamespace}\\{$classprefix}{$controller}{$classsuffix}";
        return $class;
    }

    /**
     *
     * @param \oxide\http\Route $route
     * @return null|\oxide\http\class
     */
    public function create(Route $route)
    {
        $class = $this->generateClassName($route);
        $instance = $this->context->instantiate($class, null, 'controller');

        return $instance;
    }

    /**
     * Validate given class name
     *
     * @note code found on Stackoverflow
     * @param $class
     * @return int
     */
    public function validateClassname($class)
    {
        return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $class);
    }


    /**
     * Convert string to a controller/action name
     * @param type $string
     * @return type
     */
    public static function stringToName($string)
    {
        return
            str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }
}