<?php
# Mail transport stuff
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
<?php


# Actual form action to update mailbox
function actionUpdate() {
	global $soap;
	global $transportID;


	# Create update hash
	$update = NULL;

	if ($_POST["policyID"] != "nochange") {
		$update["PolicyID"] = $_POST["policyID"];
	}		

	if ($_POST["agentDisabled"] != "") {
		$update["AgentDisabled"] = $_POST["agentDisabled"];
	}		

	if ($_POST["agentRef"] != "") {
		$update["AgentRef"] = $_POST["agentRef"];
	}		

	# If there are still updates to be done, do them
	if ($update != NULL) {
		$update["ID"] = $transportID;
		
		$res = $soap->updateMailTransport($update);
		if ($res == 0) {
			echo "Updated transport<br>\n";
		} else {
			echo "Error updating transport($res): ".strSoapError($res);
		}
	# Or report no updates to be made
	} else {
		echo "No updates to be made!\n";
	}
}




# Display edit screen
function screenEdit() {
	global $soap;
	global $transportID;


	$transportInfo = $soap->getMailTransportInfo($transportID);
	if (!is_object($transportInfo)) {
		echo "getMailTransportInfo: ".strSoapError($transportInfo);
		return;
	}
		
	$mailPolicies = $soap->getMailPolicies();
	if (!is_array($mailPolicies)) {
		echo "getMailPolicies: ".strSoapError($mailPolicies);
		return;
	}

?>
	<form action="mailTransports.php?transportID=<?php echo $transportID; ?>" method="POST">
	<table border="1">
		<tr>
			<td colspan="3" align="center">
				Transport: <?php printf("%s => %s",$transportInfo->DomainName,
						$transportInfo->Transport."/".$transportInfo->Detail); ?>
			</td>
		</tr>
		<tr>
			<td>Attribute</td>
			<td>Value</td>
			<td>New Value</td>
		</tr>
<?php

		$policyName = "";
		foreach ($mailPolicies as $policy) {
			if ($transportInfo->PolicyID == $policy->ID) {
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
			<td>AgentRef</td>
			<td><?php echo $transportInfo->AgentRef; ?></td>
			<td><input type="text" name="agentRef"></td>
		</tr>

		<tr>
			<td>Disabled</td>
			<td><?php echo $transportInfo->AgentDisabled ? "yes" : "no"; ?></td>
			<td>
				<select name="agentDisabled">
					<option value="0" <?php if (!$transportInfo->AgentDisabled) { echo "selected"; } ?>>no</option>
					<option value="1" <?php if ($transportInfo->AgentDisabled) { echo "selected"; } ?>>yes</option>
				</select>
			</td>
			<td></td>
		</tr>
	</table>

	<input type="hidden" name="action" value="update">
	<input type="submit" value="Update">
	</form>		
<?php
}




# List mailboxes
function transportList($searchOptions) {
	global $soap;


	$mailTransports = $soap->getMailTransports($searchOptions);
	if (is_array($mailTransports)) {
?>
		<a href="mailTransports.php">Back to mail transport search</a><br><br>

		<table class="mtlisttable">
			<tr class="mtlisttabletitle">
				<td colspan="6">Search Results for Mail Transports</td>
			</tr>
			<tr class="mtlisttablehead">
				<td rowspan="2">Domain</td>
				<td rowspan="2">Transport</td>
				<td rowspan="2">AgentRef</td>
				<td colspan="2">Disabled</td>
			</tr>
			<tr class="mtlisttablehead">
				<td>Agent</td>
				<td>Delivery</td>
			</tr>
<?php
			$i = 0;
			foreach ($mailTransports as $transport) {
				# Check if number is odd or even
				if ($i % 2 == 0) {
					$j = "1";
				} else {
					$j = "2";
				}

?>
				<tr class="mtlisttabledata<?php echo $j ?>">
					<td><?php 
						echo "<a href=\"mailTransports.php?transportID=".$transport->ID."&screen=edit\"?>";
						echo $transport->DomainName; 
						echo "</a>";
					?></td>
					<td><?php echo $transport->Transport . "/" . $transport->Detail; ?></td>
					<td><?php echo $transport->AgentRef; ?></td>
					<td><?php echo $transport->AgentDisabled ? "yes" : "no"; ?></td>
					<td><?php echo $transport->DisableDelivery ? "yes" : "no"; ?></td>
					<td>
<?php
						if ($transport->Transport == "virtual") {
?>
							<a href="mailboxes.php?transportID=<?php echo $transport->ID ?>">Mailboxes</a> | 
							<a href="mailboxAliases.php?transportID=<?php echo $transport->ID ?>">Aliases</a>
<?php
						}
?>
					</td>
				</tr>
<?php
				$i++;
			}
?>
		</table>
		<br>
		<a href="mailTransports.php">Back to mail transport search</a><br><br>
<?php
	} else {
		echo "getMailTransports: ".strSoapError($mailTransports);
	}
}




# Function to display search box
function searchBox() {
?>
	<form action="mailTransports.php" method="GET">
	<input type="hidden" name="search" value="1">
	<table class="mtsearchtable">
		<tr class="mtsearchtabletitle">
			<td colspan="3">Search Mail Transports</td>
		</tr>
		<tr>
			<td colspan="2" class="mtsearchtableblank"></td>
			<td>Order by</td>
		</tr>
		<tr>
			<td class="mtsearchtablehead">Domain Name</td>
			<td class="mtsearchtabledata">
				<input type="text" name="searchDomainName" value="<?php
						echo $_SESSION['transport_searchDomainName'] 
				?>">
			</td>
			<td class="mtsearchtableorder">
				<input type="radio" name="searchOrderBy" value="DomainName" <?php
					if ($_SESSION['transport_searchOrderBy'] == "" 
							|| $_SESSION['transport_searchOrderBy'] == "DomainName") {
						echo "checked";
					}
				?>>
			</td>
		</tr>
		<tr>
			<td class="mtsearchtablehead">Agent Ref</td>
			<td class="mtsearchtabledata">
				<input type="text" name="searchAgentRef" value="<?php
						 echo $_SESSION['transport_searchAgentRef']
				?>">
			</td>
			<td class="mtsearchtableorder">
				<input type="radio" name="searchOrderBy" value="AgentRef" <?php
					if ($_SESSION['transport_searchOrderBy'] == "AgentRef") {
						echo "checked";
					}
				?>>
			</td>
		</tr>
		<tr class="mtsearchtablesubmit">
			<td colspan="3"><input type="submit"></td>
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


	echo("<a href=\"mailTransports.php?search=1\">Back to mail transports</a><br><br>");

	# Check if we have a special action to perform
	if ($_POST["action"] == "update") {
		actionUpdate();
	# Edit screen
	} elseif ($_REQUEST["screen"] == "edit")  {
		screenEdit();
	}

# We came from our search box
} elseif ($_REQUEST['search'] == 1) {
	# Process search options
	if (isset($_REQUEST['searchDomainName'])) {
		$_SESSION['transport_searchDomainName'] = $_REQUEST['searchDomainName'];
	}
	if (isset($_REQUEST['searchAgentRef'])) {
		$_SESSION['transport_searchAgentRef'] = $_REQUEST['searchAgentRef'];
	}
	if (isset($_REQUEST['searchOrderBy'])) {
		$_SESSION['transport_searchOrderBy'] = $_REQUEST['searchOrderBy'];
	}

	# Setup search
	$searchOptions->searchDomainName = $_SESSION['transport_searchDomainName'];
	$searchOptions->searchAgentRef = $_SESSION['transport_searchAgentRef'];
	$searchOptions->searchOrderBy = $_SESSION['transport_searchOrderBy'];



	transportList($searchOptions);

# Anything else
} else {
	searchBox();
}


# Footer
include("include/footer.php");
?>
