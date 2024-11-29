<?php

require __DIR__ . "/../../bootstrapper.php";
header("Content-Type: application/json");

$term = $_GET['term'] ?? null;

if (!$term || strlen($term) < $config["search_min_length"]) {
    die(json_encode(["error" => replace($lang["tag_has_to_be_at_least_x_characters_long"], "[count]", $config["search_min_length"])]));
}

$term = $conn->real_escape_string($term);

//If it starts with a minus, remove it
if (substr($term, 0, 1) === '-') {
    $term = ltrim($term, '-');
}

if (substr($term, -1) === '*') {
    $term = rtrim($term, '*') . '%';
} else {
    $term .= '%';
}

if (substr($term, 0, 1) === '*') {
    $term = '%' . ltrim($term, '*');
}

$sql = "SELECT tag_id, tag_name AS name, category FROM tags WHERE tag_name LIKE ? LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $term, $config["ajax_search_limit"]);
$stmt->execute();
$result = $stmt->get_result();
$tags = $result->fetch_all(MYSQLI_ASSOC);

foreach ($tags as $key => $tag) {
    $sql = "SELECT COUNT(*) AS count FROM post_tags WHERE tag_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tag["tag_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $tags[$key]["count"] = $result->fetch_assoc()["count"];
    unset($tags[$key]["tag_id"]);
}

usort($tags, function ($a, $b) {
    return $b['count'] <=> $a['count'];
});

echo json_encode($tags);

$conn->close();
