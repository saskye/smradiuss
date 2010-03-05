<?php

include_once("include/db.php");


# Return user logs summary
function getAdminUserLogsSummary($params) {

	# Get group attributes
	# fixme - user might be member of multiple groups
	$res = DBSelect("
			SELECT
				@TP@group_attributes.Name,
				@TP@group_attributes.Value
			FROM
				@TP@group_attributes, @TP@users_to_groups, @TP@groups
			WHERE
				@TP@group_attributes.GroupID = @TP@groups.ID
				AND @TP@groups.ID = @TP@users_to_groups.GroupID
				AND @TP@users_to_groups.UserID = ?",
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
				@TP@user_attributes.Name,
				@TP@user_attributes.Value
			FROM
				@TP@user_attributes
			WHERE
				@TP@user_attributes.UserID = ?",
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
			@TP@topups_summary.Balance,
			@TP@topups.Type,
			@TP@topups.Value
		FROM
			@TP@topups_summary,
			@TP@topups
		WHERE
			@TP@topups_summary.TopupID = @TP@topups.ID
			AND @TP@topups.UserID = ?
			AND @TP@topups_summary.PeriodKey = ?
		ORDER BY
			@TP@topups.Timestamp",
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
			@TP@topups
		WHERE
			@TP@topups.UserID = ?
			AND @TP@topups.ValidFrom = ?
			AND @TP@topups.ValidTo >= ?
		ORDER BY
			@TP@topups.Timestamp",
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
			SUM(@TP@accounting.AcctSessionTime) / 60 AS TotalSessionTime,
			SUM(@TP@accounting.AcctInputOctets) / 1024 / 1024 +
			SUM(@TP@accounting.AcctInputGigawords) * 4096 +
			SUM(@TP@accounting.AcctOutputOctets) / 1024 / 1024 +
			SUM(@TP@accounting.AcctOutputGigawords) * 4096 AS TotalTraffic
		FROM
			@TP@accounting, @TP@users
		WHERE
			@TP@users.ID = ?
			AND @TP@accounting.PeriodKey = ?
			AND @TP@accounting.Username = @TP@users.Username",
			array($params[0]['ID'],$periodKey->format('Y-m'))
	);

	if (!is_object($res)) {
		return $res;
	}

	# Set total traffic and uptime used
	$row = $res->fetchObject();

	# Add usage to our return array
	$resultArray['trafficUsage'] = 0;
	$resultArray['uptimeUsage'] = 0;
	if (isset($row->totaltraffic) && $row->totaltraffic > 0) {
		$resultArray['trafficUsage'] += $row->totaltraffic;
	}
	if (isset($row->totalsessiontime) && $row->totalsessiontime > 0) {
		$resultArray['uptimeUsage'] += $row->totalsessiontime;
	}

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
function getAdminUserLogs($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@accounting.ID',
		'EventTimestamp' => '@TP@accounting.EventTimestamp',
		'AcctStatusType' => '@TP@accounting.AcctStatusType',
		'ServiceType' => '@TP@accounting.ServiceType',
		'FramedProtocol' => '@TP@accounting.FramedProtocol',
		'NASPortType' => '@TP@accounting.NASPortType',
		'NASPortID' => '@TP@accounting.NASPortID',
		'CallingStationID' => '@TP@accounting.CallingStationID',
		'CalledStationID' => '@TP@accounting.CalledStationID',
		'AcctSessionID' => '@TP@accounting.AcctSessionID',
		'FramedIPAddress' => '@TP@accounting.FramedIPAddress',
	);

	# Perform query
	$res = DBSelectSearch("
				SELECT
					@TP@accounting.ID,
					@TP@accounting.EventTimestamp,
					@TP@accounting.AcctStatusType,
					@TP@accounting.ServiceType,
					@TP@accounting.FramedProtocol,
					@TP@accounting.NASPortType,
					@TP@accounting.NASPortID,
					@TP@accounting.CallingStationID,
					@TP@accounting.CalledStationID,
					@TP@accounting.AcctSessionID,
					@TP@accounting.FramedIPAddress,
					@TP@accounting.AcctInputOctets / 1024 / 1024 +
					@TP@accounting.AcctInputGigawords * 4096 AS AcctInput,
					@TP@accounting.AcctOutputOctets / 1024 / 1024 +
					@TP@accounting.AcctOutputGigawords * 4096 AS AcctOutput,
					@TP@accounting.AcctTerminateCause,
					@TP@accounting.AcctSessionTime / 60 AS AcctSessionTime
				FROM
					@TP@accounting, @TP@users
				WHERE
					@TP@users.Username = @TP@accounting.Username
				AND
					@TP@users.ID = ".DBQuote($params[0])."
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
		$acctInput = 0;
		if (isset($row->acctinput) && $row->acctinput > 0) {
			$acctInput += $row->acctinput;
		}
		# Output
		$acctOutput = 0;
		if (isset($row->acctoutput) && $row->acctoutput > 0) {
			$acctOutput += $row->acctoutput;
		}

		# Uptime
		$acctSessionTime = 0;
		if (isset($row->acctsessiontime) && $row->acctsessiontime > 0) {
			$acctSessionTime += $row->acctsessiontime;
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
		$item['AcctInput'] = $acctInput;
		$item['AcctOutput'] = $acctOutput;
		$item['AcctSessionTime'] = (int)$acctSessionTime;
		$item['ConnectTermReason'] = strRadiusTermCode($row->acctterminatecause);

		# Push this row onto main array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# vim: ts=4
