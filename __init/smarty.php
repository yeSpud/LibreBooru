<?php

if (!defined('WORLD')) {
    die("The World!");
}

require __DIR__ . "/../software/smarty/libs/Smarty.class.php";

use Smarty\Smarty;

$smarty = new Smarty();

$tmpDirs = [
    __DIR__ . "/.smarty/config",
    __DIR__ . "/.smarty/compile",
    __DIR__ . "/.smarty/cache"
];

$error = false;
foreach ($tmpDirs as $dir) {
    if (!file_exists($dir)) {
        try {
            mkdir($dir, 0755, true);
        } catch (Exception $e) {
            $error = true;
            echo "<span style='color:red'>Error: Failed to create directory: $dir</span><br>";
        }
    }
}

if (!file_exists(__DIR__ . "/../templates/{$config["theme"]}")) {
    $error = true;
    echo "<span style='color:red'>Error: Theme not found</span><br>";
}

if ($error) {
    exit;
}
$error = false;

$smarty->setTemplateDir(__DIR__ . "/../templates/{$config["theme"]}");
$smarty->setConfigDir(__DIR__ . "/.smarty/config");
$smarty->setCompileDir(__DIR__ . "/.smarty/compile");
$smarty->setCacheDir(__DIR__ . "/.smarty/cache");

unset($tmpDirs, $error, $dir);