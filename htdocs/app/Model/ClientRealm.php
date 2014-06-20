<?php
/**
 * Client Realm Model
 *
 */

class ClientRealm extends AppModel
{
	public $useTable = 'clients_to_realms';
	
	//Validating form controller.
	public $validate = array('Type' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please select value')));
	
	// Fetch realms for select box controler.
	public function selectRealms()
	{
		return $res = $this->query("select ID,Name from realms");
	}
	
	// Insert record in table.
	public function insertRec($clientID, $data)
	{
		$res = $this->query("INSERT INTO clients_to_realms (ClientID,RealmID) VALUES ('".$clientID."','".$data['ClientRealm']['Type']."')");
	}
	
	// Get realms name via realms id.
	public function getRealmsById($realmID)
	{
		return $res = $this->query("select Name from realms where ID = ".$realmID);
	}
}