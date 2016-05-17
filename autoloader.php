<?php
    set_include_path(get_include_path().PATH_SEPARATOR.dirname(__DIR__));

    spl_autoload_extensions('.php');

    // spl_autoload needs to be passed because of PHPUnit
    spl_autoload_register('spl_autoload');
