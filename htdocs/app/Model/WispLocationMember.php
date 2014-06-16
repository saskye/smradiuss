<?php
class WispLocationMember extends AppModel
{
	public $useTable = 'wisp_userdata';
	
	public function selectUsername($userName)
	{
		return $res = $this->query("select Username from users where ID = '".$userName."'");
	}
	
	public function deleteMembers($id)
	{
		//echo "update wisp_userdata set LocationID = '0' where ID = '".$id."'";exit;
		$res = $this->query("update wisp_userdata set LocationID = '0' where ID = '".$id."'");
	}
}