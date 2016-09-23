<?php

namespace CodeJetter\core\utility;

use CodeJetter\core\io\Request;
use CodeJetter\core\Registry;
use TableGenerator\HeadCell;

/**
 * Class MysqlUtility
 * @package CodeJetter\core\utility
 */
class MysqlUtility
{
    /**
     * @param      $table
     * @param null $database
     *
     * @return array|bool
     */
    public function getTableColumns($table, $database = null)
    {
        try {
            $connection = Registry::getMySQLDBClass()->getConnection($database);
            $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :table";
            $st = $connection->prepare($query);
            $st->bindValue(':table', $table, \PDO::PARAM_STR);
            $st->execute();
            $columns = $st->fetchAll(\PDO::FETCH_ASSOC);
            return !empty($columns) ? array_column($columns, 'COLUMN_NAME') : false;
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param      $column
     * @param      $table
     * @param null $database
     *
     * @return bool
     */
    public function getEnumValues($column, $table, $database = null)
    {
        try {
            $connection = Registry::getMySQLDBClass()->getConnection($database);

            // bind table name?
            $query = "SHOW COLUMNS FROM `{$table}` LIKE '{$column}'";

            $st = $connection->prepare($query);

            $st->execute();

            $columnInfo = $st->fetch();

            if (!empty($columnInfo)) {
                $values = $columnInfo['Type'];
                /**
                 * extract enums e.g. array ( 0 => 'active', 1 => 'inactive', 2 => 'suspended', )
                 * from 'enum('active','inactive','suspended')'
                 */
                preg_match_all("/'(.*?)'/", $values, $enumValuesArray);

                return $enumValuesArray[1];
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array  $listHeaders
     * @param null   $query
     * @param string $requestMethod
     *
     * @return array
     */
    public function generateSearchCriteria(array $listHeaders, $query = null, $requestMethod = 'GET')
    {
        if ($query === null) {
            $config = Registry::getConfigClass();
            $queryKey = $config->get('list')['query'];

            $queryInput = (new Request())->getInputs([$queryKey], $requestMethod);
            if (isset($queryInput[$queryKey])) {
                $query = $queryInput[$queryKey];
            }
        }

        if (empty($query)) {
            return [];
        }

        $criteria = [];
        foreach ($listHeaders as $listHeader) {
            if (!$listHeader instanceof HeadCell) {
                continue;
            }

            // on purpose === true is not used, since by default we want a header to be searchable
            if ($listHeader->isSearchable() !== false) {
                switch ($listHeader->getSearchPattern()) {
                    case 'q':
                        $searchQuery = $query;
                        break;
                    case '*q':
                        $searchQuery = "%{$query}";
                        break;
                    case 'q*':
                        $searchQuery = "{$query}%";
                        break;
                    case '*q*':
                        $searchQuery = "%{$query}%";
                        break;
                    default:
                        $searchQuery = "%{$query}%";
                }

                $criteria[] = [
                    'logicalOperator' => 'OR',
                    'column' => $listHeader->getAlias(),
                    'value' => $searchQuery,
                    'operator' => 'LIKE',
                    'nested' => [
                        'key' => 'search'
                    ]
                ];
            }
        }

        return $criteria;
    }
}
