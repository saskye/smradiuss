<?php
class GroupMember extends AppModel
{
	public $useTable = 'users_to_groups';
	
	public function getUserNameById($userId)
	{
		return $res = $this->query("select Username from users where ID = ".$userId);
	}
}