<?php

namespace CodeJetter\components\page\controllers;

use CodeJetter\components\page\models\Page;
use CodeJetter\core\BaseController;
use CodeJetter\core\layout\blocks\ComponentTemplate;
use CodeJetter\core\View;

/**
 * Class PageController
 * @package CodeJetter\components\page\controllers
 */
class PageController extends BaseController
{
    /**
     * @throws \Exception
     */
    public function index()
    {
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Welcome');

        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath() . 'defaultIndex.php');

        (new View())->make(
            $page,
            [
                'index' => $componentTemplate
            ]
        );
    }
}
