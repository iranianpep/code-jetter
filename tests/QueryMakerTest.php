<?php

class QueryMakerTest extends \PHPUnit_Framework_TestCase
{
    public function testSelectQuery()
    {
        $queryMaker = new \CodeJetter\core\database\QueryMaker('testTable');

        $criteria = [
            [
                'logicalOperator' => '',
                'column' => 'parentId',
                'value' => 456,
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'status',
                'value' => 'active'
            ],
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL'
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], 'name ASC', 5, 10);
        $expectedQuery = "SELECT * FROM testTable WHERE parentId = :parentId1 AND status = :status2 AND archivedAt IS NULL ORDER BY name ASC LIMIT :start, :limit;";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'parentId',
                'value' => 50,
                'type' => \PDO::PARAM_INT
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], 'name ASC', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE parentId = :parentId1 ORDER BY name ASC;";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'testColumn1',
                'operator' => 'NOT LIKE',
                'value' => 'test value',
                'type' => \PDO::PARAM_INT
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], 'name ASC', 0, 10);
        $expectedQuery = "SELECT * FROM testTable WHERE testColumn1 NOT LIKE :testColumn11 ORDER BY name ASC LIMIT :limit;";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'id',
                'value' => 50,
                'type' => \PDO::PARAM_INT
            ],
            [
                'logicalOperator' => 'OR',
                'column' => 'id',
                'value' => 37,
                'type' => \PDO::PARAM_INT
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE id = :id1 OR id = :id2;";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL'
            ],
            [
                'logicalOperator' => 'OR',
                'column' => 'live',
                'value' => 1,
                'type' => \PDO::PARAM_INT
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE archivedAt IS NULL OR live = :live2;";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL',
                'nested' => [
                    'key' => 'live'
                ]
            ],
            [
                'logicalOperator' => 'OR',
                'column' => 'live',
                'value' => 1,
                'type' => \PDO::PARAM_INT,
                'nested' => [
                    'key' => 'live'
                ]
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE (archivedAt IS NULL OR live = :live2);";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL',
                'nested' => [
                    'key' => 'live1'
                ]
            ],
            [
                'column' => 'live',
                'value' => 1,
                'type' => \PDO::PARAM_INT,
                'nested' => [
                    'key' => 'live2',
                    'before' => 'AND'
                ]
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE (archivedAt IS NULL) AND (live = :live2);";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL',
            ],
            [
                'column' => 'live',
                'value' => 1,
                'type' => \PDO::PARAM_INT,
                'nested' => [
                    'key' => 'live2',
                    'before' => 'AND'
                ]
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE archivedAt IS NULL AND (live = :live2);";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL',
                'nested' => [
                    'key' => 'live2',
                    'after' => 'AND'
                ]
            ],
            [
                'column' => 'live',
                'value' => 1,
                'type' => \PDO::PARAM_INT
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE (archivedAt IS NULL) AND live = :live2;";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL',
                'nested' => [
                    'key' => 'live2'
                ]
            ],
            [
                'column' => 'archivedAtTest',
                'operator' => 'IS NULL',
                'nested' => [
                    'key' => 'live2'
                ]
            ],
            [
                'column' => 'live',
                'value' => 1,
                'type' => \PDO::PARAM_INT
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE (archivedAt IS NULL AND archivedAtTest IS NULL) AND live = :live3;";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'live',
                'value' => 1,
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL',
                'nested' => [
                    'key' => 'live2',
                    'before' => 'OR'
                ]
            ],
            [
                'column' => 'archivedAtTest',
                'operator' => 'IS NULL',
                'nested' => [
                    'key' => 'live2'
                ]
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE live = :live1 OR (archivedAt IS NULL AND archivedAtTest IS NULL);";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'id',
                'operator' => 'IN',
                'value' => 2
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE id IN (:id1);";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'id',
                'operator' => 'IN',
                'value' => [2, 3, 24]
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE id IN (:id10,:id11,:id12);";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'id',
                'operator' => 'IN',
                'value' => [2, 3, 24]
            ],
            [
                'column' => 'id',
                'operator' => 'IN',
                'value' => 2
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE id IN (:id10,:id11,:id12) AND id IN (:id2);";

        $this->assertEquals($expectedQuery, $query);

        $criteria = [
            [
                'column' => 'id',
                'operator' => 'IN',
                'value' => 2
            ],
            [
                'column' => 'id',
                'operator' => 'IN',
                'value' => [2, 3, 24]
            ]
        ];

        $query = $queryMaker->selectQuery($criteria, [], '', 0, 0);
        $expectedQuery = "SELECT * FROM testTable WHERE id IN (:id1) AND id IN (:id20,:id21,:id22);";

        $this->assertEquals($expectedQuery, $query);
    }

    public function testUpdateQueryWithException()
    {
        $queryMaker = new \CodeJetter\core\database\QueryMaker('testTable');

        $criteria = [
            [
                'logicalOperator' => '',
                'column' => 'parentId',
                'value' => 456,
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'status',
                'value' => 'active'
            ],
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL'
            ]
        ];

        $fieldsValues = [

        ];

        $this->setExpectedException('Exception', 'fieldsValues cannot be empty in updateQuery function');

        $queryMaker->updateQuery($criteria, $fieldsValues, 0, 0);
    }

    public function testUpdateQuery()
    {
        $queryMaker = new \CodeJetter\core\database\QueryMaker('testTable');

        $criteria = [
            [
                'logicalOperator' => '',
                'column' => 'parentId',
                'value' => 456,
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'status',
                'value' => 'active'
            ],
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL'
            ]
        ];

        $fieldsValues = [
            [
                'column' => 'name',
                'value' => 'dummyName'
            ],
            [
                'column' => 'email',
                'value' => 'dummyValue'
            ],
            [
                'column' => 'phone',
                'value' => 'dummyPhone'
            ]
        ];

        $query = $queryMaker->updateQuery($criteria, $fieldsValues, 0, 0);
        $expectedQuery = "UPDATE testTable SET name = :name, email = :email, phone = :phone WHERE parentId = :parentId1 AND status = :status2 AND archivedAt IS NULL;";

        $this->assertEquals($expectedQuery, $query);
    }

    public function testInsertQuery()
    {
        $queryMaker = new \CodeJetter\core\database\QueryMaker('testTable');

        $fieldsValues = [
            [
                'column' => 'name',
                'value' => 'dummyName'
            ],
            [
                'column' => 'email',
                'value' => 'dummyValue'
            ],
            [
                'column' => 'phone',
                'value' => 'dummyPhone'
            ]
        ];

        $query = $queryMaker->insertQuery($fieldsValues);
        $expectedQuery = "INSERT INTO testTable (name,email,phone) VALUES (:name,:email,:phone);";

        $this->assertEquals($expectedQuery,$query);
    }

    public function testDeleteQuery()
    {
        $queryMaker = new \CodeJetter\core\database\QueryMaker('testTable');

        $criteria = [
            [
                'logicalOperator' => '',
                'column' => 'parentId',
                'value' => 456,
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'status',
                'value' => 'active'
            ],
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL'
            ]
        ];

        $query = $queryMaker->deleteQuery($criteria, 0, 0);
        $expectedQuery = "DELETE FROM testTable WHERE parentId = :parentId1 AND status = :status2 AND archivedAt IS NULL;";

        $this->assertEquals($expectedQuery, $query);
    }

    public function testCountQuery()
    {
        $queryMaker = new \CodeJetter\core\database\QueryMaker('testTable');

        $criteria = [
            [
                'logicalOperator' => '',
                'column' => 'parentId',
                'value' => 456,
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'status',
                'value' => 'active'
            ],
            [
                'column' => 'archivedAt',
                'operator' => 'IS NULL'
            ]
        ];

        $query = $queryMaker->countQuery($criteria);
        $expectedQuery = "SELECT COUNT(*) FROM testTable WHERE parentId = :parentId1 AND status = :status2 AND archivedAt IS NULL;";

        $this->assertEquals($expectedQuery, $query);
    }
}
 