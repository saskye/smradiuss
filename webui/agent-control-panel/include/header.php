<?php
# Header of page
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



# Grab version
require_once("include/version.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>SOAP Backend Interface</title> 
		<link rel="stylesheet" href="../static/styles.css" type="text/css" />
		<link rel="stylesheet" href="../static/menu.css" type="text/css" />
	</head>

	<body>
		<div class="pagetitle">Agent Control Panel</div>	
<?php
	if ($auth->loggedIn) {
?>
		<div class="smallinfo">Logged in as <?php echo $auth->username ?> (<a href="./?logout=1">Logout</a>).</div>
<?php
	} else {
?>
		<div class="smallinfo">v<?php echo $VERSION ?></div>
<?php
	}
?>
