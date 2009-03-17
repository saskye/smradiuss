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


# Javascript stuff
include("js.getRandomPass");

?>
<a href=".">Home</a><br><br>
<a href="mailTransports.php">Back to mail transport search</a><br><br>
<a href="mailTransports.php?search=1">Back to mail transports</a><br><br>
<?php



# Actual form action to add mailbox
function actionAdd() {
	global $soap;	
	global $transportID;

	
	$mailboxInfo = NULL;

	# Verify data
	if ($_POST["address"] != "") {
		$mailboxInfo["Address"] = $_POST["address"];
	} else {
		echo "Address must be specified!<br>";
		return;
	}

	if ($_POST["password"] != "") {
		$mailboxInfo["Password"] = $_POST["password"];
	} else {
		echo "Password must be specified!<br>";
		return;
	}

	# Check optional data
	if ($_POST["quota"] != "") {
		$mailboxInfo["Quota"] = $_POST["quota"];
	} else {
		echo "Quota must be defined!, you probably want to choose 5 for a 5Mb mailbox.<br>";
		return;
	}

	if ($_POST["policyID"] != "") {
		$mailboxInfo["PolicyID"] = $_POST["policyID"];
	}		

	if ($_POST["name"] != "") {
		$mailboxInfo["Name"] = $_POST["name"];
	}		

	if ($_POST["agentRef"] != "") {
		$mailboxInfo["AgentRef"] = $_POST["agentRef"];
	}		

	if ($_POST["premiumSMTP"] != "") {
		$mailboxInfo["PremiumSMTP"] = $_POST["premiumSMTP"];
	}		

	if ($_POST["premiumPolicy"] != "") {
		$mailboxInfo["PremiumPolicy"] = $_POST["premiumPolicy"];
	}		

	if ($_POST["agentDisabled"] != "") {
		$mailboxInfo["AgentDisabled"] = $_POST["agentDisabled"];
	}		

	# Create mailbox and check for error
	$res = $soap->createMailbox($transportID,$mailboxInfo);
	if ($res > 0) {
		echo "Added mailbox<br>\n";
	} else {
		echo "Error creating mailbox: ".strSoapError($res);
	}

}




# Actual form action to update mailbox
function actionUpdate() {
	global $soap;
	global $mailboxID;


	# Create update hash
	$update = NULL;

	if ($_POST["password"] != "") {
		$update["Password"] = $_POST["password"];
	}		

	if ($_POST["quota"] != "") {
		$update["Quota"] = $_POST["quota"];
	}		

	if ($_POST["policyID"] != "nochange") {
		$update["PolicyID"] = $_POST["policyID"];
	}		

	if ($_POST["name"] != "") {
		$update["Name"] = $_POST["name"];
	}		

	if ($_POST["agentRef"] != "") {
		$update["AgentRef"] = $_POST["agentRef"];
	}		

	if ($_POST["premiumSMTP"] != "") {
		$update["PremiumSMTP"] = $_POST["premiumSMTP"];
	}		

	if ($_POST["premiumPolicy"] != "") {
		$update["PremiumPolicy"] = $_POST["premiumPolicy"];
	}		

	if ($_POST["agentDisabled"] != "") {
		$update["AgentDisabled"] = $_POST["agentDisabled"];
	}		

	# If there are still updates to be done, do them
	if ($update != NULL) {
		$update["ID"] = $mailboxID;
		
		$res = $soap->updateMailbox($update);
		if ($res == 0) {
			echo "Updated mailbox<br>\n";
		} else {
			echo "Error updating mailbox: ".strSoapError($res);
		}
	# Or report no updates to be made
	} else {
		echo "No updates to be made!\n";
	}
}




# Actual form action to remove a mailbox
function actionRemove() {
	global $soap;
	global $mailboxID;


	$res = $soap->removeMailbox($mailboxID);
	if ($res == 0) {
		echo "Removed mailbox\n";
	} else {
		echo "Error removing mailbox: ".strSoapError($res);
	}

}




