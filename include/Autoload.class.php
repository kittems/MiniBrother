<?php
/**
 * Given a classname, this is the function that will determine
 * the classes potential file location for including it.
 *
 * This class uses arbitrary names to attempt to find the include file.
 * The dirs provided should be of the format 'model/dogs/cats/includes'
 * from the root dir provided in buildPath.
 *
 * @depends on a global var autoloadDirs to check for autoloading.
 * @depends on the build functions (ala buildPath)
 */

/* e.g.
 * $autoloadDirs = array( 'model', 'controller' );
 */

function basicAutoload($className) {
    global $autoloadDirs;
    // Prep array of potentials to try
    $fileNames = array();
    if(class_exists($className) !== true) {
        // Add the dirs.
        foreach($autoloadDirs as $dir) {
            // Dependency buildPath
            $fileName = buildPath($dir) . '/' . $className . '.class.php';
            array_push($fileNames, $fileName);
        }
        // Now attempt each potential
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

// Registers autoloader
spl_autoload_register('basicAutoload');
?>
