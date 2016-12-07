<?php
include( 'include/backend.php' );
secureSessionStart();

if (isset($_REQUEST['submit']) && $_REQUEST['submit'] == "Login") {
	$error = "";
	if (AuthController::get()->login($_REQUEST['email'], $_REQUEST['password'], $error)) {
		// login was successful, go ahead and redirect.
        $redirectUrl = $_SESSION['redirectAfterLogin'];
		unset($_SESSION['redirectAfterLogin']);
		redirect($redirectUrl);
	} else {
        echo "Credentials provided are invalid: ";
		if (isset($error)) {
			echo $error;
		}
    }
}

?>

<div class="container">

    <form class="form-signin" action="" method="post">
        <h2 class="form-signin-heading">Please sign in</h2>

        <label for="email" class="sr-only">Email</label>
        <input type="text" name="email" class="form-control" placeholder="email" required autofocus />
        <label for="password" class="sr-only">Password (User or Team PW)</label>
        <input type="password" name="password" class="form-control" placeholder="Password" required />

        <input class="btn btn-lg btn-primary btn-block" type="submit" name="submit" value="Login">

    </form>

</div>

<?php include(buildPath('includes/foot.inc.php')); ?>
