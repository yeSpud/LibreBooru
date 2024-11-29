<?php

if (!defined('WORLD')) {
    die("The World!");
}

require __DIR__ . "/../software/parsedown/Parsedown.php";

$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
$parsedown->setMarkupEscaped(true);
$parsedown->setUrlsLinked(true);
$parsedown->setBreaksEnabled(true);
