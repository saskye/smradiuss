<?php
# Admin Client Attributes functions
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

# Add client attribute
function addAdminClientAttribute($params) {

	# Perform query
	$res = DBDo("
				INSERT INTO 
						@TP@client_attributes (ClientID,Name,Operator,Value,Disabled) 
				VALUES 
						(?,?,?,?,?)",
				array(	$params[0]['ClientID'],
						$params[0]['Name'],
						$params[0]['Operator'],
						$params[0]['Value'],
						$params[0]['Disabled'])
	);

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Remove client attribute
function removeAdminClientAttribute($params) {

	# Perform query
	$res = DBDo("DELETE FROM @TP@client_attributes WHERE ID = ?",array($params[0]));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit client attribute
function updateAdminClientAttribute($params) {

	# Perform query
	$res = DBDo("UPDATE @TP@client_attributes SET Name = ?, Operator = ?, Value = ?, Disabled = ? WHERE ID = ?",
				array($params[0]['Name'],
				$params[0]['Operator'],
				$params[0]['Value'],
				$params[0]['Disabled'],
				$params[0]['ID'])
	);

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Return specific attribute row
function getAdminClientAttribute($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Name, Operator, Value, Disabled FROM @TP@client_attributes WHERE ID = ?",array($params[0]));

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
function getAdminClientAttributes($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@client_attributes.ID',
		'Name' => '@TP@client_attributes.Name',
		'Operator' => '@TP@client_attributes.Operator',
		'Value' => '@TP@client_attributes.Value',
		'Disabled' => '@TP@client_attributes.Disabled'
	);

	# Perform query
	$res = DBSelectSearch("
			SELECT 
				ID, Name, Operator, Value, Disabled 
			FROM 
				@TP@client_attributes 
			WHERE 
				ClientID = ".DBQuote($params[0])."
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
