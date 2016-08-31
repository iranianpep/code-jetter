<?php

namespace CodeJetter\components\geolocation\mappers;

use CodeJetter\core\BaseMapper;
use CodeJetter\core\database\QueryMaker;
use CodeJetter\core\Registry;

class StateMapper extends BaseMapper
{
    public function add(array $inputs, array $fieldsValues = [], $additionalDefinedInputs = [])
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

            $result = $st->fetchAll(\PDO::FETCH_ASSOC);

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

    public function getStatesCitiesGroupedByStates(
        array $criteria = [],
        $order = null,
        $start = 0,
        $limit = 0,
        $excludeArchived = true
    ) {
        $stateTable = $this->getTable();
        $cityTable = (new CityMapper())->getTable();

        $fromColumns = [
            "{$stateTable}.name AS state",
            "{$cityTable}.name AS city",
            "{$cityTable}.id AS cityId",
        ];

        $stateCities = $this->getStatesCities($criteria, $fromColumns, $order, $start, $limit, false, $excludeArchived);

        $groupedCities = [];
        if (!empty($stateCities)) {
            foreach ($stateCities as $city) {
                $groupedCities[$city['state']][] = $city;
            }
        }

        return $groupedCities;
    }

    public function getDefinedInputs($case = null)
    {
        // TODO: Implement getDefinedInputs() method.
    }
}
