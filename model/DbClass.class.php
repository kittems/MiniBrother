<?php
/**
 * This class represents the basic model component.
 * DbClass, at the cost of allowing read/write access to all components
 * from within the class, will give the basic functionality to interact
 * with the database.
 * The fields given to the DbClass must be protected or have a protected
 * getter of the form getFieldName();
 * In addition, if there is an automatic formatting step when exiting the
 * database, DbFormat and DbRead can be used.
 * E.g. permissionIdDbRead($data) will pass the database value on reading
 * through the function as a filter before it is applied.
 *
 * This is a heavier and more intense version of DbMapper, which
 * is just a skin above the sql table mappers that come with f3.
 *
 * The id field is managed by DbClass, and the database must have an active
 * column. This is because there is no reason a table shouldn't have an
 * active column, and since it's good practice, it'll be mandated for this class.
 *
 * Provided abilities:
 * - getId: Retrieves the id from the database.
 * - isActive: Determines if this object is set as active in DB.
 * - save: If no id, inserts. If id, updates the object to its current state in DB.
 * - fill: Given a set of parameters, fills the object, like a constructor.
 * - pfill: Partial fills an object given an associative array.
 * - updateField: Updates a specific field in the database.
 * - init: Initializes the object from the information in the DB based on id.
 * - getAsArray: Gives the object as an array, used in JS transfer.
 * - FIELD:dbFormat - Create function to use for formatting when update/insert.
 * - FIELD:dbRead - Create function to use when reading from DB in init/load.
 * - FIELD:GetAsArray - Format function for specific fields when formatting to array.
 * - get:FIELD - Create function to be used when retrieving field, else must be protected.
 * - set:FIELD - Create function to be used when setting field, else must be protected.
 *
 * - getAllIds: Provides all ids of this object type from the DB. (not specific)
 * - getFilteredIds: Provides ids after a filter is applied from the DB (not specific)
 *
 * Expected:
 * - active field in DB (default to 1)
 * - id field in DB (primary key)
 * - field + fieldArray + fieldObjects for knowledge about what fields are kept.
 * - tableName for how to pull from the DB.
 *
 * Supported Types:
 * - arrays (if in fieldArrays)
 * - DbClass objects (if in fieldObjects)
 *      Stored as id, loaded as object.
 *      Class name must be the key.
 * - primitive types
 *
 * Debugging:
 *  - If you are having issues with DbClass, try turning on debugging.
 *  - The constructor's last parameter is to turn on debug mode for that class.
 */
abstract class DbClass {
    /** Override to turn on for all DbClasses */
    const DEBUG = false;
    /** Whether or not this unit is in debug mode */
    private $debug;

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
    /** Anything that is a DbClass object goes into here */
    private $fieldObjects = array();

    /* constants for the name of dbformat and dbread function additions */
    private $dbFormatSuffix = "DbFormat";
    private $dbReadSuffix = "DbRead";
    /** for retrieving as an array */
    private $getAsArraySuffix = "GetAsArray";
    /** For getting and setting */
    private $classGetPrefix = "get";
    private $classSetPrefix = "set";

    /* whether or not it can be loaded via id. Must be database column */
    private $active;
        function isActive() { return $this->active; }
        function getActive() { return $this->active; }
        /** Reactivates entry. Updates DB immediately. */
        function reactivate() {
            global $db;
            if(isset($this->id)) {
                $this->active = 1;
                return $this->updateField('active');
            } else {
                return false;
            }
        }
        /** Deactivates entry. Updates DB immediately. */
        function deactivate() {
            global $db;
            if(isset($this->id)) {
                $this->active = 0;
                return $this->updateField('active');
            } else {
                return false;
            }
        }
    /** Returns the class type without namespace */
    function getClassType() {return end(explode('\\', get_class($this))); }

