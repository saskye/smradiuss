<?php
# Admin Users functions
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


# Return list of users
function getAdminUsers($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@users.ID',
		'Username' => '@TP@users.Username',
		'Disabled' => '@TP@users.Disabled',
	);

	# Perform query
	$res = DBSelectSearch("SELECT ID, Username, Disabled FROM @TP@users",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];

	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	# Loop through rows
	$resultArray = array();
	while ($row = $sth->fetchObject()) {

		# Array for this row
		$item = array();

		$item['ID'] = $row->id;
		$item['Username'] = $row->username;
		$item['Disabled'] = $row->disabled;

		# Push this row onto main array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# Return specific user
function getAdminUser($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Username, Disabled FROM @TP@users WHERE ID = ?",array($params[0]));

	# Return error if failed
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Username'] = $row->username;
	$resultArray['Disabled'] = $row->disabled;

	# Return results
	return $resultArray;
}

# Remove admin user
function removeAdminUser($params) {

	# Begin transaction
	DBBegin();

	# Delete user information, if any
	$res = DBDo("DELETE FROM @TP@wisp_userdata WHERE UserID = ?",array($params[0]));

	# Delete user attribtues
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM @TP@user_attributes WHERE UserID = ?",array($params[0]));
	}

	# Remove user from groups
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM @TP@users_to_groups WHERE UserID = ?",array($params[0]));
	}

	# Get list of topups and delete summaries
	if ($res !== FALSE) {
		$topupList = array();
		$res = DBSelect("
			SELECT
				@TP@topups_summary.TopupID
			FROM
				@TP@topups_summary, @TP@topups
			WHERE
				@TP@topups_summary.TopupID = @TP@topups.ID
				AND @TP@topups.UserID = ?",
				array($params[0])
		);

		if (!is_object($res)) {
			$res = FALSE;
		} else {
			while ($row = $res->fetchObject()) {
				array_push($topupList,$row->topupid);
			}
			$res = TRUE;
		}

		if (sizeof($topupList) > 0 && $res !== FALSE) {
			# Remove topup summaries
			foreach ($topupList as $id) {
				if ($res !== FALSE) {
					$res = DBDo("
						DELETE FROM
							@TP@topups_summary
						WHERE
							TopupID = ?",
							array($id)
					);
				}
			}
		}
	}

	# Remove topups
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM @TP@topups WHERE UserID = ?",array($params[0]));
	}

	# Delete user
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM @TP@users WHERE ID = ?",array($params[0]));
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

# Add admin user
function createAdminUser($params) {

	# Perform query
	$res = DBDo("INSERT INTO @TP@users (Username) VALUES (?)",array($params[0]['Username']));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit admin user
function updateAdminUser($params) {

	# Perform query
	$res = DBDo("UPDATE @TP@users SET Username = ? WHERE ID = ?",array($params[0]['Username'],$params[0]['ID']));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
