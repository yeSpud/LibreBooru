<?php

$version = "0.1.0-devel";
$config["debug"] = true;
$config["externalapi"] = false;
$config["language"] = "en";
define("WORLD", "World");

require __DIR__ . "/../functions.php";
require __DIR__ . "/../__init/debug.php";
require __DIR__ . "/../__init/language.php";

if (file_exists(__DIR__ . "/../version")) {
    writeStep(0);
    echo "OpenBooru is already installed. Use the update system to update.<br><i>It's recommended to delete the install.php file after installation, but that's completely up to you.</i>";
    header("refresh:2;url=/account.php?a=l");
    exit;
}

if (file_exists(__DIR__ . "/../db.env")) {
    $db = readConfig(__DIR__ . "/../db.env");
    $conn = new mysqli($db["host"], $db["user"], $db["pass"], $db["name"]);

    // Try connecting and return error if failed
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

if (isset($_POST["create_config"])) {
    $post = $_POST;
    unset($post["create_config"]);
    writeConfig(__DIR__ . "/../config.env", $post);
    exit;
}

if (isset($_POST["create_colors"])) {
    $post = $_POST;
    unset($post["create_colors"]);
    writeConfig(__DIR__ . "/../colors.env", $post);
    exit;
}

if (isset($_POST["create_levels"])) {
    $post = $_POST;
    unset($post["create_levels"]);
    writeConfig(__DIR__ . "/../levels.env", $post);
    exit;
}

if (isset($_POST["create_db"])) {
    $post = $_POST;
    // Try a connection
    try {
        $conn = new mysqli($post["host"], $post["user"], $post["pass"], $post["name"]);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        } else {
            // Now check if the database exists
            $result = $conn->query("SHOW TABLES");
            // If not throw an error
            if (!$result) {
                throw new Exception("Database does not exist");
            }
            unset($post["create_db"]);
            writeConfig(__DIR__ . "/../db.env", $post);
            exit;
        }
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

if (isset($_POST["fill_db"])) {
    if (!isset($conn)) {
        die("Something went wrong when entering the database credentials. Go back and try again. If this persist, contact me.");
    }
    $dbFile = __DIR__ . "/../__init/.tmp/$version.sql";
    if (!file_exists($dbFile)) {
        $dbUrl = "https://raw.githubusercontent.com/5ynchrogazer/OpenBooru-Extras/refs/heads/master/sql/$version.sql";
        $dbContent = file_get_contents($dbUrl);
        file_put_contents($dbFile, $dbContent);
    }

    $handle = fopen($dbFile, "r");
    if ($handle) {
        $query = '';
        while (($line = fgets($handle)) !== false) {
            $query .= $line;
            if (substr(trim($line), -1) == ';') {
                if (!$conn->query($query)) {
                    // Completely clear the database
                    $conn->query("DROP DATABASE " . $db["name"]);
                    $conn->query("CREATE DATABASE " . $db["name"]);
                    die("Error executing query: " . $conn->error);
                }
                $query = '';
            }
        }
        fclose($handle);
    } else {
        die("Error opening the file.");
    }
}

if (isset($_POST["create_admin"])) {
    if (!isset($conn)) {
        die("Something went wrong when entering the database credentials. Go back and try again. If this persist, contact me.");
    }

    $config = readConfig(__DIR__ . "/../config.env");
    $levelIds = readConfig(__DIR__ . "/../levels.env");

    $username = "admin";
    $password = "password";
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, email, password_hash, last_login, user_level, is_banned, profile_picture) VALUES (?, NULL, ?, NULL, ?, 0, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", $username, $password_hash, $levelIds["admin"], $config["default_profile_picture"]);
    $stmt->execute();
}

if (isset($_POST["finish"])) {
    writeStep(0);
    file_put_contents(__DIR__ . "/../version", $version);
    exit;
}

if (isset($_GET["reset"])) {
    writeStep(0);
    header("Location: /install.php");
}

$error = false;
$step = 0;
if (isset($_GET["step"]) && is_numeric($_GET["step"])) {
    $step = intval($_GET["step"]);
}
$steps = [0, 1, 2, 3, 4, 5, 6];
$stepFile = __DIR__ . "/../__init/.tmp/step.txt";
if (file_exists($stepFile) && !isset($_GET["step"])) {
    $step = intval(file_get_contents($stepFile));
} else {
    writeStep($step);
}
if (!in_array($step, $steps)) {
    $step = 0;
    writeStep($step);
} else {
    if (isset($_GET["step"])) {
        writeStep($step);
        header("Location: /install.php");
    }
}

if ($step == 0) {
    $jqueryExists = false;
    $smartyExists = false;
    $parsedownExists = false;
    file_exists(__DIR__ . "/assets/classic/js/jquery.min.js") ? $jqueryExists = true : $error = true;
    file_exists(__DIR__ . "/../software/smarty/libs/Smarty.class.php") ? $smartyExists = true : $error = true;
    file_exists(__DIR__ . "/../software/parsedown/Parsedown.php") ? $parsedownExists = true : $error = true;
    $phpVersion = phpversion();
    $phpVersionCheck = version_compare($phpVersion, "8.3", ">=");
}

?>

<!DOCTYPE html>
<html lang="<?= $locale ?>">

<head>
    <meta charset="utf-8">
    <title>OpenBooru Installer - <?= $lang["step"] ?> <?= $step ?></title>
    <link rel="stylesheet" href="/assets/classic/main.css">
    <link rel="stylesheet" href="/assets/classic/install.css">

    <style>
        .Yes {
            color: green;
        }

        .No {
            color: red;
        }
    </style>

    <script>
        const locales = <?= json_encode($locales) ?>;
    </script>

    <script src="/assets/classic/js/main.js"></script>
    <script src="/assets/classic/js/jquery.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="home_container">
            <h1><a href="javascript:void">OpenBooru</a></h1>
            <div class="home_menu">
                <a href="https://github.com/5ynchrogazer/OpenBooru" target="_blank">GitHub</a>
                <a href="https://discord.5ynchro.net" target="_blank"><?= $lang["support"] ?></a>
                <span>
                    <a href="javascript:void" id="localeSelector"><?= $lang["language"] ?></a>
                </span>
                <a href="javascript:confirmReset()"><?= $lang["reset"] ?></a>
            </div>

            <?php if ($step == 0) { ?>
                <div class="panel">
                    <h3 class="m-0 p-0"><?= $lang["requirements"] ?></h3>
                    jQuery: <span class="<?= $jqueryExists ? "Yes" : "No" ?>"><?= $jqueryExists ? $lang["yes"] : $lang["no"] ?></span><br>
                    Smarty: <span class="<?= $smartyExists ? "Yes" : "No" ?>"><?= $smartyExists ? $lang["yes"] : $lang["no"] ?></span><br>
                    Parsedown: <span class="<?= $parsedownExists ? "Yes" : "No" ?>"><?= $parsedownExists ? $lang["yes"] : $lang["no"] ?></span><br>
                    <br>
                    <?php if ($error) { ?>
                        <?= replace($lang["please_read_the_documentation_on_how_to_fix_the_errors"], "[read_the_documentation", '<a href="" target="_blank">' . $lang["read_the_documentation"] . '</a>') ?>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($step == 1) { ?>
                <div class="panel">
                    <h3 class="m-0 p-0"><?= $lang["configuration"] ?></h3>
                    <form method="POST" name="create_config" id="create_config">
                        <input type="hidden" name="create_config" value="true">
                        <div class="split">
                            <label class="element">
                                Site Name
                                <input type="text" name="sitename" value="OpenBooru" required>
                            </label>
                            <label class="element">
                                Default Language
                                <select name="language" required>
                                    <?php foreach ($locales as $key => $value) { ?>
                                        <option value="<?= $key ?>"><?= $value ?> <?= $key ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                        </div>
                        <div class="split">
                            <label class="element">
                                Default Theme
                                <select name="theme" required>
                                    <option value="classic">Classic</option>
                                </select>
                            </label>
                            <label class="element">
                                Debug Mode
                                <select name="debug" required>
                                    <option value="true">True</option>
                                    <option value="false" selected>False</option>
                                </select>
                            </label>
                        </div>
                        <hr>
                        <div class="split">
                            <label class="element">
                                Search Min Length
                                <input type="number" name="search_min_length" value="2" required>
                            </label>
                            <label class="element">
                                AJAX Search Limit
                                <input type="number" name="ajax_search_limit" value="10" required>
                            </label>
                        </div>
                        <div class="split">
                            <label class="element">
                                Search Max Tags
                                <input type="number" name="search_max_tags" value="5" required>
                            </label>
                            <label class="element">
                                Post Display Limit
                                <input type="number" name="post_display_limit" value="18" required>
                            </label>
                        </div>
                        <hr>
                        <label>
                            Default Profile Picture
                            <input type="text" name="default_profile_picture" value="https://rav.h33t.moe/data/c7469ad9-40e8-462a-9d29-0e8cf99fe8c8.jpg">
                        </label>
                        <div class="split">
                            <label class="element">
                                Allow Change Profile Picture
                                <select name="allow_change_profile_picture" required>
                                    <option value="true">True</option>
                                    <option value="false" selected>False</option>
                                </select>
                            </label>
                            <label class="element">
                                Show Profile Picture
                                <select name="show_profile_picture" required>
                                    <option value="true">True</option>
                                    <option value="false" selected>False</option>
                                </select>
                            </label>
                        </div>
                        <hr>
                        <label>
                            Upload Extensions (seperate by comma)
                            <input type="text" name="upload_extensions" value="jpg,jpeg,png,gif,mp4,mkv,webm" required>
                        </label>
                        <div class="split">
                            <label class="element">
                                Upload Max Size (<a href="https://www.gbmb.org/mb-to-bytes" target="_blank">bytes</a>)
                                <input type="number" name="upload_max_size" value="33554432" required>
                            </label>
                            <label class="element">
                                Upload Min Tags
                                <input type="number" name="upload_min_tags" value="5" required>
                            </label>
                        </div>
                        <hr>
                        <div class="split">
                            <label class="element">
                                FFmpeg Path
                                <input type="text" name="ffmpeg_path" value="ffmpeg" required>
                            </label>
                            <label class="element">
                                FFprobe Path
                                <input type="text" name="ffprobe_path" value="ffprobe" required>
                            </label>
                        </div>
                        <hr>
                        <div class="split">
                            <label class="element">
                                Image Max Width
                                <input type="number" name="image_max_width" value="850" required>
                            </label>
                            <label class="element">
                                Image Max Height
                                <input type="number" name="image_max_height" value="1400" required>
                            </label>
                        </div>
                        <div class="split">
                            <label class="element">
                                Thumbnail Width
                                <input type="number" name="thumbnail_width" value="200" required>
                            </label>
                            <label class="element">
                                Upload Theshold
                                <input type="number" name="upload_threshold" value="3" required>
                            </label>
                        </div>
                        <hr>
                        <div class="split">
                            <label class="element">
                                External Access
                                <select name="externalapi" required>
                                    <option value="true">True</option>
                                    <option value="false" selected>False</option>
                                </select>
                            </label>
                            <label class="element">
                                Changing Password Requires Old
                                <select name="change_pass_requires_old" required>
                                    <option value="true">True</option>
                                    <option value="false" selected>False</option>
                                </select>
                            </label>
                        </div>
                        <label>
                            Contact Email (empty to hide)
                            <input type="email" name="contact">
                        </label>
                    </form>
                </div>

                <div class="panel">
                    <h3 class="m-0 p-0"><?= $lang["colors"] ?></h3>
                    <form method="POST" name="create_colors" id="create_colors">
                        <input type="hidden" name="create_colors" value="true">
                        <div class="split">
                            <label class="element">
                                Copyright
                                <input type="color" name="copyright" required value="#FF0000">
                            </label>
                            <label class="element">
                                Character
                                <input type="color" name="character" value="#800080" required>
                            </label>
                        </div>
                        <div class="split">
                            <label class="element">
                                Artist
                                <input type="color" name="artist" value="#FFA500" required>
                            </label>
                            <label class="element">
                                General
                                <input type="color" name="general" value="#0000FF" required>
                            </label>
                        </div>
                        <div class="split">
                            <label class="element">
                                Meta
                                <input type="color" name="meta" value="#808080" required>
                            </label>
                            <label class="element">
                                Other
                                <input type="color" name="other" value="#808080" required>
                            </label>
                        </div>
                        <div class="split">
                            <label class="element">
                                Awaiting
                                <input type="color" name="awaiting" value="#FF0000" required>
                            </label>
                            <label class="element">
                                Video
                                <input type="color" name="video" value="#0000FF" required>
                            </label>
                        </div>
                        <div class="split">
                            <label class="element">
                                GIF
                                <input type="color" name="gif" value="#ADD8E6" required>
                            </label>
                        </div>
                    </form>
                </div>

                <div class="panel" style="display:none;">
                    <h3 class="m-0 p-0">Levels</h3>
                    <form method="POST" name="create_levels" id="create_levels">
                        <input type="hidden" name="create_levels" value="true">
                        <div class="split">
                            <label class="element">
                                Guest
                                <input type="number" name="guest" value="1" required>
                            </label>
                            <label class="element">
                                User
                                <input type="number" name="user" value="2" required>
                            </label>
                        </div>
                        <div class="split">
                            <label class="element">
                                Mod
                                <input type="number" name="mod" value="3" required>
                            </label>
                            <label class="element">
                                Admin
                                <input type="number" name="admin" value="4" required>
                            </label>
                        </div>
                    </form>
                </div>
            <?php } ?>

            <?php if ($step == 2) { ?>
                <div class="panel">
                    <h3 class="m-0 p-0"><?= $lang["database"] ?></h3>
                    <form method="POST" name="create_db" id="create_db">
                        <input type="hidden" name="create_db" value="true">
                        <div class="split">
                            <label class="element">
                                Host
                                <input type="text" name="host" value="localhost" required>
                            </label>
                            <label class="element">
                                Database Name
                                <input type="text" name="name" value="openbooru" required>
                            </label>
                        </div>
                        <div class="split">
                            <label class="element">
                                <?= $lang["username"] ?>
                                <input type="text" name="user" value="root" required>
                            </label>
                            <label class="element">
                                <?= $lang["password"] ?>
                                <input type="password" name="pass" value="root" required>
                            </label>
                        </div>
                    </form>
                </div>
            <?php } ?>

            <?php if ($step == 3) { ?>
                <div class="panel">
                    <h3 class="m-0 p-0"><?= $lang["fill_database"] ?></h3>
                    <form method="POST" name="fill_db" id="fill_db">
                        <input type="hidden" name="fill_db" value="true">
                        <?= $lang["click_next_to_fill_the_database"] ?>
                    </form>
                </div>
            <?php } ?>

            <?php if ($step == 4) { ?>
                <div class="panel">
                    <h3 class="m-0 p-0"><?= $lang["admin_account"] ?></h3>
                    <form method="POST" name="create_admin" id="create_admin">
                        <input type="hidden" name="create_admin" value="true">
                        <?= $lang["click_next_to_create_the_default_admin_account"] ?><br><br>
                        <b><?= $lang["username"] ?>:</b> admin<br>
                        <b><?= $lang["password"] ?>:</b> password<br><br>
                        <b><?= $lang["make_sure_to_change_the_username_and_password_after_logging_in"] ?></b>
                    </form>
                </div>
            <?php } ?>

            <?php if ($step == 5) { ?>
                <div class="panel">
                    <h3 class="m-0 p-0"><?= $lang["thanks_for_installing_openbooru"] ?></h3>
                    <form method="POST" name="finish" id="finish">
                        <input type="hidden" name="finish" value="true">
                        <?= $lang["installation_complete"] ?><br><br>
                        If you think OpenBooru is worth a cup of coffee, please consider donating on <a href="https://ko-fi.com/aetherwellen" target="_blank">Ko-fi</a> or <a href="https://github.com/5ynchrogazer/OpenBooru" target="_blank">star the repository</a>, it really helps me out a lot!<br><br>
                        Also, if you ever find bugs, have suggestions, need support or simply want to hang out with me, <a href="https://discord.5ynchro.net" target="_blank">join the Discord server</a>!<br><br>
                        To lock the installer and start using OpenBooru, click the button below.<br>
                    </form>
                </div>
            <?php } ?>

            <div class="panel">
                <div class="split">
                    <button onclick="location.href='/install.php?step=<?= $step - 1 ?>'" class="element" <?php if ($step - 1 < 0) echo "disabled" ?>><?= $lang["previous"] ?></button>
                    <button onclick="javascript:submitForms();" class="element" <?php if ($error || ($step + 1 >= count($steps))) echo "disabled" ?>><?= $lang["next"] ?></button>
                </div>
                <p class="m-0 p-0 mt-5">
                    <?= replace($lang["installing_openbooru_version"], "[version]", "v$version") ?> -
                    <?= replace($lang["step_x_of_y"], ["[current]", "[total]"], [$step, count($steps) - 1]) ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        function submitForms() {
            let error = false;
            let ajaxCalls = [];
            // Disable the button
            document.querySelectorAll("button").forEach(function(button) {
                button.disabled = true;
            });

            if (document.getElementById('create_config')) {
                ajaxCalls.push(
                    $.ajax({
                        type: 'POST',
                        url: '/install.php',
                        data: $('#create_config').serialize(),
                        success: function(data) {
                            console.log(data);
                        }
                    })
                );
            }
            if (document.getElementById('create_colors')) {
                ajaxCalls.push(
                    $.ajax({
                        type: 'POST',
                        url: '/install.php',
                        data: $('#create_colors').serialize(),
                        success: function(data) {
                            console.log(data);
                        }
                    })
                );
            }
            if (document.getElementById('create_levels')) {
                ajaxCalls.push(
                    $.ajax({
                        type: 'POST',
                        url: '/install.php',
                        data: $('#create_levels').serialize(),
                        success: function(data) {
                            console.log(data);
                        }
                    })
                );
            }
            if (document.getElementById('create_db')) {
                ajaxCalls.push(
                    $.ajax({
                        type: 'POST',
                        url: '/install.php',
                        data: $('#create_db').serialize(),
                        success: function(data) {
                            if (data !== "") {
                                alert(data);
                                error = true;
                            }
                        }
                    })
                );
            }
            if (document.getElementById('fill_db')) {
                ajaxCalls.push(
                    $.ajax({
                        type: 'POST',
                        url: '/install.php',
                        data: $('#fill_db').serialize(),
                        success: function(data) {
                            console.log(data);
                        }
                    })
                );
            }
            if (document.getElementById('create_admin')) {
                ajaxCalls.push(
                    $.ajax({
                        type: 'POST',
                        url: '/install.php',
                        data: $('#create_admin').serialize(),
                        success: function(data) {
                            console.log(data);
                        }
                    })
                );
            }
            if (document.getElementById("finish")) {
                ajaxCalls.push(
                    $.ajax({
                        type: 'POST',
                        url: '/install.php',
                        data: $('#finish').serialize(),
                        success: function(data) {
                            console.log(data);
                            location.href = '/';
                        }
                    })
                );
            }

            if (ajaxCalls.length > 0) {
                $.when.apply($, ajaxCalls).done(function() {
                    document.querySelectorAll("button").forEach(function(button) {
                        button.disabled = false;
                    });
                    if (!error) {
                        location.href = '/install.php?step=<?= $step + 1 ?>';
                    }
                });
            } else {
                location.href = '/install.php?step=<?= $step + 1 ?>';
            }
        }

        function confirmReset() {
            if (confirm("Are you sure you want to reset the installation?")) {
                location.href = "/install.php?reset";
            }
        }

        $(document).ready(function() {
            const localeSelector = $("#localeSelector");
            const localeSelectorMenu = $("<div>").addClass("localeSelectorMenu");
            for (const [key, value] of Object.entries(locales)) {
                const localeSelectorItem = $("<a>").attr("href", "javascript:void").text(value);
                localeSelectorItem.click(function() {
                    document.cookie = `locale=${key}; expires=Fri, 31 Dec 9999 23:59:59 GMT`;
                    location.reload();
                });
                localeSelectorMenu.append(localeSelectorItem);
            }
            localeSelector.after(localeSelectorMenu);
            localeSelector.hover(function() {
                const offset = localeSelector.offset();
                const spaceBelow = $(window).height() - (offset.top + localeSelector.outerHeight());
                const spaceAbove = offset.top;
                if (spaceBelow > localeSelectorMenu.outerHeight()) {
                    localeSelectorMenu.removeClass("top").addClass("bottom");
                } else {
                    localeSelectorMenu.removeClass("bottom").addClass("top");
                }
                localeSelectorMenu.show();
            }, function() {
                localeSelectorMenu.hide();
            });
            localeSelectorMenu.hover(function() {
                $(this).show();
            }, function() {
                $(this).hide();
            });
        });
    </script>
</body>

</html>