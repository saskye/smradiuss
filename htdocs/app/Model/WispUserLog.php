<?php
/**
 * Wisp User Log Model
 *
 */

class WispUserLog extends AppModel
{
	//Validating form controllers.
	public $validate = array('Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value'),'numeric' => array('rule' => 'naturalNumber','required' => true,'message'=> 'numbers only')));

	public $useTable = 'accounting';

	//Fetching records form table.
	public function SelectRec($userId, $data)
	{
		return $userLog = $this->query("select * from topups where ValidFrom = '".$data."' and UserID = '".$userId."'");
	}

	//Fetching username.
	public function SelectAcc($userId)
	{
		return $userLog = $this->query("select Username from users where ID = '".$userId."'");
	}
}