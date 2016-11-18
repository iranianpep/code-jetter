<?php

namespace CodeJetter\core\database;

use CodeJetter\core\utility\MysqlUtility;

/**
 * Class QueryMaker
 * @package CodeJetter\core\database
 */
class QueryMaker
{
    private $tables;
    private $validComparisonOperators = ['LIKE', 'NOT LIKE', '=', '!=', '<>', '<', '<=', '>', '>=', '<=>', 'IS NOT',
        'IS', 'IS NOT NULL', 'IS NULL', 'IN', 'NOT IN'];
    private $validLogicalOperators = ['AND', 'OR', 'XOR', 'NOT'];

    /**
     * QueryMaker constructor.
     *
     * @param null $tables
     */
    public function __construct($tables = null)
    {
        if ($tables !== null) {
            if (is_array($tables)) {
                $this->setTables($tables);
            } else {
                // $tables is string, treat it as a table
                $this->addTable(
                    $tables,
                    [
                        'name' => $tables
                    ]
                );
            }
        }
    }

    /**
     * @param array $criteria
     * @param       $fromColumns
     * @param       $order
     * @param       $start
     * @param       $limit
     *
     * @return string
     * @throws \Exception
     */
    public function selectQuery(array $criteria = [], $fromColumns = '*', $order = '', $start = 0, $limit = 0)
    {
        // append from columns
        if (empty($fromColumns) && $fromColumns !== null) {
            // from all
            $fromColumns = '*';
        } elseif (is_array($fromColumns)) {
            $fromColumns = implode(', ', $fromColumns);
        }

        $query = $this->getSelectFromTables($fromColumns);

        // append where clause - criteria
        $query .= $this->where($criteria);

        // append order by if it is specified
        $query .= $this->orderBy($order);

        // append start and limit if they are specified
        $query .= $this->startLimit($start, $limit);

        $query .= ';';

        return $query;
    }

    /**
     * @param array $criteria
     * @param       $fieldsValues
     * @param       $start
     * @param       $limit
     *
     * @return string
     * @throws \Exception
     */
    public function updateQuery(array $criteria, $fieldsValues, $start, $limit)
    {
        if (empty($fieldsValues)) {
            throw new \Exception('fieldsValues cannot be empty in updateQuery function');
        }

        $query = "UPDATE {$this->getTable()['name']} SET ";

        // point to end of the array
        end($fieldsValues);

        // fetch key of the last element of the array.
        $LastFieldKey = key($fieldsValues);

        foreach ($fieldsValues as $fieldValueKey => $fieldValue) {
            // Do not append comma if it is the last element
            $comma = $LastFieldKey === $fieldValueKey ? '' : ', ';

            // This is to avoid converting null to an empty string
//            if ($fieldValue['value'] === null) {
//                $fieldValue['value'] = 'null';
//            }

            if (isset($fieldValue['bind']) && $fieldValue['bind'] === false) {
                $query .= "{$fieldValue['column']} = {$fieldValue['value']}{$comma}";
            } else {
                $placeholder = $this->preparePlaceholder($fieldValue['column']);
                $query .= "{$fieldValue['column']} = :{$placeholder}{$comma}";
            }
        }

        $query = rtrim($query);

        $query .= $this->where($criteria);
        $query .= $this->startLimit($start, $limit);
        $query .= ';';
        return $query;
    }

    /**
     * @param array $fieldsValues
     *
     * @return string
     * @throws \Exception
     */
    public function insertQuery(array $fieldsValues)
    {
        if (empty($fieldsValues)) {
            throw new \Exception('fieldsValues cannot be empty in insertQuery function');
        }

        $query = "INSERT INTO {$this->getTable()['name']}";

        $columns = [];
        $parameters = [];

        foreach ($fieldsValues as $fieldValueKey => $fieldValue) {
            // This is to avoid converting null to an empty string
//            if ($fieldValue['value'] === null) {
//                $fieldValue['value'] = 'null';
//            }

            $columns[] = "{$fieldValue['column']}";

            if (isset($fieldValue['bind']) && $fieldValue['bind'] === false) {
                $parameters[] = "{$fieldValue['value']}";
            } else {
                $placeholder = $this->preparePlaceholder($fieldValue['column']);
                $parameters[] = ":{$placeholder}";
            }
        }

        if (!empty($columns) && !empty($parameters)) {
            $query .= ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $parameters) . ');';
        }

