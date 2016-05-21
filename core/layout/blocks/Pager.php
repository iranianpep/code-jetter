<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 24/04/15
 * Time: 7:26 PM
 */

namespace CodeJetter\core\layout\blocks;

use CodeJetter\core\io\Request;
use CodeJetter\core\Registry;

/**
 * Class Pager
 * @package CodeJetter\core\layout\blocks
 */
class Pager extends BaseBlock
{
    private $total;
    private $totalPagesNo;
    private $currentPage;
    private $currentPageResultNo;
    private $start;
    private $limit;
    private $basePath;
    private $path;

    /**
     * @param array $parameters
     * @param string $basePath
     * @param string $path
     *
     * @throws \Exception
     */
    public function __construct($parameters, $basePath, $path)
    {
        parent::__construct();

        $defaultLimit = Registry::getConfigClass()->get('list')['pager']['defaultLimit'];

        $limit = isset($parameters['limit']) ? (int) $parameters['limit'] : $defaultLimit;
        $page = isset($parameters['page']) ? (int) $parameters['page'] : 1;

        $this->setLimit($limit);
        $this->setCurrentPage($page);
        $this->setBasePath($basePath);
        $this->setPath($path);
    }

    /**
     * calculate start based on the limit and current page number
     *
     * @return int
     */
    public function calculateStart()
    {
        return (int) (($this->getCurrentPage() - 1) * $this->getLimit());
    }

    /**
     * calculate the total number of pages based on total records and the limit
     *
     * @return int
     */
    public function calculateTotalPagesNo()
    {
        if ($this->getLimit() <= 0) {
            return 1;
        }

        return (int) ceil($this->getTotal() / $this->getLimit());
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        if (empty($this->currentPage)) {
            $this->setCurrentPage(1);
        }

        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = (int) $currentPage;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = (int) $total;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    /**
     * @return int
     */
    public function getTotalPagesNo()
    {
        if (!isset($this->totalPagesNo)) {
            $this->setTotalPagesNo($this->calculateTotalPagesNo());
        }

        return $this->totalPagesNo;
    }

    /**
     * @param int $totalPagesNo
     */
    public function setTotalPagesNo($totalPagesNo)
    {
        $this->totalPagesNo = (int) $totalPagesNo;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        if (!isset($this->start)) {
            $this->setStart($this->calculateStart());
        }

        return $this->start;
    }

    /**
     * @param int $start
     */
    public function setStart($start)
    {
        $this->start = (int) $start;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getCurrentPageResultNo()
    {
        return $this->currentPageResultNo;
    }

    /**
     * @param int $currentPageResultNo
     */
    public function setCurrentPageResultNo($currentPageResultNo)
    {
        $this->currentPageResultNo = (int) $currentPageResultNo;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        $queryString = (new Request())->getQueryString();

        return !empty($queryString) ? "?{$queryString}" : '';
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPagerLimits()
    {
        return $this->getConfig()->get('list')['pager']['limits'];
    }

    /**
     * @param $pageNumber
     *
     * @return string
     */
    public function getFullPathByPageNumber($pageNumber)
    {
        $path = !empty($this->getBasePath()) ? $this->getBasePath() : $this->getPath();
        $limit = $this->getLimit();
        $queryString = $this->getQueryString();

        return "{$path}/page/{$pageNumber}/limit/{$limit}{$queryString}";
    }
}
