<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 25/04/15
 * Time: 5:29 PM
 */

namespace CodeJetter\core;

/**
 * Interface ICrud
 * @package CodeJetter\core
 */
interface ICrud
{
    public function add(array $inputs, array $fieldsValues = []);
    public function getAll(array $criteria = [], array $fromColumns = [], $order = null, $start = 0, $limit = 0);
    public function update(array $criteria, array $fieldsValues, $limit = 0);
    public function delete(array $criteria, $start = 0, $limit = 0);
}
