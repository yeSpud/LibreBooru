<?php

if (!file_exists(__DIR__ . "/version")) {
    header("Location: /install.php");
    exit;
}

define("WORLD", "World");

require __DIR__ . "/functions.php";

$config = readConfig(__DIR__ . "/config.env");
$db = readConfig(__DIR__ . "/db.env");
$colors = readConfig(__DIR__ . "/colors.env");
// Is this really necessary?
// Oh, yeah. One line for the guest level in session.php lol
$levelIds = readConfig(__DIR__ . "/levels.env");

include __DIR__ . "/__init/debug.php";
include __DIR__ . "/__init/external.php";
include __DIR__ . "/__init/db.php";
include __DIR__ . "/__init/smarty.php";
include __DIR__ . "/__init/session.php";
include __DIR__ . "/__init/parsedown.php";
include __DIR__ . "/__init/uploads.php";
include __DIR__ . "/__init/language.php";
include __DIR__ . "/__init/version.php";

$smarty->assign('config', $config);
$smarty->assign('colors', $colors);

$smarty->registerPlugin('function', 'replace', 'trl_replace');