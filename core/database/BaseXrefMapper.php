<?php

namespace CodeJetter\core\database;

use CodeJetter\core\BaseMapper;
use CodeJetter\core\utility\ArrayUtility;

abstract class BaseXrefMapper extends BaseMapper
{
    abstract public function batchAdd(array $toBeAdded);

    /**
     * @param array $oldXrefs
     * @param array $newXrefs
     *
     * @throws \Exception
     */
    public function updateXref(array $oldXrefs, array $newXrefs)
    {
        $result = (new ArrayUtility())->arrayComparison($oldXrefs, $newXrefs);

        if (!empty($result['toBeDeleted'])) {
            // remove relations
            $criteria = [];

            $counter = 0;
            foreach ($result['toBeDeleted'] as $toBeDeletedId => $toBeDeleted) {
                if ($counter !== 0) {
                    // is not first element
                    $tempCriteria = ['logicalOperator' => 'OR'];
                } else {
                    $tempCriteria = [];
                }

                $tempCriteria['column'] = 'id';
                $tempCriteria['operator'] = '=';
                $tempCriteria['value'] = $toBeDeletedId;
                $tempCriteria['type'] = \PDO::PARAM_INT;

                $criteria[] = $tempCriteria;

                $counter++;
            }

            $this->delete($criteria);
        }

        if (!empty($result['toBeAdded'])) {
            // add relations
            //$this->batchAdd($result['toBeAdded']);
            $this->batchAdd($result['toBeAdded']);
        }
    }
}
