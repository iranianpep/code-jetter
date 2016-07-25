<?php

namespace CodeJetter\core;

use CodeJetter\core\database\QueryMaker;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\MysqlUtility;
use CodeJetter\core\utility\StringUtility;

/**
 * Class BaseMapper
 * @package CodeJetter\core
 */
abstract class BaseMapper implements ICrud
{
    protected $database;
    protected $table;
    protected $modelName;
    protected $lastQuery;
    protected $component;

    /**
     * BaseMapper constructor.
     *
     * @param null $database
     * @param      $component
     */
    public function __construct($database = null, $component = null)
    {
        // TODO this needs to be in its own function
        $className = (new StringUtility())->getClassNameFromNamespace(get_called_class());
        $mapperTableRelations = Registry::getConfigClass()->get('mapperTableRelations');

        $mapperTable = isset($mapperTableRelations[$className]) ? $mapperTableRelations[$className] : '';

        if (empty($mapperTable)) {
            // mapper table is not specified in the config file, generate the name automatically

            // convert camel case to snake case
            $snakeCaseClassName = strtolower((new StringUtility())->camelCaseToSnakeCase($className));

            // remove mapper from the end of the string
            $snakeCaseClassName = preg_replace('#_mapper$#', '', $snakeCaseClassName);

            // replace 'y' with 'ie' if 'y' is the last character
            if (substr($snakeCaseClassName, -1) === 'y') {
                $snakeCaseClassName = substr_replace($snakeCaseClassName, 'ie', -1);
            }

            $mapperTable = $snakeCaseClassName . 's';
        }

        $this->setTable($mapperTable);
        $this->setDatabase($database);
        $this->setComponent($component);
    }

