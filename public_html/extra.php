<?php

require __DIR__ . "/../bootstrapper.php";

$action = "a";
$actions = ["a", "c", "t", "s", "h", "o", "b", "p"];
// "a"bout, "c"omments, "t"ags, "s"tats, "h"elp, t"o"s, "b"anned, "p"laylist
// Banned is ??
// Playlist is for viewing the user's playlist

if ((isset($_GET["a"]) && in_array($_GET["a"], $actions)) || (isset($_POST["a"]) && in_array($_POST["a"], $actions))) {
    $action = $_GET["a"] ?? $_POST["a"];
}

if ($action == "t") {
    $searchTerm = "";
    if (isset($_GET["t"])) {
        $searchTerm = $_GET["t"];
    }

    if (isset($_GET["e"]) || isset($_POST["edit"]) || isset($_POST["cancel"])) {
        if (!in_array("tag", $permissions)) {
            header("Location: /extra.php?a=t&t=" . ($_POST["t"] ?? "") . "&s=" . ($_POST["s"] ?? "") . "&o=" . ($_POST["o"] ?? "") . "&p=" . ($_POST["p"] ?? ""));
            exit;
        }
        $e = $_GET["e"] ?? ($_POST["n"] ?? "");
        $s = $_GET["s"] ?? ($_POST["s"] ?? "");
        $o = $_GET["o"] ?? ($_POST["o"] ?? "");
        if (empty($e)) {
            header("Location: /extra.php?a=t&t={$searchTerm}&s=$s&o=$o");
        }

        $sql = "SELECT * FROM tags WHERE tag_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $e);
        $stmt->execute();
        $result = $stmt->get_result();
        $tag = $result->fetch_assoc();
        if (!$tag) {
            header("Location: /extra.php?a=t&t={$searchTerm}&s=$s&o=$o");
        }
        $smarty->assign("tag", $tag);

        if ($tag["locked"] && !in_array("moderate", $permissions) && !in_array("admin", $permissions)) {
            header("Location: /extra.php?a=t&t=" . ($_POST["t"] ?? "") . "&s=" . ($_POST["s"] ?? "") . "&o=" . ($_POST["o"] ?? "") . "&p=" . ($_POST["p"] ?? ""));
            exit;
        }

        if (isset($_POST["edit"])) {
            $category = $_POST["c"] ?? "general";
            if (in_array("moderate", $permissions) || in_array("admin", $permissions)) {
                $locked = isset($_POST["l"]) ? 1 : 0;
            } else {
                $locked = $tag["locked"];
            }
            if ($locked != $tag["locked"] && $locked == 1) {
                $locked_by = $user["user_id"];
            }
            if (!in_array($category, ["copyright", "character", "artist", "general", "meta", "other"])) {
                $category = "general";
            }

            $sql = "UPDATE tags SET category = ?, locked = $locked, locked_by = " . ($locked_by ?? "NULL") . " WHERE tag_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $category, $tag["tag_id"]);
            $stmt->execute();

            $sql = "INSERT INTO tag_edit_history (tag_name, user_id, category, locked) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisi", $tag["tag_name"], $user["user_id"], $category, $locked);
            $stmt->execute();

            header("Location: /extra.php?a=t&t=" . ($_POST["t"] ?? "") . "&s=" . ($_POST["s"] ?? "") . "&o=" . ($_POST["o"] ?? "") . "&p=" . ($_POST["p"] ?? ""));
        }
        if (isset($_POST["cancel"])) {
            header("Location: /extra.php?a=t&t=" . ($_POST["t"] ?? "") . "&s=" . ($_POST["s"] ?? "") . "&o=" . ($_POST["o"] ?? "") . "&p=" . ($_POST["p"] ?? ""));
            exit;
        }
    } else {
        $_searchTerm = str_replace("*", "%", $searchTerm == "" ? "%" : $searchTerm);
        if (substr($searchTerm, -1) != "%") {
            //$_searchTerm .= "%";
        }
        $order = "DESC";
        if (isset($_GET["o"]) && $_GET["o"] == "a") {
            $order = "ASC";
        }
        if (isset($_GET["s"])) {
            switch ($_GET["s"]) {
                case "n":
                    $sort = "ORDER BY tags.tag_name $order";
                    break;
                case "c":
                    $sort = "ORDER BY post_count $order";
                    break;
                case "u":
                default:
                    $sort = "ORDER BY last_used $order";
                    break;
            }
        } else {
            $sort = "ORDER BY last_used $order";
        }

        $page = 1;
        if (isset($_GET["p"]) && is_numeric($_GET["p"]) && $_GET["p"] > 0) {
            $page = intval($_GET["p"]);
        }
        $perpage = $config["post_display_limit"] * 2;
        $offset = ($page - 1) * $perpage;

        $sql = "
        SELECT tags.tag_id, tags.tag_name, tags.category, tags.locked,
               (SELECT COUNT(*) FROM post_tags WHERE post_tags.tag_id = tags.tag_id) AS post_count,
               (SELECT MAX(timestamp) FROM tag_history WHERE tag_history.tag_id = tags.tag_id) AS last_used
        FROM tags 
        WHERE tag_name LIKE ? 
        $sort 
        LIMIT $offset, $perpage";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $_searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $tags = [];
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }

        // Get total number of tags for pagination
        $sqlTotal = "SELECT COUNT(*) as total FROM tags WHERE tag_name LIKE ?";
        $stmtTotal = $conn->prepare($sqlTotal);
        $stmtTotal->bind_param("s", $_searchTerm);
        $stmtTotal->execute();
        $resultTotal = $stmtTotal->get_result();
        $totalTags = $resultTotal->fetch_assoc()["total"];
        $totalPages = ceil($totalTags / $perpage);

        $smarty->assign("totalPages", $totalPages);

        $smarty->assign("tags", $tags);
        $smarty->assign("page", $page);
        $smarty->assign("totalPages", $totalPages);
    }
    $smarty->assign("searchTerm", $searchTerm);
    $smarty->assign("pagetitle", "Browse all Tags on " . $config["sitename"]);
    $smarty->assign("activePage", "tags");
}

$smarty->assign("action", $action);
$smarty->display("extra.tpl");
