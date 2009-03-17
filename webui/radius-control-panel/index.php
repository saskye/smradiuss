<?php
# Main control panel page
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


# pre takes care of authentication and creates soap object we need
include("include/pre.php");
# Page header
include("include/header.php");

# NB: We will only end up here if we authenticated!


# Display details
function displayDetails() { 
	global $soap;


	$userDetails = $soap->getRadiusUserDetails();
	
	$isDialup = preg_match('/dialup/i',$userDetails->Service);
	if (!$isDialup) {
		$topups = $soap->getRadiusUserCurrentTopups();
		$usage = $soap->getRadiusUserCurrentUsage();
	}

?>
	<table class="blockcenter">
		<tr>
			<td colspan="2" class="section">Account Information</td>
		</tr>
		<tr>
			<td class="title">Username</td>
			<td class="value"><?php echo $userDetails->Username ?></td>
		</tr>
		<tr>
			<td class="title">Service</td>
			<td class="value"><?php echo $userDetails->Service ?></td>
		</tr>
<?php
		# Only display cap for DSL users
		if (!$isDialup) {
?>
			<tr>
				<td colspan="2" class="section">Usage Info &amp; Topups</td>
			</tr>
			<tr>
				<td class="title">Bandwidth Cap</td>
				<td class="value"><?php echo $userDetails->UsageCap ?>MB</td>
			</tr>
			<tr>
				<td class="title">Topups</td>
				<td class="value"><?php echo $topups; ?>MB</td>
			</tr>
			<tr>
				<td class="title">Used This Month</td>
				<td class="value"><?php echo ($usage >= 0) ? "${usage}MB" : "[data unavailable]"; ?></td>
			</tr>
			<tr>
				<td colspan="2" class="section">Notifications</td>
			</tr>
			<form method="post">
			<tr>
				<td class="title">Email Address</td>
				<td class="value">
					<input type="text" name="notifyMethodEmail" value="<?php echo $userDetails->NotifyMethodEmail ?>"></input>
					<input type="submit" name="notifyUpdate" value="update">
				</td>
			</tr>
			<tr>
				<td class="title">Cell Number</td>
				<td class="value">
					<input type="text" name="notifyMethodCell" value="<?php echo $userDetails->NotifyMethodCell ?>"></input>
					<input type="submit" name="notifyUpdate" value="update">
				</td>
			</tr>
			</form>
<?php
		}
?>
		<tr>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<a href="logs.php">Usage Logs</a>
<?php
				# Only display cap for DSL users
				if (!$isDialup && 0) {
?>
					| <a href="portlocks.php">Port Locking</a>
					| <a href="topups.php">Topups</a>
<?php
				}
?>
			</td>
		</tr>
	</table>

	<br><br>

	<font size="-1">
		Note:
		<li>Please contact your ISP if you have any problem using this interface.
	</font>
<?php
}

# If this is a post and we're updating then do it
if (isset($_POST['notifyUpdate']) && $_POST['notifyUpdate'] == "update") {
	$i->NotifyMethodEmail = $_POST['notifyMethodEmail'];
	$i->NotifyMethodCell = $_POST['notifyMethodCell'];
	$res = $soap->updateRadiusUser($i);

	if ($res == 0) {
		echo "<center>Notification data updated</center>";
	} else {
		echo "<center>Error updating notification data, please contact your ISP. (Err: $res)</center>";
	}
}


displayDetails();

# Footer
include("include/footer.php");
?>
