<?php
    /* The Header
     * This header file includes bootstrap and jquery.
     *
     * Dynamic variables:
     * - $title: Changes the title of the page if set.
     * - $extraCss: (array) Links to additional CSS style files to include.
     * - $extraJs: (array) Links to additional JS files.
     */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>
        <?php
            if (isset($title)) {
                echo $title . " - Mini Brother";
            } else {
                echo "Mini Brother";
            }
        ?>
    </title>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale = 1.0,maximum-scale = 1.0" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <?php
        // Includes any additional custom CSS files.
        if (isset($extraCss)) {
            foreach($extraCss as $css) {
                echo '<link rel="stylesheet" href="' . $css . '">';
            }
        }
        // Includes any additional custom JS files.
        if (isset($extraJs)) {
            foreach($extraJs as $js) {
                echo '<script src="' . $js . '"></script>';
            }
        }
    ?>
</head>
<body>
