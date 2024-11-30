<?php

if (!defined('WORLD')) {
    die("The World!");
}

$versionFile = __DIR__ . "/../version";
if (!file_exists($versionFile)) {
    die("Missing version file.");
}

$version = file_get_contents($versionFile);
if ($version === false) {
    die("Failed to read version file.");
}

$smarty->assign('version', $version);

unset($versionFile);
