<?php
# Radius user info
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
require_once("soapfuncs.php");



function display($userID)
{
	global $soap;


	$userInfo = $soap->getRadiusUser($userID);
	if (!is_object($userInfo)) {
		echo "getRadiusUser: ".strSoapError($userInfo);
		return;
	}

	$isDialup = preg_match('/dialup/i',$userInfo->Service);

	# DSL USER ONLY INFO
	if (!$isDialup) {
		$topups = $soap->getRadiusUserCurrentTopups($userID);
		$currentUsage = $soap->getRadiusUserCurrentUsage($userID);
?>
		<div class="sectiontitle">User Information</div>
		<p />

		<table class="entrytable">
			<tr>
				<td class="title text-right">Usage cap</td>
				<td class="value text-right" style="width: 50px;"><?php echo $userInfo->UsageCap ?></td>
			</tr>
			<tr>
				<td class="title text-right">+ Current topups</td>
				<td class="value text-right"><?php echo $topups ?></td>
			</tr>
			<tr>
				<td class="title text-right">Total usage allowed</td>
				<td class="value text-right" style="border-top: 2px solid black;"><?php echo $userInfo->UsageCap + $topups ?></td>
			</tr>
			<tr>
				<td class="title text-right">Current usage</td>
				<td class="value text-right" style="border-top: 2px solid black;"><?php echo $currentUsage ?></td>
			</tr>
		</table>
		<p />
<?php
	}
}





# Check if we have an action
if (!isset($_REQUEST['frmaction'])) {
	# FIXME - redirect to main page

} elseif ($_REQUEST['frmaction'] == "info_main") { 
	display($_REQUEST['user_id']);
}


# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
