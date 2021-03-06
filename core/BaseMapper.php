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
 * Class BaseMapper.
 */
abstract class BaseMapper extends Base implements ICrud
{
    protected $database;
    protected $table;
    protected $modelName;
    protected $lastQuery;
    protected $component;

    /**
     * @param array $options
     *
     * @return array
     */
    abstract public function getDefinedInputs($action = null, array $includingInputs = [], array $excludingInputs = []);

    /**
     * @param array $inputs
     * @param array $definedInputs
     * @param null  $case
     *
     * @return mixed
     */
    abstract public function getFieldsValues(array $inputs, array $definedInputs = [], $action = null);

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
            $mapperTable = $this->getTableNameByClassName($className);
        }

        $this->setTable($mapperTable);
        $this->setDatabase($database);
        $this->setComponent($component);
    }

    /**
     * Return table name WITHOUT suffix or prefix by model OR mapper class name
     * For example, AdminUser table is admin_users.
     *
     * @param $className
     *
     * @return string
     */
    public function getTableNameByClassName($className)
    {
        if (empty(trim($className))) {
            $className = $this->getModelName(false);
        }

        // convert camel case to snake case
        $stringUtility = new StringUtility();
        $snakeCaseClassName = strtolower($stringUtility->camelCaseToSnakeCase($className));

        // remove mapper from the end of the string if exist
        $snakeCaseClassName = preg_replace('#_mapper$#', '', $snakeCaseClassName);

        return $stringUtility->singularToPlural($snakeCaseClassName);
    }

    /**
     * Return class (model) name by table name
     * For example, cj_jobs model is Job.
     *
     * @param      $tableName
     * @param null $baseNamespace
     * @param null $tablePrefix
     * @param null $tableSuffix
     *
     * @return bool|mixed|string
     */
    public function getClassNameByTableName($tableName, $baseNamespace = null, $tablePrefix = null, $tableSuffix = null)
    {
        $trimmedTableName = $this->removeTablePrefixAndSuffix($tableName, $tablePrefix, $tableSuffix);

        $stringUtility = new StringUtility();
        $className = $stringUtility->pluralToSingular($trimmedTableName);

        // snakeCase to camelCase
        $className = $stringUtility->snakeCaseToCamelCase($className);

        if ($baseNamespace !== null) {
            $className = "{$baseNamespace}\\{$className}";
        }

        return $className;
    }

    /**
     * Generate a table alias by removing prefix and suffix.
     *
     * @param null $tableName
     * @param null $tablePrefix
     * @param null $tableSuffix
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getTableAlias($tableName = null, $tablePrefix = null, $tableSuffix = null)
    {
        if (empty(trim($tableName))) {
            $tableName = $this->getTable();
        }

        $tableName = $this->removeTablePrefixAndSuffix($tableName, $tablePrefix, $tableSuffix);

        return strtolower($tableName);
    }

    /**
     * Remove table name prefix and suffix if they exist.
     *
     * @param null $tableName
     * @param null $tablePrefix
     * @param null $tableSuffix
     *
     * @throws \Exception
     *
     * @return bool|mixed|null|string
     */
    public function removeTablePrefixAndSuffix($tableName = null, $tablePrefix = null, $tableSuffix = null)
    {
        if (empty(trim($tableName))) {
            $tableName = $this->getTable();
        }

        // get the prefix
        if ($tablePrefix === null) {
            $defaultDbInfo = $this->getDefaultDbInfo();

            if (isset($defaultDbInfo['tablePrefix'])) {
                $tablePrefix = $defaultDbInfo['tablePrefix'];
            }
        }

        // get the suffix
        if ($tableSuffix === null) {
            $defaultDbInfo = $this->getDefaultDbInfo();

            if (isset($defaultDbInfo['tableSuffix'])) {
                $tableSuffix = $defaultDbInfo['tableSuffix'];
            }
        }

        $stringUtility = new StringUtility();

        // drop the prefix
        if (!empty($tablePrefix)) {
            $tableName = $stringUtility->removePrefix($tableName, $tablePrefix);
        }

        // drop the suffix
        if (!empty($tableSuffix)) {
            $tableName = $stringUtility->removeSuffix($tableName, $tableSuffix);
        }

        return $tableName;
    }

    /**
     * @param      $id
     * @param bool $excludeArchived
     *
     * @throws \Exception
     *
     * @return Output
     */
    public function getOneById($id, $excludeArchived = true)
    {
        $output = new Output();
        /*
         * start validating
         */
        try {
            $rules = [
                new ValidatorRule('required'),
                new ValidatorRule('id'),
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
         * finish validating.
         */
        $criteria = [
            [
                'column' => 'id',
                'value'  => $id,
                'type'   => \PDO::PARAM_INT,
            ],
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
     * @param int   $fetchStyle
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getAll(
        array $criteria = [],
        array $fromColumns = [],
        $order = null,
        $start = 0,
        $limit = 0,
        $returnTotalNo = false,
        $excludeArchived = true,
        $fetchStyle = \PDO::FETCH_CLASS
    ) {
        // By default do not return archived records
        if ($excludeArchived === true) {
            $criteria = array_merge($criteria, $this->getExcludeArchivedCriteria());
        }

        // generate the query
        $query = (new QueryMaker($this->getTable()))->selectQuery($criteria, $fromColumns, $order, $start, $limit);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            $statement = $connection->prepare($query);

            // set last query
            $this->setLastQuery($statement->queryString);

            // bind values
            $statement = (new QueryMaker())->bindValues($statement, $criteria, $start, $limit);
            $statement->execute();

            if ($fetchStyle === \PDO::FETCH_CLASS) {
                $result = $statement->fetchAll(\PDO::FETCH_CLASS, $this->getModelName());
            } else {
                $result = $statement->fetchAll($fetchStyle);
            }

            if ($returnTotalNo == true) {
                $total = $this->countByCriteria($criteria);
                //$total = $connection->query("SELECT COUNT(*) FROM {$this->getTable()}")->fetchColumn();
                return ['result' => $result, 'total' => $total];
            }

            return $result;
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    public function getExcludeArchivedCriteria($table = '')
    {
        $columnPrefix = !empty($table) ? "`{$table}`." : '';

        return [
            [
                'column'   => "{$columnPrefix}`archivedAt`",
                'operator' => 'IS NULL',
            ],
            [
                'column' => "{$columnPrefix}`live`",
                'value'  => '1',
            ],
        ];
    }

    /**
     * @param array $criteria
     * @param array $fromColumns
     * @param bool  $excludeArchived
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getOne(array $criteria = [], array $fromColumns = [], $excludeArchived = true)
    {
        // By default do not return archived records
        if ($excludeArchived === true) {
            $criteria = array_merge($criteria, $this->getExcludeArchivedCriteria());
        }

        // generate the query
        $query = (new QueryMaker($this->getTable()))->selectQuery($criteria, $fromColumns, null, 0, 1);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            $statement = $connection->prepare($query);

            // set last query
            $this->setLastQuery($statement->queryString);

            // bind values
            $statement = (new QueryMaker())->bindValues($statement, $criteria, 0, 1);
            $statement->execute();

            return $statement->fetchObject($this->getModelName());
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array $criteria
     * @param array $fieldsValues
     * @param bool  $excludeArchived
     *
     * @throws \Exception
     *
     * @return int
     */
    public function updateOne(array $criteria, array $fieldsValues, $excludeArchived = true)
    {
        // 'self' is used instead of 'this' to avoid update function overridden in subclasses
        return self::update($criteria, [], $fieldsValues, 1, $excludeArchived);
    }

    /**
     * @param array $criteria
     * @param array $fieldsValues
     * @param int   $limit
     * @param bool  $excludeArchived
     *
     * @throws \Exception
     *
     * @return int
     */
    public function update(
        array $criteria,
        array $inputs,
        array $fieldsValues,
        $limit = 0,
        $extraDefinedInputs = [],
        $excludeArchived = true,
        $batchAction = false
    ) {

        // TODO validation for those who use this function instead of overridden one?

        // By default do not update archived records
        if ($excludeArchived === true) {
            $criteria = array_merge($criteria, $this->getExcludeArchivedCriteria());
        }

        // generate the query
        $query = (new QueryMaker($this->getTable()))->updateQuery($criteria, $fieldsValues, 0, $limit);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());

            if (!$connection instanceof \PDO) {
                throw new \Exception('Connection must be an instance of PDO class');
            }

            $connection->beginTransaction();
            $statement = $connection->prepare($query);

            // set last query
            $this->setLastQuery($statement->queryString);

            // bind values
            $statement = (new QueryMaker())->bindValues($statement, $criteria, 0, $limit, $fieldsValues);
            $statement->execute();
            $connection->commit();

            return $statement->rowCount();
        } catch (\PDOException $e) {
            $connection->rollBack();
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array $fieldsValues
     *
     * @throws \Exception
     *
     * @return mixed
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
            $statement = $connection->prepare($query);

            // set last query
            $this->setLastQuery($statement->queryString);

            // bind values
            $statement = (new QueryMaker())->bindValues($statement, [], 0, 0, $fieldsValues);
            $statement->execute();
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
     * @throws \Exception
     *
     * @return string
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
            $statement = $connection->prepare($query);

            // set last query
            $this->setLastQuery($statement->queryString);

            // bind values
            $statement = (new QueryMaker())->batchBindValues($statement, [], 0, 0, $fieldsValuesCollection);
            $statement->execute();
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
     * @throws \Exception
     *
     * @return bool
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
     * @throws \Exception
     *
     * @return bool
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
            $statement = $connection->prepare($query);

            // set last query
            $this->setLastQuery($statement->queryString);

            // bind values
            $statement = (new QueryMaker())->bindValues($statement, $criteria, $start, $limit);
            $statement->execute();

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
     * @throws \Exception
     *
     * @return int
     */
    public function safeDelete(array $criteria, $limit = 0)
    {
        $fieldsValues = [
            'archivedAt' => [
                'column' => 'archivedAt',
                'value'  => 'NOW()',
                'bind'   => false,
            ],
            'live' => [
                'column' => 'live',
                'value'  => null,
                'bind'   => false,
            ],
        ];

        return $this->update($criteria, [], $fieldsValues, $limit, [], true, true);
    }

    /**
     * Change the archived to 1 and keep the record.
     *
     * @param array $criteria
     *
     * @return int
     */
    public function safeDeleteOne(array $criteria)
    {
        //$this->safeDelete($criteria, 1);
        // TODO check if NULL needs to be binded in PDO
        $fieldsValues = [
            'archivedAt' => [
                'column' => 'archivedAt',
                'value'  => 'NOW()',
                'bind'   => false,
            ],
            'live' => [
                'column' => 'live',
                'value'  => null,
                'bind'   => false,
            ],
        ];

        return $this->updateOne($criteria, $fieldsValues);
    }

    /**
     * count total records in the table without considering any criteria.
     *
     * @throws \Exception
     *
     * @return int
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
     * @throws \Exception
     *
     * @return int
     */
    public function countByCriteria(array $criteria, $excludeArchived = true)
    {
        // By default do not return archived records
        if ($excludeArchived === true) {
            $criteria = array_merge($criteria, $this->getExcludeArchivedCriteria());
        }

        $query = (new QueryMaker($this->getTable()))->countQuery($criteria);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            $statement = $connection->prepare($query);

            // set last query
            $this->setLastQuery($statement->queryString);

            // bind values
            $statement = (new QueryMaker())->bindValues($statement, $criteria);
            $statement->execute();

            return (int) $statement->fetchColumn();
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * Return enum values of a column
     * This function is missing setLastQuery.
     *
     * @param $column
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function getEnumValues($column)
    {
        return (new MysqlUtility())->getEnumValues($column, $this->getTable(), $this->getDatabase());
    }

    /**
     * Return columns of a table
     * This function is missing setLastQuery.
     *
     * @throws \Exception
     *
     * @return array|bool
     */
    public function getTableColumns()
    {
        return (new MysqlUtility())->getTableColumns($this->getTable(), $this->getDatabase());
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getTable()
    {
        if (empty($this->table)) {
            throw new \Exception('Table name has not been specified for this mapper');
        }

        $defaultDbInfo = $this->getDefaultDbInfo();

        $table = $this->table;

        // append suffix if specified
        if (isset($defaultDbInfo['tableSuffix'])) {
            $table = $table.$defaultDbInfo['tableSuffix'];
        }

        // append prefix if specified
        if (isset($defaultDbInfo['tablePrefix'])) {
            $table = $defaultDbInfo['tablePrefix'].$table;
        }

        return $table;
    }

    /**
     * @throws \Exception
     *
     * @return mixed
     */
    public function getDefaultDbInfo()
    {
        $defaultDb = Registry::getConfigClass()->get('defaultDB');
        $databases = Registry::getConfigClass()->get('databases');

        return $databases[$defaultDb];
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
        if (!empty($this->modelName)) {
            return $this->modelName;
        }

        // get last part of the namespace which is mapper class name
        $classNameParts = explode('\\', get_class($this));
        $classNamePartsEnd = end($classNameParts);

        // remove 'Mapper' from the end of mapper class name
        $modelName = preg_replace('#Mapper$#', '', $classNamePartsEnd);

        if ($fullPath === true) {
            // remove starting from mappers, replace the rest with models and model name
            $pattern = "#mappers\\\\{$classNamePartsEnd}$#";
            $this->setModelName(preg_replace($pattern, '', get_class($this)).'models\\'.$modelName);

            return $this->modelName;
        }

        $this->setModelName($modelName);

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

    /**
     * TODO enhance this function to be able to construct $tables array, if nothing is passed
     * Map rows to the relevant object.
     *
     * @param array $tables Contains table alias / name as the key for each array element. Each element must have class
     * @param array $rows   Table rows
     *
     * @throws \Exception
     *
     * @return array
     */
    public function mapRowsToObjects(array $rows, array $tables = [])
    {
        /**
         * Initialize $mappedObjects.
         */
        $mappedObjects = [];

        if (!empty($rows)) {
            foreach ($rows as $row) {
                if (empty($row)) {
                    continue;
                }

                $mappedObjects[] = $this->mapRowToObject($row, $tables);
            }
        }

        return $mappedObjects;
    }

    /**
     * Map row to the relevant object.
     *
     * @param array $tables Contains table alias / name as the key for each array element. Each element must have class
     * @param array $row    Table row
     *
     * @throws \Exception
     *
     * @return array
     */
    private function mapRowToObject(array $row, array $tables = [])
    {
        /**
         * Initialize $mappedObject.
         */
        $mappedObject = [];

        if (empty($tables)) {
            // TODO can be used to generate $tables using the current mapper table
        }

        foreach ($tables as $tableAlias => $table) {
            if (empty($table['class'])) {
                throw new \Exception('Class must be specified for a table to map a row to its object');
            }

            $mappedObject[$tableAlias] = new $table['class']();
        }

        foreach ($row as $key => $value) {
            $keySegments = explode('.', $key);

            if (isset($keySegments[0]) && array_key_exists($keySegments[0], $mappedObject)) {
                // call setProperty() on $mappedObjects[$keySegments[0]] object
                $mappedObject[$keySegments[0]]->{'set'.ucwords($keySegments[1])}($value);
            }
        }

        return $mappedObject;
    }
}
