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

if (!isset($_POST["action"]) || empty($_POST["action"]) || !in_array($_POST["action"], ["up", "down", "remove"])) {
    echo json_encode(["error" => $lang["invalid_action"]]);
    http_response_code(400);
    exit();
}

if (!in_array("vote", $permissions)) {
    echo json_encode(["error" => $lang["insufficient_permissions"]]);
    http_response_code(403);
    exit();
}

$action = $_POST["action"];

if ($action == "up") {
    $vote = 1;
} else if ($action == "down") {
    $vote = -1;
} else {
    $vote = 0;
}

$voteSql = "SELECT * FROM post_votes WHERE post_id = ? AND user_id = ? LIMIT 1";
$voteStmt = $conn->prepare($voteSql);
$voteStmt->bind_param("ii", $_POST["id"], $user["user_id"]);
$voteStmt->execute();
$voteResult = $voteStmt->get_result();
$_vote = $voteResult->fetch_assoc();

if ($_vote) {
    if ($action == "remove") {
        $deleteSql = "DELETE FROM post_votes WHERE post_id = ? AND user_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("ii", $_POST["id"], $user["user_id"]);
        $deleteStmt->execute();

        $returnVote = $_vote["vote"] == 1 ? -1 : 1;

        echo json_encode(["success" => true, "message" => $lang["vote_removed"], "vote" => $returnVote]);
        http_response_code(200);
        exit();
    } else {
        $updateSql = "UPDATE post_votes SET vote = ? WHERE post_id = ? AND user_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("iii", $vote, $_POST["id"], $user["user_id"]);
        $updateStmt->execute();

        if (($_vote["vote"] == 1 && $vote == 1) || ($_vote["vote"] == -1 && $vote == -1)) {
            $vote = 0;
        } else {
            if ($vote == -1) {
                $vote = -2;
            }

            if ($_vote["vote"] == -1) {
                $vote = 2;
            }
        }

        echo json_encode(["success" => true, "message" => $lang["vote_updated"], "vote" => $vote]);
        http_response_code(200);
        exit();
    }
} else {
    if ($action != "remove") {
        $insertSql = "INSERT INTO post_votes (post_id, user_id, vote) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("iii", $_POST["id"], $user["user_id"], $vote);
        $insertStmt->execute();

        echo json_encode(["success" => true, "message" => $lang["vote_cast"], "vote" => $vote]);
        http_response_code(200);
        exit();
    }
}

echo json_encode(["success" => true, "message" => $lang["no_changes_made"], "vote" => 0]);
http_response_code(200);
exit();
