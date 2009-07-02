<?php

	# Requires / Includes
	include_once("include/ajax/json.php");

	include_once("include/ajax/functions/AdminUsers.php");
	include_once("include/ajax/functions/AdminUserAttributes.php");
	include_once("include/ajax/functions/AdminUserGroups.php");
	include_once("include/ajax/functions/AdminUserLogs.php");
	include_once("include/ajax/functions/AdminUserTopups.php");

	include_once("include/ajax/functions/AdminGroups.php");
	include_once("include/ajax/functions/AdminGroupAttributes.php");
	include_once("include/ajax/functions/AdminGroupMembers.php");

	include_once("include/ajax/functions/AdminRealms.php");
	include_once("include/ajax/functions/AdminRealmAttributes.php");

	include_once("include/ajax/functions/WiSPUsers.php");
	include_once("include/ajax/functions/WiSPLocations.php");
	include_once("include/ajax/functions/WiSPLocationMembers.php");
	include_once("include/ajax/functions/WiSPUserLogs.php");
	include_once("include/ajax/functions/WiSPUserTopups.php");

	include_once("include/radiuscodes.php");

	define('RES_OK',0);
	define('RES_ERR',-1);


/*
 * AJAX Interface to SMEngine SOAP
 */

 	# Fire an exception off to Ajax
	function ajaxException($msg) {
/*
		$res = array(
			'success' => FALSE,
			'errors' => array(
				$msg
			)
		);
		echo json_encode($res);
*/
		echo json_encode($msg);
		exit;
	}

	# PHP exception handler
	function exception_handler($exception) {
		ajaxException("Exception: ". $exception->getMessage());
	}

	# Set PHP exception handler
	set_exception_handler('exception_handler');


	if (!isset($_REQUEST['SOAPUsername']) || empty($_REQUEST['SOAPUsername'])) {
		ajaxException("SOAPUsername undefined");
	}
	if (!is_string($_REQUEST['SOAPUsername'])) {
		ajaxException("SOAPUsername is not a string");
	}
	$username = $_REQUEST['SOAPUsername'];

	if (!isset($_REQUEST['SOAPPassword']) || empty($_REQUEST['SOAPPassword'])) {
		ajaxEception("SOAPPassword undefined");
	}
	if (!is_string($_REQUEST['SOAPPassword'])) {
		ajaxException("SOAPPassword is not a string");
	}
	$password = $_REQUEST['SOAPPassword'];

	if (!isset($_REQUEST['SOAPAuthType']) || empty($_REQUEST['SOAPAuthType'])) {
		ajaxException("SOAPAuthType undefined");
	}
	if (!is_string($_REQUEST['SOAPAuthType'])) {
		ajaxException("SOAPAuthType is not a string");
	}
	$authType = $_REQUEST['SOAPAuthType'];

	if (!isset($_REQUEST['SOAPModule']) || empty($_REQUEST['SOAPModule'])) {
		ajaxException("SOAPModule undefined");
	}
	if (!is_string($_REQUEST['SOAPModule'])) {
		ajaxException("SOAPModule is not a string");
	}
	$module = $_REQUEST['SOAPModule'];

	if (!isset($_REQUEST['SOAPFunction']) || empty($_REQUEST['SOAPFunction'])) {
		ajaxException("SOAPFunction undefined");
	}
	if (!is_string($_REQUEST['SOAPFunction'])) {
		ajaxException("SOAPFunction is not a string");
	}
	$function = $_REQUEST['SOAPFunction'];

	/*
		__search
		ID,__search
		1:Name,1:Contact,1:Telephone,1:Email
	*/

	$soapParamTemplate = explode(',',$_REQUEST['SOAPParams']);
	$soapParams = array();
	foreach ($soapParamTemplate as $param) {
		# Special case '__search'
		if ($param == "__search") {
			# Build hash and push into param list
			$search = array(
				'Filter' => isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '',
				'Start' => isset($_REQUEST['start']) ? $_REQUEST['start'] : NULL,
				'Limit' => isset($_REQUEST['limit']) ? $_REQUEST['limit'] : NULL,
				'Sort' => isset($_REQUEST['sort']) ? $_REQUEST['sort'] : '',
				'SortDirection' => isset($_REQUEST['dir']) ? $_REQUEST['dir'] : '',
			);
			array_push($soapParams,$search);

		# Special case '__null'
		} elseif ($param == "__null") {
			array_push($soapParams,NULL);

		# Everything else
		} else {
			# Split off param number if we're using it
			$items = explode(':',$param);

			# We have a parameter number
			if (count($items) > 1) {
				$array_pos = $items[0];
				$array_item = $items[1];

				# Initialize array
				if (!isset($soapParams[$array_pos])) {
					$soapParams[$array_pos] = array();
				}
				# Check if we have an explicit type
				if (isset($items[2])) {
					$array_type = $items[2];
					# Check type
					if ($array_type == "boolean") {
						# Checkboxes/booleans are undefined if false
						if (isset($_REQUEST[$array_item])) {
							$item_value = 'true';
						} else {
							$item_value = 'false';
						}
					# And bitch if we invalid
					} else {
						ajaxException("Unknown AJAX=>SOAP type: '$array_type'");
					}
				} else {
					$item_value = isset($_REQUEST[$array_item]) ? $_REQUEST[$array_item] : NULL;
				}
				# Set item
				$soapParams[$array_pos][$array_item] = $item_value;

			} else {
				array_push($soapParams,$_REQUEST[$items[0]]);
			}
		}
	}


	switch ($function) {

		# AdminUserTopups.js functions
		case "getAdminUserTopups":

			$res = getAdminUserTopups($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Timestamp','date');
			$res->addField('Type','int');
			$res->addField('Value','int');
			$res->addField('ValidFrom','string');
			$res->addField('ValidTo','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());

			break;
	
		case "createAdminUserTopup":

			$res = createAdminUserTopup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "updateAdminUserTopup":

			$res = updateAdminUserTopup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getAdminUserTopup":
			$rawData = getAdminUserTopup($soapParams);

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Type','int');
			$res->addField('Value','int');
			$res->addField('ValidFrom','date');
			$res->addField('ValidTo','date');
			$res->parseHash($rawData);

			echo json_encode($res->export());
			break;

		case "removeAdminUserTopup":

			$res = removeAdminUserTopup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		# WiSPUserTopups.js functions
		case "getWiSPUserTopups":

			$res = getWiSPUserTopups($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Timestamp','date');
			$res->addField('Type','int');
			$res->addField('Value','int');
			$res->addField('ValidFrom','string');
			$res->addField('ValidTo','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());

			break;
	
		case "createWiSPUserTopup":

			$res = createWiSPUserTopup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "updateWiSPUserTopup":

			$res = updateWiSPUserTopup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getWiSPUserTopup":
			$rawData = getWiSPUserTopup($soapParams);

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Type','int');
			$res->addField('Value','int');
			$res->addField('ValidFrom','date');
			$res->addField('ValidTo','date');
			$res->parseHash($rawData);

			echo json_encode($res->export());
			break;

		case "removeWiSPUserTopup":

			$res = removeWiSPUserTopup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		# AdminGroupMembers.js functions
		case "getAdminGroupMembers":

			$res = getAdminGroupMembers($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Username','string');
			$res->addField('Disabled','boolean');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());

			break;

		case "removeAdminGroupMember":

			$res = removeAdminGroupMember($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		# AdminUserLogs.js functions
		case "getAdminUserLogs":

			$res = getAdminUserLogs($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('EventTimestamp','string');
			$res->addField('AcctStatusType','int');
			$res->addField('ServiceType','int');
			$res->addField('FramedProtocol','int');
			$res->addField('NASPortType','int');
			$res->addField('NASPortID','string');
			$res->addField('CallingStationID','string');
			$res->addField('CalledStationID','string');
			$res->addField('AcctSessionID','string');
			$res->addField('FramedIPAddress','string');
			$res->addField('AcctInputMbyte','int');
			$res->addField('AcctOutputMbyte','int');
			$res->addField('ConnectTermReason','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());

			break;
	
		# addAdminUserGroup.js functions
		case "addAdminUserGroup":

			$res = addAdminUserGroup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "removeAdminUserGroup":

			$res = removeAdminUserGroup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getAdminUserGroups":

			$res = getAdminUserGroups($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());

			break;

		# AdminRealmAttributes.js functions
		case "addAdminRealmAttribute":

			$res = addAdminRealmAttribute($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "updateAdminRealmAttribute":

			$res = updateAdminRealmAttribute($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getAdminRealmAttribute":
			$rawData = getAdminRealmAttribute($soapParams);

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Operator','string');
			$res->addField('Value','string');
			$res->addField('Disabled','boolean');
			$res->parseHash($rawData);

			echo json_encode($res->export());
			break;

		case "getAdminRealmAttributes":

			$res = getAdminRealmAttributes($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Operator','string');
			$res->addField('Value','string');
			$res->addField('Disabled','boolean');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		case "removeAdminRealmAttribute":

			$res = removeAdminRealmAttribute($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		# AdminGroupAttributes.js functions
		case "addAdminGroupAttribute":

			$res = addAdminGroupAttribute($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "updateAdminGroupAttribute":

			$res = updateAdminGroupAttribute($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getAdminGroupAttribute":
			$rawData = getAdminGroupAttribute($soapParams);

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Operator','string');
			$res->addField('Value','string');
			$res->addField('Disabled','boolean');
			$res->parseHash($rawData);

			echo json_encode($res->export());
			break;

		case "getAdminGroupAttributes":

			$res = getAdminGroupAttributes($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Operator','string');
			$res->addField('Value','string');
			$res->addField('Disabled','boolean');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		case "removeAdminGroupAttribute":

			$res = removeAdminGroupAttribute($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		# AdminUserAttributes.js functions
		case "addAdminUserAttribute":

			$res = addAdminUserAttribute($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "updateAdminUserAttribute":

			$res = updateAdminUserAttribute($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getAdminUserAttribute":
			$rawData = getAdminUserAttribute($soapParams);

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Operator','string');
			$res->addField('Value','string');
			$res->addField('Disabled','boolean');
			$res->parseHash($rawData);

			echo json_encode($res->export());
			break;

		case "getAdminUserAttributes":

			$res = getAdminUserAttributes($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Operator','string');
			$res->addField('Value','string');
			$res->addField('Disabled','boolean');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		case "removeAdminUserAttribute":

			$res = removeAdminUserAttribute($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		# WiSPUsers.js functions
		case "updateWiSPUser":

			$res = updateWiSPUser($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "createWiSPUser":

			$res = createWiSPUser($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "removeWiSPUser":

			$res = removeWiSPUser($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getWiSPUsers":

			$res = getWiSPUsers($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Username','string');
			$res->addField('Disabled','boolean');
			$res->addField('Firstname','string');
			$res->addField('Lastname','string');
			$res->addField('Email','string');
			$res->addField('Phone','int');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		case "getWiSPUser":
			$res = getWiSPUser($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Username','string');
			$res->addField('Password','string');
			$res->addField('Firstname','string');
			$res->addField('Lastname','string');
			$res->addField('Phone','string');
			$res->addField('Email','string');
			$res->addField('LocationID','int');
			$res->parseHash($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		# WiSPUserLogs.js functions
		case "getWiSPUserLogs":

			$res = getWiSPUserLogs($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('EventTimestamp','string');
			$res->addField('AcctStatusType','int');
			$res->addField('ServiceType','int');
			$res->addField('FramedProtocol','int');
			$res->addField('NASPortType','int');
			$res->addField('NASPortID','string');
			$res->addField('CallingStationID','string');
			$res->addField('CalledStationID','string');
			$res->addField('AcctSessionID','string');
			$res->addField('FramedIPAddress','string');
			$res->addField('AcctInputMbyte','int');
			$res->addField('AcctOutputMbyte','int');
			$res->addField('ConnectTermReason','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());

			break;
	
		# WiSPLocationMembers.js functions
		case "getWiSPLocationMembers":

			$res = getWiSPLocationMembers($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Username','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());

			break;

		case "removeWiSPLocationMember":

			$res = removeWiSPLocationMember($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		# WiSPLocations.js functions
		case "updateWiSPLocation":

			$res = updateWiSPLocation($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "createWiSPLocation":

			$res = createWiSPLocation($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "removeWiSPLocation":

			$res = removeWiSPLocation($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getWiSPLocations":

			$res = getWiSPLocations($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		case "getWiSPLocation":
			$rawData = getWiSPLocation($soapParams);

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->parseHash($rawData);

			echo json_encode($res->export());
			break;

		# AdminUsers.js functions
		case "updateAdminUser":

			$res = updateAdminUser($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "createAdminUser":

			$res = createAdminUser($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "removeAdminUser":

			$res = removeAdminUser($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getAdminUsers":

			$res = getAdminUsers($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Username','string');
			$res->addField('Disabled','boolean');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		case "getAdminUser":
			$rawData = getAdminUser($soapParams);

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Username','string');
			$res->addField('Disabled','boolean');
			$res->parseHash($rawData);

			echo json_encode($res->export());
			break;

		# AdminRealms.js functions
		case "updateAdminRealm":

			$res = updateAdminRealm($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "createAdminRealm":

			$res = createAdminRealm($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "removeAdminRealm":

			$res = removeAdminRealm($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getAdminRealms":

			$res = getAdminRealms($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Disabled','boolean');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		case "getAdminRealm":
			$rawData = getAdminRealm($soapParams);

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Disabled','boolean');
			$res->parseHash($rawData);

			echo json_encode($res->export());
			break;

		# AdminGroups.js functions
		case "updateAdminGroup":

			$res = updateAdminGroup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "createAdminGroup":

			$res = createAdminGroup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "removeAdminGroup":

			$res = removeAdminGroup($soapParams);
			if (isset($res)) {
				ajaxException($res);
			}

			break;

		case "getAdminGroups":

			$res = getAdminGroups($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Priority','int');
			$res->addField('Disabled','boolean');
			$res->addField('Comment','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());

			break;

		case "getAdminGroup":
			$rawData = getAdminGroup($soapParams);

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Priority','int');
			$res->addField('Disabled','boolean');
			$res->addField('Comment','string');
			$res->parseHash($rawData);

			echo json_encode($res->export());

			break;

		case "getWiSPResellers":

			$rawData = array (

				array(
					'ID' => 10,
					'Name' => 'TestReseller1'
				)
			);

			$numResults = 1;

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		case "getAdminRealms":

			$res = getAdminRealms($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Name','string');
			$res->addField('Disabled','boolean');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

	}


	exit;

# vim: ts=4
