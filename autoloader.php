<?php

    // If the later function cannot handle autoloading, comment out the default one as well
//    set_include_path(get_include_path().PATH_SEPARATOR.dirname(__DIR__));

//    spl_autoload_extensions('.php');
//
//    // spl_autoload needs to be passed because of PHPUnit
//    spl_autoload_register('spl_autoload');

    // This is used to handle Linux which is case sensitive
    spl_autoload_register(function ($className) {

        $className = str_replace('\\', '/', $className);

        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . $className . '.php';

        if (file_exists($file)) {
            require_once $file;
        } else {
            // TODO capture error
        }
    });
