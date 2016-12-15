<?php
    require_once 'autoloader.php';

    $app = CodeJetter\core\App::getInstance();
    $app->init();

    $databaseName = \CodeJetter\core\Registry::getMySQLDBClass()->getDatabaseName();
    $databaseInfo = \CodeJetter\core\Registry::getMySQLDBClass()->getDbInfo();
    $connection = \CodeJetter\core\Registry::getMySQLDBClass()->getConnection();

    $tablePrefix = isset($databaseInfo['tablePrefix']) ? $databaseInfo['tablePrefix'] : '';
    $tableSuffix = isset($databaseInfo['tableSuffix']) ? $databaseInfo['tableSuffix'] : '';

    return [
        'paths' => [
            // TODO move the path to config
            'migrations' => 'db/migrations',
            'seeds'      => 'db/seeds',
        ],
        'environments' => [
            'default_database' => 'development',
            'development'      => [
                'name'         => $databaseName,
                'connection'   => $connection,
                'table_prefix' => $tablePrefix,
                'table_suffix' => $tableSuffix,
            ],
        ],
    ];
