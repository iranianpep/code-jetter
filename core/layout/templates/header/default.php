<?php

/** @var CodeJetter\core\layout\blocks\Header $this */
$title = $this->getPage()->getTitle();

    $metaTagsHtml = $this->getAdditionalMetaTagsHtml();

// add style files
$styles = $this->getStyleFiles();

    $styleHtml = '';
if (!empty($styles)) {
    foreach ($styles as $style) {
        $styleHtml .= "<link rel='stylesheet' href='{$style}'>";
    }
}

// add scripts
$scriptFiles = $this->getScriptFiles();

    $scriptFileHtml = '';
if (!empty($scriptFiles)) {
    foreach ($scriptFiles as $scriptFile) {
        $scriptFileHtml .= "<script type='text/javascript' src='{$scriptFile}'></script>";
    }
}

    $scriptHtml = $this->getScript();

return "<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<meta charset=\"utf-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no\">
<!-- Fav icon -->
<link rel=\"apple-touch-icon\" sizes=\"57x57\" href=\"/apple-touch-icon-57x57.png\">
<link rel=\"apple-touch-icon\" sizes=\"60x60\" href=\"/apple-touch-icon-60x60.png\">
<link rel=\"apple-touch-icon\" sizes=\"72x72\" href=\"/apple-touch-icon-72x72.png\">
<link rel=\"apple-touch-icon\" sizes=\"76x76\" href=\"/apple-touch-icon-76x76.png\">
<link rel=\"apple-touch-icon\" sizes=\"114x114\" href=\"/apple-touch-icon-114x114.png\">
<link rel=\"apple-touch-icon\" sizes=\"120x120\" href=\"/apple-touch-icon-120x120.png\">
<link rel=\"apple-touch-icon\" sizes=\"144x144\" href=\"/apple-touch-icon-144x144.png\">
<link rel=\"apple-touch-icon\" sizes=\"152x152\" href=\"/apple-touch-icon-152x152.png\">
<link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"/apple-touch-icon-180x180.png\">
<link rel=\"icon\" type=\"image/png\" href=\"/favicon-32x32.png\" sizes=\"32x32\">
<link rel=\"icon\" type=\"image/png\" href=\"/favicon-194x194.png\" sizes=\"194x194\">
<link rel=\"icon\" type=\"image/png\" href=\"/favicon-96x96.png\" sizes=\"96x96\">
<link rel=\"icon\" type=\"image/png\" href=\"/android-chrome-192x192.png\" sizes=\"192x192\">
<link rel=\"icon\" type=\"image/png\" href=\"/favicon-16x16.png\" sizes=\"16x16\">
<link rel=\"manifest\" href=\"/manifest.json\">
<link rel=\"mask-icon\" href=\"/safari-pinned-tab.svg\" color=\"#5bbad5\">
<meta name=\"msapplication-TileColor\" content=\"#da532c\">
<meta name=\"msapplication-TileImage\" content=\"/mstile-144x144.png\">
<meta name=\"theme-color\" content=\"#ffffff\">
<!--/ Fav icon -->
{$metaTagsHtml}
<title>{$title}</title>
<!-- Google Fonts -->
<link href='https://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
{$styleHtml}
{$scriptFileHtml}
{$scriptHtml}
</head>";
