<?php
class WispUserLog extends AppModel {
	public $validate = array('Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value'),'numeric' => array('rule'     => 'naturalNumber','required' => true,'message'=> 'numbers only')));
															   
	public $useTable = 'accounting';

	public function SelectRec($userId, $data)
	{
		return $userLog = $this->query("select * from topups where ValidFrom = '".$data."' and UserID = '".$userId."'");
	}
	
	public function SelectAcc($userId)
	{
		return $userLog = $this->query("select Username from users where ID = '".$userId."'");
	}
}