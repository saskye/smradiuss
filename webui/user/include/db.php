<?php

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
		die("Error connecting to Policyd v2 DB: " . $e->getMessage());
	}

	return $dbh;
}

# Grab DB handle
$db = connect_db();

# vim: ts=4
?>
