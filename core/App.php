<?php

namespace CodeJetter\core;

use CodeJetter\core\io\Request;
use CodeJetter\core\security\Security;
use CodeJetter\core\utility\DateTimeUtility;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Class App
 * @package CodeJetter\core
 */
class App extends Singleton
{
    /**
     * App class config
     *
     * This cannot be moved to Config class since it includes Config as well
     */
    private static $singletons = [
        'Config' => 'CodeJetter\Config',
        'Router' => 'CodeJetter\core\Router',
        'MySQL' => 'CodeJetter\core\database\MySQLDatabase',
        'Language' => 'CodeJetter\core\Language'
    ];

    private $environment;
    private $isInitialized;
    private $registry;
    // Global CSRF token
    private $antiCSRFToken;

    /**
     * If any arguments need to be passed to App once it is instantiated, can be here,
     * otherwise it can be in construct
     *
     * Also this avoids infinite loop
     *
     * @param null $environment
     */
    public function init($environment = null)
    {
        if ($this->isInitialized !== true) {
            $this->checkRequirements();

            $this->setEnvironment($environment);

            $this->registry = Registry::getInstance();

            // store singleton classes
            foreach (static::$singletons as $singletonKey => $singleton) {
                Registry::add(new $singleton, $singletonKey);
            }

            // get & set default time zone
            $timeZone = Registry::getConfigClass()->get('defaultTimeZone');
            // set default time zone, if user is registered, time zone is set in the router
            $this->setTimeZone($timeZone);

            // set anti CSRF attack token
            $this->setAntiCSRFToken();

            $this->isInitialized = true;
        }
    }

    /**
     * set environment
     *
     * @param $environment
     */
    public function setEnvironment($environment)
    {
        if ($environment !== null) {
            $this->environment = $environment;
        } else {
            $this->detectEnvironment();
        }
    }

    /**
     * detect environment
     *
     * @return string
     */
    private function detectEnvironment()
    {
        $request = new Request();
        $serverName = $request->getServerName();

        if ($serverName === 'localhost' || $serverName === '127.0.0.1' || empty($serverName)) {
            $this->setEnvironment('dev');
        } else {
            $this->setEnvironment('prod');
        }
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        if (empty($this->environment)) {
            $this->detectEnvironment();
        }

        return $this->environment;
    }

    /**
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return array
     */
    public function getSingletons()
    {
        return static::$singletons;
    }

    /**
     * @return string
     */
    public function getAntiCSRFToken()
    {
        return $this->antiCSRFToken;
    }

    /**
     * Set anti CSRF attack token
     *
     * This is private to restrict being set from outside of this class
     */
    private function setAntiCSRFToken()
    {
        if (empty($_SESSION['antiCSRFToken'])) {
            $_SESSION['antiCSRFToken'] = (new Security())->generateToken();
        }

        $this->antiCSRFToken = $_SESSION['antiCSRFToken'];
    }

    /**
     * Set time zone in PHP and database
     *
     * @param $timeZone
     */
    public function setTimeZone($timeZone)
    {
        $timeZones = (new DateTimeUtility())->getTimeZones();

        if (in_array($timeZone, $timeZones)) {
            // set PHP timezone
            date_default_timezone_set($timeZone);

            /**
             * set database timezone
             * This only works for the default database
             */
            Registry::getMySQLDBClass()->setTimeZone($timeZone);
        } else {
            (new \CodeJetter\core\ErrorHandler())->logError("Time zone: '{$timeZone}' is not valid.");
        }
    }

    /**
     * Check the framework requirements to run
     *
     * @throws \Exception
     */
    public function checkRequirements()
    {
        if (version_compare(phpversion(), '5.6', '<')) {
            throw new \Exception('Code Jetter needs at least PHP 5.6');
        }
    }
}
