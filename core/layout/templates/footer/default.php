<?php

/** @var CodeJetter\core\layout\blocks\Footer $this */

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

$scriptFilesHtml = '';
if (!empty($scriptFiles)) {
    foreach ($scriptFiles as $scriptFile) {
        $scriptFilesHtml .= "<script type='text/javascript' src='{$scriptFile}'></script>";
    }
}

$scriptHtml = $this->getScript();

$year = date('Y');

    return "<footer id=\"footer\">
    <div class=\"container\">
        <div class=\"row\">
            <!-- copyright -->
            <div>
                <p>Copyright &copy; {$year} - All Right reserved.</p>
            </div>
            <!-- end copyright -->

        </div><!-- end row -->
    </div><!-- end container -->
</footer>
{$styleHtml}
{$scriptFilesHtml}
{$scriptHtml}";
