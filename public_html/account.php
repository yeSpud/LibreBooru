<?php

require __DIR__ . "/../bootstrapper.php";

$action = "i";
$actions = ["i", "l", "u", "r", "o", "p", "f", "c", "n"];
// "i"ndex, "l"ogin, logo"u"t, "r"egister, "o"ptions, "p"rofile, "f"avourites, "c"hange password, change user"n"ame

if (isset($_GET["a"]) && in_array($_GET["a"], $actions)) {
    $action = $_GET["a"];
}

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
}

if (!empty($errors)) {
    $errors[] = "There are errors! To continue, please fix them.";
}

$activePage = "account";

$smarty->assign("errors", $errors);
$smarty->assign("action", $action);
$smarty->assign("activePage", $activePage);
$smarty->assign("pagetitle", "Your Account on " . $config["sitename"]);
$smarty->display("account.tpl");
