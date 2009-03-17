<?php
# Authentication class
#
# Copyright (c) 2005-2008, AllWorldIT
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


$soap_location = "http://localhost:1080";


# Authentication class
class Auth {
	var $loggedIn = false;
	var $username = "";
	var $key = "";
	var $section = "";

	var $loginBoxUsername = "Username";
	var $loginBoxMsg = "";


	# Clean session
	function _unsetSession() {
		$this->loggedIn = $_SESSION['loggedIn'] = false;
		$this->username = $_SESSION['username'] = "";
		$this->key = $_SESSION['key'] = "";
		$this->section = $_SESSION['section'] = "";
	}


	# Populate session
	function _setSession($username,$key) {
		$this->loggedIn = $_SESSION['loggedIn'] = true;
		$this->username = $_SESSION['username'] = $username;
		$this->key = $_SESSION['key'] = $key;
		$this->section = $_SESSION['section'] = $this->section;
	}


	# Load object data from session
	function _loadSession() {
		$this->loggedIn = $_SESSION['loggedIn'];
		$this->username = $_SESSION['username'];
		$this->key = $_SESSION['key'];
		$this->section = $_SESSION['section'];
	} 


	# Create object
	# Args: <section to authenticate to>
	function Auth($section) {
		# Make sure sessions are active
		session_start();

		# Check if we logged in, if we are pull in data
		if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] == true) {
			$this->_loadSession();
			if ($this->section != $section) {
				$this->logout("Login section mismatch!");
			}
		} else {
			# Set section
			$this->section = $section;
		}
	}


	# Set login box username
	function setLoginBoxUsername($msg) {
		$this->loginBoxUsername = $msg;
	}


	# Login
	function _login($username,$password) {
		global $soap_location;

		$this->_setSession("joesoap","mykey");

		return 0;

	}


	# Logout
	function logout($msg) {
		if ($msg != "") {
			$_SESSION['logoutMsg'] = $msg;
		}
		$this->_unsetSession();
	}


	# Display login screen
	function displayLogin() {
?>
		<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<?php
			displayError($this->loginBoxMsg);
?>
			<table class="block-center">
				<tr>
					<td><?php echo $this->loginBoxUsername ?></td>
					<td><input type="text" name="username" /></td>
				</tr>
				<tr>
					<td>Password</td>
					<td><input type="password" name="password" /></td>
				</tr>
			</table>
			<div class="text-center">
				<input type="submit" value="Login" />
			</div>
		</form>
		<p />
<?php
	}


	# Function to check login details
	function checkLogin($username,$password) {
		$res = -1;

		# Set res to 0 if we logged in
		if ($username != "" && $password != "" && !$this->loggedIn) {
			switch ($this->_login($username,$password)) {
				case 0:
					$res = 0;
					break;
				case -1:
				case -3:
					$this->loginBoxMsg = $this->loginBoxUsername. " or Password invalid.";
					break;
				case -2:
					$this->loginBoxMsg = "Cannot connect to SOAP server, please contact your ISP.";
					break;
				case -4:
				default:
					$this->loginBoxMsg = "Unknown error contacting SOAP server, please contact your ISP.";
					break;
			}
		} else {
			# Check if we have a logout message
			if (isset($_SESSION['logoutMsg'])) {
				$this->loginBoxMsg = $_SESSION['logoutMsg'];
				unset($_SESSION['logoutMsg']);
			} else {
				$this->loginBoxMsg = "Unauthorized Access Prohibited";
			}
		}

		return $res;
	}

}


?>
