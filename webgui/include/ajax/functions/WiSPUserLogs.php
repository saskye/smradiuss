<?php

include_once("include/db.php");


# Return user logs summary
function getWiSPUserLogsSummary($params) {

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

	# Array of results
	$resultArray = array();

	# Fetch uptime and traffic limits, if not  found, this is prepaid account.. use -1 as we need int
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

	# Add cap type / amount to result
	$resultArray['trafficCap'] = $trafficCap;
	$resultArray['uptimeCap'] = $uptimeCap;

	# Dates we want to use to search search
	$periodKey = new DateTime($params[0]['PeriodKey']);

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
			AND topups.Depleted = 0
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
	$res = DBSelect("
		SELECT
			Value, Type
		FROM
			topups
		WHERE
			topups.UserID = ?
			AND topups.ValidFrom = ?
			AND topups.ValidTo >= ?
			AND topups.Depleted = 0
		ORDER BY
			topups.Timestamp",
			array($params[0]['ID'],$periodKey->format('Y-m-d'),$periodKey->format('Y-m-d'))
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
		# Round up
		$totalUptime = ceil($totalUptime / 60);
	}

	# Set excess traffic usage
	$excessTraffic = 0;
	if (is_numeric($trafficCap) && $trafficCap > 0) {
		$excessTraffic += $totalTraffic - $trafficCap;
	} else {
		$excessTraffic += $totalTraffic;
	}

	# Set excess uptime usage
	$excessUptime = 0;
	if (is_numeric($uptimeCap) && $uptimeCap > 0) {
		$excessUptime += $totalUptime - $uptimeCap;
	} else {
		$excessUptime += $totalUptime;
	}

	$currentTrafficTopup = array();
	$topupTrafficRemaining = 0;
	# Loop through traffic topups and check for current topup, total topups not being used
	if (is_string($trafficCap) || $trafficCap != 0) {
		$i = 0;
		# User is using traffic from topups
		if ($excessTraffic > 0) {
			foreach ($topups as $topupItem) {
				if ($topupItem['Type'] == 1) {
					if ($excessTraffic <= 0) {
						$topupTrafficRemaining += $topupItem['Limit'];
						next($topupItem);
					} elseif ($excessTraffic >= $topupItem['Limit']) {
						$excessTraffic -= $topupItem['Limit'];
					} else {
						if (isset($topupItem['OriginalLimit'])) {
							$currentTrafficTopup['Cap'] = $topupItem['OriginalLimit'];
						} else {
							$currentTrafficTopup['Cap'] = $topupItem['Limit'];
						}
						$currentTrafficTopup['Used'] = $excessTraffic;
						$excessTraffic -= $topupItem['Limit'];
					}
				}
			}
		# User has not used traffic topups yet
		} else {
			foreach ($topups as $topupItem) {
				if ($topupItem['Type'] == 1) {
					if ($i == 0) {
						if (isset($topupItem['OriginalLimit'])) {
							$currentTrafficTopup['Cap'] = $topupItem['OriginalLimit'];
						} else {
							$currentTrafficTopup['Cap'] = $topupItem['Limit'];
						}
						$i = 1;
							$currentTrafficTopup['Used'] = 0;
					} else {
						$topupTrafficRemaining += $topupItem['Limit'];
					}
				}
			}
		}
	}

	$currentUptimeTopup = array();
	$topupUptimeRemaining = 0;
	# Loop through uptime topups and check for current topup, total topups not being used
	if (is_string($uptimeCap) || $uptimeCap != 0) {
		$i = 0;
		# User is using uptime from topups
		if ($excessUptime > 0) {
			foreach ($topups as $topupItem) {
				if ($topupItem['Type'] == 2) {
					if ($excessUptime <= 0) {
						$topupUptimeRemaining += $topupItem['Limit'];
						next($topupItem);
					} elseif ($excessUptime >= $topupItem['Limit']) {
						$excessUptime -= $topupItem['Limit'];
					} else {
						if (isset($topupItem['OriginalLimit'])) {
							$currentUptimeTopup['Cap'] = $topupItem['OriginalLimit'];
						} else {
							$currentUptimeTopup['Cap'] = $topupItem['Limit'];
						}
						$currentUptimeTopup['Used'] = $excessUptime;
						$excessUptime -= $topupItem['Limit'];
					}
				}
			}
		# User has not used uptime topups yet
		} else {
			foreach ($topups as $topupItem) {
				if ($topupItem['Type'] == 2) {
					if ($i == 0) {
						if (isset($topupItem['OriginalLimit'])) {
							$currentUptimeTopup['Cap'] = $topupItem['OriginalLimit'];
						} else {
							$currentUptimeTopup['Cap'] = $topupItem['Limit'];
						}
						$i = 1;
							$currentUptimeTopup['Used'] = 0;
					} else {
						$topupUptimeRemaining += $topupItem['Limit'];
					}
				}
			}
		}
	}

	# Traffic..
	$resultArray['trafficCurrentTopupUsed'] = -1;
	$resultArray['trafficCurrentTopupCap'] = -1;
	if (count($currentTrafficTopup) > 0) {
		$resultArray['trafficCurrentTopupUsed'] = $currentTrafficTopup['Used'];
		$resultArray['trafficCurrentTopupCap'] = (int)$currentTrafficTopup['Cap'];
	}
	$resultArray['trafficTopupRemaining'] = $topupTrafficRemaining;

	# Uptime..
	$resultArray['uptimeCurrentTopupUsed'] = -1;
	$resultArray['uptimeCurrentTopupCap'] = -1;
	if (count($currentUptimeTopup) > 0) {
		$resultArray['uptimeCurrentTopupUsed'] = $currentUptimeTopup['Used'];
		$resultArray['uptimeCurrentTopupCap'] = (int)$currentUptimeTopup['Cap'];
	}
	$resultArray['uptimeTopupRemaining'] = $topupUptimeRemaining;

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