    /**
     * Constructor sets up the class to interact with the
     * database properly. It also allows the class to
     * automatically load data from the database via id.
     * In order to fill the class with data and blank, just leave
     * off the last param, and use the fill method to enter data.
     * @param $tableName The name of the DB table
     * @param $fields An array of field names that will be kept track of
     * @param $fieldArrays All the fields that are kept as arrays in DB
     * @param $unsafeId (optional) The id to load from the database upon creation
     */
    function __construct($tableName, $fields, $fieldArrays, $fieldObjects, $unsafeId = 0, $debug = false) {
        $this->tableName = $tableName;
        $this->fields = $fields;
        $this->fieldArrays = $fieldArrays;
        $this->fieldObjects = $fieldObjects;
        $this->debug = $debug;
        $this->active = 1; // Assume active by default.
        // if an ID is not given, it defaults to 0 (id can never be 0).
        if ($unsafeId != 0) {
            $this->id = intval($unsafeId);
            $this->init();
        }
    }
    /**
     * Fills the DbClass, based on their database fields, in mass.
     * Allows for a constructor that takes in values, but avoids the confliction
     * with the constructor to init based on an id.
     * @param $values, all parameters MUST be in the order of the array of fields.
     */
    function fill() {
        // Assumes correct order.
        $i = 0;
        if (func_num_args() != count($this->fields)) {
            throw new Exception("Invalid fill: Not enough fields.");
        }
        foreach($this->fields as $field) {
            $this->setField($field, func_get_args()[$i]);
            $i++;
        }
    }
    /**
     * pfill or Partial Fill.
     * Given a string-indexed array where the key is the field and the value
     * is the value to set for the array.
     * @param string-indexed array.
     */
    function pfill($fill) {
        // Set those provided.
        foreach($fill as $field => $value) {
            $this->setField($field, $value);
        }
    }

    /**
     * Sets this field to be the given value
     * via its setter or through its property value
     * if no setter exists.
     * @param $field The field string name to set.
     * @param $value The value to set it to.
     * @throws Exception if setter/property DNS or bad privacy
     */
    private function setField($field, $value) {
        // Generates the correct string for the set name of the field.
        $setMethodName = $this->classSetPrefix . ucfirst($field);
        if (method_exists(get_class($this), $setMethodName)) {
            $this->$setMethodName($value);
        } else if (property_exists(get_class($this), $field)) {
            $this->$field = $value;
        } else {
            throw new Exception('Setter and field not found: ' . get_class($this) . '-> ' . $field);
        }
    }
    /**
     * Returns the current value of the field
     * using its getter or through its property value
     * if no getter exists.
     * @param $field The field string name to set.
     * @throws Exception if setter/property DNS or bad privacy
     * @return Current value of field
     */
    private function getField($field) {
        // Generates the correct get name of the field.
        $getMethodName = $this->classGetPrefix . ucfirst($field);
        if (method_exists(get_class($this), $getMethodName)) {
            return $this->$getMethodName();
        } else if (property_exists(get_class($this), $field)) {
            return $this->$field;
        } else {
            throw new Exception('Getter and field not found: ' . get_class($this) . '->' . $field);
        }
    }

    /* gathers info from the database. Requires database name to match field name */
    protected function init() {
        global $db;
        if(isset($this->id)) {
            if ($storedInfo = $this->info($this->id)) {
                foreach($this->fields as $field) {
                    if(isset($storedInfo[$field])) {
                        $this->setField($field, $this->dbRead($storedInfo[$field], $field));
                    } else {
                        throw new Exception(get_class($this) . '->' . $field . ' had no db data.');
                    }
                }
            }
        }
        return false;
    }
    /* add attempts to add the instance to the db */
    protected function add() {
        global $db;
        if(isset($this->id) !== true) {
            $sql = "INSERT INTO " . $this->tableName . " SET ";
            $statements = array();
            foreach($this->fields as $field) {
                $statement = $field . "='" . $this->dbFormat($this->getField($field), $field) . "'";
                array_push($statements, $statement);
            }
            $sql .= implode(",", $statements);
            // Print if debug.
            $this->printSql("Add Query", $sql);
            $result = $db->query($sql);
            if (!$result) {
                $this->heavyError("Add", "Invalid Result:" . $db->error);
            }
            $this->id = $db->insert_id;
            return $result;
        }
        $this->heavyError("Add", "No Id provided.");
        return false;
    }

