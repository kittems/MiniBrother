<?php

/** Creates a header */
function startTest($name) {
    echo "<h4>" . $name . "</h4>";
    echo "<hr />";
    echo "<blockquote>";
}

/** Outputs an assert. */
function printAssert($name, $expected, $actual) {
    echo "<hr />";
    echo "<h4>" . $name . " (equal)</h4>";
    echo "<blockquote>";
    echo "<p>Expected: " . $expected . "</p>";
    echo "<p>Received: " . $actual . "</p>";
    echo "</blockquote>";
    if ($expected == $actual) {
        echo "<h4>Passsed.</h4>";
    } else {
        echo "<h4>Failed.</h4>";
    }
}
/** Assert backwards */
function printAssertUnequal($name, $expected, $actual) {
    echo "<hr />";
    echo "<h4>" . $name . " (unequal)</h4>";
    echo "<blockquote>";
    echo "<p>Expected: " . $expected . "</p>";
    echo "<p>Received: " . $actual . "</p>";
    echo "</blockquote>";
    if ($expected != $actual) {
        echo "<h4>Passsed.</h4>";
    } else {
        echo "<h4>Failed.</h4>";
    }
}
/** Asserts not null */
function printNotNull($name, $value) {
    echo "<hr />";
    if ($value == null) {
        echo "<h4>" . $name . " Failed: Value is null.</h4>";
    } else {
        echo "<h4>" . $name . " Passed: Value is not null.</h4>";
    }
}

/** Ends test */
function stopTest() {
    echo "</blockquote>";
}

?>
