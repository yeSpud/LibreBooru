<?php

require __DIR__ . "/../../bootstrapper.php";
header("Content-Type: application/json");

$term = $_GET['term'] ?? null;

if (!$term || strlen($term) < $config["search_min_length"]) {
    die(json_encode(["error" => replace($lang["tag_has_to_be_at_least_x_characters_long"], "[count]", $config["search_min_length"])]));
}

$term = $conn->real_escape_string($term);
$term = $term . '%';

$sql = "SELECT wiki_term AS term FROM wiki WHERE wiki_term LIKE ? LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $term, $config["search_limit"]);
$stmt->execute();
$result = $stmt->get_result();
$entries = $result->fetch_all(MYSQLI_ASSOC);

foreach ($entries as $key => $entry) {
    $categorySql = "SELECT category FROM tags WHERE tag_name = ? LIMIT 1";
    $categoryStmt = $conn->prepare($categorySql);
    $categoryStmt->bind_param("s", $entry["term"]);
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
    $category = $categoryResult->fetch_assoc();
    $entries[$key]["category"] = $category["category"];
}

echo json_encode($entries);

$conn->close();
