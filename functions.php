<?php

if (!defined('WORLD')) {
    die("The World!");
}

function readConfig($file)
{
    $config = [];
    $lines = file($file);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $config[trim($key)] = trim($value);
    }
    return $config;
}

function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function processBBCode($input)
{
    // [[wiki_term]] -> <a href="/wiki.php?a=t&t=wiki_term">wiki_term</a>
    // [[wiki_term|display]] -> <a href="/wiki.php?a=t&t=wiki_term">display</a>
    // [[wiki_term|]] -> <a href="/wiki.php?a=t&t=wiki_term">wiki_term</a>

    $pattern = '/\[\[(.*?)\]\]/';
    $replacement = '<a href="/wiki.php?a=t&t=$1">$1</a>';

    // [external link](http://example.com) -> <a href="/linkfilter.php?t=http://example.com" target="_blank">[external link]</a>
    // [external link](http://example.com|title) -> <a href="/linkfilter.php?t=http://example.com" target="_blank" title="title">[external link]</a>
    // [http://example.com] -> <a href="/linkfilter.php?t=http://example.com" target="_blank">http://example.com</a>
    // [http://example.com|title] -> <a href="/linkfilter.php?t=http://example.com" target="_blank" title="title">http://example.com</a>

    $pattern2 = '/\[(.*?)\]\((.*?)\)/';
    $replacement2 = '<a href="/linkfilter.php?t=$2" target="_blank">$1</a>';

    $pattern3 = '/\[(.*?)\]\((.*?)\|(.*?)\)/';
    $replacement3 = '<a href="/linkfilter.php?t=$2" target="_blank" title="$3">$1</a>';

    $pattern4 = '/\[(.*?)\]\((.*?)\|(.*?)\)/';
    $replacement4 = '<a href="/linkfilter.php?t=$2" target="_blank" title="$3">$1</a>';

    // [b]bold[/b] -> <strong>bold</strong>
    // [i]italic[/i] -> <em>italic</em>
    // [u]underline[/u] -> <u>underline</u>
    // [s]strikethrough[/s] -> <s>strikethrough</s>
    // [spoiler]spoiler[/spoiler] -> <span class="spoiler">spoiler</span>

    $pattern5 = '/\[b\](.*?)\[\/b\]/';
    $replacement5 = '<strong>$1</strong>';

    $pattern6 = '/\[i\](.*?)\[\/i\]/';
    $replacement6 = '<em>$1</em>';

    $pattern7 = '/\[u\](.*?)\[\/u\]/';
    $replacement7 = '<u>$1</u>';

    $pattern8 = '/\[s\](.*?)\[\/s\]/';
    $replacement8 = '<s>$1</s>';

    $pattern9 = '/\[spoiler\](.*?)\[\/spoiler\]/';
    $replacement9 = '<span class="spoiler">$1</span>';

    $input = preg_replace($pattern, $replacement, $input);
    $input = preg_replace($pattern2, $replacement2, $input);
    $input = preg_replace($pattern3, $replacement3, $input);
    $input = preg_replace($pattern4, $replacement4, $input);
    $input = preg_replace($pattern5, $replacement5, $input);
    $input = preg_replace($pattern6, $replacement6, $input);
    $input = preg_replace($pattern7, $replacement7, $input);
    $input = preg_replace($pattern8, $replacement8, $input);
    $input = preg_replace($pattern9, $replacement9, $input);

    return $input;
}

function validateRating($rating)
{
    $safeRatings = [
        "s",
        "safe",
    ];
    $questionableRatings = [
        "q",
        "ques",
        "questionable",
    ];
    $explicitRatings = [
        "e",
        "exp",
        "explicit",
    ];
    $safeQuestionableRatings = [
        "sq",
        "saqu",
        "safequestionable",
    ];
    $questionableExplicitRatings = [
        "qe",
        "quesexp",
        "questionableexplicit",
    ];
    $safeExplicitRatings = [
        "se",
        "saex",
        "safeexplicit",
    ];
    $allRatings = [
        "a",
        "all",
    ];

    if (in_array($rating, $safeRatings)) {
        return "safe";
    } elseif (in_array($rating, $questionableRatings)) {
        return "questionable";
    } elseif (in_array($rating, $explicitRatings)) {
        return "explicit";
    } elseif (in_array($rating, $safeQuestionableRatings)) {
        return "safequestionable";
    } elseif (in_array($rating, $questionableExplicitRatings)) {
        return "questionableexplicit";
    } elseif (in_array($rating, $safeExplicitRatings)) {
        return "safeexplicit";
    } elseif (in_array($rating, $allRatings)) {
        return "all";
    } else {
        return "all";
    }
}

