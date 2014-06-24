<?php
/**
 * User Topup Model
 *
 */

class UserTopup extends AppModel
{
	//Validating form controllers.
	public $validate = array('Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value'),'numeric' => array('rule' => 'naturalNumber','required' => true,'message'=> 'numbers only')));

	public $useTable = 'topups';



	//Insert record in topups table.
	public function insertRec($userId, $data)
	{
		$timestamp = date("Y-m-d H:i:s");
		$res = $this->query("INSERT INTO topups (UserID,Timestamp,Type,Value,ValidFrom,ValidTo) VALUES (?,?,?,?,?,?)",array($userId,$timestamp,$data['UserTopup']['Type'],$data['UserTopup']['Value'],$data['UserTopup']['valid_from'], $data['UserTopup']['valid_to']));
	}



	//Update topups table.
	public function editRec($id, $data)
	{
		$res = $this->query("UPDATE topups SET `Type` = '".$data['UserTopup']['Type']."',`Value` = '".$data['UserTopup']['Value']."',`ValidFrom` = '".$data['UserTopup']['valid_from']."',`ValidTo` = '".$data['UserTopup']['valid_to']."' where `ID` = ".$id);
	}



}



// vim: ts=4
