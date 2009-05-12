<?php
# Database Interface
# Copyright (C) 2007-2009, AllWorldIT
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

require_once('include/config.php');


# Connect to DB
function connect_db()
{
	global $DB_DSN;
	global $DB_USER;
	global $DB_PASS;

	try {
		$dbh = new PDO($DB_DSN, $DB_USER, $DB_PASS, array(
			PDO::ATTR_PERSISTENT => false
		));

		$dbh->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);

	} catch (PDOException $e) {
		die("Error connecting to SMRadius DB: " . $e->getMessage());
	}

	return $dbh;
}


# Connect to postfix DB
function connect_postfix_db()
{
	global $DB_POSTFIX_DSN;
	global $DB_POSTFIX_USER;
	global $DB_POSTFIX_PASS;

	try {
		$dbh = new PDO($DB_POSTFIX_DSN, $DB_POSTFIX_USER, $DB_POSTFIX_PASS, array(
			PDO::ATTR_PERSISTENT => false
		));

		$dbh->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);

	} catch (PDOException $e) {
		die("Error connecting to Postfix DB: " . $e->getMessage());
	}

	return $dbh;
}


## @fn DBSelect($query,$args)
# Return database selection results...
#
# @param query Query to run
# @param args Array of arguments we substitute in ?'s place
#
# @return DBI statement handle, undef on error
function DBSelect($query,$args = array())
{
	global $db;

	# Try prepare, and catch exceptions
	try {
		$stmt = $db->prepare($query);

	} catch (PDOException $e) {
		return $e->getMessage();

	}

	# Execute query
	$res = $stmt->execute($args);
	if ($res === FALSE) {
		return $stmt->errorInfo();
	}

	return $stmt;
}


## @fn DBDo($query,$args)
# Perform a database command
#
# @param command Command to execute in database
# @param args Arguments to quote in the command string
#
# @return Number of results, undef on error
function DBDo($command,$args = array())
{
	global $db;

	# Try prepare, and catch exceptions
	try {
		$stmt = $db->prepare($command);

	} catch (PDOException $e) {
		return $e->getMessage();

	}

	# Execute query
	$res = $stmt->execute($args);
	if ($res === FALSE) {
		return $stmt->errorInfo();
	}

	return $res;
}

## @fn DBSelectNumResults($query,$args)
# Return how many results came up from the specific SELECT query
#
# @param query Query to perform, minus "SELECT COUNT(*) AS num_results"
# @param args Arguments to quote in the query string
#
# @return Number of results, undef on error
function DBSelectNumResults($query,$args = array())
{
	global $db;


	$res = DBSelect("SELECT COUNT(*) AS num_results $query",$args);
	if (!is_object($res)) {
		return $res;
	}

	# Grab row
	$row = $res->fetchObject();

	# Pull number
	$num_results = $row->num_results;

	return $num_results;
}



