<?php
# Author: Nigel Kukard  <nkukard@lbsd.net>
# Date: 2007-06-21
# Desc: This file takes care of authentication for us and gets the soap object
# License: GPL


ob_start();

require_once("auth.php");
require_once("miscfuncs.php");

# Main authentication object
$auth = new Auth('Radius');

# First of all check if we in maintenance mode
if (file_exists("../maintmode")) {
	include("header.php");
?>
	<center>System unavailable due to maintenance, sorry for the inconvenience. Please try again in 5 minutes.</center>
<?php
	include("include/footer.php");
	exit;
}

# If not ... carry on
$auth->setLoginBoxUsername('Username');

# Check if we logged in
if (!$auth->loggedIn) {
	$username = isset($_POST['username']) ? $_POST['username'] : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';
	# If not, check credentials
	if ($auth->checkLogin($username,$password) != 0) {
		include("header.php");
		$auth->displayLogin();
		include("include/footer.php");
		exit;
	}
} else {
	# Log client out
	if (isset($_REQUEST['logout']) && $_REQUEST['logout'] == 1) {
		$auth->logout("You have been logged out.");
		require_once('HTTP.php');
		HTTP::Redirect('.');
		exit;
	}
}

# vim: ts=4
?>
