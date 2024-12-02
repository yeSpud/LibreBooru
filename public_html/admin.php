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
} elseif ($action == "r") {
    $reportsType = "p";
    if (isset($_GET["t"]) && !empty($_GET["t"]) && $_GET["t"] == "c") {
        $reportsType = "c";
    }
    $smarty->assign("reportsType", $reportsType);

    $status = "reported";
    if (isset($_GET["s"]) && !empty($_GET["s"]) && $_GET["s"] == "a") {
        $status = "approved";
    } elseif (isset($_GET["s"]) && !empty($_GET["s"]) && $_GET["s"] == "r") {
        $status = "rejected";
    }

    $reportsSql = "SELECT * FROM post_reports WHERE status = ? ORDER BY report_id DESC";
    $reportsStmt = $conn->prepare($reportsSql);
    $reportsStmt->bind_param("s", $status);
    $reportsStmt->execute();
    $reportsResult = $reportsStmt->get_result();
    $reports = [];
    while ($report = $reportsResult->fetch_assoc()) {
        $userSql = "SELECT username FROM users WHERE user_id = ?";
        $userStmt = $conn->prepare($userSql);
        $userStmt->bind_param("i", $report["user_id"]);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $report["username"] = $userResult->fetch_assoc()["username"];
        $reports[] = $report;
    }

    $smarty->assign("reports", $reports);
}

if (!empty($errors)) {
    $errors[] = "There are errors! To continue, please fix them.";
}

$smarty->assign("errors", $errors);
$smarty->assign("action", $action);
$smarty->assign("activePage", "admin");
$smarty->assign("pagetitle", "The Admin Panel of " . $config["sitename"]);
$smarty->display("admin.tpl");
