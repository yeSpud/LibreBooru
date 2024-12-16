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

if (!in_array("report", $permissions) && !in_array("moderate", $permissions) && !in_array("admin", $permissions)) {
    echo json_encode(["error" => $lang["insufficient_permissions"]]);
    http_response_code(403);
    exit();
}

$postSql = "SELECT * FROM comments WHERE comment_id = ? LIMIT 1";
$postStmt = $conn->prepare($postSql);
$postStmt->bind_param("i", $_POST["id"]);
$postStmt->execute();
$postResult = $postStmt->get_result();
$post = $postResult->fetch_assoc();

if (!$post) {
    echo json_encode(["error" => $lang["comment_not_found"]]);
    http_response_code(404);
    exit();
}

$checkReportSql = "SELECT report_id FROM comment_reports WHERE comment_id = ? LIMIT 1";
$checkReportStmt = $conn->prepare($checkReportSql);
$checkReportStmt->bind_param("i", $_POST["id"]);
$checkReportStmt->execute();
$checkReportResult = $checkReportStmt->get_result();
$checkReport = $checkReportResult->fetch_assoc();

if ($checkReport) {
    echo json_encode(["error" => $lang["comment_already_reported"]]);
    http_response_code(400);
    exit();
}

$reportSql = "INSERT INTO comment_reports (comment_id, user_id, reason) VALUES (?, ?, ?)";
$reportStmt = $conn->prepare($reportSql);
$reportStmt->bind_param("iis", $_POST["id"], $user["user_id"], $_POST["reason"]);
$reportStmt->execute();

echo json_encode(["success" => true, "message" => $lang["comment_reported"]]);
http_response_code(200);
exit();
