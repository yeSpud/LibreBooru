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

$sql = "SELECT * FROM posts WHERE post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_POST["id"]);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo json_encode(["error" => $lang["post_not_found"]]);
    http_response_code(404);
    exit();
}

if ($post["is_approved"] == 1) {
    echo json_encode(["error" => $lang["post_already_approved"]]);
    http_response_code(400);
    exit();
}

$sql = "UPDATE posts SET is_approved = 1, approved_by = ? WHERE post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user["user_id"], $_POST["id"]);
$stmt->execute();

echo json_encode(["success" => $lang["post_approved"]]);
http_response_code(200);
exit();
