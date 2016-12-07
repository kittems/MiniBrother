<?php
/**
 * Given a classname, this is the function that will determine
 * the classes potential file location for including it.
 *
 * This class determines the location based on the namespace.
 */
function __autoload($className) {
    // explode to remove namespace at first, if it exists.
    $classNameArray = explode('\\', $className);
    $fileNames = array();
    if(class_exists($className) !== true) {
        if(count($classNameArray) > 1) {
            // check classes/classname.class.php first
            $fileName = __DIR__ . '/' . end($classNameArray) . '.class.php';
            array_push($fileNames, $fileName);
            // use additional pieces, excluding the first, as subdirectories
            $fileName2 = __DIR__ . '/';
            for($i = 1; $i < count($classNameArray) - 1; $i++) {
                $fileName2 .= $classNameArray[$i] . '/';
            }
            $fileName2 .= end($classNameArray) . '.class.php';
            array_push($fileNames, $fileName2);
        } else {
            $fileName = __DIR__ . '/' . $classNameArray[0] . '.class.php';
            array_push($fileNames, $fileName);
        }
        foreach($fileNames as $fileName) {
            if(file_exists($fileName)) {
                require $fileName;
                return true;
            }
        }
        return false;
    } else {
        return false;
    }
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