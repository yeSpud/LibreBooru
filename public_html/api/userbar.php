<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: image/png');

function addText($im, $text, $font, $fontSize, $x, $y, $textColor, $strokeColor, $strokeWidth)
{
    $color = imagecolorallocate($im, $textColor[0], $textColor[1], $textColor[2]);
    $color2 = imagecolorallocate($im, $strokeColor[0], $strokeColor[1], $strokeColor[2]);

    for ($offsetX = -$strokeWidth; $offsetX <= $strokeWidth; $offsetX++) {
        for ($offsetY = -$strokeWidth; $offsetY <= $strokeWidth; $offsetY++) {
            if ($offsetX === 0 && $offsetY === 0) {
                continue;
            }

            imagettftext($im, $fontSize, 0, $x + $offsetX, $y + $offsetY, $color2 * -1, $font, $text);
        }
    }

    imagettftext($im, $fontSize, 0, $x, $y, $color * -1, $font, $text);
}

function getFiles($directory, $extensions)
{
    $files = [];

    $dir = opendir(__DIR__ . '/' . $directory);

    if ($dir !== false) {
        while (($file = readdir($dir)) !== false) {
            $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

            if (in_array($fileExtension, $extensions)) {
                $files[] = $file;
            }
        }

        closedir($dir);
    }

    return $files;
}

$type = "twitter";
$types = ["pixiv", "twitter", "patreon", "fanbox", "kofi"];

if (isset($_GET["t"]) && in_array($_GET["t"], $types)) {
    $type = $_GET["t"];
}

if (!isset($_GET["h"]) || empty($_GET["h"])) {
    die("No handle provided");
}

$backgrounds = getFiles('../assets/userbars/backgrounds', ['png', 'jpg', 'jpeg']);
$props = getFiles('../assets/userbars/props', ['png', 'gif']);

/** Set current prop*/
$currentProp = $type . '.png';

if ($type == "twitter") {
    $currentText = "twitter.com/" . $_GET["h"];
} elseif ($type == "pixiv") {
    $currentText = "pixiv.net/u/" . $_GET["h"];
} elseif ($type == "patreon") {
    $currentText = "patreon.com/" . $_GET["h"];
} elseif ($type == "fanbox") {
    $currentText = $_GET["h"] . ".fanbox.cc";
} elseif ($type == "kofi") {
    $currentText = "ko-fi.com/" . $_GET["h"];
}

/** Set current font */
$currentFont = __DIR__ . '/../assets/userbars/visitor2.ttf';

/** Make sure font exists */
if (!file_exists($currentFont)) {
    die('Font file not found: ' . $currentFont);
}

/** Create base image from background */
$image = imagecreatefrompng(sprintf('../assets/userbars/backgrounds/%s', $type . '.png'));

/** Set alpha blending on */
imagealphablending($image, true);
imagesavealpha($image, true);

/** Initial font x/y */
$textX = 6;
$textY = 12;

/** Font values */
$fontSize = 10;
$fontColor = [255, 255, 255];

/** Stroke */
$strokeColor = [0, 0, 0];
$strokeWidth = 1;

/** Calculate text width */
$bbox = imagettfbbox($fontSize, 0, $currentFont, $currentText);
$textWidth = $bbox[2] - $bbox[0];

/** Right-align the text */
$textX = (imagesx($image) - $textWidth) - $textX;

/** Read prop used */
if ($currentProp) {
    $propX = 5;
    $propY = 2;

    $propImage = imagecreatefrompng(sprintf('../assets/userbars/props/%s', $currentProp));

    $propWidth = imagesx($propImage);
    $propHeight = imagesy($propImage);

    $scaledWidth = $propWidth * 0.5;
    $scaledHeight = $propHeight * 0.5;

    $scaledPropImage = imagecreatetruecolor($scaledWidth, $scaledHeight);
    imagealphablending($scaledPropImage, false);
    imagesavealpha($scaledPropImage, true);
    imagecopyresampled(
        $scaledPropImage,
        $propImage,
        0,
        0,
        0,
        0,
        $scaledWidth,
        $scaledHeight,
        $propWidth,
        $propHeight
    );

    imagecopy(
        $image,
        $scaledPropImage,
        $propX,
        $propY,
        0,
        0,
        $scaledWidth,
        $scaledHeight
    );

    imagedestroy($scaledPropImage);
    imagedestroy($propImage);
}

addText(
    $image,
    $currentText,
    $currentFont,
    $fontSize,
    $textX,
    $textY,
    $fontColor,
    $strokeColor,
    $strokeWidth
);

imagepng($image);

imagedestroy($image);
