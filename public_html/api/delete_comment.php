<?php

require __DIR__ . "/../../bootstrapper.php";
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => $lang["method_not_allowed"]]);
    http_response_code(405);
    exit();
}

if (!isset($_POST["id"]) || empty($_POST["id"]) || !is_numeric($_POST["id"])) {
    echo json_encode(["error" => $lang["comment_id_is_required"]]);
    http_response_code(400);
    exit();
}

if (!isset($_POST["reason"]) || empty($_POST["reason"])) {
    echo json_encode(["error" => $lang["reason_is_required"]]);
    http_response_code(400);
    exit();
}

if (!in_array("moderate", $permissions) && !in_array("admin", $permissions)) {
    echo json_encode(["error" => $lang["you_dont_have_permission_to_delete_posts"]]);
    http_response_code(403);
    exit();
}

$commentSql = "SELECT * FROM comments WHERE comment_id = ? LIMIT 1";
$commentStmt = $conn->prepare($commentSql);
$commentStmt->bind_param("i", $_POST["id"]);
$commentStmt->execute();
$commentResult = $commentStmt->get_result();
$comment = $commentResult->fetch_assoc();

if (!$comment) {
    echo json_encode(["error" => $lang["comment_not_found"]]);
    http_response_code(404);
    exit();
}

if ($comment["deleted"] == 1) {
    echo json_encode(["error" => $lang["comment_already_deleted"]]);
    http_response_code(400);
    exit();
}

$deleteSql = "UPDATE comments SET deleted = 1, deleted_by = ? WHERE comment_id = ?";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("ii", $user["user_id"], $_POST["id"]);
$deleteStmt->execute();

echo json_encode(["success" => true, "message" => $lang["comment_deleted_successfully"]]);
http_response_code(200);
exit();