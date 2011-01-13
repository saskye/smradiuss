<?php
# WiSP Locations functions
# Copyright (C) 2007-2011, AllWorldIT
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

include_once("include/db.php");


# Return list of locations
function getWiSPLocations($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@wisp_locations.ID',
		'Name' => '@TP@wisp_locations.Name'
	);

	# Perform query
	$res = DBSelectSearch("SELECT ID, Name FROM @TP@wisp_locations",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];

	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	# Loop through rows
	$resultArray = array();
	while ($row = $sth->fetchObject()) {

		# Build array for this row
		$item = array();

		$item['ID'] = $row->id;
		$item['Name'] = $row->name;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# Return specific location row
function getWiSPLocation($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Name FROM @TP@wisp_locations WHERE ID = ?",array($params[0]));

	# Return if error or nothing to return
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;

	# Return results
	return $resultArray;
}

# Remove wisp location
function removeWiSPLocation($params) {

	# Begin transaction
	DBBegin();

	# Unlink users from this location
	$res = DBDo("UPDATE @TP@wisp_userdata SET LocationID = NULL WHERE LocationID = ?",array($params[0]));

	# Delete location
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM @TP@wisp_locations WHERE ID = ?",array($params[0]));
	}

	# Return result
	if ($res !== TRUE) {
		DBRollback();
		return $res;
	} else {
		DBCommit();
	}

	return NULL;
}

# Add wisp location
function createWiSPLocation($params) {

	# Perform query
	$res = DBDo("INSERT INTO @TP@wisp_locations (Name) VALUES (?)",array($params[0]['Name']));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit wisp location
function updateWiSPLocation($params) {

	# Perform query
	$res = DBDo("UPDATE @TP@wisp_locations SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
