<?php
# Header
# Copyright (C) 2007-2009, AllWorldIT
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

include_once("includes/config.php");



# Print out HTML header
function printHeader($params = NULL)
{
	global $DB_POSTFIX_DSN;


    # Pull in params
    if (!is_null($params)) {
		if (isset($params['Tabs'])) {
			$tabs = $params['Tabs'];
		}
		if (isset($params['js.onLoad'])) {
			$jsOnLoad = $params['js.onLoad'];
		}
		if (isset($params['Title'])) {
			$title = $params['Title'];
		}
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

    <head>
	<title>SMRadiusd Web Administration</title>
	<link rel="stylesheet" type="text/css" href="stylesheet.css" />
	
	<script type="text/javascript" src="tooltips/BubbleTooltips.js"></script>
	<script type="text/javascript">
		window.onload=function(){enableTooltips(null,"img")};
	</script>
    </head>


	<body<?php if (!empty($jsOnLoad)) { echo " onLoad=\"".$jsOnLoad."\""; } ?>>


	<table id="maintable">
		<tr>
			<td id="header">SMRadiusd Web Administration</td>
		</tr>

		<tr>
			<td>
				<table>
					<tr>
						<td id="menu">
	    					<img style="margin-top:-1px; margin-left:-1px;" src="images/top2.jpg" alt="" />
	    					<p><a href=".">Home</a></p>

							<p>Control Panel</p>
							<ul>
								<li><a href="user-main.php">User List</a></li>
								<li><a href="group-main.php">Groups</a></li>
							</ul>

							<p>WiSP</p>
							<ul>
								<li><a href="wisp-user-list.php">User List</a></li>
								<li><a href="wisp-user-add.php">Add User</a></li>
								<li><a href="wisp-multiuser-add.php">Add Many Users</a></li>
							</ul>
								

	    			<!--		<img style="margin-left:-1px; margin-bottom: -6px" src="images/specs_bottom.jpg" alt="" />-->
						</td>

						<td class="content">
							<table class="content">
<?php
								# Check if we must display tabs or not
								if (!empty($tabs)) {
?>
									<tr><td id="topmenu"><ul>
<?php
										foreach ($tabs as $key => $value) {
?>											<li>
												<a href="<?php echo $value ?>" 
													title="<?php echo $key ?>">
												<span><?php echo $key ?></span></a>
											</li>
<?php
										}
?>
								    	</ul></td></tr>
<?php
								}	
?>
								<tr>
									<td>
<?php
}


# vim: ts=4
?>
