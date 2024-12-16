<?php

$to = "https://5ynchro.net";

if (isset($_GET["t"]) && !empty($_GET["t"])) {
    $to = $_GET["t"];
}

header("Refresh: 2, url=" . $to, true, 302);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Redirecting...</title>
</head>

<body>
    <p>Redirecting to <a href="<?php echo $to; ?>"><?php echo $to; ?></a>...</p>
    <p>
        <b>Please note that you are being redirected to an external website.</b><br>
        If you are not redirected automatically, please click the link above.
    </p>
</body>