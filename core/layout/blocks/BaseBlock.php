<?php

namespace CodeJetter\core\layout\blocks;

use CodeJetter\core\Registry;
use CodeJetter\core\utility\StringUtility;
use CodeJetter\core\View;
use CodeJetter\components\page\models\Page;

/**
 * Class BaseBlock
 * @package CodeJetter\core\layout\blocks
 */
abstract class BaseBlock
{
    protected $view;
    protected $config;
    protected $html;
    protected $data;
    protected $script;
    protected $scriptFiles;
    protected $style;
    protected $styleFiles;
    protected $templateName;
    protected $templatePath;

    /**
     * BaseBlock constructor.
     *
     * @param View|null $view
     */
    public function __construct(View $view = null)
    {
        if ($view !== null) {
            $this->setView($view);
        }

        $this->config = Registry::getConfigClass();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getHtml()
    {
        if (!empty($this->getTemplatePath()) && file_exists($this->getTemplatePath())) {
            $html = include_once $this->getTemplatePath();

            // include returns 1 or true if there is no content, this is used to fix
            if ($html === true) {
                $html = '';
            }

            $this->setHtml($html);
        }

        // add the the template path to template list of the view each time getHtml is called
        if ($this->getView() instanceof View) {
            $this->getView()->setTemplateList($this->getTemplatePath());
        } else {
            throw new \Exception('View needs to be set in all layout blocks');
        }

        return $this->html;
    }

    /**
     * print html
     */
    public function printHtml()
    {
        echo $this->getHtml();
    }

    /**
     * @param $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param array $files
     */
    public function setScriptFiles($files)
    {
        $this->scriptFiles = $files;
    }

    /**
     * @param $script
     */
    public function addScript($script)
    {
        $this->script = $script;
    }

    /**
     * @param      $filePath
     * @param null $orderNumber
     *
     * @throws \Exception
     */
    public function addScriptFile($filePath, $orderNumber = null)
    {
        if ($orderNumber !== null) {
            // validate order number
            if (!is_numeric($orderNumber) || $orderNumber < 0) {
                throw new \Exception('Order number must be valid');
            } else {
                $files = $this->getScriptFiles();

                if (!empty($files)) {
                    array_splice($files, $orderNumber, 0, $filePath);
                    $this->setScriptFiles($files);
                } else {
                    // array is empty
                    $this->addScriptFile($filePath);
                }
            }
        } else {
            // order number is not defined, add the file normally
            $this->scriptFiles[] = $filePath;
        }
    }

    /**
     * @return array
     */
    public function getScriptFiles()
    {
        return $this->scriptFiles;
    }

    /**
     * @param array $files
     */
    public function setStyleFiles($files)
    {
        $this->styleFiles = $files;
    }

    /**
     * @param $style
     */
    public function addStyle($style)
    {
        $this->style = $style;
    }

    /**
     * @param $filePath
     * @param $orderNumber
     *
     * @throws \Exception
     */
    public function addStyleFile($filePath, $orderNumber = null)
    {
        if ($orderNumber !== null) {
            // validate order number
            if (!is_numeric($orderNumber) || $orderNumber < 0) {
                throw new \Exception('Order number must be valid');
            } else {
                $files = $this->getStyleFiles();

                if (!empty($files)) {
                    array_splice($files, $orderNumber, 0, $filePath);
                    $this->setStyleFiles($files);
                } else {
                    // array is empty
                    $this->addStyleFile($filePath);
                }

            }
        } else {
            // order number is not defined, add the file normally
            $this->styleFiles[] = $filePath;
        }
    }

    /**
     * @return mixed
     */
    public function getStyleFiles()
    {
        return $this->styleFiles;
    }

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @param string $templateName
     * @return void
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param View $view
     */
    public function setView(View $view)
    {
        $this->view = $view;
    }

    /**
     * @return string
     */
    public function getTemplatePath()
    {
        if (!isset($this->templatePath)) {
            // get_called_class is used to determine which child class has been called this function
            $blockName = strtolower((new StringUtility())->getClassNameFromNamespace(get_called_class()));
            $DS = $this->getConfig()->get('DS');
            $templateName = $this->getTemplateName();

            // if template name is not specified, consider 'default.php' as the template
            $templateName = isset($templateName) ? $templateName : 'default.php';

            return dirname(__DIR__) . $DS . 'templates' . $DS . $blockName . $DS . $templateName;
        } else {
            return $this->templatePath;
        }
    }

    /**
     * @param $templatePath
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->getView()->getPage();
    }

    /**
     * @return array
     */
    public function getComponentTemplates()
    {
        return $this->getView()->getComponentTemplates();
    }

    /**
     * @return \CodeJetter\core\Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