    /**
     * @param      $id
     * @param bool $excludeArchived
     *
     * @return Output
     * @throws \Exception
     */
    public function getOneById($id, $excludeArchived = true)
    {
        $output = new Output();
        /**
         * start validating
         */
        try {
            $rules = [
                new ValidatorRule('required'),
                new ValidatorRule('id')
            ];

            $idInput = new Input('id', $rules);

            $validatorOutput = (new Validator([$idInput], ['id' => $id]))->validate();

            if ($validatorOutput->getSuccess() !== true) {
                $output->setSuccess(false);
                $output->setMessages($validatorOutput->getMessages());
                return $output;
            }
        } catch (\Exception $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
        /**
         * finish validating
         */

        $criteria = [
            [
                'column' => 'id',
                'value' => $id,
                'type' => \PDO::PARAM_INT
            ]
        ];

        try {
            $output->setSuccess(true);
            $output->setData($this->getOne($criteria, [], $excludeArchived));
            return $output;
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array $criteria
     * @param array $fromColumns
     * @param null  $order
     * @param int   $start
     * @param int   $limit
     * @param bool  $returnTotalNo
     * @param bool  $excludeArchived
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getAll(
        array $criteria = [],
        array $fromColumns = [],
        $order = null,
        $start = 0,
        $limit = 0,
        $returnTotalNo = false,
        $excludeArchived = true
    ) {
        // By default do not return archived records
        if ($excludeArchived === true) {
            $criteria[] = [
                'column' => 'archivedAt',
                'operator' => 'IS NULL'
            ];

            $criteria[] = [
                'column' => 'live',
                'value' => '1'
            ];
        }

        // generate the query
        $query = (new QueryMaker($this->getTable()))->selectQuery($criteria, $fromColumns, $order, $start, $limit);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            $st = $connection->prepare($query);

            // set last query
            $this->setLastQuery($st->queryString);

            // bind values
            $st = (new QueryMaker())->bindValues($st, $criteria, $start, $limit);
            $st->execute();

            $result = $st->fetchAll(\PDO::FETCH_CLASS, $this->getModelName());

            if ($returnTotalNo == true) {
                $total = $this->countByCriteria($criteria);
                //$total = $connection->query("SELECT COUNT(*) FROM {$this->getTable()}")->fetchColumn();
                return ['result' => $result, 'total' => $total];
            } else {
                return $result;
            }
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array $criteria
     * @param array $fromColumns
     * @param bool  $excludeArchived
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getOne(array $criteria = [], array $fromColumns = [], $excludeArchived = true)
    {
        // By default do not return archived records
        if ($excludeArchived === true) {
            $criteria[] = [
                'column' => 'archivedAt',
                'operator' => 'IS NULL'
            ];

            $criteria[] = [
                'column' => 'live',
                'value' => '1'
            ];
        }

        // generate the query
        $query = (new QueryMaker($this->getTable()))->selectQuery($criteria, $fromColumns, null, 0, 1);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            $st = $connection->prepare($query);

            // set last query
            $this->setLastQuery($st->queryString);

            // bind values
            $st = (new QueryMaker())->bindValues($st, $criteria, 0, 1);
            $st->execute();

            return $st->fetchObject($this->getModelName());
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array $criteria
     * @param array $fieldsValues
     * @param bool  $excludeArchived
     *
     * @return int
     * @throws \Exception
     */
    public function updateOne(array $criteria, array $fieldsValues, $excludeArchived = true)
    {
        // 'self' is used instead of 'this' to avoid update function overridden in subclasses
        return self::update($criteria, $fieldsValues, 1, $excludeArchived);
    }

    /**
     * @param array $criteria
     * @param array $fieldsValues
     * @param int   $limit
     * @param bool  $excludeArchived
     *
     * @return int
     * @throws \Exception
     */
    public function update(array $criteria, array $fieldsValues, $limit = 0, $excludeArchived = true)
    {

        // TODO validation for those who use this function instead of overridden one?

        // By default do not return archived records
        if ($excludeArchived === true) {
            $criteria[] = [
                'column' => 'archivedAt',
                'operator' => 'IS NULL'
            ];

            $criteria[] = [
                'column' => 'live',
                'value' => '1'
            ];
        }

        // generate the query
        $query = (new QueryMaker($this->getTable()))->updateQuery($criteria, $fieldsValues, 0, $limit);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());

            if (!$connection instanceof \PDO) {
                throw new \Exception('Connection must be an instance of PDO class');
            }

            $connection->beginTransaction();
            $st = $connection->prepare($query);

            // set last query
            $this->setLastQuery($st->queryString);

            // bind values
            $st = (new QueryMaker())->bindValues($st, $criteria, 0, $limit, $fieldsValues);
            $st->execute();
            $connection->commit();

            return $st->rowCount();
        } catch (\PDOException $e) {
            $connection->rollBack();
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array $fieldsValues
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function insertOne(array $fieldsValues)
    {
        // generate the query
        $query = (new QueryMaker($this->getTable()))->insertQuery($fieldsValues);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            if (!$connection instanceof \PDO) {
                throw new \Exception('Connection must be an instance of PDO class');
            }

            $connection->beginTransaction();
            $st = $connection->prepare($query);

            // set last query
            $this->setLastQuery($st->queryString);

            // bind values
            $st = (new QueryMaker())->bindValues($st, [], 0, 0, $fieldsValues);
            $st->execute();
            $lastInsertedId = $connection->lastInsertId();
            $connection->commit();

            return $lastInsertedId;
        } catch (\PDOException $e) {
            $connection->rollBack();
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array $fieldsValuesCollection
     *
     * @return string
     * @throws \Exception
     */
    public function batchInsert(array $fieldsValuesCollection)
    {
        // generate the query
        $query = (new QueryMaker($this->getTable()))->batchInsertQuery($fieldsValuesCollection);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            if (!$connection instanceof \PDO) {
                throw new \Exception('Connection must be an instance of PDO class');
            }

            $connection->beginTransaction();
            $st = $connection->prepare($query);

            // set last query
            $this->setLastQuery($st->queryString);

            // bind values
            $st = (new QueryMaker())->batchBindValues($st, [], 0, 0, $fieldsValuesCollection);
            $st->execute();
            $lastInsertedId = $connection->lastInsertId();
            $connection->commit();

            return $lastInsertedId;
        } catch (\PDOException $e) {
            $connection->rollBack();
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array $criteria
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteOne(array $criteria = [])
    {
        return $this->delete($criteria, 0, 1);
    }

    /**
     * @param array $criteria
     * @param int   $start
     * @param int   $limit
     *
     * @return bool
     * @throws \Exception
     */
    public function delete(array $criteria = [], $start = 0, $limit = 0)
    {
        // generate the query
        $query = (new QueryMaker($this->getTable()))->deleteQuery($criteria, $start, $limit);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            if (!$connection instanceof \PDO) {
                throw new \Exception('Connection must be an instance of PDO class');
            }

            $connection->beginTransaction();
            $st = $connection->prepare($query);

            // set last query
            $this->setLastQuery($st->queryString);

            // bind values
            $st = (new QueryMaker())->bindValues($st, $criteria, $start, $limit);
            $st->execute();
            return $connection->commit();
        } catch (\PDOException $e) {
            $connection->rollBack();
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array $criteria
     * @param int   $limit
     *
     * @return int
     * @throws \Exception
     */
    public function safeDelete(array $criteria, $limit = 0)
    {
        $fieldsValues = [
            'archivedAt' => [
                'column' => 'archivedAt',
                'value' => 'NOW()',
                'bind' => false],
            'live' => [
                'column' => 'live',
                'value' => null,
                'bind' => false]
        ];

        return $this->update($criteria, $fieldsValues, $limit);
    }

    /**
     * Change the archived to 1 and keep the record
     *
     * @param array $criteria
     *
     * @return int
     */
    public function safeDeleteOne(array $criteria)
    {
        // TODO check if NULL needs to be binded in PDO
        $fieldsValues = [
            [
                'column' => 'archivedAt',
                'value' => 'NOW()',
                'bind' => false
            ],
            [
                'column' => 'live',
                'value' => null,
                'bind' => false
            ]
        ];

        return $this->updateOne($criteria, $fieldsValues);
    }

    /**
     * count total records in the table without considering any criteria
     *
     * @return int
     *
     * @throws \Exception
     */
    public function countAll()
    {
        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            return (int) $connection->query("SELECT COUNT(*) FROM {$this->getTable()}")->fetchColumn();
        } catch (\Exception $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array $criteria
     *
     * @return int
     *
     * @throws \Exception
     */
    public function countByCriteria(array $criteria, $excludeArchived = true)
    {
        // By default do not return archived records
        if ($excludeArchived === true) {
            $criteria[] = [
                'column' => 'archivedAt',
                'operator' => 'IS NULL'
            ];

            $criteria[] = [
                'column' => 'live',
                'value' => '1'
            ];
        }

        $query = (new QueryMaker($this->getTable()))->countQuery($criteria);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            $st = $connection->prepare($query);

            // set last query
            $this->setLastQuery($st->queryString);

            // bind values
            $st = (new QueryMaker())->bindValues($st, $criteria);
            $st->execute();

            return (int) $st->fetchColumn();
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * Return enum values of a column
     * This function is missing setLastQuery
     *
     * @param $column
     *
     * @return bool
     * @throws \Exception
     */
    public function getEnumValues($column)
    {
        return (new MysqlUtility())->getEnumValues($column, $this->getTable(), $this->getDatabase());
    }

    /**
     * Return columns of a table
     * This function is missing setLastQuery
     *
     * @return array|bool
     * @throws \Exception
     */
    public function getTableColumns()
    {
        return (new MysqlUtility())->getTableColumns($this->getTable(), $this->getDatabase());
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getTable()
    {
        if (empty($this->table)) {
            throw new \Exception('Table name has not been specified for this mapper');
        }

        // append suffix if specified
        $defaultDb = Registry::getConfigClass()->get('defaultDB');
        $databases = Registry::getConfigClass()->get('databases');
        $defaultDbInfo = $databases[$defaultDb];

        if (isset($defaultDbInfo['tableSuffix'])) {
            return $this->table . $defaultDbInfo['tableSuffix'];
        } elseif (isset($defaultDbInfo['tablePrefix'])) {
            return $defaultDbInfo['tablePrefix'] . $this->table;
        } else {
            return $this->table;
        }
    }

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @param string $modelName
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    /**
     * @param bool $fullPath
     *
     * @return mixed
     */
    public function getModelName($fullPath = true)
    {
        if (empty($this->modelName)) {
            // get last part of the namespace which is mapper class name
            $classNameParts = explode('\\', get_class($this));
            $classNamePartsEnd = end($classNameParts);

            // remove 'Mapper' from the end of mapper class name
            $modelName = preg_replace('#Mapper$#', '', $classNamePartsEnd);

            if ($fullPath === true) {
                // remove starting from mappers, replace the rest with models and model name
                $pattern = "#mappers\\\\{$classNamePartsEnd}$#";
                $this->setModelName(preg_replace($pattern, '', get_class($this)) . 'models\\' . $modelName);
            } else {
                $this->setModelName($modelName);
            }
        }

        return $this->modelName;
    }

    /**
     * @return string
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    /**
     * @param string $lastQuery
     */
    public function setLastQuery($lastQuery)
    {
        $this->lastQuery = $lastQuery;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @param string $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }
}