## @fn DBSelectSearch($query,$search,$filters,$sorts)
# Select results from database and return the total number aswell
#
# @param query Base query
#
# @param search Search array
# @li Filter - Filter based on this...
# [filter] => Array ( 
#	[0] => Array ( 
#		[field] => Name 
#		[data] => Array ( 
#			[type] => string 
#			[value] => hi there 
#		) 
#	)
# )
# { 'data' => { 'comparison' => 'gt', 'value' => '5', 'type' => 'numeric' }, 'field' => 'ID' }
# @li Start - Start item number, indexed from 0 onwards
# @li Limit - Limit number of results
# @li Sort - Sort by this item
# @li SortDirection - Sort in this direction, either ASC or DESC 
#
# @param filters Filter array ref
# Hash:  'Column' -> 'Table.DBColumn'
#
# @param sorts Hash ref of valid sort criteria, indexed by what we get, pointing to the DB column in the query
# Hash:  'Column' -> 'Table.DBColumn'
#
# @return Number of results, undef on error
function DBSelectSearch($query,$search,$filters,$sorts) {
	global $db;

	# Stuff we need to add to the SQL query
	$where = array(); # Where clauses
	$sqlWhere = "";
	$sqlLimit = "";
	$sqlOffset = "";
	$sqlOrderBy = "";
	$sqlOrderByDirection = "";

	# Check if we're searching
	if (isset($search)) {
		# Check it is a array
		if (gettype($search) != "array") {
			return array(NULL,"Parameter 'search' is not a array");
		}
		# Check if we need to filter
		if (isset($search['Filter']) && !empty($search['Filter'])) {
			# We need filters in order to use filtering
			if (!isset($filters)) {
				return array(NULL,"Parameter 'search' element 'Filter' requires 'filters' to be defined");
			}

			# Check type of Filter
			if (isset($search['Filter']) != "array") {
				return array(NULL,"Parameter 'search' element 'Filter' is of invalid type, it must be an array'");
			}

			# Loop with filters
			foreach ($search['Filter'] as $item) {
				$data = $item['data'];  # value, type, comparison
				$field = $item['field'];

				# Check if field is in our allowed filters
				if (!isset($filters[$field])) {
					return array(NULL,"Parameter 'search' element 'Filter' has invalid field item '$field' according to 'filters'"); 
				}
				$column = $filters[$field];

				# Check data
				if (!isset($data['type'])) {
					return array(NULL,"Parameter 'search' element 'Filter' requires field data element 'type' for field '$field'"); 
				}
				if (!isset($data['value'])) {
					return array(NULL,"Parameter 'search' element 'Filter' requires field data element 'value' for field '$field'"); 
				}

				# match =, LIKE, IN (
				# matchEnd '' or )
				$match;
				$matchEnd = "";
				# value is the $db->quote()'d value
				$value;

				# Check what type of comparison
				if ($data['type'] == "boolean") {
					$match = '=';
					$value = $db->quote($data['value']);


				} elseif ($data['type'] == "date") {

					# The comparison type must be defined
					if (!isset($data['comparison'])) {
						return array(NULL,"Parameter 'search' element 'Filter' requires field data element 'comparison' for date field '$field'"); 
					}

					# Check comparison type
					if ($data['comparison'] == "gt") {
						$match = ">";

					} elseif ($data['comparison'] == "lt") {
						$match = "<";
					
					} elseif ($data['comparison'] == "eq") {
						$match = "=";
					}
					# Convert to ISO format	
					# FIXME
#					$unixtime = str2time($data['value']);
#					$date = DateTime->from_epoch( epoch => $unixtime );
#					$value = $db->quote($date->ymd());


				} elseif ($data['type'] == "list") {
					# Quote all values
					$valueList = array();
					foreach (explode(",",$data['value']) as $i) {
						array_push($valueList,$db->quote($i));
					}

					$match = "IN (";
					# Join up 'xx','yy','zz'
					$value = implode(',',$valueList);
					$matchEnd = ")";


				} elseif ($data['type'] == "numeric") {

					# The comparison type must be defined
					if (!isset($data['comparison'])) {
						return array(NULL,"Parameter 'search' element 'Filter' requires field data element 'comparison' for numeric field '$field'"); 
					}

					# Check comparison type
					if ($data['comparison'] == "gt") {
						$match = ">";

					} elseif ($data['comparison'] == "lt") {
						$match = "<";
					
					} elseif ($data['comparison'] == "eq") {
						$match = "=";
					}
					
					$value = $db->quote($data['value']);


				} elseif ($data['type'] == "string") {
					$match = "LIKE";
					$value = $db->quote("%".$data['value']."%");

				}

				# Add to list
				array_push($where,"$column $match $value $matchEnd");
			}

			# Check if we have any WHERE clauses to add ...
			if (count($where) > 0) {
				# Check if we have WHERE clauses in the query
				if (preg_match("/\sWHERE\s/i",$query)) {
					# If so start off with AND
					$sqlWhere .= "AND ";
				} else {
					$sqlWhere = "WHERE ";
				}
				$sqlWhere .= implode(" AND ",$where);
			}
		}

		# Check if we starting at an OFFSET
		if (isset($search['Start'])) {
			# Check if Start is valid
			if (!is_numeric($search['Start']) || $search['Start'] < 0) {
				return array(NULL,"Parameter 'search' element 'Start' invalid value '".$search['Start']."'"); 
			}

			$sqlOffset = sprintf("OFFSET %d",$search['Start']);
		}

		# Check if results will be LIMIT'd
		if (isset($search['Limit'])) {
			# Check if Limit is valid
			if (!is_numeric($search['Limit']) || $search['Limit'] < 1) {
				return array(NULL,"Parameter 'search' element 'Limit' invalid value '".$search['Limit']."'"); 
			}

			$sqlLimit = sprintf("LIMIT %d",$search['Limit']);
		}

		# Check if we going to be sorting
		if (isset($search['Sort']) && !empty($search['Sort'])) {
			# We need sorts in order to use sorting
			if (!isset($sorts)) {
				return array(NULL,"Parameter 'search' element 'Filter' requires 'filters' to be defined");
			}

			# Check if sort is defined
			if (!isset($sorts[$search['Sort']])) {
				return array(NULL,"Parameter 'search' element 'Sort' invalid item '".$search['Sort']."' according to 'sorts'"); 
			}

			# Build ORDER By
			$sqlOrderBy = "ORDER BY ".$sorts[$search['Sort']];

			# Check for sort ORDER
			if (isset($search['SortDirection']) && !empty($search['SortDirection'])) {

				# Check for valid directions
				if (strtolower($search['SortDirection']) == "asc") {
					$sqlOrderByDirection = "ASC";

				} elseif (strtolower($search['SortDirection']) == "desc") {
					$sqlOrderByDirection = "DESC";

				} else {
					return array(NULL,"Parameter 'search' element 'SortDirection' invalid value '".$search['SortDirection']."'"); 
				}
			}
		}
	}

	# Select row count, pull out   "SELECT .... "  as we replace this in the NumResults query
	$queryCount = $query; $queryCount = preg_replace("/^\s*SELECT\s.*\sFROM/is","FROM",$queryCount);
	$numResults = DBSelectNumResults("$queryCount $sqlWhere");
	if (!isset($numResults)) {
		return array(NULL,"Backend database query 1 failed");
	}

	# Add Start, Limit, Sort, Direction
	$sth = DBSelect("$query $sqlWhere $sqlOrderBy $sqlOrderByDirection $sqlLimit $sqlOffset");
	if (!isset($sth)) {
		return array(NULL,"Backend database query 2 failed");
	}

	return array($sth,$numResults);
}


## @fn DBLastInsertID($table,$column)
# Function to get last insert id
#
# @param table Table to check
# @param column Column to get last insert on
#
# @return Last insert ID or undef on error
function DBLastInsertID()
{
	global $db;

	# TODO: Implement $table nad $column??
	$res = $db->lastInsertID();

	return $res;
}


# Function to begin a transaction
# Args: none
function DBBegin()
{
	global $db;

	$res = $db->beginTransaction();

	return $res;
}


# Function to commit a transaction
# Args: none
function DBCommit()
{
	global $db;

	$res = $db->commit();

	return $res;
}


# Function to rollback a transaction
# Args: none
function DBRollback()
{
	global $db;

	$res = $db->rollback();

	return $res;
}

# Connet to database when we load this file
$db = connect_db();


# vim: ts=4
