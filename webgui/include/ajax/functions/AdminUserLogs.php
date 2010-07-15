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
				Name,
				Value
			FROM
				@TP@user_attributes
			WHERE
				UserID = ?",
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
			@TP@topups.ID,
			@TP@topups.Type,
			@TP@topups.Value,
			@TP@topups.ValidTo
		FROM
			@TP@topups_summary,
			@TP@topups
		WHERE
			@TP@topups_summary.TopupID = @TP@topups.ID
			AND @TP@topups.UserID = ?
			AND @TP@topups_summary.PeriodKey = ?
			AND @TP@topups_summary.Depleted = 0
		ORDER BY
			@TP@topups.Timestamp ASC",
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
		$topups[$i]['ID'] = $row->id;
		$topups[$i]['ValidTo'] = $row->validto;
		$topups[$i]['Type'] = $row->type;
		$topups[$i]['CurrentLimit'] = $row->balance;
		$topups[$i]['Limit'] = $row->value;
		$i++;
	}

	# Fetch user uptime and traffic topups
	$periodKeyEnd = new DateTime($periodKey->format('Y-m-d'));
	$periodKeyEnd->modify("+1 month");
	$res = DBSelect("
		SELECT
			ID, Value, Type, ValidTo
		FROM
			@TP@topups
		WHERE
			UserID = ?
			AND ValidFrom = ?
			AND ValidTo >= ?
			AND Depleted = 0
		ORDER BY
			Timestamp ASC",
			array($params[0]['ID'],$periodKey->format('Y-m-d'),$periodKeyEnd->format('Y-m-d'))
	);

	# Return if error
	if (!is_object($res)) {
		return $res;
	}

	# Store normal topups
	while ($row = $res->fetchObject()) {
		$topups[$i] = array();
		$topups[$i]['ID'] = $row->id;
		$topups[$i]['ValidTo'] = $row->validto;
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

	# Excess usage
	$excessTraffic = 0;
	if ($trafficCap == -1) {
		$excessTraffic = $resultArray['trafficUsage'];
	} else {
		$excessTraffic = $resultArray['trafficUsage'] > $trafficCap ? ($resultArray['trafficUsage'] - $trafficCap) : 0;
	}
	$excessUptime = 0;
	if ($uptimeCap == -1) {
		$excessUptime = $resultArray['uptimeUsage'];
	} else {
		$excessUptime = $resultArray['uptimeUsage'] > $uptimeCap ? ($resultArray['uptimeUsage'] - $uptimeCap) : 0;
	}

	# Loop through topups and add to return array
	$resultArray['trafficTopups'] = 0;
	$resultArray['uptimeTopups'] = 0;
	$resultArray['TotalTrafficTopups'] = 0;
	$resultArray['TotalUptimeTopups'] = 0;
	# Traffic and uptime topups
	$resultArray['AllTrafficTopups'] = array();
	$resultArray['AllUptimeTopups'] = array();
	$t = 0; $u = 0;
	foreach ($topups as $topupItem) {
		if ($topupItem['Type'] == 1) {
			# Topup not currently in use
			if ($excessTraffic <= 0) {

				# Add stats to topup array
				$resultArray['AllTrafficTopups'][$t] = array();
				$resultArray['AllTrafficTopups'][$t]['Used'] = isset($topupItem['CurrentLimit']) ? ($topupItem['Limit'] - $topupItem['CurrentLimit']) : 0;
				$resultArray['AllTrafficTopups'][$t]['Cap'] = (int)$topupItem['Limit'];
				$resultArray['AllTrafficTopups'][$t]['ID'] = $topupItem['ID'];
				$resultArray['AllTrafficTopups'][$t]['ValidTo'] = $topupItem['ValidTo'];
				$t++;

				# Set total available topups
				$resultArray['trafficTopups'] += isset($topupItem['CurrentLimit']) ? $topupItem['CurrentLimit'] : $topupItem['Limit'];
				$resultArray['TotalTrafficTopups'] += $topupItem['Limit'];

			# Topup currently in use
			} elseif (!isset($topupItem['CurrentLimit']) && $excessTraffic < $topupItem['Limit']) {

				# Add stats to topup array
				$resultArray['AllTrafficTopups'][$t] = array();
				$resultArray['AllTrafficTopups'][$t]['Used'] = $excessTraffic;
				$resultArray['AllTrafficTopups'][$t]['Cap'] = (int)$topupItem['Limit'];
				$resultArray['AllTrafficTopups'][$t]['ID'] = $topupItem['ID'];
				$resultArray['AllTrafficTopups'][$t]['ValidTo'] = $topupItem['ValidTo'];
				$t++;

				# Set total available topups
				$resultArray['trafficTopups'] += $topupItem['Limit'];
				$resultArray['TotalTrafficTopups'] += $topupItem['Limit'];

				# If we hit this topup then all the rest of them are available
				$excessTraffic = 0;
			} elseif (isset($topupItem['CurrentLimit']) && $excessTraffic < $topupItem['CurrentLimit']) {

				# Add stats to topup array
				$resultArray['AllTrafficTopups'][$t] = array();
				$resultArray['AllTrafficTopups'][$t]['Used'] = ($topupItem['Limit'] - $topupItem['CurrentLimit']) + $excessTraffic;
				$resultArray['AllTrafficTopups'][$t]['Cap'] = (int)$topupItem['Limit'];
				$resultArray['AllTrafficTopups'][$t]['ID'] = $topupItem['ID'];
				$resultArray['AllTrafficTopups'][$t]['ValidTo'] = $topupItem['ValidTo'];
				$t++;

				# Set total available topups
				$resultArray['trafficTopups'] += $topupItem['CurrentLimit'];
				$resultArray['TotalTrafficTopups'] += $topupItem['Limit'];

				# If we hit this topup then all the rest of them are available
				$excessTraffic = 0;
			# Topup has been used up
			} else {

				# Add stats to topup array
				$resultArray['AllTrafficTopups'][$t] = array();
				$resultArray['AllTrafficTopups'][$t]['Used'] = (int)$topupItem['Limit'];
				$resultArray['AllTrafficTopups'][$t]['Cap'] = (int)$topupItem['Limit'];
				$resultArray['AllTrafficTopups'][$t]['ID'] = $topupItem['ID'];
				$resultArray['AllTrafficTopups'][$t]['ValidTo'] = $topupItem['ValidTo'];
				$t++;

				$resultArray['trafficTopups'] += isset($topupItem['CurrentLimit']) ? $topupItem['CurrentLimit'] : $topupItem['Limit'];
				$resultArray['TotalTrafficTopups'] += $topupItem['Limit'];

				# Subtract this topup from excessTraffic usage
				$excessTraffic -= isset($topupItem['CurrentLimit']) ? $topupItem['CurrentLimit'] : $topupItem['Limit'];
			}
		}
		if ($topupItem['Type'] == 2) {
			# Topup not currently in use
			if ($excessUptime <= 0) {

				# Add stats to topup array
				$resultArray['AllUptimeTopups'][$u] = array();
				$resultArray['AllUptimeTopups'][$u]['Used'] = isset($topupItem['CurrentLimit']) ? ($topupItem['Limit'] - $topupItem['CurrentLimit']) : 0;
				$resultArray['AllUptimeTopups'][$u]['Cap'] = (int)$topupItem['Limit'];
				$resultArray['AllUptimeTopups'][$u]['ID'] = $topupItem['ID'];
				$resultArray['AllUptimeTopups'][$u]['ValidTo'] = $topupItem['ValidTo'];
				$u++;

				# Set total available topups
				$resultArray['uptimeTopups'] += isset($topupItem['CurrentLimit']) ? $topupItem['CurrentLimit'] : $topupItem['Limit'];
				$resultArray['TotalUptimeTopups'] += $topupItem['Limit'];

			# Topup currently in use
			} elseif (!isset($topupItem['CurrentLimit']) && $excessUptime < $topupItem['Limit']) {

				# Add stats to topup array
				$resultArray['AllUptimeTopups'][$u] = array();
				$resultArray['AllUptimeTopups'][$u]['Used'] = $excessTraffic;
				$resultArray['AllUptimeTopups'][$u]['Cap'] = (int)$topupItem['Limit'];
				$resultArray['AllUptimeTopups'][$u]['ID'] = $topupItem['ID'];
				$resultArray['AllUptimeTopups'][$u]['ValidTo'] = $topupItem['ValidTo'];
				$u++;

				# Set total available topups
				$resultArray['uptimeTopups'] += $topupItem['Limit'];
				$resultArray['TotalUptimeTopups'] += $topupItem['Limit'];

				# If we hit this topup then all the rest of them are available
				$excessUptime = 0;
			} elseif (isset($topupItem['CurrentLimit']) && $excessUptime < $topupItem['CurrentLimit']) {

				# Add stats to topup array
				$resultArray['AllUptimeTopups'][$u] = array();
				$resultArray['AllUptimeTopups'][$u]['Used'] = ($topupItem['Limit'] - $topupItem['CurrentLimit']) + $excessTraffic;
				$resultArray['AllUptimeTopups'][$u]['Cap'] = (int)$topupItem['Limit'];
				$resultArray['AllUptimeTopups'][$u]['ID'] = $topupItem['ID'];
				$resultArray['AllUptimeTopups'][$u]['ValidTo'] = $topupItem['ValidTo'];
				$u++;

				# Set total available topups
				$resultArray['uptimeTopups'] += $topupItem['CurrentLimit'];
				$resultArray['TotalUptimeTopups'] += $topupItem['Limit'];

				# If we hit this topup then all the rest of them are available
				$excessUptime = 0;
			# Topup has been used up
			} else {

				# Add stats to topup array
				$resultArray['AllUptimeTopups'][$u] = array();
				$resultArray['AllUptimeTopups'][$u]['Used'] = (int)$topupItem['Limit'];
				$resultArray['AllUptimeTopups'][$u]['Cap'] = (int)$topupItem['Limit'];
				$resultArray['AllUptimeTopups'][$u]['ID'] = $topupItem['ID'];
				$resultArray['AllUptimeTopups'][$u]['ValidTo'] = $topupItem['ValidTo'];
				$u++;

				$resultArray['uptimeTopups'] += isset($topupItem['CurrentLimit']) ? $topupItem['CurrentLimit'] : $topupItem['Limit'];
				$resultArray['TotalUptimeTopups'] += $topupItem['Limit'];

				# Subtract this topup from excessUptime usage
				$excessUptime -= isset($topupItem['CurrentLimit']) ? $topupItem['CurrentLimit'] : $topupItem['Limit'];
			}
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
