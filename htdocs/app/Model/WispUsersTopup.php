<?php
class WispUsersTopup extends AppModel 
{
	public $useTable = 'topups';
	
	public $validate = array('Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value'),'numeric' => array('rule' => 'naturalNumber','required' => true,'message'=> 'numbers only')), 'Type' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please select value')));
															   
	public function insertRec($userId, $data)
	{
		//echo "<pre>";print_r($data);exit;
		$timestamp = date("Y-m-d H:i:s");
		$res = $this->query("INSERT INTO topups (UserID,Timestamp,Type,Value,ValidFrom,ValidTo) VALUES (?,?,?,?,?,?)",array($userId,$timestamp,$data['WispUsersTopup']['Type'],$data['WispUsersTopup']['Value'],$data['WispUsersTopup']['valid_from'], $data['WispUsersTopup']['valid_to']));
	}
	
	public function editRec($id, $data)
	{
		//echo "<pre>";print_r($data.$id);exit;
		$res = $this->query("UPDATE topups SET `Type` = '".$data['WispUsersTopup']['Type']."',`Value` = '".$data['WispUsersTopup']['Value']."',`ValidFrom` = '".$data['WispUsersTopup']['valid_from']."',`ValidTo` = '".$data['WispUsersTopup']['valid_to']."' where `ID` = ".$id);
	}
}