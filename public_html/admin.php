<?php

require __DIR__ . "/../bootstrapper.php";

if (!in_array("moderate", $permissions) && !in_array("admin", $permissions)) {
    header("Location: /");
    exit;
}

$action = "i";
$actions = ["i", "u", "r"];
// "i"ndex, "u"pdate, "r"eports
$errors = [];

if (isset($_GET["a"]) && !empty($_GET["a"]) && in_array($_GET["a"], $actions)) {
    $action = $_GET["a"];
}

if ($action == "u") {
    $branch = str_contains($version, "devel") ? "devel" : "master";
    $latestStableVersionFile = "https://raw.githubusercontent.com/5ynchrogazer/OpenBooru-Extras/refs/heads/master/latest_stable.txt";
    $latestDevelVersionFile = "https://raw.githubusercontent.com/5ynchrogazer/OpenBooru-Extras/refs/heads/master/latest_devel.txt";
    $latestStableVersion = trim(file_get_contents($latestStableVersionFile));
    $latestDevelVersion = trim(file_get_contents($latestDevelVersionFile));
    $latestVersion = $branch === "devel" ? $latestDevelVersion : $latestStableVersion;

    $smarty->assign("latestStableVersion", $latestStableVersion);
    $smarty->assign("latestDevelVersion", $latestDevelVersion);
    $smarty->assign("branch", $branch);
    $smarty->assign("latestVersion", $latestVersion);
}

if (!empty($errors)) {
    $errors[] = "There are errors! To continue, please fix them.";
}

$smarty->assign("errors", $errors);
$smarty->assign("action", $action);
$smarty->assign("activePage", "admin");
$smarty->assign("pagetitle", "The Admin Panel of " . $config["sitename"]);
$smarty->display("admin.tpl");
