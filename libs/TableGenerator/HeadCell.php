<?php
/**
 * Contains Head Cell class
 */

namespace CodeJetter\libs\TableGenerator;

use CodeJetter\core\IO\Request;
use CodeJetter\core\Registry;

/**
 * Class Cell
 */
class HeadCell extends Cell
{
    /**
     * head cell title
     *
     * @var string $title
     */
    private $title;

    /**
     * head cell alias
     *
     * @var string $alias
     */
    private $alias;

    /**
     * @var boolean $sortable
     */
    private $sortable;

    /**
     * @var boolean $searchable
     */
    private $searchable;

    /**
     * @var boolean $selectable
     */
    private $selectable;

    private $selectAllSelector;

    public function __construct($title = null, $alias = null, $content = null, $htmlspecialchars = false)
    {
        if ($title !== null) {
            $this->setTitle($title);
        }

        if ($alias !== null) {
            $this->setAlias($alias);
        }

        parent::__construct($content, $htmlspecialchars);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        // if alias is not set, consider lower case of the title as alias
        if (!isset($this->alias)) {
            return strtolower($this->getTitle());
        }

        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return boolean
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * @param boolean $sortable
     */
    public function setSortable($sortable)
    {
        $this->sortable = $sortable;
    }

    /**
     * @return boolean
     */
    public function isSearchable()
    {
        return $this->searchable;
    }

    /**
     * @param boolean $searchable
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;
    }

    /**
     * @return boolean
     */
    public function isSelectable()
    {
        return $this->selectable;
    }

    /**
     * @param $selectable
     * @param $selectAllSelector
     */
    public function setSelectable($selectable, $selectAllSelector = '')
    {
        $this->selectable = $selectable;
        $this->selectAllSelector = $selectAllSelector;
    }

    public function getContent()
    {
        // if content is not null return the content
        if ($this->content !== null) {
            return $this->content;
        }

        $title = $this->getTitle();

        $checkboxHtml = ($this->isSelectable() === true) ? "<input type='checkbox' onclick=\"checkAllByCheckbox(this, '{$this->selectAllSelector}');\">" : '';

        // if head cell is not sortable only return title as the content
        if ($this->isSortable() === false) {
            return $checkboxHtml . ' ' . $title;
        }

        $alias = $this->getAlias();

        $listConfig = Registry::getConfigClass()->get('list');
        $request = new Request();
        $sortBy = $request->getQueryStringVariables($listConfig['orderBy']);
        $sortDir = $request->getQueryStringVariables($listConfig['orderDir']);

        $newSortDir = ($alias == $sortBy && $sortDir == 'asc') ? 'desc' : 'asc';
        return "{$checkboxHtml} <a style='cursor: pointer;' onclick='sort(\"{$listConfig['orderBy']}\", \"{$alias}\", \"{$listConfig['orderDir']}\", \"{$newSortDir}\");'>{$title}</a>";
    }
}
