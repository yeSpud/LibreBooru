<?php

if (!defined('WORLD')) {
    die("The World!");
}

$conn = new mysqli($db["host"], $db["user"], $db["pass"], $db["name"]);

// Try connecting and return error if failed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
