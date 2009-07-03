<?php

include_once("include/db.php");


# Return list of user logs
function getWiSPUserLogs($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'accounting.ID',
		'EventTimestamp' => 'accounting.EventTimestamp',
		'AcctStatusType' => 'accounting.AcctStatusType',
		'ServiceType' => 'accounting.ServiceType',
		'FramedProtocol' => 'accounting.FramedProtocol',
		'NASPortType' => 'accounting.NASPortType',
		'NASPortID' => 'accounting.NASPortID',
		'CallingStationID' => 'accounting.CallingStationID',
		'CalledStationID' => 'accounting.CalledStationID',
		'AcctSessionID' => 'accounting.AcctSessionID',
		'FramedIPAddress' => 'accounting.FramedIPAddress',
	);

	# Perform query
	$res = DBSelectSearch("
				SELECT
					accounting.ID,
					accounting.EventTimestamp,
					accounting.AcctStatusType,
					accounting.ServiceType,
					accounting.FramedProtocol,
					accounting.NASPortType,
					accounting.NASPortID,
					accounting.CallingStationID,
					accounting.CalledStationID,
					accounting.AcctSessionID,
					accounting.FramedIPAddress,
					accounting.AcctInputOctets,
					accounting.AcctInputGigawords,
					accounting.AcctOutputOctets,
					accounting.AcctOutputGigawords,
					accounting.AcctTerminateCause
				FROM
					accounting, users
				WHERE
					users.Username = accounting.Username
				AND
					users.ID = ".DBQuote($params[0])."
					",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];

	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	# Loop through rows
	$resultArray = array();
	while ($row = $sth->fetchObject()) {

		# Input
		$acctInputMbyte = 0;

		if (isset($row->acctinputoctets) && $row->acctinputoctets > 0) {
			$acctInputMbyte += ($row->acctinputoctets / 1024) / 1024;
		}
		if (isset($row->acctinputgigawords) && $row->acctinputgigawords > 0) {
			$acctInputMbyte += ($row->acctinputgigawords * 4096);
		}

		# Output
		$acctOutputMbyte = 0;

		if (isset($row->acctoutputoctets) && $row->acctoutputoctets > 0) {
			$acctOutputMbyte += ($row->acctoutputoctets / 1024) / 1024;
		}
		if (isset($row->acctoutputgigawords) && $row->acctoutputgigawords > 0) {
			$acctOutputMbyte += ($row->acctoutputgigawords * 4096);
		}

		# Build array for this row
		$item = array();

		$item['ID'] = $row->id;
		# Convert to ISO format
		$date = new DateTime($row->eventtimestamp);
		$value = $date->format("Y-m-d H:i:s");
		$item['EventTimestamp'] = $value;
		$item['AcctStatusType'] = $row->acctstatustype;
		$item['ServiceType'] = $row->servicetype;
		$item['FramedProtocol'] = $row->framedprotocol;
		$item['NASPortType'] = $row->nasporttype;
		$item['NASPortID'] = $row->nasportid;
		$item['CallingStationID'] = $row->callingstationid;
		$item['CalledStationID'] = $row->calledstationid;
		$item['AcctSessionID'] = $row->acctsessionid;
		$item['FramedIPAddress'] = $row->framedipaddress;
		$item['AcctInputMbyte'] = $acctInputMbyte;
		$item['AcctOutputMbyte'] = $acctOutputMbyte;
		$item['ConnectTermReason'] = strRadiusTermCode($row->servicetype);

		# Push this row onto main array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# vim: ts=4
