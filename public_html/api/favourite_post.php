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

if (!isset($user["user_id"])) {
    echo json_encode(["error" => $lang["you_need_to_be_logged_in_to_favourite_a_post"]]);
    http_response_code(403);
    exit();
}

$favouriteSql = "SELECT * FROM favourites WHERE post_id = ? AND user_id = ? LIMIT 1";
$favouriteStmt = $conn->prepare($favouriteSql);
$favouriteStmt->bind_param("ii", $_POST["id"], $user["user_id"]);
$favouriteStmt->execute();
$favouriteResult = $favouriteStmt->get_result();
$favourite = $favouriteResult->fetch_assoc();

if ($favourite) {
    $deleteSql = "DELETE FROM favourites WHERE post_id = ? AND user_id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("ii", $_POST["id"], $user["user_id"]);
    $deleteStmt->execute();

    echo json_encode(["success" => true, "message" => $lang["post_removed_from_favourites"]]);
    exit();
}

$insertSql = "INSERT INTO favourites (post_id, user_id) VALUES (?, ?)";
$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param("ii", $_POST["id"], $user["user_id"]);
$insertStmt->execute();

echo json_encode(["success" => true, "message" => $lang["post_added_to_favourites"]]);
exit();
