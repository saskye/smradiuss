<?php
# Mailbox stuff
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

# Soap functions
require_once("php/soapfuncs.php");

?>
<a href=".">Home</a><br><br>
<a href="mailTransports.php">Back to mail transport search</a><br><br>
<a href="mailTransports.php?search=1">Back to mail transports</a><br><br>
<?php


# Actual form action to add mailbox
function actionAdd() {
	global $soap;	
	global $transportID;

	
	$mailboxAliasInfo = NULL;

	$mailboxAliasInfo["Address"] = $_POST["address"];

	if ($_POST["goto"] != "") {
		$mailboxAliasInfo["Goto"] = $_POST["goto"];
	} else {
		echo "Forward to must be specified!<br>";
		return;
	}

	# Check optional data
	if ($_POST["agentDisabled"] != "") {
		$mailboxAliasInfo["AgentDisabled"] = $_POST["agentDisabled"];
	}		

	if ($_POST["agentRef"] != "") {
		$mailboxAliasInfo["AgentRef"] = $_POST["agentRef"];
	}		

	# Create mailbox and check for error
	$res = $soap->createMailboxAlias($transportID,$mailboxAliasInfo);
	if ($res > 0) {
		echo "Added mailbox alias\n";
	} else {
		echo "Error creating mailbox alias: ".strSoapError($res);
	}

}



# Actual form action to update mailbox
function actionUpdate() {
	global $soap;
	global $mailboxAliasID;


	# Create update hash
	$update = NULL;

	if ($_POST["goto"] != "") {
		$update["Goto"] = $_POST["goto"];
	}		

	if ($_POST["agentDisabled"] != "") {
		$update["AgentDisabled"] = $_POST["agentDisabled"];
	}		

	if ($_POST["agentRef"] != "") {
		$update["AgentRef"] = $_POST["agentRef"];
	}		

	# If there are still updates to be done, do them
	if ($update != NULL) {
		$update["ID"] = $mailboxAliasID;
		
		$res = $soap->updateMailboxAlias($update);
		if ($res == 0) {
			echo "Updated mailbox alias\n";
		} else {
			echo "Error updating mailbox alias: ".strSoapError($res);
		}
	# Or report no updates to be made
	} else {
		echo "No updates to be made!\n";
	}
}



# Actual form action to remove a mailbox
function actionRemove() {
	global $soap;
	global $mailboxAliasID;


	$res = $soap->removeMailboxAlias($mailboxAliasID);
	if ($res == 0) {
		echo "Removed mailbox alias\n";
	} else {
		echo "Error removing mailbox alias: ".strSoapError($res);
	}

}


# Display edit screen
function screenEdit() {
	global $soap;
	global $transportID;
	global $mailboxAliasID;


	$transportInfo = $soap->getMailTransportInfo($transportID);
	if (!is_object($transportInfo)) {
		echo "getMailTransportInfo: ".strSoapError($transportInfo);
		return;
	}
		
	$aliasInfo = $soap->getMailboxAliasInfo($mailboxAliasID);
	if (!is_object($aliasInfo)) {
		echo "getMailboxAliasInfo: ".strSoapError($aliasInfo);
		return;
	}

?>
	<form action="mailboxAliases.php?transportID=<?php echo $transportID; ?>&mailboxAliasID=<?php echo $mailboxAliasID; ?>" method="POST">
	<table border="1">
		<tr>
			<td colspan="3" align="center">
				Alias: <?php printf("%s@%s",$aliasInfo->Address,$transportInfo->DomainName); ?>
			</td>
		</tr>
		<tr>
			<td>Attribute</td>
			<td>Value</td>
			<td>New Value</td>
		</tr>
		<tr>
			<td>Foward to</td>
			<td><?php echo $aliasInfo->Goto; ?></td>
			<td><input type="text" name="goto"></td>
		</tr>

		<tr>
			<td>AgentRef</td>
			<td><?php echo $aliasInfo->AgentRef; ?></td>
			<td><input type="text" name="agentRef"></td>
		</tr>
		<tr>
			<td>Disabled</td>
			<td><?php echo $aliasInfo->AgentDisabled ? "yes" : "no"; ?></td>
			<td>
				<select name="agentDisabled">
					<option value="0" <?php if (!$aliasInfo->AgentDisabled) { echo "selected"; } ?>>no</option>
					<option value="1" <?php if ($aliasInfo->AgentDisabled) { echo "selected"; } ?>>yes</option>
				</select>
			</td>
		</tr>
	</table>

	<input type="hidden" name="action" value="update">
	<input type="submit" value="Update">
</form>		
<?php
}



