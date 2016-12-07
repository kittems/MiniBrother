<?php
/**
 * This class produces the site configuration.
 * Configuration could be converted into a class instead of constants in the future.
 * However, constants are relatively easy to work with.
 */

// general infomation
    // name of the project
    define("NAME", "gametheory");
    // formal brand
    define("BRAND", "Game Theory");
    // debug true/false
    define("DEBUG", true);

    // major URL
    define("MAIN_PATH", "http://45.55.137.86/kenny/");
    // major server path
    define("SERVER_PATH", "/kenny/");

    // primary delimiter
    define("DELIMITER", ",");
    // second level delimiter
    define("DELIMITER_L2", "|");

// authentication constants
    define("ACCOUNT_DATA", NAME."Account");
    define("SESSION_TOKEN", NAME."SessionToken");
    if (DEBUG) {
        define("SECURE", false);
    } else {
        define("SECURE", true);
    }

// Used for general site functionality and layout.
    // Set up autoloading for the main controller folder, uses default autoload class
    $autoloadDirs = array( 'model', 'controller', 'model/account' );


?>
