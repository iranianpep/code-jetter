<?php

namespace CodeJetter\components\geolocation\mappers;

use CodeJetter\components\geolocation\models\City;
use CodeJetter\components\geolocation\models\State;
use CodeJetter\core\BaseMapper;
use CodeJetter\core\database\QueryMaker;
use CodeJetter\core\Registry;

class StateMapper extends BaseMapper
{
    public function add(array $inputs, array $fieldsValues = [], $extraDefinedInputs = [])
    {
        // TODO: No need to implement the body at this point
    }

    public function getStatesCities(
        array $criteria = [],
        $fromColumns = null,
        $order = null,
        $start = 0,
        $limit = 0,
        $returnTotalNo = false,
        $excludeArchived = true
    ) {
        $cityMapper = new CityMapper();
        $cityTable = $cityMapper->getTable();
        $cityTableAlias = $cityMapper->getTableAlias();

        $stateTable = $this->getTable();
        $stateTableAlias = $this->getTableAlias();

        $tables = [
            $stateTableAlias => [
                'name'  => $stateTable,
                'class' => $this->getModelName(),
            ],
            $cityTableAlias => [
                'name'  => $cityTable,
                'class' => $this->getModelsNamespace('geolocation').'City',
                'on'    => [
                    "`{$stateTableAlias}`.`id`",
                    "`{$cityTableAlias}`.`stateId`",
                ],
            ],
        ];

        // By default do not return archived records
        if ($excludeArchived === true) {
            $criteria = array_merge($criteria, $this->getExcludeArchivedCriteria($stateTableAlias));
            $criteria = array_merge($criteria, $this->getExcludeArchivedCriteria($cityTableAlias));
        }

        $queryMaker = new QueryMaker($tables);
        $query = $queryMaker->selectQuery($criteria, $fromColumns, $order, $start, $limit);

        try {
            $connection = Registry::getMySQLDBClass()->getConnection($this->getDatabase());
            $statement = $connection->prepare($query);

            // set last query
            $this->setLastQuery($statement->queryString);

            // bind values
            $statement = $queryMaker->bindValues($statement, $criteria, $start, $limit);
            $statement->execute();

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            /**
             * Map rows to objects.
             */
            $mappedObjects = $this->mapRowsToObjects($result, $tables);

            if ($returnTotalNo == true) {
                $query = $queryMaker->countQuery($criteria);

                $statement = $connection->prepare($query);

                // set last query
                $this->setLastQuery($statement->queryString);

                // bind values
                $statement = $queryMaker->bindValues($statement, $criteria);
                $statement->execute();

                $total = (int) $statement->fetchColumn();

                return ['result' => $mappedObjects, 'total' => $total];
            } else {
                return $mappedObjects;
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
        $statesCities = $this->getStatesCities($criteria, null, $order, $start, $limit, false, $excludeArchived);
        $stateTableAlias = $this->getTableAlias();

        $cityTableAlias = (new CityMapper())->getTableAlias();

        $groupedCities = [];
        if (!empty($statesCities)) {
            foreach ($statesCities as $stateCity) {
                if (!isset($stateCity[$stateTableAlias]) || !isset($stateCity[$cityTableAlias])) {
                    continue;
                }

                $state = $stateCity[$stateTableAlias];
                $city = $stateCity[$cityTableAlias];

                if (!$state instanceof State || !$city instanceof City) {
                    continue;
                }

                $groupedCities[$state->getName()][] = ['cityId' => $city->getId(), 'city' => $city->getName()];
            }
        }

        return $groupedCities;
    }

    public function getDefinedInputs($action = null, array $includingInputs = [], array $excludingInputs = [])
    {
        // TODO: Implement getDefinedInputs() method.
    }

    public function getFieldsValues(array $inputs, array $definedInputs = [], $action = null)
    {
        // TODO: Implement getFieldsValues() method.
    }
}