        return $query;
    }

    /**
     * @param array $fieldsValuesCollection
     *
     * @return string
     * @throws \Exception
     */
    public function batchInsertQuery(array $fieldsValuesCollection)
    {
        if (empty($fieldsValuesCollection)) {
            throw new \Exception('fieldsValues cannot be empty in insertQuery function');
        }

        $query = "INSERT INTO {$this->getTable()['name']}";

        $parametersArray = [];
        foreach ($fieldsValuesCollection as $key => $fieldsValues) {
            // reset parameters
            $columns = [];
            $parameters = [];

            foreach ($fieldsValues as $fieldValueKey => $fieldValue) {
                // This is to avoid converting null to an empty string
//                if ($fieldValue['value'] === null) {
//                    $fieldValue['value'] = 'null';
//                }

                $columns[] = "{$fieldValue['column']}";

                if (isset($fieldValue['bind']) && $fieldValue['bind'] === false) {
                    $parameters[] = "{$fieldValue['value']}";
                } else {
                    $placeholder = $this->preparePlaceholder($fieldValue['column']);
                    $parameters[] = ":{$placeholder}{$key}";
                }
            }
            $parameters = implode(',', $parameters);
            $parametersArray[] = "({$parameters})";
        }

        if (!empty($columns) && !empty($parameters)) {
            $query .= ' (' . implode(',', $columns) . ') VALUES ' . implode(',', $parametersArray) . ';';
        }

        return $query;
    }

    /**
     * @param array $criteria
     * @param       $start
     * @param       $limit
     *
     * @return string
     * @throws \Exception
     */
    public function deleteQuery(array $criteria, $start, $limit)
    {
        $query = "DELETE FROM {$this->getTable()['name']}";
        $query .= $this->where($criteria);
        $query .= $this->startLimit($start, $limit);
        $query .= ';';
        return $query;
    }

    /**
     * @param array $criteria
     *
     * @return string
     * @throws \Exception
     */
    public function countQuery(array $criteria)
    {
        $query = $this->getSelectFromTables('COUNT(*)');
        $query .= $this->where($criteria);
        $query .= ';';
        return $query;
    }

    /**
     * @param array $criteria
     *
     * @return string
     * @throws \Exception
     */
    private function where(array $criteria)
    {
        if (!empty($criteria)) {
            $where = ' WHERE ';

            $counter = 1;
            $nested = [];
            foreach ($criteria as $aCriteria) {
                if (!empty($aCriteria['nested'])) {
                    // check placeholder is not already added
                    $before = isset($aCriteria['nested']['before']) ? "{$aCriteria['nested']['before']} " : '';
                    $after = isset($aCriteria['nested']['after']) ? " {$aCriteria['nested']['after']}" : '';

                    $placeholder = $before . '({' . $aCriteria['nested']['key'] . '})' . $after . ' ';
                    $placeholderExists = strpos($where, $placeholder);

                    // if placeholder does not exist append it to where clause
                    if ($placeholderExists === false) {
                        // add placeholder
                        $where .= $placeholder;
                    }
                }

                if (empty($aCriteria['column'])) {
                    throw new \Exception('Column name cannot be empty');
                }

                if (empty($aCriteria['operator'])) {
                    // If operator is not specified, consider '=' as the operator
                    $aCriteria['operator'] = '=';
                }

                if (!in_array($aCriteria['operator'], $this->validComparisonOperators)) {
                    throw new \Exception("'{$aCriteria['operator']}' is not a valid comparison operator");
                }

                if ($counter === 1 || !empty($before) || !empty($after)) {
                    $logicalOperator = '';
                } elseif (empty($aCriteria['logicalOperator'])) {
                    // counter is greater than 1 and $aCriteria['logicalOperator'] is empty, consider 'AND' as default
                    $logicalOperator = 'AND ';
                } else {
                    // counter is greater than 1 and $aCriteria['logicalOperator'] is NOT empty, validate it first
                    if (!in_array($aCriteria['logicalOperator'], $this->validLogicalOperators)) {
                        throw new \Exception("'{$aCriteria['logicalOperator']}' is not a valid logical operator");
                    }

                    $logicalOperator = "{$aCriteria['logicalOperator']} ";
                }

                $toBeAppended = "{$logicalOperator}{$aCriteria['column']} {$aCriteria['operator']} ";

                // Form the query for IN or NOT IN
                if ($aCriteria['operator'] === 'IN' || $aCriteria['operator'] === 'NOT IN') {
                    if (is_array($aCriteria['value'])) {
                        // value is array
                        $newParameters = [];
                        foreach ($aCriteria['value'] as $key => $value) {
                            $placeholder = $this->preparePlaceholder($aCriteria['column']);
                            $newParameters[] = ":{$placeholder}{$counter}{$key}";
                        }

                        $toBeAppended .= '('. implode(',', $newParameters) .') ';
                    } else {
                        // value is not array
                        $placeholder = $this->preparePlaceholder($aCriteria['column']);
                        $toBeAppended .= "(:{$placeholder}{$counter}) ";
                    }
                } else {
                    // IS NULL and IS NOT NULL do NOT need a parameter
                    if ($aCriteria['operator'] !== 'IS NULL' && $aCriteria['operator'] !== 'IS NOT NULL') {
                        $placeholder = $this->preparePlaceholder($aCriteria['column']);
                        $toBeAppended .= ":{$placeholder}{$counter} ";
                    }
                }

                if (empty($aCriteria['nested'])) {
                    $where .= $toBeAppended;
                } else {
                    // append it to the key in $nested
                    if (!isset($nested[$aCriteria['nested']['key']])) {
                        $nested[$aCriteria['nested']['key']] = $toBeAppended;
                    } else {
                        $nested[$aCriteria['nested']['key']] .= $toBeAppended;
                    }
                }

                $counter++;
            }

            if (!empty($nested)) {
                foreach ($nested as $aNestedKey => $aNestedValue) {
                    // find and replace $aNestedKey placeholder in where clause with the nested query
                    $where = str_replace('{' . $aNestedKey . '}', rtrim($aNestedValue), $where);
                }
            }

            return rtrim($where);
        } else {
            return '';
        }
    }

    /**
     * @param $field
     *
     * @return string
     */
    private function orderBy($field)
    {
        if (!empty($field)) {
            return " ORDER BY {$field}";
        }
    }

    /**
     * @param $field
     *
     * @return string
     */
    private function groupBy($field)
    {
        if (!empty($field)) {
            return " GROUP BY {$field}";
        }
    }

    /**
     * @param int $start
     * @param int $limit
     *
     * @return string
     */
    private function startLimit($start = 0, $limit = 0)
    {
        if (!empty($start) && !empty($limit)) {
            return ' LIMIT :start, :limit';
        } elseif (!empty($limit)) {
            return ' LIMIT :limit';
        }
    }

    /**
     * @param \PDOStatement $st
     * @param array         $criteria
     * @param int           $start
     * @param int           $limit
     * @param array         $fieldsValues is used for update query
     *
     * @return \PDOStatement
     */
    public function bindValues(\PDOStatement $st, array $criteria, $start = 0, $limit = 0, array $fieldsValues = [])
    {
        // bind criteria values
        $this->bindCriteria($st, $criteria);

        // bind field values
        if (!empty($fieldsValues)) {
            foreach ($fieldsValues as $fieldValue) {
                if (isset($fieldValue['bind']) && $fieldValue['bind'] === false) {
                    continue;
                }

                // This is to avoid converting null to an empty string
//                if ($fieldValue['value'] === null) {
//                    $fieldValue['value'] = 'null';
//                }

                // set the type to string if it is empty
                if (empty($fieldValue['type'])) {
                    $fieldValue['type'] = \PDO::PARAM_STR;
                }

                $placeholder = $this->preparePlaceholder($fieldValue['column']);
                $st->bindValue(':' . $placeholder, $fieldValue['value'], $fieldValue['type']);
            }
        }

        // bind start and limit if they are specified
        if (!empty($start) && !empty($limit)) {
            $st->bindValue(':start', (int) $start, \PDO::PARAM_INT);
            $st->bindValue(':limit', (int) $limit, \PDO::PARAM_INT);
        } elseif (!empty($limit)) {
            $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }

        return $st;
    }

    /**
     * @param \PDOStatement $st
     * @param array         $criteria
     *
     * @return \PDOStatement
     */
    private function bindCriteria(\PDOStatement $st, array $criteria)
    {
        // bind criteria values
        if (!empty($criteria)) {
            $counter = 0;
            foreach ($criteria as $aCriteria) {
                $counter++;

                if (empty($aCriteria['operator'])) {
                    // If operator is not specified, consider = as the operator
                    $aCriteria['operator'] = '=';
                }

                if ($aCriteria['operator'] === 'IS NULL' || $aCriteria['operator'] === 'IS NOT NULL') {
                    continue;
                }

                $placeholder = $this->preparePlaceholder($aCriteria['column']);
                if ($aCriteria['operator'] === 'IN' || $aCriteria['operator'] === 'NOT IN') {
                    if (is_array($aCriteria['value'])) {
                        // value is array
                        foreach ($aCriteria['value'] as $key => $value) {
                            // to override the automatic detection $aCriteria['type'] needs to be passed
                            $type = empty($aCriteria['type']) ? $this->detectParameterType($value) : $aCriteria['type'];
                            $st->bindValue(':' . $placeholder . $counter . $key, $value, $type);
                        }
                    } else {
                        // value is not array
                        // to override the automatic detection $aCriteria['type'] needs to be passed
                        $type = empty($aCriteria['type']) ? $this->detectParameterType($aCriteria['value']) : $aCriteria['type'];
                        $st->bindValue(':' . $placeholder . $counter, $aCriteria['value'], $type);
                    }
                } else {
                    // set the type to string if it is empty
                    if (empty($aCriteria['type'])) {
                        $aCriteria['type'] = \PDO::PARAM_STR;
                    }

                    $st->bindValue(':' . $placeholder . $counter, $aCriteria['value'], $aCriteria['type']);
                }
            }
        }

        return $st;
    }

    /**
     * @param \PDOStatement $st
     * @param array         $criteria
     * @param int           $start
     * @param int           $limit
     * @param array         $fieldsValuesCollection
     *
     * @return \PDOStatement
     */
    public function batchBindValues(
        \PDOStatement $st,
        array $criteria,
        $start = 0,
        $limit = 0,
        array $fieldsValuesCollection = []
    ) {
        if (!empty($fieldsValuesCollection)) {
            // bind criteria values
            $this->bindCriteria($st, $criteria);

            foreach ($fieldsValuesCollection as $key => $fieldsValues) {
                // bind field values
                if (!empty($fieldsValues)) {
                    foreach ($fieldsValues as $fieldValue) {
                        if (isset($fieldValue['bind']) && $fieldValue['bind'] === false) {
                            continue;
                        }

                        // This is to avoid converting null to an empty string
//                        if ($fieldValue['value'] === null) {
//                            $fieldValue['value'] = 'null';
//                        }

                        // set the type to string if it is empty
                        if (empty($fieldValue['type'])) {
                            $fieldValue['type'] = \PDO::PARAM_STR;
                        }

                        $placeholder = $this->preparePlaceholder($fieldValue['column']);
                        $st->bindValue(':' . $placeholder . $key, $fieldValue['value'], $fieldValue['type']);
                    }
                }
            }

            // bind start and limit if they are specified
            if (!empty($start) && !empty($limit)) {
                $st->bindValue(':start', (int) $start, \PDO::PARAM_INT);
                $st->bindValue(':limit', (int) $limit, \PDO::PARAM_INT);
            } elseif (!empty($limit)) {
                $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
            }

            return $st;
        }
    }

    /**
     * @param null $tableAlias
     *
     * @return mixed
     * @throws \Exception
     */
    public function getTable($tableAlias = null)
    {
        $tables = $this->getTables();
        if ($tableAlias === null) {
            return array_shift($tables);
        } else {
            if (array_key_exists($tableAlias, $tables)) {
                return $tables[$tableAlias];
            } else {
                throw new \Exception("Requested table does not exist for the alias: {$tableAlias}");
            }
        }
    }

    /**
     * @param array $tables
     */
    public function setTables(array $tables)
    {
        $this->tables = $tables;
    }

    /**
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param null $fromColumns
     *
     * @return string
     * @throws \Exception
     */
    public function getSelectFromTables($fromColumns = null)
    {
        $joinedSelect = [];
        $counter = 1;
        $from = '';

        if (empty($this->getTables())) {
            throw new \Exception('Tables cannot be empty');
        }

        foreach ($this->getTables() as $tableAlias => $table) {
            if ($counter === 1) {
                $from .= "`{$table['name']}` AS `{$tableAlias}`";
            } else {
                $from .= " JOIN `{$table['name']}` AS `{$tableAlias}`";

                if (empty($table['on']) || !is_array($table['on'])) {
                    throw new \Exception("join array must have 'on'");
                }

                $from .= ' ON ' . implode(' = ', $table['on']);
            }

            $columns = (new MysqlUtility())->getTableColumns($table['name']);

            if ($fromColumns === null && !empty($columns)) {
                foreach ($columns as $column) {
                    $joinedSelect[] = "`{$tableAlias}`.`{$column}` AS `{$tableAlias}.{$column}`";
                }
            }

            $counter++;
        }

        if ($fromColumns === null) {
            $fromColumns = implode(', ', $joinedSelect);
        } elseif (is_array($fromColumns)) {
            $fromColumns = implode(', ', $fromColumns);
        }

        return "SELECT {$fromColumns} FROM {$from}";
    }

    /**
     * @param $tableAlias
     * @param $table
     */
    public function addTable($tableAlias, $table)
    {
        $tables = $this->getTables();
        $tables[$tableAlias] = $table;
        $this->setTables($tables);
    }

    /**
     * @param $value
     *
     * @return int
     */
    private function detectParameterType($value)
    {
        return is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
    }

    /**
     * PDO placeholders can only be: [a-zA-Z0-9_]+
     *
     * @param $placeholder
     *
     * @return mixed
     */
    private function preparePlaceholder($placeholder)
    {
        $placeholder = str_replace('`', '', $placeholder);
        return preg_replace("/[^a-zA-Z0-9_]/", '_', $placeholder);
    }
}
