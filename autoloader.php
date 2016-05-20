<?php
    set_include_path(get_include_path().PATH_SEPARATOR.dirname(__DIR__));

    /**
     * Start old implementation: This does not work in linux which is case sensitive
     */
    //spl_autoload_extensions('.php');

    // spl_autoload needs to be passed because of PHPUnit
    //spl_autoload_register('spl_autoload');
    /**
     * Finish old implementation
     */

spl_autoload_register(function ($className) {
    $className = str_replace('\\', '/', $className);

    require_once $className . '.php';
});
