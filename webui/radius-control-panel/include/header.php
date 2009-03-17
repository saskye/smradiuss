<?php
# Top part of radius control panel
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
<html>
	<title>User Control Panel</title> 
	<link rel="stylesheet" href="styles.css" type="text/css">

	<body>
		<div class="pagetitle">User Control Panel</div>	
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
		<br>
