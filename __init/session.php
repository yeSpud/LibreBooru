<?php

if (!defined('WORLD')) {
    die("The World!");
}

$logged = false;
$user = [];
$levelId = $levelIds["guest"];

if (isset($_COOKIE["token"]) && !empty($_COOKIE["token"])) {
    $token = $conn->real_escape_string($_COOKIE["token"]);
    if (strlen($token) == 64) {
        $userAgent = $conn->real_escape_string($_SERVER["HTTP_USER_AGENT"]);
        $_ipAddress = $conn->real_escape_string($_SERVER["REMOTE_ADDR"]);
        $ipAddress = hash("sha256", $_ipAddress);

        //$sql = "SELECT * FROM sessions WHERE session_id = ? AND user_agent = ? AND ip_address = ? LIMIT 1";
        //$sql = "SELECT * FROM sessions WHERE session_id = ? AND ip_address = ? LIMIT 1";
        $sql = "SELECT * FROM sessions WHERE session_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        //$stmt->bind_param("sss", $token, $userAgent, $ipAddress);
        //$stmt->bind_param("ss", $token, $ipAddress);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();

        if (empty($session)) {
            setcookie("token", "", time() - 3600, "/", "", false, true);
            header("Location: /account.php?a=l");
            exit;
        }

        //if ($session["ip_address"] == hash("sha256", $_SERVER["REMOTE_ADDR"])) {
        if ($result->num_rows > 0) {
            //print_r($session);
            $sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $session["user_id"]);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user["is_banned"] == 1) {
                $sql = "DELETE FROM sessions WHERE session_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $token);
                $stmt->execute();

                setcookie("token", "", time() - 3600, "/", "", false, true);
                header("Location: /extra.php?a=b");
                exit;
            }

            $logged = true;
            $levelId = $user["user_level"];
        }
        //}
    }
}

$tmpSql = "SELECT * FROM user_levels WHERE level_id = ? LIMIT 1";
$tmpStmt = $conn->prepare($tmpSql);
$tmpStmt->bind_param("i", $levelId);
$tmpStmt->execute();
$userLevel = $tmpStmt->get_result()->fetch_assoc();
$permissions = explode(",", $userLevel["permissions"]);
foreach ($permissions as $key => $p) {
    $permissions[$key] = trim($p);
}

$showOriginal = false;
if (isset($_COOKIE["showOriginal"]) && !empty($_COOKIE["showOriginal"])) {
    $showOriginal = true;
}
$hideOriginalMessage = false;
if (isset($_COOKIE["hideOriginalMessage"]) && !empty($_COOKIE["hideOriginalMessage"])) {
    $hideOriginalMessage = true;
}

$smarty->assign("showOriginal", $showOriginal);
$smarty->assign("hideOriginalMessage", $hideOriginalMessage);
$smarty->assign("logged", $logged);
$smarty->assign("user", $user);
$smarty->assign("levelId", $levelId);
$smarty->assign("userLevel", $userLevel);
$smarty->assign("permissions", $permissions);
