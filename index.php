<?php
include( 'include/backend.php' );
// This page requires login.
secureSessionStart();
AuthController::get()->makeRequireLogin();

// Include header files for HTML
include(buildPath('include_ui/head.inc.php'));
include(buildPath('include_ui/nav.inc.php'));
?>

Hello World

<?php include(buildPath('include_ui/foot.inc.php')); ?>
