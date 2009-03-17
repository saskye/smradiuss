<?php
# Radius user topups
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
# Menu header
include("../shared-php/menu-header.php");
# Functions we need
require_once("../shared-php/miscfuncs.php");



# Display radius user topups
function displayTopups($userID,$searchOptions) {
	global $soap;


	$userInfo = $soap->getRadiusUser($userID);
	if (!is_object($userInfo)) {
		displayError("getRadiusUser: ".strSoapError($userInfo));
		return;
	}

	$userTopups = $soap->getRadiusUserTopups($userID,$searchOptions);
	if (!is_array($userTopups)) {
		displayError("getRadiusUserTopups: ".strSoapError($userTopups));
		return;
	}
?>
	
	<div class="sectiontitle">Topup Search Results</div>
	<p />

	<table class="resulttable">
		<tr>
			<td class="title">Bandwidth</td>
			<td class="title">Timestamp</td>
			<td class="title">Valid From</td>
			<td class="title">Valid To</td>
			<td class="title">Agent Ref</td>
		</tr>
<?php
		foreach ($userTopups as $entry) {
?>
			<tr>
				<td class="text-right"><?php echo $entry->Bandwidth; ?></td>
				<td><?php echo $entry->Timestamp; ?></td>
				<td><?php echo $entry->ValidFrom; ?></td>
				<td><?php echo $entry->ValidTo; ?></td>
				<td><?php echo $entry->AgentRef; ?></td>
			</tr>
<?php
		}
?>
	</table>
<?php
}






# Function to display topup search box
function displaySearchBox($userID) {
?>
	<div class="sectiontitle">Topup Search</div>
	<p />

	<form action="radius-user-topup-main.php" method="post">
		<div>
			<input type="hidden" name="user_id" value="<?php echo $userID ?>" />
			<input type="hidden" name="frmaction" value="topups_final" />
		</div>
		<table class="entrytable">
			<tr>
				<td class="title">From</td>
				<td class="entry">
					<input type="text" name="searchTopupsFrom" value="<?php
							if (isset($_SESSION['radiusUserTopups_searchFrom'])) {
								echo $_SESSION['radiusUserTopups_searchFrom'];
							}
					?>" />
				</td>
			</tr>
			<tr>
				<td class="title">To</td>
				<td class="entry">
					<input type="text" name="searchTopupsTo" value="<?php
							if (isset($_SESSION['radiusUserTopups_searchTo'])) {
								echo $_SESSION['radiusUserTopups_searchTo'];
							}
					?>" />
				</td>
			</tr>
			<tr>
				<td class="title">Order By</td>
				<td class="entry" colspan="2">
					<input type="radio" name="searchTopupsOrderBy" value="date" checked="checked" /> Date
				</td>
			</tr>
		</table>
		<div class="text-center">
			<input type="submit" />
		</div>
	</form>

Note On Searching:
<ul>
	<li>Dates are in the format of YYYY-MM-DD</li>
	<li>Blank search criteria matches last 50 topups</li>
</ul>
<?php
}





# Check if we have an action
if (!isset($_REQUEST['frmaction'])) {
	# FIXME : redirect

} elseif ($_REQUEST['frmaction'] == "topups_main") {
	displaySearchBox($_REQUEST['user_id']);

} elseif ($_REQUEST['frmaction'] == "topups_final") {
	# Process search options
	if (isset($_REQUEST['searchTopupsFrom'])) {
		$_SESSION['radiusUserTopups_searchFrom'] = $_REQUEST['searchTopupsFrom'];
	}
	if (isset($_REQUEST['searchTopupsTo'])) {
		$_SESSION['radiusUserTopups_searchTo'] = $_REQUEST['searchTopupsTo'];
	}
	if (isset($_REQUEST['searchTopupsOrderBy'])) {
		$_SESSION['radiusUserTopups_searchOrderBy'] = $_REQUEST['searchTopupsOrderBy'];
	}

	# Setup search
	$searchOptions->searchFrom = $_SESSION['radiusUserTopups_searchFrom'];
	$searchOptions->searchTo = $_SESSION['radiusUserTopups_searchTo'];
	$searchOptions->searchOrderBy = $_SESSION['radiusUserTopups_searchOrderBy'];
	displayTopups($_REQUEST['user_id'],$searchOptions);
}


# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
