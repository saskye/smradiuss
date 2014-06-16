<?php
class ClientRealm extends AppModel
{
	public $useTable = 'clients_to_realms';
	
	public $validate = array('Type' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please select value')));
	
	public function selectGroup()
	{
		return $res = $this->query("select ID,Name from realms");
	}
	
	public function insertRec($clientID, $data)
	{
		$res = $this->query("INSERT INTO clients_to_realms (ClientID,RealmID) VALUES ('".$clientID."','".$data['ClientRealm']['Type']."')");
	}
	
	public function getGroupById($realmID)
	{
		return $res = $this->query("select Name from realms where ID = ".$realmID);
	}
}