function sanitize($data, $type = 'string')
{
    switch ($type) {
        case 'string':
            $data = trim($data); // Remove whitespace from both sides of the string
            $data = stripslashes($data); // Remove backslashes
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Convert special characters to HTML entities
            break;

        case 'email':
            $data = filter_var($data, FILTER_SANITIZE_EMAIL); // Sanitize email address
            break;

        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL); // Sanitize URL
            break;

        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT); // Sanitize integer
            break;

        case 'float':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); // Sanitize float
            break;

        case 'bool':
            $data = filter_var($data, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE); // Validate boolean
            break;

        case 'array':
            if (is_array($data)) {
                $data = array_map(function ($item) {
                    return sanitize($item); // Recursively sanitize array items
                }, $data);
            } else {
                $data = null;
            }
            break;

        default:
            $data = htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8'); // Default sanitation
            break;
    }

    return $data;
}

function fetchLanguagesJson($url, $tmpDir)
{
    $languagesJson = file_get_contents($url);
    if ($languagesJson === false) {
        die("Failed to fetch languages JSON.");
    }
    $timestampFile = $tmpDir . "/languages.json." . time();
    file_put_contents($timestampFile, $languagesJson);
    return $languagesJson;
}

function loadLocaleFile($file)
{
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    } else {
        die("Failed to read locale file: $file");
    }
}

function createTmpDir($dir)
{
    if (!file_exists($dir)) {
        try {
            mkdir($dir, 0775, true);
        } catch (Exception $e) {
            die("Failed to create temporary directory. Reason: " . $e->getMessage());
        }
    }
}

function getLatestFile($pattern, $cacheDuration)
{
    $files = glob($pattern);
    $latestFile = null;

    foreach ($files as $file) {
        if (is_file($file)) {
            if (time() - filemtime($file) >= $cacheDuration) {
                unlink($file);
            } else {
                $latestFile = $file;
            }
        }
    }

    return $latestFile;
}

function determineCategory($tag)
{
    // If $tag starts with...
    // "copyright", "copy" return "copyright"
    // "character", "char" return "character"
    // "artist", "art" return "artist"
    // "general", "gen" return "general"
    // "meta" return "meta"
    // "other" return "other"
    // Otherwise, return "general"

    // Added categories: "spirit", "liqueur", "preparation", "garnish", "glass"
    // (spirit|spi|liqueur|liq|preparation|prep|garnish|gar|glass|gl|copyright|copy|artist|art|character|char|general|meta|other)

    if (str_starts_with($tag, "spi")) {
        return "spirit";
    } elseif (str_starts_with($tag, "liq") ) {
        return "liqueur";
    } elseif (str_starts_with($tag, "prep")) {
        return "preparation";
    } elseif (str_starts_with($tag, "gar")) {
        return "garnish";
    } elseif (str_starts_with($tag, "gl") ) {
        return "glass";
    } elseif (str_starts_with($tag, "copy")) {
        return "copyright";
    } elseif (str_starts_with($tag, "char")) {
        return "character";
    } elseif (str_starts_with($tag, "art") ) {
        return "artist";
    } elseif (str_starts_with($tag, "gen")) {
        return "general";
    } elseif (str_starts_with($tag, "meta")) {
        return "meta";
    } elseif (str_starts_with($tag, "other")) {
        return "other";
    } else {
        return "general";
    }
}

