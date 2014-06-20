<?php
/**
 * Wisp Users Topup Model
 *
 */
 
class WispUsersTopup extends AppModel 
{
	public $useTable = 'topups';

	//Validating form controllers.
	public $validate = array('Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value'),'numeric' => array('rule' => 'naturalNumber','required' => true,'message'=> 'numbers only')), 'Type' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please select value')));
	
	//Insert record in topups table.														   
	public function insertRec($userId, $data)
	{
		$timestamp = date("Y-m-d H:i:s");
		$res = $this->query("INSERT INTO topups (UserID,Timestamp,Type,Value,ValidFrom,ValidTo) VALUES (?,?,?,?,?,?)",array($userId,$timestamp,$data['WispUsersTopup']['Type'],$data['WispUsersTopup']['Value'],$data['WispUsersTopup']['valid_from'], $data['WispUsersTopup']['valid_to']));
	}
	
	//Update topups table.
	public function editRec($id, $data)
	{
		$res = $this->query("UPDATE topups SET `Type` = '".$data['WispUsersTopup']['Type']."',`Value` = '".$data['WispUsersTopup']['Value']."',`ValidFrom` = '".$data['WispUsersTopup']['valid_from']."',`ValidTo` = '".$data['WispUsersTopup']['valid_to']."' where `ID` = ".$id);
	}
}