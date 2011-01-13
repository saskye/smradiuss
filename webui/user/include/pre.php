<?php
# Web User UI PRE
# Copyright (C) 2007-2011, AllWorldIT
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.


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
