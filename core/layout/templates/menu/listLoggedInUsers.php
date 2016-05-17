<?php

/** @var CodeJetter\core\layout\blocks\Menu $this */
$usersDefaultLinks = $this->getData();

$linksHtml = '';
if (!empty($usersDefaultLinks)) {
    foreach ($usersDefaultLinks as $user => $usersDefaultLink) {
        $linksHtml .= "<li><a href='{$usersDefaultLink}'>{$user}</a></li>";
    }
}

return $linksHtml;
