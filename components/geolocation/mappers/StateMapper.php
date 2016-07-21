<?php

namespace CodeJetter\components\geolocation\mappers;

use CodeJetter\core\BaseMapper;
use CodeJetter\core\database\QueryMaker;
use CodeJetter\core\Registry;

class StateMapper extends BaseMapper
{
    public function add(array $inputs)
    {
        // TODO: No need to implement the body at this point
    }

    public function getStatesCities(
        array $criteria = [],
        array $fromColumns = [],
        $order = null,
        $start = 0,
        $limit = 0,
        $returnTotalNo = false,
        $excludeArchived = true
    ) {
        $cityTable = (new CityMapper())->getTable();

        $stateTable = $this->getTable();

        // By default do not return archived records
        if ($excludeArchived === true) {
            $criteria[] = [
                'column' => "{$stateTable}.archivedAt",
                'operator' => 'IS NULL'
            ];

            $criteria[] = [
                'column' => "{$stateTable}.live",
                'value' => '1'
            ];

            $criteria[] = [
                'column' => "{$cityTable}.archivedAt",
                'operator' => 'IS NULL'
            ];

            $criteria[] = [
                'column' => "{$cityTable}.live",
                'value' => '1'
            ];
        }

        $joins = [
            [
                'table' => $cityTable,
                'on' => [
                    "{$stateTable}.id",
                    "{$cityTable}.stateId"
                ]
            ]
        ];

        $query = (new QueryMaker($stateTable))->selectJoinQuery($joins, $criteria, $fromColumns, $order, $start, $limit);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            $st = $connection->prepare($query);

            // set last query
            $this->setLastQuery($st->queryString);

            // bind values
            $st = (new QueryMaker())->bindValues($st, $criteria, $start, $limit);
            $st->execute();

            $result = $st->fetchAll();

            if ($returnTotalNo == true) {
                $total = $this->countByCriteria($criteria);
                return ['result' => $result, 'total' => $total];
            } else {
                return $result;
            }
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }
}
