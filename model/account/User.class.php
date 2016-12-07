<?php
/**
 * The user class is responsible for representing
 * a User on the system, and the Authentication Controller
 * handles the process of logging in.
 *
 */
class User extends DbClass {
    /** The table used for the class */
    const TABLE_NAME = "users";
    /** The fields tracked by the database for this class */
    private $fields = array('first', 'last', 'teamId', 'email', 'password', 'sessionToken');
    /** All the fields that are stored as arrays */
    private $fieldArrays = array();
    /** All the fields that are stored as objects */
    private $fieldObjects = array();
    
    /** The first name of the user */
	private $first;
		function getFirst() {return $this->first;}
		function setFirst($value) {$this->first=$value; return true;}
    /** The last name of the user */
	private $last;
		function getLast() {return $this->last;}
		function setLast($value) {$this->last=$value; return true;}
        function getFullName() {return ucwords($this->first . ' ' . $this->last);}
    /** The user's email */
	private $email;
		function getEmail() {return $this->email;}
		function setEmail($value) {$this->email=$value; return true;}
    /** The encrypted user password */
	private $password;
		function getPassword() {return $this->password;}
		function setPassword($value) {$this->password=$value; return true;}
        function randomizePassword() {
            $crypt = new Crypt();
            $password = $crypt->randomPassword();
            $this->password = $crypt->hash($password);
        }
    /** The user's stored session token (may be empty) */
    protected $sessionToken;
        function getSessionToken() { return $this->sessionToken; }

    /** The team id of the user. */
    protected $teamId;
        function getTeamId() {return $this->teamId;}
    /**
     * Uses the parent constructor to load Users from the DB
     * via ID using DbClass.
     * However, does not currently initialize the session.
     * @param $unsafeId The id to load from the database.
     */
    function __construct($unsafeId = 0) {
        parent::__construct(self::TABLE_NAME, $this->fields, $this->fieldArrays, $this->fieldObjects, $unsafeId);
    }

	/*function email_password($email, $password) {
		$subject = "Account Information";
		$message = "You have been assigned an account on <name>. Your username is your email:\n\n
		Email/Username: " . $email . "\n Password: " . $password . "\n\n Upon logging in, you will be asked to change your password from this randomly generated password. \n Thank you! Have a good day.";
		mail($email,$subject,$message,"from:AEDpulse <accounts@<name>.com>","-faccounts@<name>.com");
	}*/

	/**
     * Compares credentials, checks if they match.
     * @param password Password to compare against one-way pass.
     * @param crypt The encryption object used for passwords.
     * @return true/false if the credentials match DB credentials.
     */
	function checkCredentials($password, $crypt) {
        // Compare passwords.
		return $crypt->checkPassword($this->password, $password);
	}

    /**
     * Changes the current session token to be the given one.
     * Used within the login process.
     * @param token The token to use.
     */
    function updateSessionToken($token) {
        $this->sessionToken = $token;
        $this->updateField('sessionToken');
    }

    /**
     * Returns the user object by email.
     * @param email The email to find a user for.
     * @throws Exception if more than one user exists with that email.
     * @returns false if no user exists, or the user object.
     */
    static function getByEmail($email) {
        global $db;
        $email = $db->real_escape_string($email);
        // Attempt to find correct user.
        $sql = "SELECT id FROM " . self::TABLE_NAME . " WHERE email='$email' AND active='1'";
        $result = $db->query($sql);
        // Make sure there's at least a result.
        if (!$result || $result->num_rows < 1) {
            return false;
        }
        // Make sure there's only one result.
        if ($result->num_rows > 1) {
            throw new Exception("More than one user with email: " . $email);
        }
        // There is only one. Return user.
        $id = $result->fetch_assoc()['id'];
        $result->free();
        return new self($id);
    }

    static function get() {
        global $db;
        $users = array();
        $result = $db->query("SELECT id FROM " . self::TABLE_NAME . " WHERE active='1'");
        while($row = $result->fetch_assoc()) {
            $user = new self($row['id']);
            array_push($users, $user);
        }
        $result->free();
        return $users;
    }
}
?>