    /* update attempts to save the information over the current info in the db */
    protected function update() {
        global $db;
        if(isset($this->id) === true) {
            $sql = "UPDATE " . $this->tableName . " SET ";
            $statements = array();
            foreach($this->fields as $field) {
                $statement = $field . "='" . $this->dbFormat($this->getField($field), $field) . "'";
                array_push($statements, $statement);
            }
            $sql .= implode(",", $statements);
            $sql .= " WHERE id='".$this->id."'";
            // Print if debug.
            $this->printSql("Update Query", $sql);
            $result = $db->query($sql);
            if (!$result) {
                $this->heavyError("Update", "Invalid Result:" . $db->error);
            }
            return $result;
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
    /**
     * Attempts to format the field for the database using a
     * per-class-field-basis function that can be overwritten.
     * given data, checks type, cleans for db, overwriteable per field
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
        if (is_bool($data)) {
            // Need to convert true/false into 1/0
            if ($data) { return 1; }
            return 0;
        } else if(is_string($data) && ctype_digit($data) !== true) {
            // Need to escape strings and nondigits.
            return $db->real_escape_string($data);
        } else if(is_string($data) && ctype_digit($data) === true) {
            // Need to convert to int.
            return intval($data);
        } else if(is_array($data)) {
            // Need to turn into string.
            return implode(DELIMITER, $data);
        } else if (in_array($field, $this->fieldObjects)) {
            // If the object is null, we want to save as id 0.
            if ($data == null || $data == 0) {
                return 0;
            }
            // Object, need to save & get id.
            if (!$data->save()) {
                $this->heavyError("DbFormat", "Invalid object or object could not save.");
                return $data;
            }
            return $data->getId();
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

        if (in_array($field, $this->fieldArrays)) {
            // Convert back to array.
            return explode(DELIMITER, $data);
        } else if (in_array($field, $this->fieldObjects)) {
            // If it is 0, we want to set the field to be null.
            if ($data == 0 || $data == null) {
                return null;
            }
            // Convert back into object, class should be key.
            $class = array_search($field, $this->fieldObjects);
            // Make sure it exists.
            if (!class_exists($class)) {
                $this->heavyError("DbRead", "Invalid object cannot be read.");
                return $data;
            }
            // Create into object using id.
            return new $class($data);
        } else {
            return $data;
        }
    }
    /**
     * Checks to see if the field has set a special get as array function,
     * else just returns its value.
     */
    protected function getFieldAsArray($field) {
        // Create version with suffix.
        $potentialFunction = $field . $this->getAsArraySuffix;
        // Check if it exists.
        if(isset($field) && method_exists(get_class($this), $potentialFunction)) {
            return $this->$potentialFunction();
        }

        // If it's an object, we need to get it as an array.
        if (in_array($field, $this->fieldObjects)) {
            $obj = $this->getField($field);
            if ($obj == null) {
                return null;
            }
            return $obj->getAsArray();
        }
        // Else treat as normal.
        return $this->getField($field);
    }
    /**
     * Does a single instance update to the database
     * for a single field. Used for important immediate
     * data changes.
     */
    function updateField($field) {
        global $db;
        $value = $this->getField($field);
        $sql = "UPDATE " . $this->tableName . " SET $field='" . $value . "' WHERE id='".$this->id."'";
        // Print if debug.
        $this->printSql("Update Field", $sql);
        return $db->query($sql);
    }
    /* retrieves info from db */
    protected function info($id,$idtype='id',$column='*',$active=1) {
        global $db;
        $id = $db->real_escape_string($id);
        $idtype = $db->real_escape_string($idtype);
        $column = $db->real_escape_string($column);
        // oh my god this is gross code wow ugh
        $sqlquery = "SELECT $column FROM " . $this->tableName . " WHERE $idtype='$id' AND active='$active'";
        // Print if debug.
        $this->printSql("Info", $sqlquery);
        $result = $db->query($sqlquery);
        if ($result) {
            // if there is more than one (or 0) attached, we got some big problems. So let's return false.
            if ($result->num_rows == 1) {
                return $result->fetch_assoc();
            } else {
                $this->heavyError("Info", "More than one result.");
                return false;
            }
        } else {
            $this->heavyError("Info", "No Result: " . $db->error);
            return false;
        }
    }
    /* Dangerous function, but important to use. */
    function destroy() {
        global $db;
        $sql = "DELETE FROM " . $this->tableName . " WHERE id='" . $this->id . "'";
        // Print if debug.
        $this->printSql("Destroy Query", $sql);
        $result = $db->query($sql);
        if (!$result) {
            $this->heavyError("Destroy", "Invalid Result:" . $db->error);
        }
        return $result;
    }

    /** Formatting function for printing out SQL.
     * Only runs if DEBUG is turned on
     * @param sql About to run.
     */
    private function printSql($type, $sql) {
        if (self::DEBUG || $this->debug) {
            echo "DbClass Query ( " . $type . " )<br />";
            echo $sql . "<br />";
        }
    }
    /**
     * Special function that does heavy error logging if DEBUG is true.
     */
    private function heavyError($name, $error) {
        if (self::DEBUG || $this->debug) {
            echo "DbClass Error (" . $name . ")<br />";
            echo $error . "<br />";
        }
        $message = "DbClass Error (" . $name . "): " . $error;
        trigger_error($message, E_USER_WARNING);
    }

    /**
     * Converts this class into an associative array.
     * Used for stuff like data transfer and testing.
     * @return The object as an array.
     */
    function getAsArray() {
        $object = array();
        $object['id'] = $this->id;
        foreach($this->fields as $field) {
            $object[$field] = $this->getFieldAsArray($field);
        }
        if ($this->active) {
            $object['active'] = true;
        } else {
            $object['active'] = false;
        }
        return $object;
    }

    /**
     * Function to fetch all the ids of this type.
     * Convenience function.
     */
    function getAllIds() {
        global $db;
        $ids = array();
        $sql = "SELECT id FROM " . $this->tableName . " WHERE active='1'";
        $this->printSql("Get All Ids", $sql);
        $result = $db->query($sql);
        while($row = $result->fetch_assoc()) {
            array_push($ids, $row['id']);
        }
        $result->free();
        return $ids;
    }

    /**
     * Retrieves all ids of this type, but filtered based on a provided criteria set.
     * The filter is ANDed together for all fields.
     * Filter format:
     *  associative array of the form:
     *  - filter[column] = array(comparator, value)
     * Example:
     * $filter = array(
     *      "diffId" => array("=", 2)
     * );
     */
    function getFilteredIds($filter) {
        global $db;
        $ids = array();
        // Generate sql using the filter.
        $sql = "SELECT id FROM " . $this->tableName . " WHERE active='1'";
        // Append to sql each additional query.
        foreach ($filter as $field => $info) {
            // Make sure it is in fields.
            if (in_array($field, $this->fields)) {
                // We can now append.
                $compare = $info[0];
                $value = $info[1];
                $sql .= " AND " . $field . $compare . "'" . $value . "'";
            } else {
                // It's not in the set of fields.
                $msg = "The field " . $field . " is not a provided field to filter by.";
                $this->heavyError("getFilteredIds", $msg);
            }
        }
        // Now perform the query and return the ids.
        $this->printSql("Get Filtered Ids", $sql);
        $result = $db->query($sql);
        while($row = $result->fetch_assoc()) {
            array_push($ids, $row['id']);
        }
        $result->free();
        return $ids;
    }
}
?>
