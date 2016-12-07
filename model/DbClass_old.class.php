<?php
abstract class DbClass {
    /* Database ID of the object. */
    private $id;
        function getId() { return $this->id; }
    /* The name of the table */
    private $tableName;
        function getTableName() { return $this->tableName; }
    /* Array of string variable names saved to database. Must have getters. */
    private $fields = array();
        function getFields() { return $this->fields; }
    /* Anything that is an array goes into here. */
    private $fieldArrays = array();
    /* constants for the name of dbformat and readdb function additions */
    private $dbFormatSuffix = "DbFormat";
    private $dbReadSuffix = "DbRead";
    private $classGetPrefix = "get";
    private $classSetPrefix = "set";
    
    /* whether or not it can be loaded via id. Must be database column */
    private $active;
        function isActive() { return $this->active; }
        function getActive() { return $this->active; }
        function reactivate() {
            global $db;
            if(isset($this->id)) {
                $this->active = 1;
                return $this->specificUpdate('active');
            } else {
                return false;
            }
        }
        function deactivate() {
            global $db;
            if(isset($this->id)) {
                $this->active = 0;
                return $this->specificUpdate('active');
            } else {
                return false;
            }
        }
    // database storing requires class type to exclude the namespace
    public function getClassType() {return end(explode('\\', get_class($this))); }

