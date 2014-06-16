<?php
class RealmMember extends AppModel
{
	public $useTable = 'clients_to_realms';
	
	public function getGroupById($clientID)
	{
		return $res = $this->query("select Name from clients where ID = ".$clientID);
	}
}