# Display edit screen
function screenEdit() {
	global $soap;
	global $transportID;
	global $mailboxID;


	$transportInfo = $soap->getMailTransportInfo($transportID);
	if (!is_object($transportInfo)) {
		echo "getMailTransportInfo: ".strSoapError($transportInfo);
		return;
	}
		
	$mailboxInfo = $soap->getMailboxInfo($mailboxID);
	if (!is_object($mailboxInfo)) {
		echo "getMailboxInfo: ".strSoapError($mailboxInfo);
		return;
	}

	$mailPolicies = $soap->getMailPolicies();
	if (!is_array($mailPolicies)) {
		echo "getMailPolicies: ".strSoapError($mailPolicies);
		return;
	}

?>
	<form action="mailboxes.php?transportID=<?php echo $transportID; ?>&mailboxID=<?php echo $mailboxID; ?>" method="POST">
	<input type="hidden" name="action" value="update">
	<table border="1">
		<tr>
			<td colspan="3" align="center">
				Mailbox: <?php printf("%s@%s",$mailboxInfo->Address,$transportInfo->DomainName); ?>
			</td>
		</tr>
		<tr>
			<td>Attribute</td>
			<td>Value</td>
			<td>New Value</td>
		</tr>
		<tr>
			<td>Quota (in Mbyte)</td>
			<td><?php echo $mailboxInfo->Quota; ?></td>
			<td><input type="text" name="quota"></td>
		</tr>
		<tr>
			<td>Password</td>
			<td>*encrypted*</td>
			<td>
				<input type="text" name="password">
				<input type="button" value="generate" onClick="this.form.password.value=getRandomPass(8)">
			</td>
		</tr>
<?php

		$policyName = "";
		foreach ($mailPolicies as $policy) {
			if ($mailboxInfo->PolicyID == $policy->ID) {
				$policyName = $policy->PolicyName;
			}
		}
?>
		<tr>
			<td>Policy</td>
			<td><?php echo $policyName ? $policyName : "default"; ?></td>
			<td>
				<select name="policyID">
					<option value="nochange"></option>
					<option value="">Default</option>
<?php
					foreach ($mailPolicies as $policy) {
						printf("<option value=\"%s\">%s</option>",$policy->ID,$policy->PolicyName);
					}
?>
				</select>
			</td>
		</tr>

		<tr>
			<td>Name</td>
			<td><?php echo $mailboxInfo->Name; ?></td>
			<td><input type="text" name="name"></td>
		</tr>

		<tr>
			<td>AgentRef</td>
			<td><?php echo $mailboxInfo->AgentRef; ?></td>
			<td><input type="text" name="agentRef"></td>
		</tr>

		<tr>
			<td>Premium SMTP</td>
			<td><?php echo $mailboxInfo->PremiumSMTP ? "yes" : "no"; ?></td>
			<td>
				<select name="premiumSMTP">
					<option value="0" <?php if (!$mailboxInfo->PremiumSMTP) { echo "selected"; } ?>>no</option>
					<option value="1" <?php if ($mailboxInfo->PremiumSMTP) { echo "selected"; } ?>>yes</option>
				</select>
			</td>
		</tr>

		<tr>
			<td>Premium Policy</td>
			<td><?php echo $mailboxInfo->PremiumPolicy ? "yes" : "no"; ?></td>
			<td>
				<select name="premiumPolicy">
					<option value="0" <?php if (!$mailboxInfo->PremiumPolicy) { echo "selected"; } ?>>no</option>
					<option value="1" <?php if ($mailboxInfo->PremiumPolicy) { echo "selected"; } ?>>yes</option>
				</select>
			</td>
		</tr>

		<tr>
			<td>Disabled</td>
			<td><?php echo $mailboxInfo->AgentDisabled ? "yes" : "no"; ?></td>
			<td>
				<select name="agentDisabled">
					<option value="0" <?php if (!$mailboxInfo->AgentDisabled) { echo "selected"; } ?>>no</option>
					<option value="1" <?php if ($mailboxInfo->AgentDisabled) { echo "selected"; } ?>>yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="center" colspan="3">
				<input type="submit" value="Update">
			</td>
		</tr>
	</table>

	</form>
	<br>

	<font size="-1">
		Note:
		<li>To enable clients to log in an change their anti-virus/spam settings, set [Policy] to "Client - Premium Service" and [Premium Policy] to "yes"
	</font>
<?php
}




