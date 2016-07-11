<?php

    /**
     * @var CodeJetter\core\layout\blocks\Master $this
     */
    $config = $this->getConfig();

    /**
     * Header
     */
    // TODO: for the time being use bootstrap.css, since it's changed, use min later
    $header = $this->getHeader();
    $header->addStyleFile($config->get('URL') . '/styles/bootstrap.css');
    $header->addStyleFile($config->get('URL') . '/styles/style.css');
    $header->addStyleFile($config->get('URL') . '/styles/font-awesome.min.css');
    $header->addStyleFile($config->get('URL') . '/styles/toastr.min.css');
    $header->addStyleFile($config->get('URL') . '/styles/zettaMenu.css');
    $headerHtml = $header->getHtml();

    /**
     * Menu
     */
    $menu = $this->getView()->getMenu();
    $menuHtml = $menu->getHtml();

    $page = $this->getView()->getPage();
    $pageTitle = $page->getTitle();
    $pageIntro = $page->getIntro();
    $pageCategory = $page->getCategory();

    // if page title and intro are not see, do nothing
    if (empty($pageTitle) && empty($pageIntro)) {
        $banner = '';
    } else {
        $banner = "<!-- banner -->
<div id='banner'>
    <div class='container intro_wrapper'>
        <div class='inner_content'>
            <h1>{$pageCategory}</h1>
            <h1 class='title'>{$pageTitle}</h1>
            <h1 class='intro'>{$pageIntro}</h1>
        </div>
    </div>
</div>
<!--/ banner -->";
    }

    $components = '';
foreach ($this->getComponentTemplates() as $componentTemplate) {
    /**
     * @var CodeJetter\core\layout\blocks\ComponentTemplate $componentTemplate
     */
    $components .= $componentTemplate->getHtml();
}

/**
 * Footer
 */
$footer = $this->getFooter();
$footer->addScriptFile($this->getConfig()->get('URL') . '/scripts/jquery-1.11.3.min.js', 0);
$footer->addScriptFile($this->getConfig()->get('URL') . '/scripts/bootstrap.min.js', 1);
$footer->addScriptFile($this->getConfig()->get('URL') . '/scripts/script.js', 2);
$footer->addScriptFile($this->getConfig()->get('URL') . '/scripts/toastr.min.js', 3);
/**
 * Set the global Javascript configuration
 */
$script = $this->getGlobalJSConfiguration();

/**
 * Get javascript that calls checkSessionTimeout registered users
 */
$script .= $this->getSessionTimeoutChecker();

$footer->addScript($script);
$footerHtml = $footer->getHtml();

/**
 * Debug
 */
$debug = '';
if ($config->get('debugTemplates') === true) {
    $debug .= '<pre>';
    $templates = $this->getView()->getTemplateList();

    if (!empty($templates)) {
        foreach ($templates as $template) {
            $debug .= $template . '<br>';
        }
    }

    $debug .= '</pre>';
}

    /**
     * Return the html
     */
    return "<!DOCTYPE html>
<html lang='en'>
{$headerHtml}
<body>
{$menuHtml}
{$banner}
{$components}
{$footerHtml}
{$debug}
</body>
</html>";
