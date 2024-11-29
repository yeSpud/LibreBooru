<?php

if (!defined('WORLD')) {
    die("The World!");
}

// This is fucking useless lol what is this piece of code??!
if ($config["externalapi"] == false) {
    // Check if the request is coming from the same domain
    if ($_SERVER['HTTP_ORIGIN'] != $_SERVER['HTTP_HOST']) {
        //die("Invalid request");
    }
}

//print_r($_SERVER);