<?php
/**
 * An authentication unit that binds together a group of users.
 * Also used to associate a group of users with actions and responses.
 * 
 */
class Team extends DbClass {
    /** The table used for the class */
    const TABLE_NAME = "teams";
    /** The fields tracked by the database for this class */
    private $fields = array('name', 'actionIds', 'password');
    /** All the fields that are stored as arrays */
    private $fieldArrays = array('actionIds');
    /** All the fields stored as objects */
    private $fieldObjects = array();
    
    /** The name of the Team */
    private $name;
        function getName() {return $this->name;}
		function setName($value) {$this->name=$value; return true;}
    /** A list of the the action Ids (example where no getters/setters but protected instead) */
    protected $actionIds;
    
    /** Encrypted password for the team (protected getters/setters) */
    private $password;
        protected function getPassword() {return $this->password;}
		protected function setPassword($value) {$this->password=$value; return true;}
    
    /**
     * Basic constructor that loads from the DB based on
     * a provided id, or is a blank structure if not provided.
     */
    function __construct($unsafeId = 0) {
        parent::__construct(self::TABLE_NAME, $this->fields, $this->fieldArrays, $this->fieldObjects, $unsafeId);
    }
}

?>