<?php
session_start();
include('model/model.php');

$manager = new AdminController();
$manager->loadSession();
$manager->logout();

include(buildServerPath('include_ui/head.inc.php'));

?>

<div class="container">
    <h2>You have been logged out.</h2><br />
    <h4><a href="<?php echo buildUrl('login.php'); ?>">Click Here to Log Back in</a></h4>
</div>

<?php include(buildServerPath('include_ui/foot.inc.php')); ?>