<?php

namespace CodeJetter\core;

use CodeJetter\components\page\models\Page;
use CodeJetter\components\user\services\UserAuthentication;
use CodeJetter\core\io\Response;
use CodeJetter\core\layout\blocks\Master;
use CodeJetter\core\layout\blocks\Footer;
use CodeJetter\core\layout\blocks\Header;
use CodeJetter\core\layout\blocks\Menu;
use CodeJetter\core\layout\blocks\ComponentTemplate;
use CodeJetter\core\utility\ClassUtility;
use CodeJetter\core\utility\StringUtility;

/**
 * Class View
 * @package CodeJetter\core
 */
class View
{
    private $config;
    private $master;
    private $header;
    private $footer;
    private $menu;
    private $page;
    private $componentTemplates;
    private $currentComponentTemplate;
    private $templateList;
    private $createdByClass;

    /**
     * View constructor.
     *
     * @param null $createdByClass
     */
    public function __construct($createdByClass = null)
    {
        $this->config = Registry::getConfigClass();

        if ($createdByClass === null) {
            $calledIn = (new ClassUtility())->calledIn();

            if (!empty($calledIn[1]['class'])) {
                $createdByClass = $calledIn[1]['class'];
            }
        }

        $this->setCreatedByClass($createdByClass);

        /**
         * every main block should be instantiated here
         * it also should know about View, so pass $this
         */
        $this->setMaster(new Master($this));
        $this->setHeader(new Header($this));
        $this->setFooter(new Footer($this));
        $this->setMenu(new Menu($this));
    }

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function getLoggedIn()
    {
        $routeInfo = Registry::getRouterClass()->getLastRoute();

        if (empty($routeInfo)) {
            return false;
        }

        $config = $this->getConfig();

        $accessRole = $routeInfo->getAccessRole();
        $roles = $config->get('roles');
        if (isset($accessRole) && isset($roles[$accessRole])) {
            $userModel = $roles[$accessRole];
        }

        if (isset($userModel['user'])) {
            return (new UserAuthentication())->getLoggedIn([$userModel['user']]);
        } else {
            return false;
        }
    }

    /**
     * @param Page  $page
     * @param array $templates
     * @param null  $masterTemplate
     * @param null  $formHandler
     *
     * @throws \Exception
     */
    public function make(Page $page, array $templates, $masterTemplate = null, $formHandler = null)
    {
        // set master template
        if ($masterTemplate === null) {
            $this->getMaster()->setTemplateName($this->getConfig()->get('defaultMasterTemplate'));
        } else {
            $this->getMaster()->setTemplateName($masterTemplate);
        }

        $this->setPage($page);

        /**
         * get blocks html - will be used in master
         *
         * @var ComponentTemplate $templateValue
         */
        foreach ($templates as $templateKey => $templateValue) {
            $templatePath = $templateValue->getTemplatePath();

            if (empty($templatePath)) {
                throw new \Exception('Template path must be specified');
            }

            $templateValue->setView($this);

            $this->setCurrentComponentTemplate($templateValue);

            if ($formHandler !== null) {
                $this->getCurrentComponentTemplate()->setFormHandler($formHandler);
            }

            if (!empty($this->getCurrentComponentTemplate()->getPager())) {
                $this->getCurrentComponentTemplate()->getPager()->setView($this);
            }

            $currentTemplatePath = $this->getConfig()->get('URI') . $templateValue->getTemplatePath();

            if (file_exists($currentTemplatePath)) {
                /**
                 * used include instead of require, in case there is a missing template
                 * rest of the site is still functional
                 */
                $currentTemplateHtml = include $currentTemplatePath;

                $this->getCurrentComponentTemplate()->setHtml($currentTemplateHtml);
            } else {
                $this->getCurrentComponentTemplate()->setHtml("Could not find: '{$currentTemplatePath}'");
            }

            /** @var string $html - defined in templates */
            $this->addComponentTemplate($templateKey, $templateValue);
        }

        (new Response())->echoContent($this->getMaster()->getHtml());
    }

    /**
     * @param                   $key
     * @param ComponentTemplate $componentTemplate
     *
     * @throws \Exception
     */
    public function addComponentTemplate($key, ComponentTemplate $componentTemplate)
    {
        $componentTemplates = $this->getComponentTemplates();

        if (!empty($componentTemplates) && array_key_exists($key, $componentTemplates)) {
            throw new \Exception('Another template with the same key exists');
        } else {
            $componentTemplates[$key] = $componentTemplate;
            $this->setComponentTemplates($componentTemplates);
        }
    }

    /**
     * @return array
     */
    public function getComponentTemplates()
    {
        return $this->componentTemplates;
    }

    /**
     * @return Footer
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param Footer $footer
     */
    public function setFooter(Footer $footer)
    {
        $this->footer = $footer;
    }

    /**
     * @return Header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param Header $header
     */
    public function setHeader(Header $header)
    {
        $this->header = $header;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $componentTemplates
     */
    public function setComponentTemplates(array $componentTemplates)
    {
        $this->componentTemplates = $componentTemplates;
    }

    /**
     * @param ComponentTemplate $currentComponentTemplate
     */
    public function setCurrentComponentTemplate(ComponentTemplate $currentComponentTemplate)
    {
        $this->currentComponentTemplate = $currentComponentTemplate;
    }

    /**
     * @return ComponentTemplate
     */
    public function getCurrentComponentTemplate()
    {
        return $this->currentComponentTemplate;
    }

    /**
     * @return Master
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * @param Master $master
     */
    public function setMaster(Master $master)
    {
        $this->master = $master;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page $page
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param Menu $menu
     */
    public function setMenu(Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * @param bool $fullNamespace
     *
     * @return string
     * @throws \Exception
     */
    public function getCreatedByClass($fullNamespace = true)
    {
        if ($fullNamespace === false) {
            return (new StringUtility())->getClassNameFromNamespace($this->createdByClass);
        } else {
            return $this->createdByClass;
        }
    }

    /**
     * @param string $createdByClass
     */
    public function setCreatedByClass($createdByClass)
    {
        $this->createdByClass = $createdByClass;
    }

    /**
     * @return array
     */
    public function getTemplateList()
    {
        return $this->templateList;
    }

    /**
     * @param string $templateList
     */
    public function setTemplateList($templateList)
    {
        $templateListArray = $this->getTemplateList();
        $templateListArray[] = $templateList;
        $this->templateList = $templateListArray;
    }
}
