<?php
/**
 * Group Member Model
 *
 */
 
class GroupMember extends AppModel
{
	public $useTable = 'users_to_groups';
	
	// Fetch usernname via its id.
	public function getUserNameById($userId)
	{
		return $res = $this->query("select Username from users where ID = ".$userId);
	}
}