# Remove screen
function screenRemove() {
	global $soap;
	global $transportID;
	global $mailboxID;


	$transportInfo = $soap->getMailTransportInfo($transportID);
	if (!is_object($transportInfo)) {
		echo "getMailTransportInfo: ".strSoapError($transportInfo);
		return;
	}
		
	$mailboxInfo = $soap->getMailboxInfo($mailboxID);
	if (!is_object($mailboxInfo)) {
		echo "getMailboxInfo: ".strSoapError($mailboxInfo);
		return;
	}

?>
	<form action="mailboxes.php?transportID=<?php echo $transportID ?>&mailboxID=<?php echo $mailboxID ?>" method="POST">
		<input type="hidden" name="action" value="remove">
		Are you very sure you wish to remove mailbox <?php printf("%s@%s",$mailboxInfo->Address,$transportInfo->DomainName) ?>?
		<br>
		<input type="submit" value="Yes">
	</form>
	<br
<?php
}




# Add screen
function screenAdd() {
	global $soap;
	global $transportID;
	global $mailboxID;


	$transportInfo = $soap->getMailTransportInfo($transportID);
	if (!is_object($transportInfo)) {
		echo "getMailTransportInfo: ".strSoapError($transportInfo);
		return;
	}
		

?>
	<form action="mailboxes.php?transportID=<?php echo $transportID ?>&action=add" method="POST">
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
			<td>Password</td>
			<td>
				<input type="text" name="password">
				<input type="button" value="generate" onClick="this.form.password.value=getRandomPass(8)">
			</td>
		</tr>
		<tr>
			<td>Quota (in Mbyte)</td>
			<td><input type="text" name="quota"></td>
		</tr>
		<tr>
			<td>Name</td>
			<td><input type="text" name="name"></td>
		</tr>
		<tr>
			<td>AgentRef</td>
			<td><input type="text" name="agentRef"></td>
		</tr>
		<tr>
			<td>Premium SMTP</td>
			<td>
				<select name="premiumSMTP">
					<option value="0" selected>no</option>
					<option value="1">yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Premium Policy</td>
			<td>
				<select name="premiumPolicy">
					<option value="0" selected>no</option>
					<option value="1">yes</option>
				</select>
			</td>
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




# List mailboxes
function mailboxList($searchOptions) {
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
			<td colspan="7" align="center">Search Results for Mailboxes on <?php echo $transportInfo->DomainName; ?></td>
		</tr>
		<tr>
			<td rowspan="2" align="center">Mailbox</td>
			<td rowspan="2" align="center">Quota</td>
			<td rowspan="2" align="center">AgentRef</td>
			<td colspan="3" align="center">Disable</td>
			<td rowspan="2"></td>
		</tr>
		<tr>
			<td>Agent</td>
			<td>Login</td>
			<td>Delivery</td>
		</tr>
<?php
	$mailboxes = $soap->getMailboxes($transportID,$searchOptions);
	if (is_array($mailboxes)) {
		if (count($mailboxes)) {
?>
			<tr>
				<td colspan="7" align="center">
					<a href="mailboxes.php?transportID=<?php echo $transportID ?>&screen=add">Add Mailbox</a>
				</td>
			</tr>
<?php
		}

		foreach ($mailboxes as $item) {
?>
			<tr>
				<td><?php echo $item->Address; ?></td>	
				<td><?php echo $item->Quota; ?>Mb</td>	
				<td><?php echo $item->AgentRef; ?></td>	
				<td align="center"><?php echo $item->AgentDisabled ? "yes" : "no"; ?></td>	
				<td align="center"><?php echo $item->DisableLogin ? "yes" : "no"; ?></td>	
				<td align="center"><?php echo $item->DisableDelivery ? "yes" : "no"; ?></td>	
				<td>
					<a href="mailboxes.php?transportID=<?php echo $transportID; ?>&mailboxID=<?php echo $item->ID; ?>&screen=edit">Edit</a>	
					 | <a href="mailboxes.php?transportID=<?php echo $transportID; ?>&mailboxID=<?php echo $item->ID; ?>&screen=remove">Remove</a>
				</td>
			</tr>
<?php		
		}
?>
		<tr>
			<td colspan="7" align="center">
				<a href="mailboxes.php?transportID=<?php echo $transportID ?>&screen=add">Add Mailbox</a>
			</td>
		</tr>
<?php
			
	} else {
?>
		<tr>
			<td colspan="5">
<?php
				echo "getMailboxes: ".strSoapError($mailboxes);
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

<form action="mailboxes.php" method="GET">
<input type="hidden" name="transportID" value="<?php echo $transportID ?>">
<input type="hidden" name="search" value="1">
<table border="1">
	<tr>
		<td colspan="3" align="center">Search Mailboxes</td>
	</tr>
	<tr>
		<td colspan="2"></td>
		<td align="center">Order by</td>
	</tr>
	<tr>
		<td>Address</td>
		<td>
			<input type="text" name="searchAddress" value="<?php
					echo $_SESSION['mailbox_searchAddress'] 
			?>">
		</td>
		<td align="center">
			<input type="radio" name="searchOrderBy" value="Address" <?php
				if ($_SESSION['mailbox_searchOrderBy'] == "" 
						|| $_SESSION['mailbox_searchOrderBy'] == "Address") {
					echo "checked";
				}
			?>>
		</td>
	</tr>
	<tr>
		<td>Agent Ref</td>
		<td>
			<input type="text" name="searchAgentRef" value="<?php
					 echo $_SESSION['mailbox_searchAgentRef']
			?>">
		</td>
		<td align="center"><input type="radio" name="searchOrderBy" value="AgentRef" <?php
				if ($_SESSION['mailbox_searchOrderBy'] == "AgentRef") {
					echo "checked";
				}
			?>>
		</td>
	</tr>
	<tr>	
		<td colspan="2" align="center"><input type="submit"></td>
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
	$transportInfo = $soap->getMailTransportInfo($transportID);


	# Check if we have a mailbox ID, pull in the relevant stuff
	if ($_REQUEST['mailboxID'] > 0) {
		$mailboxID = $_REQUEST['mailboxID'];


		printf("<a href=\"mailboxes.php?transportID=%s\">Back to mailbox search</a><br><br>",$transportID);
		printf("<a href=\"mailboxes.php?transportID=%s&search=1\">Back to mailboxes</a><br><br>",$transportID);

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
			printf("<a href=\"mailboxes.php?transportID=%s\">Back to mailbox search</a><br><br>",$transportID);
			printf("<a href=\"mailboxes.php?transportID=%s&search=1\">Back to mailboxes</a><br><br>",$transportID);
			screenAdd();
		} elseif ($_POST["action"] == "add") {
			printf("<a href=\"mailboxes.php?transportID=%s\">Back to mailbox search</a><br><br>",$transportID);
			printf("<a href=\"mailboxes.php?transportID=%s&search=1\">Back to mailboxes</a><br><br>",$transportID);
			actionAdd();
		# We came from search screen
		} elseif ($_REQUEST['search'] == 1) {
			printf("<a href=\"mailboxes.php?transportID=%s\">Back to mailbox search</a><br><br>",$transportID);

			# Process search options
			if (isset($_REQUEST['searchAddress'])) {
				$_SESSION['mailbox_searchAddress'] = $_REQUEST['searchAddress'];
			}
			if (isset($_REQUEST['searchAgentRef'])) {
				$_SESSION['mailbox_searchAgentRef'] = $_REQUEST['searchAgentRef'];
			}
			if (isset($_REQUEST['searchOrderBy'])) {
				$_SESSION['mailbox_searchOrderBy'] = $_REQUEST['searchOrderBy'];
			}

			# Setup search
			$searchOptions->searchAddress = $_SESSION['mailbox_searchAddress'];
			$searchOptions->searchAgentRef = $_SESSION['mailbox_searchAgentRef'];
			$searchOptions->searchOrderBy = $_SESSION['mailbox_searchOrderBy'];

			mailboxList($searchOptions);
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
