<?php

include_once("include/db.php");


# Return list of attributes
function getAdminUserAttributes($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'user_attributes.ID',
		'Name' => 'user_attributes.Name',
		'Operator' => 'user_attributes.Operator',
		'Value' => 'user_attributes.Value',
		'Disabled' => 'user_attributes.Disabled'
	);

	$res = DBSelectSearch("SELECT ID, Name, Operator, Value, Disabled FROM user_attributes WHERE UserID = $params[0]",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];
	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	$resultArray = array();

	# loop through rows
	while ($row = $sth->fetchObject()) {
		$item = array();

		$item['ID'] = $row->id;
		$item['Name'] = $row->name;
		$item['Operator'] = $row->operator;
		$item['Value'] = $row->value;
		$item['Disabled'] = $row->disabled;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

?>
