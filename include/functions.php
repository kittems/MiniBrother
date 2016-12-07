<?php
/*
 * This file contains all of the miscellaneous utility functions that are
 * often necessary for the site function.
 *
 * Most functions use the configuration file.
 */

/**
 * @param path The path to prepend with the full url.
 * @return a link with the given main site path prepended.
 */
function buildUrl($path) {
    return MAIN_PATH . $path;
}
/**
 * @param path The path to prepend with the full server path to the file.
 * @return a file path with the given main site path prepended.
 */
function buildPath($path) {
    return $_SERVER['DOCUMENT_ROOT'] . SERVER_PATH . $path;
}
/**
 * Utility function to flatten an array.
 * @param originalArray The array to flatten.
 * @return a flattened version of the original array.
 */
function flattenArray($originalArray) {
    $flatArray = array();
    array_walk_recursive($originalArray,function($v, $k) use (&$flatArray){ $flatArray[] = $v; });
    return $flatArray;
}
/** 
 * A modified version of session_start that ensures cookie usage
 * in a more secure manner.
 */
function secureSessionStart() {
    $session_name = ACCOUNT_DATA;
    $secure = SECURE;
    // kills JS from grabbing at my session!!!
    $httponly = true;
    // Forces sessions to only use cookies and not go off on any other tangents
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        die("Failed Session due to browser cookie settings.");
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"],
        $cookieParams["path"], 
        $cookieParams["domain"], 
        $secure,
        $httponly);
    // Sets the session name to the one set above.
    session_name($session_name);
    session_start();            // Start the PHP session (using their command)
}
/**
 * A function that will redirect the current page.
 * @param url The url to redirect to.
 * @statusCode The code to show (permanent v temp)
 * @source http://stackoverflow.com/questions/768431/how-to-make-a-redirect-in-php
 */
function redirect($url, $statusCode = 303) {
   header('Location: ' . $url, true, $statusCode);
   die();
}

?>