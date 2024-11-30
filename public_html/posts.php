<?php

require __DIR__ . "/../bootstrapper.php";

$action = "i";
$actions = ["i", "s", "p", "h", "a", "r"];
// "i"ndex, "s"earch, "p"ost, "h"istory, "a"dd, "r"andom
// History is tag history
$errors = [];

if (isset($_GET["a"]) && in_array($_GET["a"], $actions)) {
    $action = $_GET["a"];
}

// Get the search term from the user (sanitize input)
$searchTerm = isset($_GET['t']) ? trim($_GET['t']) : '';
if (empty($searchTerm)) {
    //$searchTerm = '*';
    $searchTerm = '';
}
$urlSearchTerm = urlencode($searchTerm);

if ($action == "s" || $action == "i") {
    $page = 1;
    if (isset($_GET["p"]) && is_numeric($_GET["p"]) && $_GET["p"] > 0) {
        $page = intval($_GET["p"]);
    }
    $perpage = $config["post_display_limit"];
    $offset = ($page - 1) * $perpage;

    // Check if there's a tag with rating: in front of it, if so, remove it from the string and set the rating
    $rating = "all";
    if (strpos($searchTerm, "rating:") !== false) {
        $rating = explode(" ", explode("rating:", $searchTerm)[1])[0];
        $rating = validateRating(strtolower(trim($rating)));
    }

    $status = "awaiting|approved";
    if (in_array("moderate", $permissions) || in_array("admin", $permissions)) {
        $status = "awaiting|approved|deleted";
    }
    if (strpos($searchTerm, "status:") !== false) {
        $status = explode(" ", explode("status:", $searchTerm)[1])[0];
        $status = determineStatus(strtolower(trim($status)), $permissions);
    }

    $searchUser = 0;
    if (strpos($searchTerm, "user:") !== false) {
        $searchUser = explode(" ", explode("user:", $searchTerm)[1])[0];
        $searchUser = sanitize(strtolower(trim($searchUser)));
    }

    $_tags = explode(" ", $searchTerm);

    $_tags = getTags($conn, $_tags, $config["search_max_tags"]);
    if (!empty($_tags)) {
        $tags = $_tags[0];
        $count = $_tags[1];
    } else {
        $tags = [];
        $count = 0;
    }
    if ($count > $config["search_max_tags"]) {
        $errors[] = "You can only search for up to " . $config["search_max_tags"] . " tags at a time.";
    }
    if (empty($tags) && ($searchTerm != '' && $searchTerm != '*' && !str_contains($searchTerm, 'rating:') && !str_contains($searchTerm, 'user:') && !str_contains($searchTerm, "status:"))) {
        $posts = [];
        $allTags = [];
        $totalPosts = 0;
        $totalPages = 0;
    } else {
        $_posts = getPosts($conn, $tags, $perpage, $offset, $rating, $status, $searchUser);
        $posts = $_posts[0];
        $allTags = $_posts[2];

        $totalPosts = $_posts[1];
        $totalPages = ceil($totalPosts / $perpage);
    }
} elseif ($action == "a") {
    if (!in_array("post", $permissions)) {
        header("Location: /posts.php?a=i");
        exit();
    }

    if (file_exists(__DIR__ . "/../software/data/{$config["language"]}_upload_guidelines.md")) {
        $guidelinesRaw = file_get_contents(__DIR__ . "/../software/data/{$config["language"]}_upload_guidelines.md");
        $guidelines = $parsedown->text($guidelinesRaw);
        $smarty->assign("guidelines", $guidelines);
    } elseif (file_exists(__DIR__ . "/../software/data/en_upload_guidelines.md")) {
        $guidelinesRaw = file_get_contents(__DIR__ . "/../software/data/en_upload_guidelines.md");
        $guidelines = $parsedown->text($guidelinesRaw);
        $smarty->assign("guidelines", $guidelines);
    }

    $smarty->assign("file_string", $config["upload_extensions"]);
    $smarty->assign("max_size", formatBytes($config["upload_max_size"]));

    if (isset($_POST["upload"])) {
        $source = $_POST["source"];
        $tags = $_POST["tags"];
        $file = $_FILES["file"];
        $rating = $_POST["rating"];

        if (!in_array($rating, ["safe", "questionable", "explicit"])) {
            $errors[] = "Invalid rating.";
        }

        $fileError = $file["error"];
        $fileSize = $file["size"];
        $fileTmpName = $file["tmp_name"];
        $fileType = $file["type"];
        $fileName = $file["name"];
        $fileName = substr(md5(uniqid()), 0, 8) . "." . pathinfo(basename($fileName), PATHINFO_EXTENSION);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = explode(",", $config["upload_extensions"]);
        $allowedExtensions = array_map("trim", $allowedExtensions);

        if (!in_array($fileExt, $allowedExtensions)) {
            $errors[] = "Invalid file extension. Allowed extensions: " . $config["upload_extensions"];
        }
        if ($fileError !== 0) {
            $errors[] = "An error occurred while uploading the file. Error code: {$fileError}";
        }
        if ($fileSize > $config["upload_max_size"]) {
            $errors[] = "The file is too large. Maximum size: " . $config["upload_max_size"] . " bytes.";
        }

        if (empty($errors)) {
            $fileDestination = __DIR__ . "/uploads/tmp/" . $fileName;
            if (!file_exists(__DIR__ . "/uploads/tmp")) {
                mkdir(__DIR__ . "/uploads/tmp", 0775, true);
            }
            if (!move_uploaded_file($fileTmpName, $fileDestination)) {
                $errors[] = "An error occurred while moving the uploaded file.";
            }

            if (empty($errors)) {
                $md5 = md5_file($fileDestination);
                if (file_exists(__DIR__ . "/uploads/images/$md5" . "." . $fileExt)) {
                    $errors[] = "File already exists.";
                    unlink($fileDestination);
                }

                if (empty($errors)) {
                    if (in_array($fileExt, ["jpg", "jpeg", "png", "gif"])) {
                        $image = imagecreatefromstring(file_get_contents($fileDestination));
                        if (!$image) {
                            $errors[] = "Failed to create image from file.";
                        } else {
                            $croppedDestination = __DIR__ . "/uploads/crops/$md5" . "." . $fileExt;
                            $width = imagesx($image);
                            $height = imagesy($image);
                            $aspectRatio = $width / $height;

                            if ($width > $config["image_max_width"] || $height > $config["image_max_height"]) {
                                if ($height > $config["image_max_height"]) {
                                    $newHeight = $config["image_max_height"];
                                    $newWidth = $newHeight * $aspectRatio;
                                }
                                if ($width > $config["image_max_width"]) {
                                    $newWidth = $config["image_max_width"];
                                    $newHeight = $newWidth / $aspectRatio;
                                }
                                /*if ($width < $height) {
                                    $newHeight = $config["image_max_height"];
                                    $newWidth = $newHeight * $aspectRatio;
                                } else {
                                    $newWidth = $config["image_max_width"];
                                    $newHeight = $newWidth / $aspectRatio;
                                }*/
                                $newImage = imagecreatetruecolor((int)$newWidth, (int)$newHeight);
                                imagecopyresampled($newImage, $image, 0, 0, 0, 0, (int)$newWidth, (int)$newHeight, $width, $height);
                                imagejpeg($newImage, $croppedDestination, 100);
                            }

                            // Now create thumbnail as well
                            $thumbnailDestination = __DIR__ . "/uploads/thumbs/$md5" . "." . $fileExt;
                            $thumbnailWidth = $config["thumbnail_width"];
                            $thumbnailHeight = $thumbnailWidth / $aspectRatio;
                            $thumbnailImage = imagecreatetruecolor((int)$thumbnailWidth, (int)$thumbnailHeight);
                            imagecopyresampled($thumbnailImage, $image, 0, 0, 0, 0, (int)$thumbnailWidth, (int)$thumbnailHeight, $width, $height);
                            imagejpeg($thumbnailImage, $thumbnailDestination, 100);

                            $type = "images";
                        }
                    } elseif (in_array($fileExt, ["mp4", "webm", "mkv"])) {
                        // This should work???
                        $thumbnailDestination = __DIR__ . "/uploads/thumbs/$md5.jpg";
                        // Get video duration
                        $durationCommand = $config["ffprobe_path"] . " -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $fileDestination";
                        exec($durationCommand, $durationOutput, $durationReturnVar);
                        $duration = floatval($durationOutput[0]);

                        // Set thumbnail extraction time to half the duration if less than 1 second
                        $thumbnailTime = $duration < 1 ? $duration / 2 : 1;

                        $thumbnailCommand = $config["ffmpeg_path"] . " -i $fileDestination -ss 00:00:0$thumbnailTime -vframes 1 $thumbnailDestination";
                        exec($thumbnailCommand, $output, $returnVar);
                        if ($returnVar !== 0) {
                            $errors[] = "Failed to create video thumbnail. Command output: " . implode("<br>", $output);
                        }
                        $type = "videos";
                    }

                    if (!file_exists(__DIR__ . "/uploads/$type")) {
                        mkdir(__DIR__ . "/uploads/$type", 0775, true);
                    }
                    $finalFile = __DIR__ . "/uploads/$type/$md5" . "." . $fileExt;

                    // Before inserting, check if md5 is already in db
                    $stmt = $conn->prepare("SELECT post_id FROM posts WHERE image_url = ?");
                    if (!$stmt) {
                        $errors[] = "Failed to prepare statement for checking existing file: {$conn->error}";
                    }

                    if (empty($errors)) {
                        $stmt->bind_param("s", $md5);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            $errors[] = "File already exists.";
                            // But why would I unlink?!????
                            // Also, this should never happen lol? idk
                            //unlink($fileDestination);
                            //unlink($croppedDestination);
                            //unlink($thumbnailDestination);
                        }

                        if (empty($errors)) {
                            rename($fileDestination, $finalFile);

                            // Insert post into db
                            $isApproved = 0;
                            if (in_array("moderate", $permissions) || in_array("admin", $permissions)) {
                                $isApproved = 1;
                            } else {
                                // If not admin, check if user has at least $config["upload_threshold"] approved posts
                                $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM posts WHERE user_id = ? AND is_approved = 1");
                                if (!$stmt) {
                                    $errors[] = "Failed to prepare statement for checking approved posts: {$conn->error}";
                                } else {
                                    $stmt->bind_param("i", $user["user_id"]);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $approvedPosts = $result->fetch_assoc()["count"];

                                    if ($approvedPosts >= $config["upload_threshold"]) {
                                        $isApproved = 1;
                                    }
                                }
                            }

                            $stmt = $conn->prepare("INSERT INTO posts (user_id, image_url, file_size, file_extension, rating, source, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            if (!$stmt) {
                                $errors[] = "Failed to prepare statement for inserting post: {$conn->error}";
                            } else {
                                $stmt->bind_param("isssssi", $user["user_id"], $md5, $fileSize, $fileExt, $rating, $source, $isApproved);
                                $stmt->execute();
                                $stmt->close();

                                $postId = $conn->insert_id;

                                $tags = explode(" ", $tags);
                                $tags = array_map("trim", $tags);
                                $tags = array_filter($tags);

                                $commitId = substr(md5(uniqid()), 0, 16);
                                foreach ($tags as $tag) {
                                    // Check if tag starts with copyright:, artist:, character:, general:, meta: or other:, remove it and add new to $_tag
                                    $tag = strtolower($tag);
                                    $_tag = preg_replace("/(copyright|artist|character|general|meta|other):/", "", $tag);
                                    if (preg_match("/(copyright|artist|character|general|meta|other):/", $tag)) {
                                        $category = explode(":", $tag)[0];
                                    } else {
                                        $category = "general";
                                    }
                                    $stmt = $conn->prepare("SELECT tag_id FROM tags WHERE tag_name = ?");
                                    if (!$stmt) {
                                        $errors[] = "Failed to prepare statement for selecting tag: {$conn->error}";
                                    } else {
                                        $stmt->bind_param("s", $_tag);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        if ($result->num_rows == 0) {
                                            $stmt = $conn->prepare("INSERT INTO tags (tag_name, category) VALUES (?, ?)");
                                            if (!$stmt) {
                                                $errors[] = "Failed to prepare statement for inserting tag: {$conn->error}";
                                            } else {
                                                $stmt->bind_param("ss", $_tag, $category);
                                                $stmt->execute();
                                                $tagId = $conn->insert_id;
                                            }
                                        } else {
                                            $tagId = $result->fetch_assoc()["tag_id"];
                                        }

                                        $stmt = $conn->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                                        if (!$stmt) {
                                            $errors[] = "Failed to prepare statement for inserting post tag: {$conn->error}";
                                        } else {
                                            $stmt->bind_param("ii", $postId, $tagId);
                                            $stmt->execute();
                                            $stmt->close();
                                        }

                                        // Log tag addition
                                        $stmt = $conn->prepare("INSERT INTO tag_history (post_id, tag_id, action, user_id, commit_id) VALUES (?, ?, 'add', ?, ?)");
                                        $stmt->bind_param("iiis", $postId, $tagId, $user["user_id"], $commitId);
                                        $stmt->execute();
                                        $stmt->close();
                                    }
                                }

                                if (empty($errors)) {
                                    header("Location: /posts.php?a=p&id=$postId");
                                    exit();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
} elseif ($action == "p") {
    if (!isset($_GET["id"]) || empty($_GET["id"]) || !is_numeric($_GET["id"]) || $_GET["id"] < 1) {
        header("Location: /posts.php?a=i");
        exit();
    }

    $id = $_GET["id"];

    $stmt = $conn->prepare("SELECT * FROM posts WHERE post_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        header("Location: /posts.php?a=i");
        exit();
    }

    $post = $result->fetch_assoc();

    $tagQuery = "SELECT t.* FROM tags t
                 JOIN post_tags pt ON pt.tag_id = t.tag_id
                 WHERE pt.post_id = ?";
    $tagStmt = $conn->prepare($tagQuery);
    $tagStmt->bind_param("i", $id);
    $tagStmt->execute();
    $tagResult = $tagStmt->get_result();

    $allTags = [];
    while ($tagRow = $tagResult->fetch_assoc()) {
        $countQuery = "SELECT COUNT(*) AS count FROM post_tags WHERE tag_id = ?";
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param("i", $tagRow["tag_id"]);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $tagCount = $countResult->fetch_assoc()["count"];
        $allTags[$tagRow["category"]][] = ["name" => $tagRow["tag_name"], "count" => $tagCount];
        // Add tags as string tags to post
        $post["tags"][] = $tagRow["tag_name"];
    }

    // Now sort allTags[category] by name asc
    foreach ($allTags as $category => $tags) {
        usort($allTags[$category], function ($a, $b) {
            return $a["name"] <=> $b["name"];
        });
    }

    $tagStmt->close();

    $post["tags"] = implode(" ", $post["tags"]);

    if (in_array($post["file_extension"], ["mp4", "webm", "mkv"])) {
        $post["is_video"] = 1;
    }

    // Get dimension, and if dimension larger than 800px width, add "has_thumbnail" to post
    if (in_array($post["file_extension"], ["jpg", "jpeg", "png", "gif"])) {
        $imagePath = __DIR__ . "/uploads/images/" . $post["image_url"] . "." . $post["file_extension"];
        if (file_exists($imagePath)) {
            $dimensions = getimagesize($imagePath);
        }
    }

    $resolution = "";
    $type = "images";
    if (in_array($post["file_extension"], ["jpg", "jpeg", "png", "gif"])) {
        $imagePath = __DIR__ . "/uploads/images/" . $post["image_url"] . "." . $post["file_extension"];
        if (file_exists($imagePath)) {
            $dimensions = getimagesize($imagePath);
            $resolution = $dimensions[0] . "x" . $dimensions[1];
            if ($dimensions[0] > 800) {
                $post["has_thumbnail"] = 1;
            }
        }
    } elseif (in_array($post["file_extension"], ["mp4", "webm", "mkv"])) {
        $type = "videos";
        $videoPath = __DIR__ . "/uploads/videos/" . $post["image_url"] . "." . $post["file_extension"];
        if (file_exists($videoPath)) {
            $command = $config["ffmpeg_path"] . " -v error -select_streams v:0 -show_entries stream=width,height -of csv=s=x:p=0 $videoPath";
            exec($command, $output, $returnVar);
            if ($returnVar === 0) {
                $resolution = $output[0];
            }
        }
    }

    $uploaderSql = "SELECT user_id, username FROM users WHERE user_id = ?";
    $uploaderStmt = $conn->prepare($uploaderSql);
    $uploaderStmt->bind_param("i", $post["user_id"]);
    $uploaderStmt->execute();
    $uploaderResult = $uploaderStmt->get_result();
    $uploader = $uploaderResult->fetch_assoc();

    $scoreSql = "SELECT SUM(vote) AS score FROM post_votes WHERE post_id = ?";
    $scoreStmt = $conn->prepare($scoreSql);
    $scoreStmt->bind_param("i", $id);
    $scoreStmt->execute();
    $scoreResult = $scoreStmt->get_result();
    $score = $scoreResult->fetch_assoc()["score"];
    if (empty($score)) {
        $score = 0;
    }

    // My idea here: Highlight the voted state in the sidebar, this needs JS as well but I'm too lazy right now
    /*$voted = "none";
    if (isset($user["user_id"])) {
        $voteSql = "SELECT vote FROM post_votes WHERE post_id = ? AND user_id = ?";
        $voteStmt = $conn->prepare($voteSql);
        $voteStmt->bind_param("ii", $id, $user["user_id"]);
        $voteStmt->execute();
        $voteResult = $voteStmt->get_result();
        if ($voteResult->num_rows > 0) {
            $voted = $voteResult->fetch_assoc()["vote"];
        }
        if ($voted == 1) {
            $voted = "up";
        } elseif ($voted == -1) {
            $voted = "down";
        }
    }
    $smarty->assign("voted", $voted);*/

    $favourited = false;
    if (isset($user["user_id"])) {
        $favouriteSql = "SELECT id FROM favourites WHERE post_id = ? AND user_id = ?";
        $favouriteStmt = $conn->prepare($favouriteSql);
        $favouriteStmt->bind_param("ii", $id, $user["user_id"]);
        $favouriteStmt->execute();
        $favouriteResult = $favouriteStmt->get_result();
        if ($favouriteResult->num_rows > 0) {
            $favourited = true;
        }
    }
    $smarty->assign("favourited", $favourited);

    $previousIdSql = "SELECT post_id FROM posts WHERE post_id < ? AND deleted = 0 ORDER BY post_id DESC LIMIT 1";
    $previousIdStmt = $conn->prepare($previousIdSql);
    $previousIdStmt->bind_param("i", $id);
    $previousIdStmt->execute();
    $previousIdResult = $previousIdStmt->get_result();
    if ($previousIdResult->num_rows == 0) {
        $previousId = null;
    } else {
        $previousId = $previousIdResult->fetch_assoc()["post_id"];
    }

    $nextIdSql = "SELECT post_id FROM posts WHERE post_id > ? AND deleted = 0 ORDER BY post_id ASC LIMIT 1";
    $nextIdStmt = $conn->prepare($nextIdSql);
    $nextIdStmt->bind_param("i", $id);
    $nextIdStmt->execute();
    $nextIdResult = $nextIdStmt->get_result();
    if ($nextIdResult->num_rows == 0) {
        $nextId = null;
    } else {
        $nextId = $nextIdResult->fetch_assoc()["post_id"];
    }

    if (isset($_POST["update"])) {
        if (!in_array("tag", $permissions) && !in_array("moderate", $permissions) && !in_array("admin", $permissions) && (!isset($user["user_id"]) || $user["user_id"] != $post["user_id"])) {
            header("Location: /posts.php?a=p&id=$id");
            exit();
        }

        $source = $_POST["source"];
        $tags = $_POST["tags"];
        $rating = $_POST["rating"];

        if (!in_array($rating, ["safe", "questionable", "explicit"])) {
            $errors[] = "Invalid rating.";
        }

        $stmt = $conn->prepare("UPDATE posts SET source = ?, rating = ? WHERE post_id = ?");
        $stmt->bind_param("ssi", $source, $rating, $id);
        $stmt->execute();
        $stmt->close();

        // Get current tags from the database
        $currentTags = [];
        $stmt = $conn->prepare("SELECT t.tag_name FROM tags t JOIN post_tags pt ON pt.tag_id = t.tag_id WHERE pt.post_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $currentTags[] = $row["tag_name"];
        }
        $stmt->close();

        // Determine tags to add, remove, and stay
        $tags = explode(" ", $tags);
        $tagsToAdd = array_diff($tags, $currentTags);
        $tagsToRemove = array_diff($currentTags, $tags);
        $tagsToStay = array_intersect($tags, $currentTags);
        // Generate commit id = varchar 16
        $commitId = substr(md5(uniqid()), 0, 16);

        // Insert new tags and log history
        foreach ($tagsToAdd as $tag) {
            $tag = trim(strtolower($tag));
            if (!empty($tag)) {
                $category = determineCategory($tag);
                $tag = preg_replace("/(copyright|artist|character|general|meta|other):/", "", $tag);
                $stmt = $conn->prepare("SELECT tag_id FROM tags WHERE tag_name = ?");
                $stmt->bind_param("s", $tag);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 0) {
                    // Insert new tag if it doesn't exist
                    $stmt = $conn->prepare("INSERT INTO tags (tag_name, category) VALUES (?, ?)");
                    $stmt->bind_param("ss", $tag, $category);
                    $stmt->execute();
                    $tagId = $stmt->insert_id;
                } else {
                    $tagId = $result->fetch_assoc()["tag_id"];
                }
                $stmt->close();

                // Associate tag with post
                $stmt = $conn->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $id, $tagId);
                $stmt->execute();
                $stmt->close();

                // Log tag addition
                $stmt = $conn->prepare("INSERT INTO tag_history (post_id, tag_id, action, user_id, commit_id) VALUES (?, ?, 'add', ?, ?)");
                $stmt->bind_param("iiis", $id, $tagId, $user["user_id"], $commitId);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Remove old tags and log history
        foreach ($tagsToRemove as $tag) {
            $stmt = $conn->prepare("SELECT tag_id FROM tags WHERE tag_name = ?");
            $stmt->bind_param("s", $tag);
            $stmt->execute();
            $result = $stmt->get_result();
            $tagId = $result->fetch_assoc()["tag_id"];
            $stmt->close();

            // Remove association of tag with post
            $stmt = $conn->prepare("DELETE FROM post_tags WHERE post_id = ? AND tag_id = ?");
            $stmt->bind_param("ii", $id, $tagId);
            $stmt->execute();
            $stmt->close();

            // Log tag removal
            $stmt = $conn->prepare("INSERT INTO tag_history (post_id, tag_id, action, user_id, commit_id) VALUES (?, ?, 'remove', ?, ?)");
            $stmt->bind_param("iiis", $id, $tagId, $user["user_id"], $commitId);
            $stmt->execute();
            $stmt->close();
        }

        // Log tags that stay
        foreach ($tagsToStay as $tag) {
            $stmt = $conn->prepare("SELECT tag_id FROM tags WHERE tag_name = ?");
            $stmt->bind_param("s", $tag);
            $stmt->execute();
            $result = $stmt->get_result();
            $tagId = $result->fetch_assoc()["tag_id"];
            $stmt->close();

            // Log tag stay
            $stmt = $conn->prepare("INSERT INTO tag_history (post_id, tag_id, action, user_id, commit_id) VALUES (?, ?, 'stay', ?, ?)");
            $stmt->bind_param("iiis", $id, $tagId, $user["user_id"], $commitId);
            $stmt->execute();
            $stmt->close();
        }
        if (empty($errors)) {
            header("Location: /posts.php?a=p&id=$id&t=" . urlencode($searchTerm));
        }
    }
} elseif ($action == "r") {
    $stmt = $conn->prepare("SELECT post_id FROM posts WHERE is_approved = 1 AND deleted = 0 ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $id = $row["post_id"];

    header("Location: /posts.php?a=p&id=$id");
    exit();
} elseif ($action == "h") {
    if (!isset($_GET["id"]) || empty($_GET["id"]) || !is_numeric($_GET["id"]) || $_GET["id"] < 1) {
        header("Location: /posts.php?a=i");
        exit();
    }

    $id = $_GET["id"];

    $stmt = $conn->prepare("SELECT post_id FROM posts WHERE post_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $historySql = "SELECT * FROM tag_history WHERE post_id = ? ORDER BY timestamp DESC";

    $historyStmt = $conn->prepare($historySql);
    $historyStmt->bind_param("i", $id);
    $historyStmt->execute();
    $historyResult = $historyStmt->get_result();

    $history = [];
    while ($row = $historyResult->fetch_assoc()) {
        $commitId = $row['commit_id'];
        if (!isset($history[$commitId])) {
            $history[$commitId] = [];
        }
        $tagSql = "SELECT tag_name, category FROM tags WHERE tag_id = ?";
        $tagStmt = $conn->prepare($tagSql);
        $tagStmt->bind_param("i", $row["tag_id"]);
        $tagStmt->execute();
        $tagResult = $tagStmt->get_result();
        $tagResult = $tagResult->fetch_assoc();
        $row["tag_name"] = $tagResult["tag_name"];
        $row["category"] = $tagResult["category"];
        $history[$commitId]["tags"][] = $row;
    }

    foreach ($history as $commitId => $commit) {
        if (!isset($history[$commitId]["user"])) {
            $_commit = $commit["tags"][0];
            $userSql = "SELECT username FROM users WHERE user_id = ?";
            $userStmt = $conn->prepare($userSql);
            $userStmt->bind_param("i", $_commit["user_id"]);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $userResult = $userResult->fetch_assoc();
            $history[$commitId]["user"] = $userResult["username"];
            $history[$commitId]["user_id"] = $_commit["user_id"];
            $history[$commitId]["timestamp"] = $_commit["timestamp"];
        }
    }
}

if (isset($searchTerm)) {
    $smarty->assign("searchTerm", $searchTerm);
    $smarty->assign("urlSearchTerm", $urlSearchTerm);
}
if ($action == "i" || $action == "s") {
    $smarty->assign("pagetitle", "Browse " . $config["sitename"]);
    $smarty->assign("posts", $posts);
    $smarty->assign("totalPosts", $totalPosts);
    $smarty->assign("totalPages", $totalPages);
    $smarty->assign("page", $page);
    $smarty->assign("allTags", $allTags);
}
if ($action == "a") {
    $smarty->assign("pagetitle", "Add a Post on " . $config["sitename"]);
}
if ($action == "h") {
    $smarty->assign("pagetitle", "Tag History for Post #$id on " . $config["sitename"]);
    $smarty->assign("id", $id);
    $smarty->assign("history", $history);
}
if ($action == "p") {
    $smarty->assign("id", $id);
    $smarty->assign("pagetitle", "Post #" . $id . " on " . $config["sitename"]);
    $smarty->assign("post", $post);
    $smarty->assign("allTags", $allTags);
    $canEdit = false;
    if (in_array("tag", $permissions) || in_array("moderate", $permissions) || in_array("admin", $permissions) || (isset($user["user_id"]) && $user["user_id"] == $post["user_id"])) {
        $canEdit = true;
    }
    $canDelete = false;
    if (in_array("moderate", $permissions) || in_array("admin", $permissions)) {
        $canDelete = true;
    }
    $smarty->assign("canEdit", $canEdit);
    $smarty->assign("canDelete", $canDelete);
    $canComment = false;
    if (in_array("comment", $permissions) || in_array("moderate", $permissions) || in_array("admin", $permissions)) {
        $canComment = true;
    }
    $smarty->assign("canComment", $canComment);
    $smarty->assign("resolution", $resolution);
    $smarty->assign("uploader", $uploader);
    $smarty->assign("score", $score);
    $smarty->assign("type", $type);
    if (!empty($previousId)) {
        $smarty->assign("previousId", $previousId);
    }
    if (!empty($nextId)) {
        $smarty->assign("nextId", $nextId);
    }
}

$smarty->assign("errors", $errors);
$smarty->assign("action", $action);
$smarty->assign("activePage", "browse");
$smarty->display("posts.tpl");

// Close the connection
$conn->close();
