<?php
# Radius user stuff
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

# Soap functions
require_once("soapfuncs.php");

?>
<a href=".">Home</a><br><br>
<?php










# Quicklink functions...
if (!empty($_REQUEST['quicklink']) && $_REQUEST['quicklink'] == "logs") {
	$username = $_REQUEST['username'];
	$userInfo = $soap->getRadiusUserByUsername($username);

	if (!$userInfo) {
?>
		User info query returned no result.<br><br>
<?php
		searchBox();
		exit;
	}
	
	$_SESSION['radiusUser_searchUsername'] = $userInfo->Username;
	$userID = $userInfo->ID;

	searchLogsBox();

# Check if we have a user
} elseif ($_REQUEST['userID'] > 0) {
	$userID = $_REQUEST['userID'];
	$userInfo = $soap->getRadiusUser($userID);

?>
	<a href="radiusUsers.php">Back to radius user search</a><br><br>
<?php
	# Check if we have a special action to perform
	if ($_POST["action"] == "update") {
		actionUpdate();
	# Actual remove action
	} elseif ($_POST["action"] == "remove")  {
		actionRemove();
	
	# Edit screen
	} elseif ($_REQUEST["screen"] == "edit")  {
		screenEdit();
	# Remove screen
	} elseif ($_REQUEST["screen"] == "remove")  {
		screenRemove();


	# Logs screen
	} elseif ($_REQUEST["screen"] == "logs")  {
		# If we have searchLogs set, means we come from the searchLogs screen
		if ($_REQUEST['searchLogs'] == 1) {
		} else {

			searchLogsBox();
		}
	
	# Add port lock
	} elseif ($_REQUEST["screen"] == "addTopup")  {
		screenAddTopup();

	} elseif ($_POST["action"] == "addTopup")  {
		actionAddTopup();


	# Topups screen
	} elseif ($_REQUEST["screen"] == "topups")  {
		# If we have searchTopups set, means we come from the searchTopups screen
		if ($_REQUEST['searchTopups'] == 1) {

			displayTopups($searchOptions);
		} else {

			searchTopupsBox();
		}
	
	# Add port lock
	} elseif ($_POST["action"] == "addLock")  {
		actionAddPortLock();
	# Remove port lock
	} elseif ($_REQUEST["action"] == "removeLock")  {
		actionRemovePortLock();
	
	# Port locks screen
	} elseif ($_REQUEST["screen"] == "portlocks")  {
		displayPortLocks();
	}





# We came from our search box
} elseif ($_REQUEST['search'] == 1) {
	# Process search options
	if (isset($_REQUEST['searchUsername'])) {
		$_SESSION['radiusUser_searchUsername'] = $_REQUEST['searchUsername'];
	}
	if (isset($_REQUEST['searchAgentRef'])) {
		$_SESSION['radiusUser_searchAgentRef'] = $_REQUEST['searchAgentRef'];
	}
	if (isset($_REQUEST['searchOrderBy'])) {
		$_SESSION['radiusUser_searchOrderBy'] = $_REQUEST['searchOrderBy'];
	}

	# Setup search
	$searchOptions->searchUsername = $_SESSION['radiusUser_searchUsername'];
	$searchOptions->searchAgentRef = $_SESSION['radiusUser_searchAgentRef'];
	$searchOptions->searchOrderBy = $_SESSION['radiusUser_searchOrderBy'];



	userList($searchOptions);

# Add user screen
} elseif ($_REQUEST["screen"] == "add") {
	echo("<a href=\"radiusUsers.php\">Back to radius user search</a><br><br>");

	screenAdd();
	
# Add the user
} elseif ($_POST["action"] == "add") {
	actionAdd();

# Anything else
} else {
	searchBox();
}


# Menu footer
include("../shared-php/menu-footer.php");

# Footer
include("include/footer.php");
?>
