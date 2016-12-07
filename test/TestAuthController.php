<?php
include( '../include/backend.php' );
secureSessionStart();

function newTest($name) {
    echo "<h4>" . $name . "</h4>";
    echo "<hr />";
}

// Create user to insert into DB and then remove.
$testUser = new User();
//('first', 'last', 'teamId', 'email', 'password', 'sessionToken'
$testUser->fill('Kenneth', 'Test-Jones', 0, 'kgjones3@ncsu.edu', Crypt::hash('testpass'), '');
// Adds user to DB
newTest("User Create");
if ($testUser->save()) {
    echo "<p>Successful User Creation: ";
    var_dump($testUser->getAsArray());
    echo "</p>";
} else {
    echo "Failed.";
}

// Tests that user can be retrieved by email
newTest("User Retrieve Email");
$getUser = User::getByEmail("kgjones3@ncsu.edu");
if ($getUser != null) {
    echo "<p>Successful User Retrieval: ";
    var_dump($getUser->getAsArray());
    echo "</p>";
} else {
    echo "Failed.";
}


newTest("User Delete");
// Removes user from DB
if ($testUser->destroy()) {
    echo "<p>Successfully destroyed user</p>";
} else {
    echo "Failed.";
}
?>
