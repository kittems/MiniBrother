<?php
/**
 * This file wraps together all of the backend pieces into a seamless and useable
 * include file for the rest of the site to use. 
 * It is effectively the main function of the backend, where it is all
 * included and tied together.
 */

// First, the database should be included, or else the backend will not function.
// If the site has no database connection, then this is not necessarily.
require_once('dbconnect.php');

// Now we should set up our site configurations for the rest to use.
require_once('Config.class.php');

// Many classes depend on these essential utility functions, they will be included next.
require_once('functions.php');

// We want to include the ability to autoload classes:
require_once('Autoload.class.php');

// Any essential model classes should be included next, these are classes
// that we don't want to rely on autoloading for, but want to always have.
require_once('model.php');

// Lastly, we should build any global variables that the site may need to access.
// These could be large site controllers, or a framework like f3's main object.
require_once('globals.php');

?>