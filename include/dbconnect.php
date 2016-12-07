<?php
/*
 * This file configures the database connections used within the site.
 * They are treated as global variables, but could be hidden within
 * another class if needed.
 */

$db = new mysqli('localhost', 'root', 'vjkk5793', 'gametheory_kenny');

if($db->connect_errno > 0) {
    die('Unable to connect to the database: ['.$db->connect_error.']');
}

?>