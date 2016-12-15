<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 10/06/15
 * Time: 11:35 PM.
 */

namespace CodeJetter\core\database;

use CodeJetter\core\Registry;

/**
 * Class BaseDatabase.
 */
abstract class BaseDatabase
{
    private $database;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $databaseName;
    private $connection;
    private $dbInfo;

    /**
     * BaseDatabase constructor.
     *
     * @param null $database
     */
    public function __construct($database = null)
    {
        if ($database === null) {
            // db is not specified, get the default one
            $database = Registry::getConfigClass()->get('defaultDB');
        }

        $this->setDatabase($database);
    }

    /**
     * @param $dbInfo
     *
     * @return mixed
     */
    abstract public function connect($dbInfo);

    /**
     * @param $timeZone
     *
     * @return mixed
     */
    abstract public function setTimeZone($timeZone);

    /**
     * set database - This is referring to database reference in config.
     *
     * @param string $database
     *
     * @throws \Exception
     */
    public function setDatabase($database)
    {
        $databases = Registry::getConfigClass()->get('databases');

        if (!array_key_exists($database, $databases)) {
            throw new \Exception('Database is not valid');
        }

        $this->database = $database;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param $databaseName
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * @param null $database
     *
     * @throws \Exception
     *
     * @return \PDO
     */
    public function getConnection($database = null)
    {
        // reset the connection if $database is specified
        if (!isset($this->connection) || $database !== null) {
            if ($database !== null) {
                $this->setDatabase($database);
            }

            $this->connection = $this->connect($this->getDbInfo());
        }

        return $this->connection;
    }

    /**
     * @param string $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param string $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    /**
     * @return array
     */
    public function getDbInfo()
    {
        if (!isset($this->dbInfo)) {
            $database = $this->getDatabase();
            $databases = Registry::getConfigClass()->get('databases');

            $this->dbInfo = $databases[$database];
        }

        return $this->dbInfo;
    }

    /**
     * @param array $dbInfo
     */
    public function setDbInfo(array $dbInfo)
    {
        $this->dbInfo = $dbInfo;
    }
}