function determineStatus($status, $permissions)
{
    // If $status starts with...
    // "awaiting" return "awaiting"
    // "approved" return "approved"
    // "deleted" return "deleted"
    // "awaiting|approved" return "awaiting|approved"
    // "awaiting|deleted" return "awaiting|deleted"
    // "approved|deleted" return "approved|deleted"
    // "awaiting|approved|deleted" return "awaiting|approved|deleted"
    // Otherwise, return "awaiting|approved"
    // If contains "deleted", check if "moderate" or "admin" is in array $permissions, if not remove "deleted"

    $moderate  = false;
    if (in_array("moderate", $permissions) || in_array("admin", $permissions)) {
        $moderate = true;
    }

    if (str_contains($status, "awaiting") && str_contains($status, "approved") && str_contains($status, "deleted")) {
        if ($moderate) {
            return "awaiting|approved|deleted";
        } else {
            return "awaiting|approved";
        }
    } elseif (str_contains($status, "awaiting") && str_contains($status, "approved")) {
        return "awaiting|approved";
    } elseif (str_contains($status, "awaiting") && str_contains($status, "deleted")) {
        if ($moderate) {
            return "awaiting|deleted";
        } else {
            return "awaiting";
        }
    } elseif (str_contains($status, "approved") && str_contains($status, "deleted")) {
        if ($moderate) {
            return "approved|deleted";
        } else {
            return "approved";
        }
    } elseif (str_starts_with($status, "awaiting")) {
        return "awaiting";
    } elseif (str_starts_with($status, "approved")) {
        return "approved";
    } elseif (str_starts_with($status, "deleted") && $moderate) {
        return "deleted";
    } else {
        return "awaiting|approved";
    }
}

function writeStep($step)
{
    $stepFile = __DIR__ . "/__init/.tmp/step.txt";
    file_put_contents($stepFile, $step);
}

function trl_replace($params, $smarty)
{
    $string = $params[0] ?? ($params['s'] ?? '');
    $needle = $params[1] ?? ($params['n'] ?? '');
    $replacement = $params[2] ?? ($params['r'] ?? '');

    if (is_array($needle) && is_array($replacement)) {
        return str_replace($needle, $replacement, $string);
    } elseif (is_string($needle) && is_string($replacement)) {
        return str_replace($needle, $replacement, $string);
    }

    return $string;
}

function replace($string, $needle, $replacement)
{
    return str_replace($needle, $replacement, $string);
}

function writeConfig($file, $post)
{
    if (!file_exists($file)) {
        touch($file);
        chmod($file, 0775);
    }
    $env = fopen($file, "w");
    foreach ($post as $key => $value) {
        fwrite($env, "$key=$value\n");
    }
    fclose($env);
}

function nanotime()
{
    return (int)(microtime(true) * 1000000);
}





function getTags($conn, $tags, $maxTags, $blacklist = [])
{
    $count = 0;
    $_tags = [];


    $tags = array_merge($blacklist, $tags);

    // Remove tags from $_tags that are in the blacklist without the minus
    foreach ($tags as $overwriteBlacklistTag) {
        $tag = "-" . $overwriteBlacklistTag;
        if (($key = array_search($tag, $tags)) !== false) {
            unset($tags[$key]);
        }
    }

    $tags = array_filter($tags);
    $tags = array_unique($tags);

    foreach ($tags as $tag) {
        if (!str_contains($tag, "rating:") && !str_contains($tag, "user:") && !str_contains($tag, "status:")) {
            $count++;
            if ($count <= $maxTags) {
                $tag = trim($tag);
                $wildcard = false;
                $isBlacklist = false;

                if ($tag == "*" || empty($tag)) {
                    return [];
                }

                // Handle blacklisted tags (prefixed with "-")
                if (strpos($tag, "-") === 0) {
                    $isBlacklist = true;
                    $tag = substr($tag, 1); // Remove the "-" prefix
                }

                // Check tag length (excluding wildcards)
                $tagWithoutWildcards = str_replace("*", "", $tag);
                if (!empty($tagWithoutWildcards) && strlen($tagWithoutWildcards) < 2) {
                    die("A tag needs to be at least 2 characters long");
                }

                if (str_contains($tag, "*")) {
                    $wildcard = true;
                    $tag = str_replace("*", "%", $tag); // Convert * to %
                    $tagQuery = "SELECT tag_id, tag_name FROM tags WHERE tag_name LIKE ?";
                } else {
                    $tagQuery = "SELECT tag_id, tag_name FROM tags WHERE tag_name = ?";
                }

                $stmt = $conn->prepare($tagQuery);
                $stmt->bind_param("s", $tag);
                $stmt->execute();
                $tagResult = $stmt->get_result();

                if ($tagResult->num_rows == 0) {
                    if (!$isBlacklist) {
                        return []; // No matching tags, terminate for positive tags
                    }
                    continue; // Skip non-matching blacklisted tags
                }

                while ($row = $tagResult->fetch_assoc()) {
                    //echo $isBlacklist ? "-" : "";
                    //echo '"' . $row["tag_name"] . "\"<br>";
                    $_tags[] = [
                        "id" => $wildcard ? $tag : $row["tag_id"],
                        "name" => $row["tag_name"],
                        "wild" => $wildcard,
                        "blacklist" => $isBlacklist,
                    ];
                }

                $stmt->close();
            }
        }
    }

    foreach ($_tags as $key => $tag) {
        foreach ($_tags as $innerKey => $innerTag) {
            //echo ($tag["blacklist"] ? "+" : "-") . $tag['name'] . " " . ($innerTag["blacklist"] ? "+" : "-") . $innerTag['name'] . "<br>";
            if ($tag['name'] === $innerTag['name'] && $tag["blacklist"] && !$innerTag["blacklist"]) {
                unset($_tags[$key]);
                break;
            }
        }
    }

    //print_r($_tags);

    return [$_tags, $count];
}