# Remove screen
function screenRemove() {
	global $soap;
	global $transportID;
	global $mailboxAliasID;


	$transportInfo = $soap->getMailTransportInfo($transportID);
	if (!is_object($transportInfo)) {
		echo "getMailTransportInfo: ".strSoapError($transportInfo);
		return;
	}
		
	$mailboxAliasInfo = $soap->getMailboxAliasInfo($mailboxAliasID);
	if (!is_object($mailboxAliasInfo)) {
		echo "getMailboxAliasInfo: ".strSoapError($mailboxAliasInfo);
		return;
	}
?>
	<form action="mailboxAliases.php?transportID=<?php echo $transportID ?>&mailboxAliasID=<?php echo $mailboxAliasID ?>" method="POST">
	<input type="hidden" name="action" value="remove">
	Are you very sure you wish to remove alias <?php printf("%s@%s",$mailboxAliasInfo->Address,$transportInfo->DomainName) ?>?<br>
	<input type="submit" value="Yes">
	<br>	
<?php
}


# Add screen
function screenAdd() {
	global $soap;
	global $transportID;
	global $mailboxAliasesID;


	$transportInfo = $soap->getMailTransportInfo($transportID);
	if (!is_object($transportInfo)) {
		echo "getMailTransportInfo: ".strSoapError($transportInfo);
		return;
	}
		
?>
	<form action="mailboxAliases.php?transportID=<?php echo $transportID ?>&action=add" method="POST">
	<input type="hidden" name="action" value="add">
	<table border="1">
		<tr>
			<td>Attribute</td>
			<td>Value</td>
		</tr>
		<tr>
			<td>Address (@<?php echo $transportInfo->DomainName ?>)</td>
			<td><input type="text" name="address"></td>
		</tr>
		<tr>
			<td>Forward to</td>
			<td><input type="text" name="goto"></td>
		</tr>
		<tr>
			<td>AgentRef</td>
			<td><input type="text" name="agentRef"></td>
		</tr>
		<tr>
			<td>Disabled</td>
			<td>
				<select name="agentDisabled">
					<option value="0" selected>no</option>
					<option value="1">yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="center" colspan="2">
				<input type="submit" value="Add">
			</td>
		</tr>
	</table>
	</form>
	<br>
<?php

}



# List mailbox aliases
function mailboxAliasList($searchOptions) {
	global $soap;
	global $transportID;


	$transportInfo = $soap->getMailTransportInfo($transportID);
	if (!is_object($transportInfo)) {
		echo "getMailTransportInfo: ".strSoapError($transportInfo);
		return;
	}

?>

	<table border="1">
		<tr>
			<td colspan="6" align="center">Aliases for <?php echo $transportInfo->DomainName; ?></td>
		</tr>
		<tr>
			<td align="center" rowspan="2">Alias</td>
			<td align="center" rowspan="2">Foward to</td>
			<td align="center" rowspan="2">AgentRef</td>
			<td align="center" colspan="2">Disable</td>
			<td></td>
		</tr>
		<tr>
			<td align="center">Agent</td>
			<td align="center">Delivery</td>
			<td></td>
		</tr>
<?php
	$mailboxAliases = $soap->getMailboxAliases($transportID,$searchOptions);
	if (is_array($mailboxAliases)) {
		if (count($mailboxAliases)) {
?>
			<tr>
				<td colspan="6" align="center">
					<a href="mailboxAliases.php?transportID=<?php echo $transportID ?>&screen=add">Add Mailbox Alias</a>
				</td>
			</tr>
<?php
		}
		foreach ($mailboxAliases as $item) {
?>
			<tr>
				<td><?php echo $item->Address != "" ? $item->Address : "[catchall]"; ?></td>	
				<td><?php echo $item->Goto; ?></td>	
				<td><?php echo $item->AgentRef; ?></td>	
				<td align="center"><?php echo $item->AgentDisabled ? "yes" : "no"; ?></td>	
				<td align="center"><?php echo $item->DisableDelivery ? "yes" : "no"; ?></td>	
				<td>
					<a href="mailboxAliases.php?transportID=<?php echo $transportID; ?>&mailboxAliasID=<?php echo $item->ID; ?>&screen=edit">Edit</a>
					 | <a href="mailboxAliases.php?transportID=<?php echo $transportID; ?>&mailboxAliasID=<?php echo $item->ID; ?>&screen=remove">Remove</a>
				</td>
			</tr>
<?php		
		}
?>
		<tr>
			<td colspan="6" align="center">
				<a href="mailboxAliases.php?transportID=<?php echo $transportID ?>&screen=add">Add Mailbox Alias</a>
			</td>
		</tr>
<?php
			
	} else {
?>
		<tr>
			<td colspan="6">
<?php
				echo "getMailboxAliases: ".strSoapError($mailboxAliases);
?>
			</td>
		</tr>
<?php
	}
?>				
	</table>

<?php
}




