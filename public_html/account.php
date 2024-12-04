<?php

require __DIR__ . "/../bootstrapper.php";

$action = "i";
$actions = ["i", "l", "u", "r", "o", "p", "f", "c", "n"];
// "i"ndex, "l"ogin, logo"u"t, "r"egister, "o"ptions, "p"rofile, "f"avourites, "c"hange password, change user"n"ame

if (isset($_GET["a"]) && in_array($_GET["a"], $actions)) {
    $action = $_GET["a"];
}

$pageTitle = "Your Account on " . $config["sitename"];

$errors = [];
if ($action == "l") {
    if (isset($_POST["login"])) {
        if (!isset($_POST["username"]) || empty($_POST["username"]) || strlen($_POST["username"]) < 3 || strlen($_POST["username"]) > 32) {
            $errors[] = "Username must be between 3 and 32 characters.";
        }

        if (!isset($_POST["password"]) || empty($_POST["password"]) || strlen($_POST["password"]) < 8) {
            $errors[] = "Password must be at least 8 characters.";
        }

        $username = $conn->real_escape_string($_POST["username"]);
        $password = $conn->real_escape_string($_POST["password"]);

        $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();
        if ($result->num_rows == 0) {
            $errors[] = "Username or password is incorrect.";
        } else {
            if (password_verify($password, $row["password_hash"])) {
                $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $row["user_id"]);
                $stmt->execute();

                $userAgent = $conn->real_escape_string($_SERVER["HTTP_USER_AGENT"]);
                $_ipAddress = $conn->real_escape_string($_SERVER["REMOTE_ADDR"]);
                $ipAddress = hash("sha256", $_ipAddress);
                //$expiresAt = date("Y-m-d H:i:s", strtotime("+1 month"));
                $expiresAt = date("Y-m-d H:i:s", strtotime("+1 year"));
                $token = bin2hex(random_bytes(32));

                $sql = "INSERT INTO sessions (user_id, session_id, user_agent, ip_address, expires_at) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issss", $row["user_id"], $token, $userAgent, $ipAddress, $expiresAt);
                $stmt->execute();

                //Check if HTTP or HTTPS and set cookie accordingly
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                    $secure = true;
                } else {
                    $secure = false;
                }

                setcookie("token", $token, strtotime($expiresAt), "/", "", $secure, true);

                header("Location: /account.php?a=i");
                exit;
            } else {
                $errors[] = "Username or password is incorrect.";
            }
        }
    }
} elseif ($action == "r") {
    if (file_exists(__DIR__ . "/../software/data/{$config["language"]}_signup_guidelines.md")) {
        $guidelinesRaw = file_get_contents(__DIR__ . "/../software/data/{$config["language"]}_signup_guidelines.md");
        $guidelines = $parsedown->text($guidelinesRaw);
        $smarty->assign("guidelines", $guidelines);
    } elseif (file_exists(__DIR__ . "/../software/data/en_signup_guidelines.md")) {
        $guidelinesRaw = file_get_contents(__DIR__ . "/../software/data/en_signup_guidelines.md");
        $guidelines = $parsedown->text($guidelinesRaw);
        $smarty->assign("guidelines", $guidelines);
    }
    if (isset($_POST["register"])) {
        if (!isset($_POST["username"]) || empty($_POST["username"]) || strlen($_POST["username"]) < 3 || strlen($_POST["username"]) > 32) {
            $errors[] = "Username must be between 3 and 32 characters.";
        }

        if (!isset($_POST["password"]) || empty($_POST["password"]) || strlen($_POST["password"]) < 8) {
            $errors[] = "Password must be at least 8 characters.";
        }

        if (!isset($_POST["password2"]) || empty($_POST["password2"]) || $_POST["password"] != $_POST["password2"]) {
            $errors[] = "Passwords do not match.";
        }

        if (empty($errors)) {
            $username = $conn->real_escape_string($_POST["username"]);
            $password = $conn->real_escape_string($_POST["password"]);
            $password2 = $conn->real_escape_string($_POST["password2"]);
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errors[] = "Username already in use.";
            } else {
                $sql = "INSERT INTO users (username, email, password_hash, last_login, user_level, is_banned, profile_picture) VALUES (?, NULL, ?, NULL, ?, 0, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssis", $username, $password_hash, $levelIds["user"], $config["default_profile_picture"]);
                $stmt->execute();

                header("Location: /account.php?a=l");
                exit;
            }
        }
    }
} elseif ($action == "u") {
    if ($logged) {
        // Delete session from db
        $sql = "DELETE FROM sessions WHERE session_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $_COOKIE["token"]);
        $stmt->execute();

        // Delete cookie
        $secure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $secure = true;
        }
        setcookie("token", "", time() - 3600, "/", "", false, true);
    } else {
    }

    header("Location: /account.php?a=i");
    exit;
} elseif ($action == "c") {
    if ($logged) {
        if (isset($_POST["change_password"])) {
            if ($config["change_pass_requires_old"]) {
                if (!isset($_POST["old_password"]) || empty($_POST["old_password"]) || strlen($_POST["old_password"]) < 8) {
                    $errors[] = "Old password must be at least 8 characters.";
                }
            }

            if (!isset($_POST["new_password"]) || empty($_POST["new_password"]) || strlen($_POST["new_password"]) < 8) {
                $errors[] = "New password must be at least 8 characters.";
            }

            if (!isset($_POST["new_password2"]) || empty($_POST["new_password2"]) || $_POST["new_password"] != $_POST["new_password2"]) {
                $errors[] = "New passwords do not match.";
            }

            if (empty($errors)) {
                if ($config["change_pass_requires_old"]) {
                    $old_password = $conn->real_escape_string($_POST["old_password"]);
                }
                $new_password = $conn->real_escape_string($_POST["new_password"]);
                $new_password2 = $conn->real_escape_string($_POST["new_password2"]);

                $sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user["user_id"]);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if ($config["change_pass_requires_old"]) {
                    $verified = password_verify($old_password, $row["password_hash"]);
                    if (!$verified) {
                        $errors[] = "Old password is incorrect.";
                    }
                } else {
                    $verified = true;
                }

                if ($verified) {
                    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

                    $sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $new_password_hash, $user["user_id"]);
                    $stmt->execute();

                    // Destroy all sessions from this user
                    $sql = "DELETE FROM sessions WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user["user_id"]);
                    $stmt->execute();

                    header("Location: /account.php?a=i");
                    exit;
                }
            }
        }
    }
} elseif ($action == "n") {
    if ($logged) {
        if (in_array("admin", $permissions)) {
            if (isset($_POST["change_username"])) {
                if (!isset($_POST["new_username"]) || empty($_POST["new_username"]) || strlen($_POST["new_username"]) < 3 || strlen($_POST["new_username"]) > 32) {
                    $errors[] = "New username must be between 3 and 32 characters.";
                }

                if (empty($errors)) {
                    $new_username = $conn->real_escape_string($_POST["new_username"]);

                    $sql = "SELECT * FROM users WHERE BINARY username = ? LIMIT 1";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $new_username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $errors[] = "Username already in use.";
                    } else {
                        $sql = "UPDATE users SET username = ? WHERE user_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("si", $new_username, $user["user_id"]);
                        $stmt->execute();

                        header("Location: /account.php?a=i");
                        exit;
                    }
                }
            }
        }
    }
} elseif ($action == "p") {
    if (!isset($_GET["id"]) || empty($_GET["id"]) || !is_numeric($_GET["id"])) {
        header("Location: /account.php?a=i");
        exit;
    }

    $_id = $_GET["id"];
    $tab = "p";

    if (isset($_GET["t"]) && !empty($_GET["t"]) && $_GET["t"] == "r") {
        $tab = "r";
    }

    $profileSql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
    $profileStmt = $conn->prepare($profileSql);
    $profileStmt->bind_param("i", $_id);
    $profileStmt->execute();
    $profileResult = $profileStmt->get_result();
    $profile = $profileResult->fetch_assoc();

    if (!$profile) {
        header("Location: /account.php?a=i");
        exit;
    }

    if ($tab == "p") {
        $levelSql = "SELECT level_name FROM user_levels WHERE level_id = ? LIMIT 1";
        $levelStmt = $conn->prepare($levelSql);
        $levelStmt->bind_param("i", $profile["user_level"]);
        $levelStmt->execute();
        $levelResult = $levelStmt->get_result();
        $profile["level_name"] = $levelResult->fetch_assoc()["level_name"];

        $postCountSql = "SELECT COUNT(*) AS post_count FROM posts WHERE user_id = ? AND deleted = 0";
        $postCountStmt = $conn->prepare($postCountSql);
        $postCountStmt->bind_param("i", $_id);
        $postCountStmt->execute();
        $postCountResult = $postCountStmt->get_result();
        $profile["post_count"] = $postCountResult->fetch_assoc()["post_count"];

        $deletedPostCountSql = "SELECT COUNT(*) AS deleted_post_count FROM posts WHERE user_id = ? AND deleted = 1";
        $deletedPostCountStmt = $conn->prepare($deletedPostCountSql);
        $deletedPostCountStmt->bind_param("i", $_id);
        $deletedPostCountStmt->execute();
        $deletedPostCountResult = $deletedPostCountStmt->get_result();
        $profile["deleted_post_count"] = $deletedPostCountResult->fetch_assoc()["deleted_post_count"];

        $favouritesCountSql = "SELECT COUNT(*) AS favourites_count FROM favourites WHERE user_id = ?";
        $favouritesCountStmt = $conn->prepare($favouritesCountSql);
        $favouritesCountStmt->bind_param("i", $_id);
        $favouritesCountStmt->execute();
        $favouritesCountResult = $favouritesCountStmt->get_result();
        $profile["favourites_count"] = $favouritesCountResult->fetch_assoc()["favourites_count"];

        $postEditCountSql = "SELECT COUNT(distinct commit_id) AS post_edit_count FROM tag_history WHERE user_id = ?";
        $postEditCountStmt = $conn->prepare($postEditCountSql);
        $postEditCountStmt->bind_param("i", $_id);
        $postEditCountStmt->execute();
        $postEditCountResult = $postEditCountStmt->get_result();
        $profile["post_edit_count"] = $postEditCountResult->fetch_assoc()["post_edit_count"];

        $wikiEditCountSql = "SELECT COUNT(*) AS wiki_edit_count FROM wiki_history WHERE user_id = ?";
        $wikiEditCountStmt = $conn->prepare($wikiEditCountSql);
        $wikiEditCountStmt->bind_param("i", $_id);
        $wikiEditCountStmt->execute();
        $wikiEditCountResult = $wikiEditCountStmt->get_result();
        $profile["wiki_edit_count"] = $wikiEditCountResult->fetch_assoc()["wiki_edit_count"];

        $favouriteIdsSql = "SELECT post_id FROM favourites WHERE user_id = ? ORDER BY id DESC LIMIT 6";
        $favouriteIdsStmt = $conn->prepare($favouriteIdsSql);
        $favouriteIdsStmt->bind_param("i", $_id);
        $favouriteIdsStmt->execute();
        $favouriteIdsResult = $favouriteIdsStmt->get_result();
        $posts = [];
        while ($favouriteId = $favouriteIdsResult->fetch_assoc()) {
            $postSql = "SELECT * FROM posts WHERE post_id = ? LIMIT 1";
            $postStmt = $conn->prepare($postSql);
            $postStmt->bind_param("i", $favouriteId["post_id"]);
            $postStmt->execute();
            $postResult = $postStmt->get_result();
            $post = $postResult->fetch_assoc();

            $posts[] = $post;
        }

        $uploads = getPosts($conn, "", 6, 0, "all", determineStatus(strtolower(trim("awaiting|approved|deleted")), $permissions), $profile["username"])[0];
        $smarty->assign("favourites", $posts);
        $smarty->assign("uploads", $uploads);
        $pageTitle = $profile["username"] . "'s Account on " . $config["sitename"];
    } else {
        $canJudge = false;
        if (in_array("judge", $permissions) || in_array("moderate", $permissions) || in_array("admin", $permissions)) {
            $canJudge = true;
        }

        if ($logged && $_GET["id"] == $user["user_id"]) {
            $canJudge = false;
        }

        if ($canJudge) {
            $judgeSql = "SELECT * FROM reputation WHERE user_id = ? AND giver_id = ? LIMIT 1";
            $judgeStmt = $conn->prepare($judgeSql);
            $judgeStmt->bind_param("ii", $_id, $user["user_id"]);
            $judgeStmt->execute();
            $judgeResult = $judgeStmt->get_result();
            $judge = $judgeResult->fetch_assoc();

            if ($judge) {
                $canJudge = false;
            }
        }

        if ($canJudge) {
            if (isset($_POST["judge"])) {
                $given = isset($_POST["r"]) ? ($_POST["r"] == 1 ? "+" : "-") : $errors[] = "Please select a reputation to give.";
                $comment = isset($_POST["comment"]) ? (strlen($_POST["comment"]) < 5 ? $errors[] = "Comment must be at least 5 characters." : $_POST["comment"]) : $errors[] = "Please enter a comment.";

                if (empty($errors)) {
                    $sql = "INSERT INTO reputation (user_id, giver_id, given, comment) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiis", $_id, $user["user_id"], $given, $comment);
                    $stmt->execute();

                    header("Location: /account.php?a=p&id={$_id}&t=r");
                    exit;
                }
            }
        }

        $reputationSql = "SELECT * FROM reputation WHERE user_id = ? ORDER BY reputation_id DESC";
        $reputationStmt = $conn->prepare($reputationSql);
        $reputationStmt->bind_param("i", $_id);
        $reputationStmt->execute();
        $reputationResult = $reputationStmt->get_result();
        $reputation = [];
        while ($rep = $reputationResult->fetch_assoc()) {
            $giverSql = "SELECT username FROM users WHERE user_id = ? LIMIT 1";
            $giverStmt = $conn->prepare($giverSql);
            $giverStmt->bind_param("i", $rep["giver_id"]);
            $giverStmt->execute();
            $giverResult = $giverStmt->get_result();
            $giver = $giverResult->fetch_assoc();

            $rep["giver_username"] = $giver["username"];
            $reputation[] = $rep;
        }

        $smarty->assign("reputation", $reputation);
        $smarty->assign("canJudge", $canJudge);
        $pageTitle = $profile["username"] . "'s Reputation on " . $config["sitename"];
    }

    $repCountPlusSql = "SELECT COUNT(*) AS rep_count_plus FROM reputation WHERE user_id = ? AND given = '+'";
    $repCountPlusStmt = $conn->prepare($repCountPlusSql);
    $repCountPlusStmt->bind_param("i", $_id);
    $repCountPlusStmt->execute();
    $repCountPlusResult = $repCountPlusStmt->get_result();
    $repCountPlus = $repCountPlusResult->fetch_assoc()["rep_count_plus"];

    $repCountMinusSql = "SELECT COUNT(*) AS rep_count_minus FROM reputation WHERE user_id = ? AND given = '-'";
    $repCountMinusStmt = $conn->prepare($repCountMinusSql);
    $repCountMinusStmt->bind_param("i", $_id);
    $repCountMinusStmt->execute();
    $repCountMinusResult = $repCountMinusStmt->get_result();
    $repCountMinus = $repCountMinusResult->fetch_assoc()["rep_count_minus"];

    $profile["rep_count_plus"] = $repCountPlus;
    $profile["rep_count_minus"] = $repCountMinus;

    $smarty->assign("tab", $tab);
    $smarty->assign("profile", $profile);
}

if (!empty($errors)) {
    $errors[] = "There are errors! To continue, please fix them.";
}

$activePage = "account";

$smarty->assign("errors", $errors);
$smarty->assign("action", $action);
$smarty->assign("activePage", $activePage);
$smarty->assign("pagetitle", $pageTitle);
$smarty->display("account.tpl");
