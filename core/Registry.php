<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 25/04/15
 * Time: 5:37 PM
 */

namespace CodeJetter\core;

use CodeJetter\core\database\MySQLDatabase;

/**
 * Class Registry
 * @package CodeJetter\core
 */
class Registry extends Singleton
{
    private static $container;

    /**
     * add class to container
     *
     * @param      $object
     * @param null $key
     */
    public static function add($object, $key = null)
    {
        if ($key === null) {
            $classNameWithNamespace = get_class($object);
            $key = substr($classNameWithNamespace, strrpos($classNameWithNamespace, '\\')+1);
        }

        static::$container[$key] = $object;
    }

    /**
     * @param $classKey
     *
     * @return object
     *
     * @throws \Exception
     */
    public static function get($classKey)
    {
        if (static::isInContainer($classKey)) {
            return static::$container[$classKey];
        } else {
            throw new \Exception("Class key: {$classKey} does not exist in Registry container");
        }
    }

    /**
     * remove a class by its key
     *
     * @param $classKey
     *
     * @return bool
     * @throws \Exception
     */
    public static function remove($classKey)
    {
        $app = App::getInstance();
        $reservedSingletons = $app->getSingletons();

        if (array_key_exists($classKey, $reservedSingletons)) {
            throw new \Exception("'{$classKey}' is a reserved class. Cannot be removed");
        }

        if (static::isInContainer($classKey)) {
            unset(static::$container[$classKey]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * check to see if the class is stored in registry container
     *
     * @param $classKey
     *
     * @return bool
     */
    public static function isInContainer($classKey)
    {
        if (empty($classKey) || !isset(static::$container)) {
            return false;
        }

        if (array_key_exists($classKey, static::$container)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * return all the stored classes
     *
     * @return mixed
     */
    public static function getClasses()
    {
        return static::$container;
    }

    /**
     * return a list of classes without their content
     *
     * @return array
     */
    public static function getClassList()
    {
        $classesList = [];
        foreach (static::getClasses() as $classKey => $classValue) {
            $classesList[$classKey] = get_class($classValue);
        }
        return $classesList;
    }

    /**
     * return config class for ease of use
     *
     * @return BaseConfig
     * @throws \Exception
     */
    public static function getConfigClass()
    {
        return static::get('Config');
    }

    /**
     * return router class for ease of use
     *
     * @return Router
     * @throws \Exception
     */
    public static function getRouterClass()
    {
        return static::get('Router');
    }

    /**
     * return mysql database class for ease of use
     *
     * @return MySQLDatabase
     * @throws \Exception
     */
    public static function getMySQLDBClass()
    {
        return static::get('MySQL');
    }

    /**
     * return language class for ease of use
     *
     * @return Language
     * @throws \Exception
     */
    public static function getLanguageClass()
    {
        return static::get('Language');
    }

    /**
     * Reset Registry container only in dev mode
     *
     * @throws \Exception
     */
    public static function resetContainer()
    {
        if (App::getInstance()->getEnvironment() === 'dev') {
            static::$container = [];
        } else {
            throw new \Exception('This function is not available in production mode');
        }
    }
}
