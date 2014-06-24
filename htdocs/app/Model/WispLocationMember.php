<?php
/**
 * Wisp Location Member Model
 *
 */
 
class WispLocationMember extends AppModel
{
	public $useTable = 'wisp_userdata';
	
	//Fetching username.
	public function selectUsername($userName)
	{
		return $res = $this->query("select Username from users where ID = '".$userName."'");
	}
	
	//Deleting record.
	public function deleteMembers($id)
	{
		$res = $this->query("update wisp_userdata set LocationID = '0' where ID = '".$id."'");
	}
}