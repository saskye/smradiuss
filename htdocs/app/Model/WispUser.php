<?php
class WispUser extends AppModel
{
	public $useTable = 'wisp_userdata';
	
	
	public $validate = array('Username' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please choose a username'), 'unique' => array('rule' => array('uniqueCheck'), 'message' => 'The username you have chosen has already been registered'))/*,
	'Password' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please choose a username')),
	'FirstName' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please choose a username')),
	'LastName' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please choose a username')),
	'Phone' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please choose a username')),
	'Email' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please choose a username')),
	'Location' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please choose a username'))*/);
	
	public function uniqueCheck($Username, $UserID)
	{
		$res = $this->query("select count(ID) from users where Username = '".$Username['Username']."'");
		
		if ($res[0][0]['count(ID)'] >= 1)
		{
	  		return false; 
    	}
    	else
		{
	  		return true;
    	}
	}
	
	public function selectById($userId)
	{
		return $res = $this->query("select Username, Disabled from users where ID = ".$userId,false);
	}
	
	public function selectLocation()
	{
		 return $res = $this->query("select * from wisp_locations");
	}
	
	public function insertUsername($userName)
	{
		 $res = $this->query("insert into users (Username) values ('".$userName."')",false);
		return $userId = $this->query('select max(ID) as id FROM `users`',false);
		 
	}
	public function insertRec($data)
	{
		 $res = $this->query("insert into wisp_userdata (UserID, LocationID, FirstName, LastName, Email, Phone) values ('".$data['WispUser']['UserId']."' , '".$data['WispUser']['Location']."', '".$data['WispUser']['FirstName']."', '".$data['WispUser']['LastName']."', '".$data['WispUser']['Email']."', '".$data['WispUser']['Phone']."')");
	}
	
	public function updateRec($data, $userId)
	{
		$res = $this->query("update wisp_userdata set LocationID = '".$data['WispUser']['Location']."', FirstName = '".$data['WispUser']['FirstName']."', LastName = '".$data['WispUser']['LastName']."', Email = '".$data['WispUser']['Email']."', Phone = '".$data['WispUser']['Phone']."' where UserID = '".$userId."'");
	}
	
	public function addValue($userId, $attName, $attoperator, $password,$modifier)
	{
		//echo $userId.', '.$attName.', '.$attoperator.', '.$password; exit;
		 $res = $this->query("insert into user_attributes (UserID, Name, Operator, Value, Disabled, modifier) values ('".$userId."' , '".$attName."', '".$attoperator."', '".$password."', '0','".$modifier."')");
	}
	
	public function getValue($userId)
	{
		 return $res = $this->query("select Value from user_attributes where UserID = ".$userId);
	}
	
	public function updateUsername($userId, $userName)
	{
		$res = $this->query("update users set Username = '".$userName."' where ID = '".$userId."'");
	}
	
	public function updateValue($userId, $userValue)
	{
		$res = $this->query("update user_attributes set Value = '".$userValue."' where UserID = '".$userId."'");
	}
	
	public function fetchUserId($id)
	{
		return $res = $this->query("select UserID from wisp_userdata where ID = '".$id."'");
	}
	
	public function deleteUserAttributes($userId)
	{
		$res = $this->query("delete from user_attributes where UserID = '".$userId."'");
	}
	
	public function deleteUsers($userId)
	{
		$res = $this->query("delete from users where ID = '".$userId."'");
		$res = $this->query("delete from user_attributes where UserID = '".$userId."'");
		$res = $this->query("delete from users_to_groups where UserID = '".$userId."'");
		$res = $this->query("delete from topups where UserID = '".$userId."'");
	}
	
	public function getUserName($userName)
	{
		$res = $this->query("select Username from users where Username = '".$userName."'");
		
//		print_r($res);
		return count($res);
		
	}
	
	public function selectGroup()
	{
		return $res = $this->query("select ID, Name from groups");
	}
	
	// Select user group from user id 
	public function selectUserGroups($userId)
	{
		return  $res = $this->query("SELECT *,g.name FROM users_to_groups as utg , groups as g WHERE UserID = ".$userId." AND g.ID = utg.GroupID",false);
	}
	

	
	// Select user attributes from user id 
	public function selectUserAttributes($userId)
	{
		return  $res = $this->query("SELECT * FROM user_attributes WHERE UserID = ".$userId,false);
	}
	
	// -- add group --
	public function insertUserGroup($userId, $groupId)
	{
		//echo $userId.", ".$groupId;exit;
		//echo "insert into users_to_groups (UserID, GroupID, Disabled, Comment) values ('".$userId."' , '".$groupId."', '0', '')";exit;
		 $res = $this->query("insert into users_to_groups (UserID, GroupID, Disabled, Comment) values ('".$userId."' , '".$groupId."', '0', '')");
	}
	
	// -- delete group --
	public function deleteUserGroup($userId)
	{
		 $res = $this->query("delete from users_to_groups where UserID =".$userId);
	}
	
	// -- delete attributes --
	public function deleteUserAttibute($userId)
	{
		$res = $this->query("delete from user_attributes where UserID = ".$userId." AND Name!='User-Password'");
	}
}