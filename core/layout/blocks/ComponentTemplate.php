<?php

namespace CodeJetter\core\layout\blocks;

use CodeJetter\core\FormHandler;

/**
 * Class ComponentTemplate
 * @package CodeJetter\core\layout\blocks
 */
class ComponentTemplate extends BaseBlock
{
    private $formHandler;
    private $pager;

    /**
     * @return Pager
     */
    public function getPager()
    {
        return $this->pager;
    }

    /**
     * @param Pager $pager
     */
    public function setPager(Pager $pager)
    {
        $this->pager = $pager;
    }

    /**
     * @return FormHandler
     */
    public function getFormHandler()
    {
        return $this->formHandler;
    }

    /**
     * @param FormHandler $formHandler
     */
    public function setFormHandler(FormHandler $formHandler)
    {
        $this->formHandler = $formHandler;
    }
}