    // Default constructor for all those who inherit db class
    function __construct($tableName, $fields, $fieldArrays, $unsafeId = 0) {
        $this->tableName = $tableName;
        $this->fields = $fields;
        $this->fieldArrays = $fieldArrays;
        // if an ID is not given, it defaults to 0 (id can never be 0).
        if ($unsafeId != 0) {
            $this->id = intval($unsafeId);
            $this->init();
        } else {
            // By default should be active.
            $this->active = 1;
        }
    }
    /* gathers info from the database. Requires database name to match field name */
    protected function init() {
        global $db;
        if(isset($this->id)) {
            if ($storedInfo = $this->info($this->id)) {
                foreach($this->fields as $field) {
                    if(isset($storedInfo[$field])) {
                        $setMethodName = $this->classSetPrefix . ucfirst($field);
                        if(method_exists(get_class($this), $setMethodName)) {
                            $this->$setMethodName($this->dbRead($storedInfo[$field], $field));
                        } else {
                            throw new Exception('setter for field not found: ' . get_class($this) . '-> ' . $field);
                        }
                    } else {
                        throw new Exception(get_class($this) . '->' . $field . ' had no db info.');
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * Returns the value of the field, like a getter, wrapped up
     * the several different ways it can be accessed.
     * Tries get first, then direct var manipulation.
     * @param field The string name of the field.
     * @throws exception when no way to access var.
     */
    protected function _getField($field) {
        // Produce a getter.
        $getMethodName = $this->classGetPrefix . ucfirst($field);
        // Try and see if you can get with getter.
        if (method_exists(get_class($this), $getMethodName)) {
            // Return w/ getter.
            return $this->$getMethodName();
        } else if () {
        
        }
    }
    /**
     * Attempts to set the value of a field.
     * Tries a setter first, then direct var manipulation.
     * @param field The field name to set.
     * @param value The value to set the field to.
     * @throws exception when no way to set variable.
     */
    protected function _setField($field, $value) {
        
    }
    
    /* add attempts to add the instance to the db */
    protected function add() {
        global $db;
        if(isset($this->id) !== true) {
            $sql = "INSERT INTO " . $this->tableName . " SET ";
            $statements = array();
            foreach($this->fields as $field) {
                $getMethodName = $this->classGetPrefix . ucfirst($field);
                if(method_exists(get_class($this), $getMethodName)) {
                    $statement = $field . "='" . $this->dbFormat($this->$getMethodName(), $field) . "'";
                    array_push($statements, $statement);
                } else {
                    throw new Exception('getter for field not found: ' . get_class($this) . '->' . $field);
                }
            }
            $sql .= implode(",", $statements);
            $result = $db->query($sql);
            $this->id = $db->insert_id;
            return $result;
        }
        return false;
    }
    /* update attempts to save the information over the current info in the db */
    protected function update() {
        global $db;
        if(isset($this->id) === true) {
            $sql = "UPDATE " . $this->tableName . " SET ";
            $statements = array();
            foreach($this->fields as $field) {
                $getMethodName = $this->classGetPrefix . ucfirst($field);
                if(method_exists(get_class($this), $getMethodName)) {
                    $statement = $field . "='" . $this->dbFormat($this->$getMethodName(), $field) . "'";
                    array_push($statements, $statement);
                } else {
                    throw new Exception('getter for field not found: ' . get_class($this) . '->' . $field);
                }
            }
            $sql .= implode(",", $statements);
            $sql .= " WHERE id='".$this->id."'";
            
            return $db->query($sql);
        }
        return false;
    }
    /* getter, based on id attempts to save / add it */
    public function save() {
        if(isset($this->id) === true && $this->id > 0) {
            return $this->update();
        } else {
            return $this->add();
        }
    }
    /* given data, checks type, cleans for db, overwriteable per field 
     * Saves arrays in imploded state.
     */
    protected function dbFormat($data, $field) {
        global $db;
        // Checks if fieldnameDbFormat method exists, if so calls it
        $potentialFunction = $field . $this->dbFormatSuffix;
        if(isset($field) && method_exists(get_class($this), $potentialFunction)) {
            return $this->$potentialFunction($data);
        }
        // else uses defaults.
        if(is_string($data) && ctype_digit($data) !== true) {
            return $db->real_escape_string($data);
        } else if(is_string($data) && ctype_digit($data) === true) {
            return intval($data);
        } else if(is_array($data)) {
            return implode(DELIMITER, $data);
        } else {
            return $data;
        }
    }
    /* given data from db, checks if has function, else defaults behavior
     * Overwrite form: fieldnameDbRead (e.g. typeDbRead)
     */
    protected function dbRead($data, $field) {
        $potentialFunction = $field . $this->dbReadSuffix;
        if(isset($field) && method_exists(get_class($this), $potentialFunction)) {
            return $this->$potentialFunction($data);
        }
        
        if(in_array($field, $this->fieldArrays)) {
            return explode(DELIMITER, $data);
        } else {
            return $data;
        }
    }
    /* sets small data from db */
    function specificUpdate($field) {
        global $db;
        if(property_exists(get_class($this), $field)) {
            return $db->query("UPDATE " . $this->tableName . " SET $field='".$this->$field."' WHERE id='".$this->id."'");
        } else {
            $getMethodName = $this->classGetPrefix . ucfirst($field);
            if(method_exists(get_class($this), $getMethodName)) {
                return $db->query("UPDATE " . $this->tableName . " SET $field='".$this->$getMethodName()."' WHERE id='".$this->id."'");
            } else {
                return false;
            }
        }
    }
    /* retrieves info from db */
    protected function info($id,$idtype='id',$column='*',$active=1) {
        global $db;
        $id = $db->real_escape_string($id);
        $idtype = $db->real_escape_string($idtype);
        $column = $db->real_escape_string($column);
        // oh my god this is gross code wow ugh
        $sqlquery = "SELECT $column FROM " . $this->tableName . " WHERE $idtype='$id' AND active='$active'";
        $result = $db->query($sqlquery);
        if ($result) {
            // if there is more than one (or 0) attached, we got some big problems. So let's return false.
            if ($result->num_rows == 1) {
                return $result->fetch_assoc();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /* Dangerous function, but important to use. */
    public function destroy() {
        global $db;
        $sql = "DELETE FROM " . $this->tableName . " WHERE id='" . $this->id . "'";
        return $db->query($sql);
    }
}
?>