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
{$metaTagsHtml}
<title>{$title}</title>
<!-- Google Fonts -->
<link href='https://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
{$styleHtml}
{$scriptFileHtml}
{$scriptHtml}
</head>";
