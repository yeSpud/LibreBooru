<?php

require __DIR__ . "/../bootstrapper.php";

$action = "i";
$actions = ["i", "t", "e", "h"];
// "i"ndex, "t"erm, "e"dit, "h"istory

if (isset($_GET["a"]) && in_array($_GET["a"], $actions)) {
    $action = $_GET["a"];
}

$pageTitle = "Browse the " . $config["sitename"] . " Wiki";
$term = "%";
if ($action == "i") {
    $page = 1;
    if (isset($_GET["p"]) && is_numeric($_GET["p"]) && $_GET["p"] > 0) {
        $page = intval($_GET["p"]);
    }
    $perpage = $config["post_display_limit"] * 2;
    $offset = ($page - 1) * $perpage;
    $termSearch = $conn->real_escape_string($_GET["t"] ?? "") . "%";

    $termSql = "SELECT COUNT(*) AS count FROM wiki WHERE wiki_term LIKE ?";
    $stmt = $conn->prepare($termSql);
    $stmt->bind_param("s", $termSearch);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row["count"];
    $stmt->close();

    $termSql = "SELECT * FROM wiki WHERE wiki_term LIKE ? ORDER BY wiki_term ASC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($termSql);
    $stmt->bind_param("sii", $termSearch, $perpage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $terms = [];
    while ($row = $result->fetch_assoc()) {
        $tagSql = "SELECT category FROM tags WHERE tag_name = ? LIMIT 1";
        $tagStmt = $conn->prepare($tagSql);
        $tagStmt->bind_param("s", $row["wiki_term"]);
        $tagStmt->execute();
        $tagResult = $tagStmt->get_result();
        $tag = $tagResult->fetch_assoc();
        $row["category"] = $tag["category"];

        $lastUpdatedSql = "SELECT timestamp FROM wiki_history WHERE wiki_term = ? ORDER BY id DESC LIMIT 1";
        $lastUpdatedStmt = $conn->prepare($lastUpdatedSql);
        $lastUpdatedStmt->bind_param("s", $row["wiki_term"]);
        $lastUpdatedStmt->execute();
        $lastUpdatedResult = $lastUpdatedStmt->get_result();
        $lastUpdated = $lastUpdatedResult->fetch_assoc();
        $row["lastUpdated"] = $lastUpdated["timestamp"];
        $terms[] = $row;
    }

    $smarty->assign("terms", $terms);
    $smarty->assign("total", $total);
    $smarty->assign("perpage", $perpage);
    $smarty->assign("page", $page);
    $smarty->assign("searchTerm", $_GET["t"] ?? "");
    $pageTitle = "Search results for " . $termSearch . " in the " . $config["sitename"] . " Wiki";
} elseif (isset($_GET["t"])) {
    if ($action == "t") {
        $_term = $conn->real_escape_string($_GET["t"]);

        $tagSql = "SELECT category FROM tags WHERE tag_name = ? LIMIT 1";
        $stmt = $conn->prepare($tagSql);
        $stmt->bind_param("s", $_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $tag = $result->fetch_assoc();
        $stmt->close();

        if (empty($tag)) {
            header("Location: /wiki.php?a=i");
            exit;
        }

        $termSql = "SELECT * FROM wiki WHERE wiki_term = ?";
        $stmt = $conn->prepare($termSql);
        $stmt->bind_param("s", $_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $term = $result->fetch_assoc();
        $stmt->close();

        if ($term) {
            $pageTitle = $term["wiki_term"] . " in the " . $config["sitename"] . " Wiki";
            $term["content"] = processBBCode($term["content"]);
            //$term["content"] = htmlspecialchars($term["content"], ENT_QUOTES, 'UTF-8');
            $term["content"] = nl2br($term["content"]);

            $creatorSql = "SELECT username FROM users WHERE user_id = ?";
            $creatorStmt = $conn->prepare($creatorSql);
            $creatorStmt->bind_param("i", $term["user_id"]);
            $creatorStmt->execute();
            $creatorResult = $creatorStmt->get_result();
            $creator = $creatorResult->fetch_assoc();

            $lastUpdatedSql = "SELECT timestamp, user_id FROM wiki_history WHERE wiki_term = ? ORDER BY id DESC LIMIT 1";
            $lastUpdatedStmt = $conn->prepare($lastUpdatedSql);
            $lastUpdatedStmt->bind_param("s", $_term);
            $lastUpdatedStmt->execute();
            $lastUpdatedResult = $lastUpdatedStmt->get_result();
            $lastUpdated = $lastUpdatedResult->fetch_assoc();

            $lastUpdatedUsernameSql = "SELECT username FROM users WHERE user_id = ?";
            $lastUpdatedUsernameStmt = $conn->prepare($lastUpdatedUsernameSql);
            $lastUpdatedUsernameStmt->bind_param("i", $lastUpdated["user_id"]);
            $lastUpdatedUsernameStmt->execute();
            $lastUpdatedUsernameResult = $lastUpdatedUsernameStmt->get_result();
            $lastUpdatedUsername = $lastUpdatedUsernameResult->fetch_assoc();
            $lastUpdated["username"] = $lastUpdatedUsername["username"];

            $smarty->assign("last_updated", $lastUpdated);
            $smarty->assign("creator", $creator["username"]);
            $smarty->assign("category", $tag["category"]);
            $smarty->assign("term", $term);
        } else {
            $pageTitle = "Term not found";
            header("Location: /wiki.php?a=e&t=" . $_term);
            exit;
        }
    } elseif ($action == "e") {
        if (!in_array("moderate", $permissions) && !in_array("admin", $permissions)) {
            if (!in_array("wiki", $permissions)) {
                header("Location: /wiki.php?a=i&t=" . $_GET["t"]);
                exit;
            }
        }

        $_term = $conn->real_escape_string($_GET["t"]);

        $tagSql = "SELECT category FROM tags WHERE tag_name = ? LIMIT 1";
        $stmt = $conn->prepare($tagSql);
        $stmt->bind_param("s", $_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $tag = $result->fetch_assoc();
        $stmt->close();

        if (empty($tag)) {
            header("Location: /wiki.php?a=i");
            exit;
        }
        $smarty->assign("tag", $tag);

        $termSql = "SELECT * FROM wiki WHERE wiki_term = ?";
        $stmt = $conn->prepare($termSql);
        $stmt->bind_param("s", $_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $term = $result->fetch_assoc();
        $stmt->close();

        if ($term) {
            $pageTitle = "Edit " . $term["wiki_term"] . " in the " . $config["sitename"] . " Wiki";
            $smarty->assign("term", $term);
        } else {
            $pageTitle = "Create the term " . $_term . " in the " . $config["sitename"] . " Wiki";
        }
        $smarty->assign("_term", $_term);

        $isMod = false;
        if (in_array("moderate", $permissions) || in_array("admin", $permissions)) {
            $isMod = true;
        }
        $smarty->assign("isMod", $isMod);

        if (isset($_POST["edit"])) {
            $content = $_POST["content"];
            $pixivId = null;
            $fanboxId = null;
            $patreon = null;
            $twitter_id = null;
            if ($term) {
                $pixivId = !empty($term["pixiv_id"]) ? $term["pixiv_id"] : null;
                $fanboxId = !empty($term["fanbox_id"]) ? $term["fanbox_id"] : null;
                $patreon = !empty($term["patreon"]) ? $term["patreon"] : null;
                $twitter_id = !empty($term["twitter_id"]) ? $term["twitter_id"] : null;
            }
            if ($tag["category"] == "artist") {
                $pixivId = $conn->real_escape_string($_POST["pixiv_id"]);
                $fanboxId = $conn->real_escape_string($_POST["fanbox_id"]);
                $patreon = $conn->real_escape_string($_POST["patreon"]);
                $twitter_id = $conn->real_escape_string($_POST["twitter_id"]);
            }
            if ($isMod) {
                $locked = isset($_POST["locked"]) ? 1 : 0;
                if (!empty($term)) {
                    if ($locked !== $term["locked"]) {
                        $lockedBy = $user["user_id"];
                    }
                }
            }

            // Create or update the term
            if ($term) {
                $termSql = "UPDATE wiki SET content = ?, locked = ?, locked_by = ?, pixiv_id = ?, fanbox_id = ?, patreon = ?, twitter_id = ? WHERE wiki_term = ?";
                $stmt = $conn->prepare($termSql);
                $stmt->bind_param("sissssss", $content, $locked, $lockedBy, $pixivId, $fanboxId, $patreon, $twitter_id, $_term);
            } else {
                $termSql = "INSERT INTO wiki (wiki_term, content, locked, locked_by, pixiv_id, fanbox_id, patreon, twitter_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($termSql);
                $stmt->bind_param("ssisssssi", $_term, $content, $locked, $lockedBy, $pixivId, $fanboxId, $patreon, $twitter_id, $user["user_id"]);
            }

            $conn->begin_transaction();
            $stmt->execute();

            if ($content != $term["content"]) {
                // Insert into wiki_history
                $historySql = "INSERT INTO wiki_history (wiki_term, old_content, user_id, pixiv_id, patreon, twitter_id, fanbox_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $historyStmt = $conn->prepare($historySql);
                $historyStmt->bind_param("ssissss", $_term, $content, $user["user_id"], $pixivId, $patreon, $twitter_id, $fanboxId);
                $historyStmt->execute();
            }
            $conn->commit();
            $stmt->close();
            if ($content != $term["content"]) {
                $historyStmt->close();
            }

            header("Location: /wiki.php?a=t&t=" . $_term);
            exit;
        }
    } elseif ($action == "h") {
        $_term = $conn->real_escape_string($_GET["t"]);
        $termSql = "SELECT wiki_term FROM wiki WHERE wiki_term = ?";
        $stmt = $conn->prepare($termSql);
        $stmt->bind_param("s", $_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $term = $result->fetch_assoc();
        $stmt->close();

        if ($term) {
            $pageTitle = "History of " . $term["wiki_term"];
            $historySql = "SELECT * FROM wiki_history WHERE wiki_term = ? ORDER BY id DESC";
            $stmt = $conn->prepare($historySql);
            $stmt->bind_param("s", $_term);
            $stmt->execute();
            $result = $stmt->get_result();
            $history = [];
            while ($row = $result->fetch_assoc()) {
                $userSql = "SELECT username FROM users WHERE user_id = ?";
                $userStmt = $conn->prepare($userSql);
                $userStmt->bind_param("i", $row["user_id"]);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                $user = $userResult->fetch_assoc();
                $row["username"] = $user["username"];

                //$row["old_content"] = processBBCode($row["old_content"]);
                $row["old_content"] = htmlspecialchars($row["old_content"], ENT_QUOTES, 'UTF-8');
                $row["old_content"] = nl2br($row["old_content"]);

                $history[] = $row;
            }
            $smarty->assign("term", $term);
            $smarty->assign("history", $history);
        } else {
            $pageTitle = "Term not found";
            header("Location: /wiki.php?a=e&t=" . $term);
            exit;
        }
    }
}

if ($action !== "h" && $action !== "e") {
    $updateHistorySql = "SELECT DISTINCT wiki_term FROM (SELECT DISTINCT wiki_term, id FROM wiki_history ORDER BY id DESC) AS subquery LIMIT 15";
    $updateHistoryStmt = $conn->prepare($updateHistorySql);
    $updateHistoryStmt->execute();
    $updateHistoryResult = $updateHistoryStmt->get_result();
    $updateHistory = [];
    while ($row = $updateHistoryResult->fetch_assoc()) {
        $tagSql = "SELECT category FROM tags WHERE tag_name = ? LIMIT 1";
        $tagStmt = $conn->prepare($tagSql);
        $tagStmt->bind_param("s", $row["wiki_term"]);
        $tagStmt->execute();
        $tagResult = $tagStmt->get_result();
        $tag = $tagResult->fetch_assoc();
        $row["category"] = $tag["category"];
        $updateHistory[] = $row;
    }
    $smarty->assign("updateHistory", $updateHistory);
}

$smarty->assign("searchTerm", ($_GET["t"] ?? ""));
$smarty->assign("action", $action);
$smarty->assign("pagetitle", $pageTitle);
$smarty->assign("activePage", "wiki");
$smarty->display("wiki.tpl");

$conn->close();
