<?php
# Admin Group Attributes functions
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

# Add group attribute
function addAdminGroupAttribute($params) {

	# Check Disabled param
	$disabled = isBoolean($params[0]['Disabled']);
	if ($disabled < 0) {
		return NULL;
	}

	$res = DBDo("
				INSERT INTO 
						@TP@group_attributes (GroupID,Name,Operator,Value,Disabled) 
				VALUES 
						(?,?,?,?,?)",
				array(	$params[0]['GroupID'],
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

# Remove group attribute
function removeAdminGroupAttribute($params) {

	$res = DBDo("DELETE FROM @TP@group_attributes WHERE ID = ?",array($params[0]));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit group attribute
function updateAdminGroupAttribute($params) {

	# Check Disabled param
	$disabled = isBoolean($params[0]['Disabled']);
	if ($disabled < 0) {
		return NULL;
	}

	$res = DBDo("UPDATE @TP@group_attributes SET Name = ?, Operator = ?, Value = ?, Disabled = ? WHERE ID = ?",
				array($params[0]['Name'],
				$params[0]['Operator'],
				$params[0]['Value'],
				$disabled,
				$params[0]['ID'])
	);

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Return specific attribute row
function getAdminGroupAttribute($params) {

	$res = DBSelect("SELECT ID, Name, Operator, Value, Disabled FROM @TP@group_attributes WHERE ID = ?",array($params[0]));
	if (!is_object($res)) {
		return $res;
	}

	$resultArray = array();

	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;
	$resultArray['Operator'] = $row->operator;
	$resultArray['Value'] = $row->value;
	$resultArray['Disabled'] = $row->disabled;

	return $resultArray;
}

# Return list of attributes
function getAdminGroupAttributes($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@group_attributes.ID',
		'Name' => '@TP@group_attributes.Name',
		'Operator' => '@TP@group_attributes.Operator',
		'Value' => '@TP@group_attributes.Value',
		'Disabled' => '@TP@group_attributes.Disabled'
	);

	# Fetch attributes
	$res = DBSelectSearch("
			SELECT 
				ID, Name, Operator, Value, Disabled 
			FROM 
				@TP@group_attributes 
			WHERE 
				GroupID = ".DBQuote($params[0])."
		",$params[1],$filtersorts,$filtersorts);

	$sth = $res[0]; $numResults = $res[1];
	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	$resultArray = array();

	# Loop through rows
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
