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
    echo json_encode(["error" => $lang["insufficient_permissions"]]);
    http_response_code(403);
    exit();
}

$reportSql = "SELECT * FROM post_reports WHERE report_id = ?";
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

if ($report["status"] == "rejected") {
    echo json_encode(["error" => $lang["report_already_rejected"]]);
    http_response_code(400);
    exit();
}

$updateReportSql = "UPDATE post_reports SET status = 'rejected' WHERE report_id = ?";
$updateReportStmt = $conn->prepare($updateReportSql);
$updateReportStmt->bind_param("i", $_POST["id"]);
$updateReportStmt->execute();

$action = "reject_post_report";
$description = $report["user_id"] . ": " . $report["reason"];
$moderationLogSql = "INSERT INTO moderation_log (user_id, action, target_id, description) VALUES (?, ?, ?, ?)";
$moderationLogStmt = $conn->prepare($moderationLogSql);
$moderationLogStmt->bind_param("isis", $user["user_id"], $action, $_GET["id"], $description);
$moderationLogStmt->execute();

echo json_encode(["success" => true, "message" => $lang["report_rejected"]]);
http_response_code(200);
exit();