function getPosts($conn, $tags, $limit, $offset, $rating = "all", $status = "awaiting|approved", $user = 0)
{
    $allPosts = false;
    $allTags = [];

    // Check for the "all posts" condition
    if (empty($tags) || (count($tags) === 1 && $tags[0]['id'] === '%')) {
        $allPosts = true;
    }

    $ratingCondition = "";
    // "all", "safe", "questionable", "explicit", "safequestionable", "questionableexplicit", "safeexplicit"
    if ($rating == "safe") {
        $ratingCondition = "AND posts.rating = 'safe'";
    } elseif ($rating == "questionable") {
        $ratingCondition = "AND posts.rating = 'questionable'";
    } elseif ($rating == "explicit") {
        $ratingCondition = "AND posts.rating = 'explicit'";
    } elseif ($rating == "safequestionable") {
        $ratingCondition = "AND (posts.rating = 'safe' OR posts.rating = 'questionable')";
    } elseif ($rating == "questionableexplicit") {
        $ratingCondition = "AND (posts.rating = 'questionable' OR posts.rating = 'explicit')";
    } elseif ($rating == "safeexplicit") {
        $ratingCondition = "AND (posts.rating = 'safe' OR posts.rating = 'explicit')";
    }

    if ($status == "awaiting") {
        $ratingCondition .= " AND posts.is_approved = 0 AND posts.deleted = 0";
    } elseif ($status == "approved") {
        $ratingCondition .= " AND posts.is_approved = 1 AND posts.deleted = 0";
    } elseif ($status == "deleted") {
        $ratingCondition .= " AND posts.deleted = 1";
    } elseif ($status == "awaiting|deleted") {
        $ratingCondition .= " AND (posts.is_approved = 0 OR posts.deleted = 1)";
    } elseif ($status == "approved|deleted") {
        $ratingCondition .= " AND (posts.is_approved = 1 OR posts.deleted = 1)";
    } elseif ($status == "awaiting|approved|deleted") {
        $ratingCondition .= " AND (posts.is_approved = 0 OR posts.is_approved = 1 OR posts.deleted = 1)";
    } else {
        $ratingCondition .= " AND (posts.is_approved = 0 OR posts.is_approved = 1) AND posts.deleted = 0";
    }

    if (!empty($user)) {
        $user = sanitize($user);
        $ratingCondition .= " AND posts.user_id = (SELECT user_id FROM users WHERE username = '$user')";
    }

    if ($allPosts) {
        $query = "SELECT SQL_CALC_FOUND_ROWS posts.* FROM posts WHERE 1=1 {$ratingCondition} ORDER BY posts.post_id DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
    } else {
        $positiveConditions = [];
        $negativeConditions = [];
        $params = [];
        $types = "";

        foreach ($tags as $tag) {
            if ($tag['blacklist']) {
                if ($tag['wild']) {
                    // Blacklisted wildcard
                    $negativeConditions[] = "NOT EXISTS (
                            SELECT 1 FROM post_tags pt
                            WHERE pt.post_id = posts.post_id
                            AND pt.tag_id IN (
                                SELECT tag_id FROM tags WHERE tag_name LIKE ?
                            )
                        )";
                    $params[] = $tag['id']; // Wildcard-adjusted tag_name (e.g., dog%)
                    $types .= "s"; // String type
                } else {
                    // Blacklisted exact match
                    $negativeConditions[] = "NOT EXISTS (
                            SELECT 1 FROM post_tags pt
                            WHERE pt.post_id = posts.post_id
                            AND pt.tag_id = ?
                        )";
                    $params[] = $tag['id']; // Exact tag ID
                    $types .= "i"; // Integer type
                }
            } else {
                if ($tag['wild']) {
                    // Positive wildcard
                    $positiveConditions[] = "EXISTS (
                            SELECT 1 FROM post_tags pt
                            WHERE pt.post_id = posts.post_id
                            AND pt.tag_id IN (
                                SELECT tag_id FROM tags WHERE tag_name LIKE ?
                            )
                        )";
                    $params[] = $tag['id']; // Wildcard-adjusted tag_name (e.g., cat%)
                    $types .= "s"; // String type
                } else {
                    // Positive exact match
                    $positiveConditions[] = "EXISTS (
                            SELECT 1 FROM post_tags pt
                            WHERE pt.post_id = posts.post_id
                            AND pt.tag_id = ?
                        )";
                    $params[] = $tag['id']; // Exact tag ID
                    $types .= "i"; // Integer type
                }
            }
        }

        $queryConditions = implode(" AND ", array_merge($positiveConditions, $negativeConditions));
        $query = "SELECT SQL_CALC_FOUND_ROWS posts.* FROM posts WHERE $queryConditions AND posts.deleted = 0 {$ratingCondition}
                      ORDER BY posts.post_id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $conn->error);
            die("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }

    $stmt->close();

    // Get the total number of posts matching the query
    $result = $conn->query("SELECT FOUND_ROWS() AS total");
    $totalPosts = $result->fetch_assoc()['total'];

    // Add tags as a string to each post and update $allTags
    foreach ($posts as $key => $post) {
        $postId = $post['post_id'];
        $tagQuery = "SELECT t.* FROM tags t
                         JOIN post_tags pt ON pt.tag_id = t.tag_id
                         WHERE pt.post_id = ?";
        $tagStmt = $conn->prepare($tagQuery);
        $tagStmt->bind_param("i", $postId);
        $tagStmt->execute();
        $tagResult = $tagStmt->get_result();

        $tags = [];
        while ($tagRow = $tagResult->fetch_assoc()) {
            // Categorize tags for $allTags
            $allTags[$tagRow["category"]][] = ["tag_id" => $tagRow["tag_id"], "tag_name" => $tagRow["tag_name"]];
            $tags[] = $tagRow['tag_name'];
        }

        $tagStmt->close();

        // Add tags as a string to the current post
        $posts[$key]['tags'] = implode(" ", $tags);
        $posts[$key]["score"] = 0;

        if (in_array($post["file_extension"], ["mp4", "webm", "mkv"])) {
            $posts[$key]["is_video"] = 1;
        }
    }

    // Deduplicate and sort $allTags by count
    foreach ($allTags as $category => $tags) {
        $allTags[$category] = array_map("unserialize", array_unique(array_map("serialize", $tags)));

        foreach ($allTags[$category] as $tagKey => $tag) {
            $sql = "SELECT COUNT(*) AS count FROM post_tags WHERE tag_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $tag["tag_id"]);
            $stmt->execute();
            $result = $stmt->get_result();
            $tagCount = $result->fetch_assoc()["count"];
            $allTags[$category][$tagKey] = [
                "name" => $tag["tag_name"],
                "count" => $tagCount,
            ];
        }

        // Sort by count in descending order
        usort($allTags[$category], function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });
    }

    return [$posts, $totalPosts, $allTags];
}
