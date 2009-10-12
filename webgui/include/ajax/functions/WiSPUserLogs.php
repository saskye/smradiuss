<?php

include_once("include/db.php");


# Return user logs summary
function getWiSPUserLogsSummary($params) {

	# Get group attributes
	# fixme - user might be member of multiple groups
	$res = DBSelect("
			SELECT
				group_attributes.Name,
				group_attributes.Value
			FROM
				group_attributes, users_to_groups, groups
			WHERE
				group_attributes.GroupID = groups.ID
				AND groups.ID = users_to_groups.GroupID
				AND users_to_groups.UserID = ?",
				array($params[0]['ID'])
	);

	# Return if error
	if (!is_object($res)) {
		return $res;
	}

	# Fetch uptime and traffic limits, if not found, this is prepaid account.. use -1 as we need int
	$trafficCap = -1;
	$uptimeCap = -1;
	while ($row = $res->fetchObject()) {
		if ($row->name == 'SMRadius-Capping-Traffic-Limit') {
			$trafficCap = (int)$row->value;
		}
		if ($row->name == 'SMRadius-Capping-Uptime-Limit') {
			$uptimeCap = (int)$row->value;
		}
	}

	# Get user attributes
	$res = DBSelect("
			SELECT
				user_attributes.Name,
				user_attributes.Value
			FROM
				user_attributes
			WHERE
				user_attributes.UserID = ?",
				array($params[0]['ID'])
	);

	# Return if error
	if (!is_object($res)) {
		return $res;
	}

	# Fetch uptime and traffic limits, if not found, this is prepaid account.. use -1 as we need int
	while ($row = $res->fetchObject()) {
		if ($row->name == 'SMRadius-Capping-Traffic-Limit') {
			$trafficCap = (int)$row->value;
		}
		if ($row->name == 'SMRadius-Capping-Uptime-Limit') {
			$uptimeCap = (int)$row->value;
		}
	}

	# Add caps to result
	$resultArray = array();
	$resultArray['trafficCap'] = $trafficCap;
	$resultArray['uptimeCap'] = $uptimeCap;

	# Dates we want to use to search search
	$periodKey = new DateTime($params[0]['PeriodKey']."-01");

	# Return if error
	if (!is_object($periodKey)) {
		return $periodKey;
	}

	# Fetch user uptime and traffic summary
	$res = DBSelect("
		SELECT
			topups_summary.Balance,
			topups.Type,
			topups.Value
		FROM
			topups_summary,
			topups
		WHERE
			topups_summary.TopupID = topups.ID
			AND topups.UserID = ?
			AND topups_summary.PeriodKey = ?
		ORDER BY
			topups.Timestamp",
			array($params[0]['ID'],$periodKey->format('Y-m'))
	);

	# Return if error
	if (!is_object($res)) {
		return $res;
	}

	# Store summary topups
	$topups = array();
	$i = 0;
	while ($row = $res->fetchObject()) {
		$topups[$i] = array();
		$topups[$i]['Type'] = $row->type;
		$topups[$i]['Limit'] = $row->balance;
		$topups[$i]['OriginalLimit'] = $row->value;
		$i++;
	}

	# Fetch user uptime and traffic topups
	$periodKeyEnd = new DateTime($periodKey->format('Y-m-d'));
	$periodKeyEnd->modify("+1 month");
	$res = DBSelect("
		SELECT
			Value, Type
		FROM
			topups
		WHERE
			topups.UserID = ?
			AND topups.ValidFrom = ?
			AND topups.ValidTo >= ?
		ORDER BY
			topups.Timestamp",
			array($params[0]['ID'],$periodKey->format('Y-m-d'),$periodKeyEnd->format('Y-m-d'))
	);

	# Return if error
	if (!is_object($res)) {
		return $res;
	}

	# Store normal topups
	while ($row = $res->fetchObject()) {
		$topups[$i] = array();
		$topups[$i]['Type'] = $row->type;
		$topups[$i]['Limit'] = $row->value;
		$i++;
	}

	$res = DBSelect("
		SELECT
			accounting.AcctSessionTime,
			accounting.AcctInputOctets,
			accounting.AcctInputGigawords,
			accounting.AcctOutputOctets,
			accounting.AcctOutputGigawords
		FROM
			accounting, users
		WHERE
			users.ID = ?
			AND PeriodKey = ?
			AND accounting.Username = users.Username",
			array($params[0]['ID'],$periodKey->format('Y-m'))
	);

	if (!is_object($res)) {
		return $res;
	}

	# Set total traffic and uptime used
	$totalTraffic = 0;
	$totalUptime = 0;
	while ($row = $res->fetchObject()) {

		# Traffic in
		$inputDataItem = 0;
		if (isset($row->acctinputoctets) && $row->acctinputoctets > 0) {
			$inputDataItem += ($row->acctinputoctets / 1024) / 1024;
		}
		if (isset($row->acctinputgigawords) && $row->acctinputgigawords > 0) {
			$inputDataItem += ($row->acctinputgigawords * 4096);
		}
		$totalTraffic += $inputDataItem;

		# Traffic out
		$outputDataItem = 0;
		if (isset($row->acctoutputoctets) && $row->acctoutputoctets > 0) {
			$outputDataItem += ($row->acctoutputoctets / 1024) / 1024;
		}
		if (isset($row->acctoutputgigawords) && $row->acctoutputgigawords > 0) {
			$outputDataItem += ($row->acctoutputgigawords * 4096);
		}
		$totalTraffic += $outputDataItem;

		# Uptime
		$sessionTimeItem = 0;
		if (isset($row->acctsessiontime) && $row->acctsessiontime > 0) {
			$sessionTimeItem += $row->acctsessiontime;
		}

		$totalUptime += $sessionTimeItem;
	}

	# Round up usage
	$totalTraffic = (int)ceil($totalTraffic);
	$totalUptime = (int)ceil($totalUptime / 60);

	# Add usage to our return array
	$resultArray['trafficUsage'] = $totalTraffic;
	$resultArray['uptimeUsage'] = $totalUptime;

	# Loop through topups and add to return array
	$resultArray['trafficTopups'] = 0;
	$resultArray['uptimeTopups'] = 0;
	foreach ($topups as $topupItem) {
		if ($topupItem['Type'] == 1) {
			$resultArray['trafficTopups'] += $topupItem['Limit'];
		}
		if ($topupItem['Type'] == 2) {
			$resultArray['uptimeTopups'] += $topupItem['Limit'];
		}
	}

	# Return results
	return array($resultArray, 1);
}

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
					accounting.AcctTerminateCause,
					accounting.AcctSessionTime
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

		# Uptime
		$acctSessionTime = 0;
		if (isset($row->acctsessiontime) && $row->acctsessiontime > 0) {
			$acctSessionTime += ($row->acctsessiontime / 60);
		}
		ceil($acctSessionTime);

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
		$item['AcctSessionTime'] = $acctSessionTime;
		$item['ConnectTermReason'] = strRadiusTermCode($row->acctterminatecause);

		# Push this row onto main array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# vim: ts=4
