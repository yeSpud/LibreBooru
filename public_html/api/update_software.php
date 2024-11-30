<?php

require __DIR__ . "/../../bootstrapper.php";
header("Content-Type: application/json");

/*if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => $lang["method_not_allowed"]]);
    http_response_code(405);
    exit();
}*/

if (!in_array("admin", $permissions)) {
    echo json_encode(["error" => $lang["insufficient_permissions"]]);
    http_response_code(403);
    exit();
}

$currentVersion = $version;
$branche = str_contains($currentVersion, "devel") ? "devel" : "master";
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
$versions = $versions[$branche];
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
print_r($requiresUpdates);

// Returns JSON like this:
/* [
  "functions.php",
  "locales/de.json",
  "public_html/assets/classic/main.css",
  "public_html/assets/classic/Noto_Serif/NotoSerif-Italic-VariableFont_wdth,wght.ttf",
  "public_html/assets/classic/Noto_Serif/NotoSerif-VariableFont_wdth,wght.ttf",
  "public_html/assets/classic/Noto_Serif/OFL.txt",
  "public_html/assets/classic/Noto_Serif/README.txt",
  "public_html/posts.php",
  "templates/classic/pages/posts_index.tpl",
  "templates/classic/parts/footer.tpl",
  "README.txt",
  "TODO.txt"
]
*/