<?php

require __DIR__ . "/../../bootstrapper.php";
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => $lang["method_not_allowed"]]);
    http_response_code(405);
    exit();
}

if (!in_array("admin", $permissions)) {
    echo json_encode(["error" => $lang["insufficient_permissions"]]);
    http_response_code(403);
    exit();
}

$currentVersion = $version;
$branch = str_contains($currentVersion, "devel") ? "devel" : "master";
$latestStableVersionFile = "https://raw.githubusercontent.com/5ynchrogazer/OpenBooru-Extras/refs/heads/master/latest_stable.txt";
$latestDevelVersionFile = "https://raw.githubusercontent.com/5ynchrogazer/OpenBooru-Extras/refs/heads/master/latest_devel.txt";
$stableVersionsFile = "https://raw.githubusercontent.com/5ynchrogazer/OpenBooru-Extras/refs/heads/master/versions_stable.json";
$develVersionsFile = "https://raw.githubusercontent.com/5ynchrogazer/OpenBooru-Extras/refs/heads/master/versions_devel.json";

$latestStableVersion = file_get_contents($latestStableVersionFile);
$latestDevelVersion = file_get_contents($latestDevelVersionFile);
$versions = [];
$versions["stable"] = json_decode(file_get_contents($stableVersionsFile), true);
$versions["devel"] = json_decode(file_get_contents($develVersionsFile), true);

// Check where the current version is
// ["0.1.0-devel", "0.1.1-devel"]
$updateOrder = [];
$versions = $versions[$branch];
foreach ($versions as $vkey => $ver) {
    if ($ver === $currentVersion) {
        $updateOrder = array_slice($versions, $vkey + 1);
        break;
    }
}

$requiresUpdates = [];
foreach ($updateOrder as $update) {
    $updateFile = "https://raw.githubusercontent.com/5ynchrogazer/OpenBooru-Extras/refs/heads/master/update/{$update}.json";
    $updateData = json_decode(file_get_contents($updateFile), true);
    $requiresUpdates = array_merge($requiresUpdates, $updateData);
}

if (empty($requiresUpdates)) {
    echo $lang["no_updates_available"];
    http_response_code(400);
    exit();
}

$tmpPath = __DIR__ . "/../../__init/.tmp/update";
$tmpPathOld = __DIR__ . "/../../__init/.tmp/update_old";
$sqlPath = __DIR__ . "/../../__init/.tmp/sql";
if (!file_exists($tmpPath)) {
    mkdir($tmpPath, 0777, true);
}
if (!file_exists($tmpPathOld)) {
    mkdir($tmpPathOld, 0777, true);
}
if (!file_exists($sqlPath)) {
    mkdir($sqlPath, 0777, true);
}

foreach ($requiresUpdates as $update) {
    $currentUpdateFile = __DIR__ . "/../../" . $update;
    $updateFile = "https://raw.githubusercontent.com/5ynchrogazer/OpenBooru/refs/heads/" . $branch . "/" . $update;
    if (file_exists($currentUpdateFile)) {
        $currentUpdateData = file_get_contents($currentUpdateFile);
        $currentUpdatePath = $tmpPathOld . "/" . $update;
        $currentUpdateDir = dirname($currentUpdatePath);

        if (!file_exists($currentUpdateDir)) {
            mkdir($currentUpdateDir, 0777, true);
        }

        file_put_contents($currentUpdatePath, $currentUpdateData);
    }

    $updateData = file_get_contents($updateFile);
    $updatePath = $tmpPath . "/" . $update;
    $updateDir = dirname($updatePath);

    if (!file_exists($updateDir)) {
        mkdir($updateDir, 0777, true);
    }

    file_put_contents($updatePath, $updateData);

    $diff = shell_exec("diff -u " . $currentUpdatePath . " " . $updatePath);

    if ($diff) {
        $updatePath = __DIR__ . "/../../" . $update;
        $updateDir = dirname($updatePath);

        if (!file_exists($updateDir)) {
            mkdir($updateDir, 0777, true);
        }

        echo "Updating: " . $update . "<br>";
        file_put_contents($updatePath, $updateData);
    } else {
        echo "No changes: " . $update . "<br>";
    }

    $sqlFile = "https://raw.githubusercontent.com/5ynchrogazer/OpenBooru-Extras/refs/heads/master/sql/update-{$update}.sql";
    // Check if file exists on GitHub
    if (file_get_contents($sqlFile)) {
        $sqlData = file_get_contents($sqlFile);
        $sqlPath = $sqlPath . "/update-{$update}.sql";
        file_put_contents($sqlPath, $sqlData);
        echo "SQL: " . $sqlPath . "<br>";

        // Import the SQL file into the database
        $sql = file_get_contents($sqlPath);
        if ($conn->multi_query($sql)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->next_result());
        } else {
            echo "Error executing SQL: " . $conn->error;
        }
    }
}

$newVersion = trim($branch === "devel" ? $latestDevelVersion : $latestStableVersion);
$versionFile = __DIR__ . "/../../version";
file_put_contents($versionFile, $newVersion);

if (file_exists($tmpPathOld)) {
    shell_exec("rm -rf " . $tmpPathOld);
}
if (file_exists($tmpPath)) {
    shell_exec("rm -rf " . $tmpPath);
}
