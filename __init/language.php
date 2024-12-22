<?php

if (!defined('WORLD')) {
    die("The World!");
}

$tmpDir = __DIR__ . "/.tmp";
$timestampFilePattern = $tmpDir . "/languages.json.*";
$languagesJsonURL = "https://raw.githubusercontent.com/5ynchrogazer/LibreBooru-Extras/refs/heads/master/locales.json";
$defaultLocaleFile = __DIR__ . "/../locales/en.json";
$languageFileString = __DIR__ . "/../locales/[lang].json";
$cacheDuration = 14400; // 4 hours

createTmpDir($tmpDir);
$latestFile = getLatestFile($timestampFilePattern, $cacheDuration);

if ($latestFile) {
    $languagesJson = file_get_contents($latestFile);
    if ($languagesJson === false) {
        die("Failed to read languages JSON from temporary file.");
    }
} else {
    $languagesJson = fetchLanguagesJson($languagesJsonURL, $tmpDir);
}

$languagesArray = json_decode($languagesJson, true);
$locales = [];

foreach ($languagesArray as $langKey => $langValue) {
    if (file_exists(str_replace("[lang]", $langKey, $languageFileString))) {
        $locales[$langKey] = $langValue;
    }
}

$defaultLocale = loadLocaleFile($defaultLocaleFile);
$configFile = __DIR__ . "/../locales/{$config["language"]}.json";
$locale = ($configFile == $defaultLocaleFile) ? $defaultLocale : loadLocaleFile($configFile);
$locale = array_replace_recursive($defaultLocale, $locale);

if (isset($_COOKIE["locale"]) && !empty($_COOKIE["locale"])) {
    $cookieLocale = sanitize($_COOKIE["locale"]);
    if (in_array($cookieLocale, array_keys($locales))) {
        $cookieLocaleFile = __DIR__ . "/../locales/{$cookieLocale}.json";
        if ($cookieLocaleFile != $defaultLocaleFile && $cookieLocaleFile != $configFile) {
            $cookieLocaleArray = loadLocaleFile($cookieLocaleFile);
            $locale = array_replace_recursive($locale, $cookieLocaleArray);
        }
    }
}

$lang = $locale;
$locale = $cookieLocale ?? $config["language"];
if (isset($smarty)) {
    $smarty->assign('lang', $lang);
    $smarty->assign("locale", $locale);
    $smarty->assign("locales", $locales);
}

unset($tmpDir, $timestampFilePattern, $languagesJsonURL, $defaultLocaleFile, $languageFileString, $cacheDuration, $languagesJson, $languagesArray, $defaultLocale, $configFile, $cookieLocale, $cookieLocaleFile, $cookieLocaleArray);
