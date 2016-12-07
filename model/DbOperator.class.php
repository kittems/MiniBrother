<?php

/**
 * This class provides mass utility functions to all classes that extend DbClass
 * These functions include CRUD operations: add, remove (and destroy), get.
 *
 * This class does not maintain a list of object.
 *
 * Abilities:
 * - add/padd: Two different methods of creating an object, returns creation.
 * - edit/pedit: Two different methods to edit an object, returns creation.
 * - getIds/getObjects: Two methods of retrieving sets of objects.
 * - remove: A method to deactivate an object, returns success true/false.
 * - destroy: A method to completely remove an object, returns success.
 *
 * Expects:
 * - Class has DbClass extended properly.
 */
class DbOperator {

    /** The type of DbClass class it is */
    private $type;
        function getType() {return $this->type;}
    /** The table name used in operations */
    private $tableName;
        function getTableName() {return $this->tableName;}
    /** The fields available */
    private $fields;
        function getFields() {return $this->fields;}

    /**
     * Creates this DbOperator from the class string provided.
     * All information is extracted from DbClass.
     * @param class The class name/type.
     */
    function __construct($class) {
        $this->extract($class);
    }

    /**
     * Loads information from the type.
     * @param class The class name/type.
     */
    private function extract($class) {
        $this->type = $class;
        $object = new $class();
        // Grab information from object.
        $this->tableName = $object->getTableName();
        $this->fields = $object->getFields();
    }

    /**
     * Full add: Works akin to fill.
     * Cleans input, creates object from param, saves object.
     * @param the values of the fields in order.
     * @return the produced and added object or null if failed.
     */
    function add() {
        // First step, make sure it was properly passed.
        if (func_num_args() != count($this->fields)) {
            throw new Exception("Invalid add: Not enough fields.");
        }
        // Now fill it appropriately.
        $info = array();
        $i = 0;
        foreach($this->fields as $field) {
            $info[$field] = func_get_args()[$i];
            $i++;
        }
        // And add it using padd.
        return $this->padd($info);
    }
    /**
     * Partial add: Adds associative-style similar to pfill.
     * Cleans input, creates object from params, saves object.
     * @param $info an associative array of field->values
     * @return The produced and added object or null if failed.
     */
    function padd($info) {
        return $this->psave($info);
    }

    /**
     * Similar to add, but the first argument MUST be the id.
     * @param The values in order of fields, with id coming first.
     * @return The object edited, or null if not saved.
     */
    function edit() {
        // First step, make sure it was properly passed as fields + 1
        if (func_num_args() != count($this->fields) + 1) {
            throw new Exception("Invalid edit: Not enough fields (or missing id).");
        }
        // Grab the id field.
        $id = func_get_args()[0];
        $info = array();
        $i = 1;
        foreach($this->fields as $field) {
            $info[$field] = func_get_args()[$i];
            $i++;
        }
        // Now edit using pedit.
        return $this->pedit($info, $id);
    }

    /**
     * Passed as an associative array, except with an id.
     * @param info The associative array of values to edit.
     * @param id The id of the object to load and edit.
     * @return The object edited, or null if not saved.
     */
    function pedit($info, $id=0) {
        if (intval($id) != 0) {
            return $this->psave($info, $id);
        }
        throw new Exception("Invalid edit: invalid id provided.");
    }

    /**
     * Used to support the combined operation for both save and edit.
     */
    private function psave($info, $id=0) {
        // See if it's an add or edit.
        if ($id != 0) {
            // It's an edit: load current from DB.
            $object = new $this->type($id);
        } else {
            // It's an add: Create empty object
            $object = new $this->type();
        }
        // Fill up as much as provided:
        $object->pfill($info);
        // DbClass cleans all input by default, so we can now save.
        if ($object->save()) {
            return $object;
        } else {
            return null;
        }
    }

    /**
     * Retrieves all active ids of this type from the database.
     * @param $filter (o) If provided, will filter the ids. See DbClass getFilteredIds.
     * @return All active object ids of this type.
     */
    function getIds($filter=0) {
        $provider = new $this->type();
        // They want all of them.
        if ($filter == 0) {
            return $provider->getAllIds();
        }
        // We should return the filter.
        return $provider->getFilteredIds($filter);
    }

    /**
     * Retrieves all active objects of this type from the database.
     * @param $ids (optional) a provided set of ids to perform on.
     * @return All active objects of this type.
     */
    function getObjects($ids=0) {
        if ($ids == 0) {
            $ids = $this->getIds();
        }
        $objects = array();
        foreach($ids as $id) {
            array_push($objects, new $this->type($id));
        }
        return $objects;
    }

    /**
     * Retrieves all active objects as associative arrays.
     * @param $ids (optional) a provided set of ids to perform on.
     * @return ALl active objects as assoctiative arrays.
     */
    function getAssocs($ids=0) {
        if ($ids == 0) {
            $ids = $this->getIds();
        }
        $objects = array();
        foreach($ids as $id) {
            array_push($objects, (new $this->type($id))->getAsArray());
        }
        return $objects;
    }

    /**
     * Deactivates a specific object, does not actually remove it from the DB.
     * @param id The id to remove.
     * @return Success true/false.
     */
    function remove($id) {
        $object = new $this->type($id);
        // 'Remove' object.
        return $object->deactivate();
    }

    /**
     * The much more dangerous destroy operation.
     * Is not shared to the public by default, a class must extend this
     * class in order to enable functionality to protect issues.
     */
    protected function destroy($id) {
        $object = new $this->type($id);
        // 'Remove' object.
        return $object->destroy();
    }
}

?>
