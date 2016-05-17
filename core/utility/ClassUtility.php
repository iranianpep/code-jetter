<?php

namespace CodeJetter\core\utility;

/**
 * Class ClassUtility
 * @package CodeJetter\core\utility
 */
class ClassUtility
{
    /**
     * @return array
     */
    public function calledIn()
    {
        $trace = debug_backtrace();
        array_shift($trace);

        return $trace;
    }
}
