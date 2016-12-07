<?php

/**
 * This autoload function searches for the class Location before requiring it. 
 * So there's no need of putting the classes all in one folder. 
 *
 * Requirements:
 * - the subfolders must be at least 3 letters long
 * - the filenames must be in the form CLASSNAME.class.php
 *
 * Note:
 * - in this example the main class folder is 'lib'
 * - define('AUTOLOAD_DIR', dirname(__FILE__).'/');
 *
 * @source http://php.net/manual/en/language.oop5.autoload.php
 * @modified by Kenny Jones
 */

function __autoload($className) {
    $folder=classFolder($className);
    if($folder) require_once($folder.'/'.$className.'.class.php');
}

function classFolder($className,$folder='lib') {
    $dir=dir(AUTOLOAD_DIR.$folder);
    if($folder=='lib' && file_exists(AUTOLOAD_DIR.$folder.'/'.$className.'.class.php')) return $folder;
    else {
        while (false!==($entry=$dir->read())) {
            $checkFolder=$folder.'/'.$entry;
            if(strlen($entry)>2) {
                if(is_dir(AUTOLOAD_DIR.$checkFolder)) {
                    if(file_exists(AUTOLOAD_DIR.$checkFolder.'/'.$className.'.class.php')) return $checkFolder;
                    else {
                        $subFolder=classFolder($className,$checkFolder);
                        if($subFolder) return $subFolder;
                    }
                }
            }
        } 
    }
    $dir->close();
    return 0;
}

/**
 * Check to see if a class can be autoloaded.
 * Will autoload the class within the check.
 * @return Whether the given classname can be autoloaded
 */
function canClassAutoload($className) {
    return class_exists($className);
}

?>