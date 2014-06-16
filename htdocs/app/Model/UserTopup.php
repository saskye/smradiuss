<?php
class UserTopup extends AppModel {
	public $validate = array('Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value'),'numeric' => array('rule'     => 'naturalNumber','required' => true,'message'=> 'numbers only')));
															   
	public $useTable = 'topups';
	/*	public function getAllResults($userId)
	{
		$res = $this->query("SELECT
					accounting.ID,
					accounting.EventTimestamp,
					accounting.AcctStatusType,
					accounting.ServiceType,
					accounting.FramedProtocol,
					accounting.NASPortType,
					accounting.NASPortID,
					accounting.CallingStationID,
					accounting.CalledStationID,
					accounting.AcctSessionID,
					accounting.FramedIPAddress,
					accounting.AcctInputOctets / 1024 / 1024 +
					accounting.AcctInputGigawords * 4096 AS AcctInput,
					accounting.AcctOutputOctets / 1024 / 1024 +
					accounting.AcctOutputGigawords * 4096 AS AcctOutput,
					accounting.AcctTerminateCause,
					accounting.AcctSessionTime / 60 AS AcctSessionTime
				FROM
					accounting, users
				WHERE
					users.Username = accounting.Username
				AND
					users.ID = ".$userId);
		return $res;
	  }	 */

	public function insertRec($userId, $data)
	{
		$timestamp = date("Y-m-d H:i:s");
		$res = $this->query("INSERT INTO topups (UserID,Timestamp,Type,Value,ValidFrom,ValidTo) VALUES (?,?,?,?,?,?)",array($userId,$timestamp,$data['UserTopup']['Type'],$data['UserTopup']['Value'],$data['UserTopup']['valid_from'], $data['UserTopup']['valid_to']));
	}
	
	public function editRec($id, $data)
	{
		$res = $this->query("UPDATE topups SET `Type` = '".$data['UserTopup']['Type']."',`Value` = '".$data['UserTopup']['Value']."',`ValidFrom` = '".$data['UserTopup']['valid_from']."',`ValidTo` = '".$data['UserTopup']['valid_to']."' where `ID` = ".$id);
	}
}