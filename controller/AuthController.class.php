<?php
/**
 * This class is in charge of all actions that involve users,
 * currently logged in users, and authentication.
 */
class AuthController {
    /** Authentication Controller is treated like a singleton. */
    public static $manager;
        public function get() {
            if (self::$manager == null) {
                self::$manager = new self();
            }
            return self::$manager;
        }
    /** Constants for session names, loaded from config file */
    const SESSION_TOKEN = SESSION_TOKEN; // contains token
    const SESSION_DATA = ACCOUNT_DATA; // contains account data

    /** The current logged in user */
    private $user;
        public function getUser() {
            return $this->user;
        }
        public function hasUser() {
            return $this->user != null;
        }

    /** Constructor for controller */
    function __construct() {

    }

    /**
     * Attempts to log in as a user, and if that fails,
     * will login using the password provided by their attached
     * team, if they have one. If successful, sets the session variables.
     * @param email The user's email.
     * @param password The user's or team's password.
     * @param &error A pointer to fill with the error message if needed.
     * @return True/False if it was successful.
     */
    function login($email, $password, &$error) {
        // Get user with provided email:
        $user = User::getByEmail($email);
        if ($user == false) {
            // No user exists.
            $error = "The email you entered is not registered with " . NAME . ". Terribly sorry.";
            return false;
        }
        // We have a proper user object to attempt to login with.
        $crypt = new Crypt();
        if ($user->checkCredentials($password, $crypt)) {
            // Valid user! Need to create session.
            $this->createSession($user, $crypt);
            // Now set current user to be that user.
            $this->user = user;
            return true;
        } else {
            // Invalid password
            $error = "Uh-oh. Your password is incorrect.";
            return false;
        }
    }

    /**
     * Logs out the current user.
     */
    function logout() {
        $this->destroySession();
    }

    /**
     * Creates session for the given user.
     * Saves session in session variables.
     * @param user The user to create session for.
     * @param crypt The encryption algorithm to use.
     */
    private function createSession($user, $crypt) {
        $_SESSION[self::SESSION_DATA] = $user->getId();
        // Generate session token
        $sessionToken = $crypt->sessionToken();
        $_SESSION[self::SESSION_TOKEN] = $sessionToken;
        // Store session token in user:
        $user->updateSessionToken($sessionToken);
    }

    /**
     * Attempts to load the current user from the given session
     * constants.
     * @throws Exception if invalid data/tokens supplied.
     */
    private function loadSession() {
        if (!isset($_SESSION[self::SESSION_DATA]) || !isset($_SESSION[self::SESSION_TOKEN])) {
            return false;
        }
        // Get token/id from client
        $potentialId = $_SESSION[self::SESSION_DATA];
        $sessionToken = $_SESSION[self::SESSION_TOKEN];
        // Try to build user from id.
        $user = new User($potentialId);
        if ($user == null || $user->getId() != $potentialId) {
            return false;
        }
        // Make sure tokens match:
        if ($user->getSessionToken() != $sessionToken) {
            return false;
        }
        // We are all good!
        $this->user = $user;
        return true;
    }
    /** Removes the session token on user */
    private function destroySession() {
        unset($_SESSION[self::SESSION_DATA]);
		unset($_SESSION[self::SESSION_TOKEN]);
        $this->user = null;
    }

    /**
     * Function that forces page to require login.
     */
    function makeRequireLogin() {
        if (!$this->loadSession()) {
            // Set session var to be the page they attempted to reach.
            $_SESSION['redirectAfterLogin'] = $_SERVER['PHP_SELF'];
            // Redirect them to login.
            redirect(buildUrl('login.php'));
        } else {
            return 'Session loaded for: ' . $this->user->getFullName() . '<br />';
        }
    }
}


?>
