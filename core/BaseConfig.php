<?php


namespace CodeJetter\core;

abstract class BaseConfig
{
    protected static $configs;

    /**
     * @param      $key
     * @param null $component
     *
     * @return mixed
     * @throws \Exception
     */
    public function get($key, $component = null)
    {
        if ($component !== null) {
            $rootNamespace = $this->get('ROOT_NAMESPACE');

            $componentConfigClassName = "{$rootNamespace}\\components\\" . strtolower($component)
                . '\\' . ucfirst($component) . 'Config';

            if (class_exists($componentConfigClassName)) {
                $componentConfigClass = new $componentConfigClassName();

                if ($componentConfigClass instanceof BaseConfig) {
                    $configs = $componentConfigClass->getConfigs();
                } else {
                    throw new \Exception("Class: '{$componentConfigClassName}' must extend BaseConfig");
                }
            } else {
                throw new \Exception("Config file: '{$componentConfigClassName}.php' does not exist");
            }
        } else {
            $configs = static::$configs;
        }

        // get app to get environment first
        $app = App::getInstance();

        if (isset($configs[$app->getEnvironment()]) && array_key_exists($key, $configs[$app->getEnvironment()])) {
            return $configs[$app->getEnvironment()][$key];
        } elseif (array_key_exists($key, $configs)) {
            return $configs[$key];
        } else {
            throw new \Exception("Key: '{$key}' does not exist in configs");
        }
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws \Exception
     */
    public function set($key, $value)
    {
        // get app to get environment first
        $app = App::getInstance();

        if (array_key_exists($key, static::$configs[$app->getEnvironment()])) {
            static::$configs[$app->getEnvironment()][$key] = $value;
        } elseif (array_key_exists($key, static::$configs)) {
            static::$configs[$key] = $value;
        } else {
            throw new \Exception("Key: '{$key}' does not exist in configs");
        }
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return static::$configs;
    }
}
