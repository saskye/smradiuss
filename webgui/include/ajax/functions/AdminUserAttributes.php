<?php
# Admin User Attributes functions
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
include_once("include/util.php");

# Add user attribute
function addAdminUserAttribute($params) {

	# Check Disabled param
	$disabled = isBoolean($params[0]['Disabled']);
	if ($disabled < 0) {
		return NULL;
	}

	# Perform query
	$res = DBDo("
				INSERT INTO 
						@TP@user_attributes (UserID,Name,Operator,Value,Disabled) 
				VALUES 
						(?,?,?,?,?)",
				array(	$params[0]['UserID'],
						$params[0]['Name'],
						$params[0]['Operator'],
						$params[0]['Value'],
						$disabled)
	);

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Remove user attribute
function removeAdminUserAttribute($params) {

	# Perform query
	$res = DBDo("DELETE FROM @TP@user_attributes WHERE ID = ?",array($params[0]));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit attribute
function updateAdminUserAttribute($params) {

	# Check Disabled param
	$disabled = isBoolean($params[0]['Disabled']);
	if ($disabled < 0) {
		return NULL;
	}

	# Perform query
	$res = DBDo("UPDATE @TP@user_attributes SET Name = ?, Operator = ?, Value = ?, Disabled = ? WHERE ID = ?",
				array($params[0]['Name'],
				$params[0]['Operator'],
				$params[0]['Value'],
				$disabled,
				$params[0]['ID'])
	);

	# Return error
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Return specific attribute row
function getAdminUserAttribute($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Name, Operator, Value, Disabled FROM @TP@user_attributes WHERE ID = ?",array($params[0]));

	# Return error if failed
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;
	$resultArray['Operator'] = $row->operator;
	$resultArray['Value'] = $row->value;
	$resultArray['Disabled'] = $row->disabled;

	# Return results
	return $resultArray;
}

# Return list of attributes
function getAdminUserAttributes($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@user_attributes.ID',
		'Name' => '@TP@user_attributes.Name',
		'Operator' => '@TP@user_attributes.Operator',
		'Value' => '@TP@user_attributes.Value',
		'Disabled' => '@TP@user_attributes.Disabled'
	);

	# Perform query
	$res = DBSelectSearch("
			SELECT 
				ID, Name, Operator, Value, Disabled 
			FROM 
				@TP@user_attributes 
			WHERE 
				UserID = ".DBQuote($params[0])."
		",$params[1],$filtersorts,$filtersorts);
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
		$item['Name'] = $row->name;
		$item['Operator'] = $row->operator;
		$item['Value'] = $row->value;
		$item['Disabled'] = $row->disabled;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# vim: ts=4
