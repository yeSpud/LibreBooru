<?php

if (!defined('WORLD')) {
    die("The World!");
}

$uploadDir = __DIR__ . "/../public_html/uploads";
$cropsDir = $uploadDir . "/crops";
$imagesDir = $uploadDir . "/images";
$thumbsDir = $uploadDir . "/thumbs";
$tmpDir = $uploadDir . "/tmp";
$videosDir = $uploadDir . "/videos";

if (!file_exists($uploadDir)) {
    try {
        mkdir($uploadDir, 0775, true);
        mkdir($cropsDir, 0775, true);
        mkdir($imagesDir, 0775, true);
        mkdir($thumbsDir, 0775, true);
        mkdir($tmpDir, 0775, true);
        mkdir($videosDir, 0775, true);
    } catch (Exception $e) {
        die("Failed to create upload directories: " . $e->getMessage());
    }
}

unset($uploadDir, $cropsDir, $imagesDir, $thumbsDir, $tmpDir, $videosDir);
