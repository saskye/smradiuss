<?php
/**
 * Realm Member Model
 *
 */
 
class RealmMember extends AppModel
{
	public $useTable = 'clients_to_realms';
	
	// Fetch client name via its id.
	public function getClientNameById($clientID)
	{
		return $res = $this->query("select Name from clients where ID = ".$clientID);
	}
}