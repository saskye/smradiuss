<?php
# Admin Realms functions
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


# Return list of realms
function getAdminRealms($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@realms.ID',
		'Name' => '@TP@realms.Name',
		'Disabled' => '@TP@realms.Disabled'
	);

	# Perform query
	$res = DBSelectSearch("SELECT ID, Name, Disabled FROM @TP@realms",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];

	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	# Loop through rows
	$resultArray = array();
	while ($row = $sth->fetchObject()) {
		$item = array();

		$item['ID'] = $row->id;
		$item['Name'] = htmlspecialchars($row->name);
		$item['Disabled'] = $row->disabled;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# Return specific realm row
function getAdminRealm($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Name, Disabled FROM @TP@realms WHERE ID = ?",array($params[0]));

	# Return error if failed
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;
	$resultArray['Disabled'] = $row->disabled;

	# Return results
	return $resultArray;
}

# Remove admin realm
function removeAdminRealm($params) {

	# Begin transaction
	DBBegin();

	# Perform query
	$res = DBDo("DELETE FROM @TP@realm_attributes WHERE RealmID = ?",array($params[0]));

	# Perform next query if successful
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM @TP@realms WHERE ID = ?",array($params[0]));
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

# Add admin realm
function createAdminRealm($params) {

	# Perform query
	$res = DBDo("INSERT INTO @TP@realms (Name) VALUES (?)",array($params[0]['Name']));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit admin realm
function updateAdminRealm($params) {

	# Perform query
	$res = DBDo("UPDATE @TP@realms SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
