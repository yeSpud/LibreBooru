<?php

require __DIR__ . "/../bootstrapper.php";

$postCountSql = "SELECT COUNT(*) FROM posts WHERE is_approved = 1 AND deleted = 0";
$postCount = $conn->query($postCountSql)->fetch_column();

$tips = [
    "Did you know that LibreBooru supports keyboard shortcuts? Press 'H' to view the help menu!",
];
$randomTip = $tips[array_rand($tips)];

$smarty->assign("randomTip", $randomTip);
$smarty->assign("postCount", $postCount);
$smarty->assign("pagetitle", $config["sitename"]);
$smarty->assign("extraCSS", ["home"]);
$smarty->display("index.tpl");

$conn->close();
