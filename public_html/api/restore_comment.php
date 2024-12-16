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

if (!in_array("moderate", $permissions) && !in_array("admin", $permissions)) {
    echo json_encode(["error" => $lang["you_dont_have_permission_to_restore_posts"]]);
    http_response_code(403);
    exit();
}

$reportSql = "SELECT * FROM comment_reports WHERE report_id = ?";
$reportStmt = $conn->prepare($reportSql);
$reportStmt->bind_param("i", $_POST["id"]);
$reportStmt->execute();
$reportResult = $reportStmt->get_result();
$report = $reportResult->fetch_assoc();

if (!$report) {
    echo json_encode(["error" => $lang["report_not_found"]]);
    http_response_code(404);
    exit();
}

$_POST["id"] = $report["comment_id"];

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

$restoreSql = "UPDATE comments SET deleted = 0, deleted_by = NULL WHERE comment_id = ?";
$restoreStmt = $conn->prepare($restoreSql);
$restoreStmt->bind_param("i", $_POST["id"]);
$restoreStmt->execute();

echo json_encode(["success" => true, "message" => $lang["comment_restored_successfully"]]);
http_response_code(200);
exit();
