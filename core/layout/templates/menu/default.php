<?php

/** @var CodeJetter\core\layout\blocks\Menu $this */

$personalizedMenu = $this->getPersonalizedMenu();

if ($personalizedMenu instanceof \CodeJetter\core\layout\blocks\Menu) {
    $personalizedMenuHtml = $personalizedMenu->getHtml();
}

if (!empty($personalizedMenuHtml)) {
            $personalizedSection = "<li class='zm-content zm-right-item zm-right-align'>
    <a><i class='fa fa-user'></i> My Account <i class='zm-caret fa fa-angle-down'></i></a>
    <ul class='w-200'>
        <!-- personalized menu -->
        {$personalizedMenuHtml}
        <!--/ personalized menu -->
    </ul>
</li>";
} else {
    $personalizedSection = '<!-- Login -->
    <li class="zm-right-item zm-right-align">
        <a href="/login"><i class="fa fa-lock"></i> Login</a>
    </li>
    <!--/ Login -->';
}

    return "<!-- Navigation -->
<nav class=\"navbar navbar-default\" role=\"navigation\">
    <div class=\"container\">
        <!--zetta menu -->
        <div>
            <div class=\"navbar-logo-container\">
                <div class=\"navbar-logo\">
                    <a href=''><img title=\"logo\" src=\"/images/logo.png\" alt=\"\" width='100%'></a>
                </div>
                <div class=\"menu-bars\" onclick=\"showHideMenu();\">
                    <i class=\"fa fa-bars fa-2x\"></i>
                </div>
            </div>
            <div class=\"navbar-menu responsive-hidden\">
                <ul class=\"zetta-menu zm-response-stack zm-full-width\">
                    <li><a href='/'>Home</a></li>
                    {$personalizedSection}
                </ul>
            </div>
        </div>
    </div><!-- end container -->
</nav>
<!-- navigation -->";
