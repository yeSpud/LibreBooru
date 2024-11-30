<?php

require __DIR__ . "/../../bootstrapper.php";
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => $lang["method_not_allowed"]]);
    http_response_code(405);
    exit();
}

if (!isset($_POST["id"]) || empty($_POST["id"]) || !is_numeric($_POST["id"])) {
    echo json_encode(["error" => $lang["post_id_is_required"]]);
    http_response_code(400);
    exit();
}

if (!in_array("moderate", $permissions) && !in_array("admin", $permissions)) {
    echo json_encode(["error" => $lang["you_dont_have_permission_to_restore_posts"]]);
    http_response_code(403);
    exit();
}

$postSql = "SELECT * FROM posts WHERE post_id = ? LIMIT 1";
$postStmt = $conn->prepare($postSql);
$postStmt->bind_param("i", $_POST["id"]);
$postStmt->execute();
$postResult = $postStmt->get_result();
$post = $postResult->fetch_assoc();

if (!$post) {
    echo json_encode(["error" => $lang["post_not_found"]]);
    http_response_code(404);
    exit();
}

$dir = "images";
if (in_array($post["file_extension"], ["mp4", "webm", "mkv"])) {
    $dir = "videos";
}

$oldFileLocation = __DIR__ . "/../uploads/$dir/." . $post["image_url"] . "." . $post["file_extension"];
$thumbExt = $dir == "images" ? $post["file_extension"] : "jpg";
$oldThumbnailLocation = __DIR__ . "/../uploads/thumbs/." . $post["image_url"] . "." . $thumbExt;
$oldCroppedLocation = __DIR__ . "/../uploads/crops/." . $post["image_url"] . "." . $post["file_extension"];

if (!file_exists($oldFileLocation)) {
    echo json_encode(["error" => $lang["main_file_deleted"]]);
    http_response_code(404);
    exit();
}

if (!file_exists($oldThumbnailLocation)) {
    echo json_encode(["error" => $lang["thumbnail_deleted"]]);
    http_response_code(404);
    exit();
}

$newFileLocation = __DIR__ . "/../uploads/$dir/" . $post["image_url"] . "." . $post["file_extension"];
$newThumbnailLocation = __DIR__ . "/../uploads/thumbs/" . $post["image_url"] . "." . $thumbExt;
$newCroppedLocation = __DIR__ . "/../uploads/crops/" . $post["image_url"] . "." . $post["file_extension"];

rename($oldFileLocation, $newFileLocation);
rename($oldThumbnailLocation, $newThumbnailLocation);
if ($dir == "images") {
    if (file_exists($oldCroppedLocation)) {
        rename($oldCroppedLocation, $newCroppedLocation);
    }
}

$restoreSql = "UPDATE posts SET deleted = 0, deleted_by = NULL, deleted_message = NULL WHERE post_id = ?";
$restoreStmt = $conn->prepare($restoreSql);
$restoreStmt->bind_param("i", $_POST["id"]);
$restoreStmt->execute();

echo json_encode(["success" => true, "message" => $lang["post_restored_successfully"]]);
http_response_code(200);
exit();
