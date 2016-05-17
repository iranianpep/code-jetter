<?php

namespace CodeJetter\core;

/**
 * Class Singleton
 * @package CodeJetter\core
 */
abstract class Singleton
{
    private static $instances;

    /**
     * Singleton constructor.
     */
    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    /**
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }

    /**
     * @return Singleton|App|Registry
     */
    public static function getInstance()
    {
        $class = get_called_class();

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static;
        }

        return self::$instances[$class];
    }
}
