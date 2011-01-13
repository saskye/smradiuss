<?php
# Admin Realm Members functions
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

# Remove realm member
function removeAdminRealmMember($params) {

	$res = DBDo("DELETE FROM @TP@clients_to_realms WHERE ID = ?",array($params[0]));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Return list of members
function getAdminRealmMembers($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@clients_to_realms.ID',
		'Name' => '@TP@realm_attributes.Name'
	);

	# Fetch members
	$res = DBSelectSearch("
			SELECT 
				@TP@clients_to_realms.ID, @TP@clients.Name
			FROM 
				@TP@clients_to_realms, @TP@clients
			WHERE 
				@TP@clients.ID = @TP@clients_to_realms.ClientID
			AND
				@TP@clients_to_realms.RealmID = ".DBQuote($params[0])."
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

		# Push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

# vim: ts=4
