<?php

namespace CodeJetter\core\database;

use CodeJetter\core\utility\DateTimeUtility;

/**
 * Class MySQLDatabase
 * @package CodeJetter\core\database
 */
class MySQLDatabase extends BaseDatabase
{
    /**
     * @param $dbInfo
     *
     * @return \PDO
     */
    public function connect($dbInfo)
    {
        try {
            $this->setHost($dbInfo['host']);
            $this->setDatabaseName($dbInfo['database']);
            $this->setPort($dbInfo['port']);
            $this->setUser($dbInfo['user']);
            $this->setPass($dbInfo['pass']);

            $host = $this->getHost();
            $databaseName = $this->getDatabaseName();
            $user = $this->getUser();
            $pass = $this->getPass();

            $connection = new \PDO("mysql:host={$host};dbname={$databaseName};charset=utf8", $user, $pass);
            $connection->setAttribute(\PDO::ATTR_PERSISTENT, true);
            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->setConnection($connection);

            return $connection;
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * Set time zone in database
     *
     * @param $timeZone
     */
    public function setTimeZone($timeZone)
    {
        /**
         * For the time being setTimeZone is only called in App which does the validation
         * however, validation needs to be happened here since this can be independently
         */
        $timeZones = (new DateTimeUtility())->getTimeZones();

        if (in_array($timeZone, $timeZones)) {
            $connection = $this->getConnection();
            $offset = (new DateTimeUtility())->calculateTimeZoneOffset($timeZone);
            $connection->exec("SET time_zone = '{$offset}';");
        } else {
            (new \CodeJetter\core\ErrorHandler())->logError("Time zone: '{$timeZone}' is not valid.");
        }
    }
}
