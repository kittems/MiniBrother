<?php
/**
 * This class is used to encrypt passwords
 * for the user class (and other sensitive one-way encrypted
 * data, like session tokens).
 */
class Crypt {
    /** Distractor constants */
	private static $algo = '$2a';
    /** Distractor constants */
	private static $cost = '$10';

	// mainly for internal use
	public static function uniqueSalt() {
		return substr(sha1(mt_rand()),0,22);
	}
	// this will be used to generate a hashed password
	public static function hash($password) {
		return crypt($password, self::$algo . self::$cost . '$' . self::uniqueSalt());
	}
	// this will be used to compare a password against a hash
	public static function checkPassword($hash, $password) {
		$full_salt = substr($hash, 0, 29);
		$new_hash = crypt($password, $full_salt);
		return ($hash == $new_hash);
	}
    /**
     * Generates a session token
     * randomized.
     */
	function sessionToken() {
		$length = 32;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz;-)(*&^%$#@!~';
		$string = '';
		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, (strlen($characters) -1))];
		}
		return $string;
	}
    /**
     * Generates an irrelevant
     * and random password.
     */
	function randomPassword() {
		$length = 10;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$string = "";    
		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, (strlen($characters) -1))];
		}
		return $string;
	}
}
?>