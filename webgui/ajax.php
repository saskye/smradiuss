<?php

	# Requires / Includes
	include_once("include/ajax/json.php");
	include_once("include/ajax/functions/AdminUsers.php");
	include_once("include/ajax/functions/AdminGroups.php");
	include_once("include/ajax/functions/AdminRealms.php");
	include_once("include/ajax/functions/AdminLocations.php");

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
				'Start' => $_REQUEST['start'],
				'Limit' => $_REQUEST['limit'],
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
					$item_value = $_REQUEST[$array_item];
				}
				# Set item
				$soapParams[$array_pos][$array_item] = $item_value;

			} else {
				array_push($soapParams,$_REQUEST[$items[0]]);
			}
		}
	}

	switch ($function) {
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

		case "getLocations":

			$res = getAdminLocations($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

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

		case "getAdminUsers":

			$res = getAdminUsers($soapParams);
			$rawData = $res[0]; $numResults = $res[1];

			# Check we have data returned
			if (!isset($rawData)) {
				# $numResults in this case is actually $msg, which is the errorm essage
				ajaxException($numResults);
			}

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Username','string');
			$res->addField('Disabled','boolean');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		case "getWiSPUsers":

			$rawData = array (

				array(
					'ID' => 10,
					'AgentID' => 5,
					'AgentName' => 'joe agent',
					'Username' => 'johnsmith',
					'UsageCap' => 1000,
					'ClassID' => 7,
					'ClassDesc' => 'ClassTest',
					'RealmDesc' => 'My Realm',
					'Service' => 'My Service',
					'AgentDisabled' => FALSE,
					'Disabled' => FALSE,
					'AgentRef' => 'Reseller ref'
				)
			);

			$numResults = 1;

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('AgentID','int');
			$res->addField('AgentName','string');
			$res->addField('Username','string');
			$res->addField('UsageCap','int');
			$res->addField('ClassID','int');
			$res->addField('ClassDesc','string');
			$res->addField('RealmDesc','string');
			$res->addField('Service','string');
			$res->addField('AgentDisabled','boolean');
			$res->addField('Disabled','boolean');
			$res->addField('AgentRef','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;

		case "getWiSPUserLogs":

			$rawData = array (

				array(
					'ID' => 10,
					'Username' => 'johnsmith',
					'Status' => 1,
					'Timestamp' => '10/03/2009',
					'AcctSessionID' => '24234',
					'AcctSessionTime' => '10:30',
					'NASIPAddress' => '192.168.1.254',
					'NASPortType' => '2',
					'NASPort' => '3128',
					'CalledStationID' => '282282',
					'CallingStationID' => '2782872',
					'NASTransmitRate' => '2000',
					'NASReceiveRate' => '4000',
					'FramedIPAddress' => '192.168.1.30',
					'AcctInputMbyte' => '1241',
					'AcctOutputMbyte' => '229',
					'LastAcctUpdate' => '1282893',
					'ConnectTermReason' => 'Failboat'
				)
			);

			$numResults = 1;

			$res = new json_response;
			$res->setID('ID');
			$res->addField('ID','int');
			$res->addField('Username','int');
			$res->addField('Status','string');
			$res->addField('Timestamp','string');
			$res->addField('AcctSessionID','int');
			$res->addField('AcctSessionTime','int');
			$res->addField('NASIPAddress','string');
			$res->addField('NASPortType','string');
			$res->addField('NASPort','string');
			$res->addField('CalledStationID','boolean');
			$res->addField('CallingStationID','boolean');
			$res->addField('NASTransmitRate','string');
			$res->addField('NASReceiveRate','string');
			$res->addField('FramedIPAddress','string');
			$res->addField('AcctInputMbyte','string');
			$res->addField('AcctOutputMbyte','string');
			$res->addField('LastAcctUpdate','string');
			$res->addField('ConnectTermReason','string');
			$res->parseArray($rawData);
			$res->setDatasetSize($numResults);

			echo json_encode($res->export());
			break;
	}

	exit;

	# Connect via soap
	$soap = new SoapClient(null,
		array(
			'location' => "http://localhost:1080/?Auth=$authType",
			'uri'      => $module,
			'login' => $username,
			'password' => $password
		)
	);

	# Try soap call
	try {
		$soapRes = $soap->__call($function,$soapParams);

	} catch (Exception $e) {
		# Build msg string
		if (is_soap_fault($e)) {
			header("$SERVER_PROTOCOL 500 Internal Server Error");
			$msg = "SOAP Fault: ".$e->faultstring;
			if (!empty($e->detail)) {
				$msg .= " (".$e->detail.")";
			}
		} else {
			header("$SERVER_PROTOCOL 400 Bad Request");
			$msg = "Fault: ".$e->getMessage();
		}

		ajaxException($msg);
	}


	echo json_encode($soapRes);


# vim: ts=4