# Display search box
function searchBox()
{
	global $transportID;

?>
<form action="mailboxAliases.php" method="GET">
<input type="hidden" name="transportID" value="<?php echo $transportID ?>">
<input type="hidden" name="search" value="1">
<table border="1">
	<tr>
		<td colspan="3" align="center">Search Mailbox Aliases</td>
	</tr>
	<tr>
		<td colspan="2"></td>
		<td align="center">Order by</td>
	</tr>
	<tr>
		<td>Address</td>
		<td>
			<input type="text" name="searchAddress" value="<?php
					echo $_SESSION['mailboxAlias_searchAddress'] 
			?>">
		</td>
		<td align="center">
			<input type="radio" name="searchOrderBy" value="Address" <?php
				if ($_SESSION['mailboxAlias_searchOrderBy'] == "" 
						|| $_SESSION['mailboxAlias_searchOrderBy'] == "Address") {
					echo "checked";
				}
			?>>
		</td>
	</tr>
	<tr>
		<td>Agent Ref</td>
		<td>
			<input type="text" name="searchAgentRef" value="<?php
					 echo $_SESSION['mailboxAlias_searchAgentRef']
			?>">
		</td>
		<td align="center"><input type="radio" name="searchOrderBy" value="AgentRef" <?php
				if ($_SESSION['mailboxAlias_searchOrderBy'] == "AgentRef") {
					echo "checked";
				}
			?>>
		</td>
	</tr>
	<tr>	
		<td colspan="3" align="center"><input type="submit"></td>
	</tr>
</table>
</form>

Note On Searching:
<li>Wildcards can be specified with *'s. For example: *.com  
<li>Blank search criteria matches everything
<?php
}




# Check if we have a transport
if ($_REQUEST['transportID'] > 0) {
	$transportID = $_REQUEST['transportID'];


	# Check if we have a mailbox ID, pull in the relevant stuff
	if ($_REQUEST['mailboxAliasID'] > 0) {
		$mailboxAliasID = $_REQUEST['mailboxAliasID'];


		printf("<a href=\"mailboxAliases.php?transportID=%s\">Back to mailbox alias search</a><br><br>",$transportID);
		printf("<a href=\"mailboxAliases.php?transportID=%s&search=1\">Back to mailbox aliases</a><br><br>",$transportID);

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
		}


	} else {
		# Check if we have a special action to perform
		if ($_REQUEST["screen"] == "add") {
			printf("<a href=\"mailboxAliases.php?transportID=%s\">Back to mailbox alias search</a><br><br>",$transportID);
			printf("<a href=\"mailboxAliases.php?transportID=%s&search=1\">Back to mailbox aliases</a><br><br>",$transportID);
			screenAdd();
		} elseif ($_POST["action"] == "add") {
			printf("<a href=\"mailboxAliases.php?transportID=%s\">Back to mailbox alias search</a><br><br>",$transportID);
			printf("<a href=\"mailboxAliases.php?transportID=%s&search=1\">Back to mailbox aliases</a><br><br>",$transportID);
			actionAdd();
		# We came from search screen
		} elseif ($_REQUEST['search'] == 1) {
			printf("<a href=\"mailboxAliases.php?transportID=%s\">Back to mailbox alias search</a><br><br>",$transportID);
			# Process search options
			if (isset($_REQUEST['searchAddress'])) {
				$_SESSION['mailboxAlias_searchAddress'] = $_REQUEST['searchAddress'];
			}
			if (isset($_REQUEST['searchAgentRef'])) {
				$_SESSION['mailboxAlias_searchAgentRef'] = $_REQUEST['searchAgentRef'];
			}
			if (isset($_REQUEST['searchOrderBy'])) {
				$_SESSION['mailboxAlias_searchOrderBy'] = $_REQUEST['searchOrderBy'];
			}

			# Setup search
			$searchOptions->searchAddress = $_SESSION['mailboxAlias_searchAddress'];
			$searchOptions->searchAgentRef = $_SESSION['mailboxAlias_searchAgentRef'];
			$searchOptions->searchOrderBy = $_SESSION['mailboxAlias_searchOrderBy'];

			mailboxAliasList($searchOptions);
		# We need to search
		} else {
			searchBox();
		}
	}
} else {
	echo "You cannot call this module directly.";
}


# Footer
include("include/footer.php");
?>
