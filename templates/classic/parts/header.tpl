<!DOCTYPE html>
<html lang="{$config["language"]}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{$pagetitle}</title>

    <link rel="stylesheet" href="/assets/{$config["theme"]}/main.css">

    {if isset($extraCSS)}
        {foreach from=$extraCSS item=item key=key name=name}
            <link rel="stylesheet" href="/assets/{$config["theme"]}/{$item}.css">
        {/foreach}
    {/if}

    <!-- Get your jQuery here: https://cdnjs.com/libraries/jquery -->
    <script src="/assets/{$config["theme"]}/js/jquery.min.js"></script>
    <script>
        // Added categories: "spirit", "liqueur", "preparation", "garnish", "glass",
        const color_spirit = "{$colors["spirit"]}";
        const color_liqueur = "{$colors["liqueur"]}";
        const color_preparation = "{$colors["preparation"]}";
        const color_garnish = "{$colors["garnish"]}";
        const color_glass = "{$colors["glass"]}";
        const color_copyright = "{$colors["copyright"]}";
        const color_character = "{$colors["character"]}";
        const color_artist = "{$colors["artist"]}";
        const color_general = "{$colors["general"]}";
        const color_meta = "{$colors["meta"]}";
        const color_other = "{$colors["other"]}";
        const color_awaiting = "{$colors["awaiting"]}";
        const color_video = "{$colors["video"]}";
        const color_gif = "{$colors["gif"]}";
    </script>
    <script src="/assets/{$config["theme"]}/js/main.js"></script>

    <script>
        const locales = {$locales|json_encode};
    </script>

    <style>
        .tag_copyright {
            color: {$colors["copyright"]};
        }

        .tag_character {
            color: {$colors["character"]};
        }

        .tag_artist {
            color: {$colors["artist"]};
        }

        .tag_general {
            color: {$colors["general"]};
        }

        .tag_meta {
            color: {$colors["meta"]};
        }

        .tag_other {
            color: {$colors["other"]};
        }

        .post_awaiting {
            border: 3px solid {$colors["awaiting"]};
        }

        .post_deleted {
            border: 3px solid red;
        }

        .post_video {
            border: 3px solid {$colors["video"]};
        }

        .post_gif {
            border: 3px solid {$colors["gif"]};
        }
    </style>
</head>

<body>