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

/**
 */
class ConfigManager
{
    use SharedInstanceTrait;

    public
        $defaultConfigType = 'json';

    protected
        $_defaultConfigFilename = 'config.json',
        $_parser = null,
        $_configs = [],
        $_dir = null;

    /**
     * Construct the config manager
     *
     * @param string $configDir
     * @param string $defaultConfigFilename
     */
    public function __construct($configDir, $defaultConfigFilename = null)
    {
        $this->_dir = $configDir;
        if ($defaultConfigFilename) {
            $this->_defaultConfigFilename = $defaultConfigFilename;
        }
    }

    /**
     * Returns the managed config directory
     *
     * @return string
     */
    public function getConfigDir()
    {
        return $this->_dir;
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public function generateFilename($name)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if (!$ext) {
            $ext = $this->defaultConfigType;
            $name = "{$name}.{$ext}";
        }

        return $name;
    }

    /**
     * Get the app config object
     *
     * @return Container
     * @throws \Exception
     */
    public function getConfig($name = null)
    {
        if (!$name) {
            $filename = $this->_defaultConfigFilename;
        } else {
            $filename = $this->generateFilename($name);
        }

        if (!isset($this->_configs[$filename])) {
            $this->_configs[$filename] = $this->openConfigByFilename($filename);
        }

        return $this->_configs[$filename];
    }

    /**
     * Store config
     *
     * @param type $name
     * @param Dictionary $config
     */
    public function setConfig($name, Dictionary $config)
    {
        $filename = $this->generateFilename($name);
        $this->_configs[$filename] = $config;
    }

    /**
     *
     * @return FileParser
     */
    public function getFileParser()
    {
        if ($this->_parser === null) {
            $this->_parser = new FileParser();
        }

        return $this->_parser;
    }

    /**
     *
     * @param \oxide\util\FileParser $parser
     */
    public function setFileParser(FileParser $parser)
    {
        $this->_parser = $parser;
    }

    /**
     * Create a config object from given $filename using current config directory
     *
     * Filename provided must be relative to the managed configuration directory
     *
     * @param string $filename
     * @return DataFile
     * @throws \Exception
     */
    public function openConfigByFilename($filename)
    {
        $dir = $this->_dir;
        $file = $dir . '/' . $filename;
        if (!is_file($file)) {
            throw new \Exception('Config file not found in location: ' . $file);
        }

        $parser = $this->getFileParser();
        $data = $parser->parse($file);
        return new Dictionary($data);
    }

    /**
     * Get config object from given relative directory
     *
     * @param string $relative_dir
     * @param string $name
     * @return DataFile
     */
    public function openConfigByDirectory($relative_dir, $name = 'config.json')
    {
        $dir = trim($relative_dir, '/');
        $filename = "{$dir}/{$name}";
        return $this->openConfigByFilename($filename);
    }
}