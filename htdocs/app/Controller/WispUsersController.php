<?php
/* 
 * Wisp Users
 */
class WispUsersController extends AppController
{
	/* index function 
	 * 
	 */
	public function index()
	{
		$this->WispUser->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		$wispUser = $this->paginate();
		$wispUserData = array();

		foreach($wispUser as $wUsers)
		{
			$userData = $this->WispUser->selectById($wUsers['WispUser']['UserID']);
			$wUsers['WispUser']['Username'] = $userData[0]['users']['Username'];
			$wUsers['WispUser']['Disabled'] = $userData[0]['users']['Disabled'];
			$wispUserData[] = $wUsers;
		}
		$wispUser = $wispUserData;
		$this->set('wispUser', $wispUser);
	}
	
	/* add function 
	 * 
	 */
	public function add()
	{
		// -- for fetching group from table --
		$groupItems = $this->WispUser->selectGroup();
		foreach($groupItems as $val)
		{
			$grouparr[$val['groups']['ID']] = $val['groups']['Name'];
		}
		$this->set('grouparr', $grouparr);
		
		// -- for fetching location from table --
		$locationData = $this->WispUser->selectLocation();
		
		foreach($locationData as $loc)
		{
			$location[$loc['wisp_locations']['ID']] = $loc['wisp_locations']['Name'];
		}
		$this->set('location', $location);
		
		$userData[] = array();
		if ($this->request->is('post'))
		{
			$requestData = $this->WispUser->set($this->request->data);
			//echo "<pre>";print_r($requestData);exit;
			
			if(!$requestData['WispUser']['Number'])
			{
				if($this->WispUser->validates())
				{
					$addUser = $this->WispUser->insertUsername($requestData['WispUser']['Username']);

					foreach($addUser as $userId)
					{
						$requestData['WispUser']['UserId'] = $userId[0]['id'];
					}
					
					// -- password is add in user_attributes table --
					$insertValue = $this->WispUser->addValue($requestData['WispUser']['UserId'],'User-Password', '2', $requestData['WispUser']['Password']);
					
					// -- add group --
					if(isset($requestData['groupId']))
					{
						foreach($requestData['groupId'] as $groupID)
						{
							$addUserGroup = $this->WispUser->insertUserGroup($requestData['WispUser']['UserId'], $groupID);
						}
					}
					// -- end of add group --
					
					// -- add attribute --
					$count1 = '';
					if(isset($requestData['attributeName']))
					{
						$i = 0;
						$count1 = count($requestData['attributeName']);
						for($i=0;$i<$count1;$i++)
						{
							if(isset($requestData['attributeModifier']))
							{
								$attrValues = $requestData['attributeValues'][$i];
								if($requestData['attributeModifier'][$i] == '')
								{
									$attrValue = $attrValues;
								}
								else
								{
									$attrValue = $this->switchModifier($requestData['attributeModifier'][$i],$attrValues);

								}
							}

							$addattribute = $this->WispUser->addValue($requestData['WispUser']['UserId'], $requestData['attributeName'][$i], $requestData['attributeoperator'][$i], $attrValue,$requestData['attributeModifier'][$i]);
						}
					}
					//exit;
					// -- end of add attribute --
					
					// -- add records in wisp_userdata table --
					$this->WispUser->insertRec($requestData);
					$this->Session->setFlash(__('Wisp user is saved succefully!', true), 'flash_success');
					} else {
			  			$this->Session->setFlash(__('Wisp user is not saved!', true), 'flash_failure');
					}
				}
				else
				{
					// for random generat user name.
					//echo "<pre>";print_r($requestData);exit;
					
					// -- loop for number of choosen number --
					$numberCount = $requestData['WispUser']['Number'];
					$prefix = $requestData['WispUser']['Prefix'];
					for($abc=0;$abc<$numberCount;$abc++)
					{
						list($user,$pass) = $this->randomUserName($prefix);
						$requestData['WispUser']['Username'] = $user;
						$requestData['WispUser']['Password'] = $pass;
						
						$addUser = $this->WispUser->insertUsername($requestData['WispUser']['Username']);
					//	print_r($addUser); 
						foreach($addUser as $userId)
						{
							$requestData['WispUser']['UserId'] = $userId[0]['id'];
						}
					// -- add group --
						if(isset($requestData['groupId']))
						{
							foreach($requestData['groupId'] as $groupID)
							{
								$addUserGroup = $this->WispUser->insertUserGroup($requestData['WispUser']['UserId'], $groupID);
							}
						}
						// -- end of add group --
						// -- password is add in user_attributes table --
						$insertValue = $this->WispUser->addValue($requestData['WispUser']['UserId'],'User-Password', '2', $requestData['WispUser']['Password'],'');
						// -- add attribute --
					$count1 = '';
					if(isset($requestData['attributeName']))
					{
						$i = $abc;
						$count1 = count($requestData['attributeName']);
						for($i=0;$i<$count1;$i++)
						{
							if(isset($requestData['attributeModifier']))
							{
								$attrValues = $requestData['attributeValues'][$i];
								if($requestData['attributeModifier'][$i] == '')
								{
									$attrValue = $attrValues;
								}
								else
								{
									$attrValue = $this->switchModifier($requestData['attributeModifier'][$i],$attrValues);

								}
							}
						//	echo "<pre>amit ===> "; print_r($requestData); echo "<hr>";
						$addattribute = $this->WispUser->addValue($requestData['WispUser']['UserId'], $requestData['attributeName'][$i], $requestData['attributeoperator'][$i], $attrValue,$requestData['attributeModifier'][$i]);
						}
					}
					$this->WispUser->insertRec($requestData);
					
						//$newDataArr[] = $requestData;
					} // end of for loop
					$this->Session->setFlash(__('Wisp user is saved succefully!', true), 'flash_success');

				} // -- end of else
				
			//exit;
		}	
	}
	
	/* switch modifier function 
	 * @param $val, $attrValues
	 */
	private function switchModifier($val,$attrValues)
	{
		switch ($val)
		{
			case "Seconds":
				$attrValue = $attrValues / 60;
				break;
			case "Minutes":
				$attrValue = $attrValues;
				break;
			case "Hours":
				$attrValue = $attrValues * 60;
				break;
			case "Days":
				$attrValue = $attrValues * 1440;
				break;
			case "Weeks":
				$attrValue = $attrValues * 10080;
				break;
			case "Months":
				$attrValue = $attrValues * 44640; 
				break;
			case "MBytes":
				$attrValue = $attrValues;
				break;
			case "GBytes":
				$attrValue = $attrValues * 1000;
				break;
			case "TBytes":
				$attrValue = $attrValues * 1000000;
				break;
		}
		return $attrValue;
		
	}
	
	/* randomUserName function 
	 * function user to generate random username and password
	 * @param $prefix
	 */
	private function randomUserName($prefix)
	{
		
			$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
			$usernameReserved = 1 ;
				// Generate random username
				$string = '';
				for ($c = 0; $c < 7; $c++)
				{
					$string .= $characters[rand(0, strlen($characters) - 1)];
					//$string.= $characters[rand(0,strlen($characters))];
				}
				//echo "<pre>";print_r($string);
				$thisUsername = $string;
				// Add prefix to string
				
				if ($prefix!='')
				{
					$thisUsername = $prefix.$string;
				}
				//$requestData['WispUser']['thisUsername'] = $thisUsername;
				//$thisUsername = "madhavi";
				// Check if username used
				$userName = $this->WispUser->getUserName($thisUsername);
				//echo "<pre>";print_r($userName);
				
				if ($userName == 0)
				{
					$usernameReserved = 0;
					$string = $thisUsername;
					// Generate random password
					$stringPass = '';
					for ($c = 0; $c < 7; $c++)
					{
						$stringPass .= $characters[rand(0, strlen($characters) - 1)];
					}
					// Add username and password onto array
					//$wispUser[$thisUsername] = $string;
				}
				if($usernameReserved == 0)
				{
					return array($string,$stringPass);
				}	
				else
				{
					return array('','');
				}
		
					
	}
		
	/* edit function 
	 * @param $id
	 */	
	public function edit($id)
	{
			// -- select all records form wisp_userdata table --
		$user = $this->WispUser->findById($id);
		//print_r($user);
		// -- fetch userNmae --
		$username = $this->WispUser->selectById($user['WispUser']['UserID']);
		$user['WispUser']['Username'] = $username[0]['users']['Username'];
		// -- fetch Value as password --
		$getvalue = $this->WispUser->getValue($user['WispUser']['UserID']);
		$user['WispUser']['Password'] = $getvalue[0]['user_attributes']['Value'];
		$this->set('user', $user);
		// fetch user groups
		$userGroups = $this->WispUser->selectUserGroups($user['WispUser']['UserID']);
		//print_r($userGroups);
		$this->set('userGroups', $userGroups);
				
		// fetcing user attribute
		$userAttrib = $this->WispUser->selectUserAttributes($user['WispUser']['UserID']);
		
		$this->set('userAttrib', $userAttrib);
		
		
		// -- for fetching location from table --
		$locationData = $this->WispUser->selectLocation();
		
		foreach($locationData as $loc)
		{
			$location[$loc['wisp_locations']['ID']] = $loc['wisp_locations']['Name'];
		}
		$this->set('location', $location);
		
		// -- for fetching groups --
		$groupItems = $this->WispUser->selectGroup();
		foreach($groupItems as $val)
		{
			$grouparr[$val['groups']['ID']] = $val['groups']['Name'];
		}
		$this->set('grouparr', $grouparr);
		
		// -- update records --
		$userData[] = array();
		
		if ($this->request->is('post'))
		{
			$requestData = $this->WispUser->set($this->request->data);
			if(!$requestData['WispUser']['Number'])
			{
			
				if($requestData['hiddenUserName'] ==$requestData['WispUser']['Username'])
				{
						
					$editUser = $this->WispUser->updateUsername($user['WispUser']['UserID'],$requestData['WispUser']['Username']);
					
					// -- update password --
					$editValue = $this->WispUser->updateValue($user['WispUser']['UserID'],$requestData['WispUser']['Password']);
					$this->WispUser->updateRec($requestData, $user['WispUser']['UserID']);
					
				// -- update group --	
					$delGroup = $this->WispUser->deleteUserGroup($user['WispUser']['UserID']);
	
					if(isset($requestData['groupId']))
					{
						foreach($requestData['groupId'] as $groupID)
						{
							$addUserGroup = $this->WispUser->insertUserGroup($user['WispUser']['UserID'], $groupID);	
						}
					}
					// -- end of update group --
						
							// -- update attribute --
						$delAttribute = $this->WispUser->deleteUserAttibute($user['WispUser']['UserID']);
						$count1 = '';
						if(isset($requestData['attributeName']))
						{
							$i = 0;
							$count1 = count($requestData['attributeName']);
							for($i=0;$i<$count1;$i++)
							{
								if(isset($requestData['attributeModifier']))
								{
									$attrValues = $requestData['attributeValues'][$i];
									if($requestData['attributeModifier'][$i] == '')
									{
										$attrValue = $attrValues;
									}
									else
									{
										$attrValue = $this->switchModifier($requestData['attributeModifier'][$i],$attrValues);
									}
								}
								$addattribute = $this->WispUser->addValue($user['WispUser']['UserID'], $requestData['attributeName'][$i], $requestData['attributeoperator'][$i], $attrValue,$requestData['attributeModifier'][$i]);
							}
						}
					
					$this->Session->setFlash(__('Wisp user is updated succefully!', true), 'flash_success');
				}
				else
				{
				   if($this->WispUser->validates($user['WispUser']['UserID']))
					{
						// -- update username --
						
					$editUser = $this->WispUser->updateUsername($user['WispUser']['UserID'],$requestData['WispUser']['Username']);
					
					// -- update password --
					$editValue = $this->WispUser->updateValue($user['WispUser']['UserID'],$requestData['WispUser']['Password']);
					// -- update other records --
					$this->WispUser->updateRec($requestData, $user['WispUser']['UserID']);
					
					// -- update group --	
					$delGroup = $this->WispUser->deleteUserGroup($user['WispUser']['UserID']);
	
					if(isset($requestData['groupId']))
					{
						foreach($requestData['groupId'] as $groupID)
						{
							$addUserGroup = $this->WispUser->insertUserGroup($user['WispUser']['UserID'], $groupID);	
						}
					}
					// -- end of update group --
						
							// -- update attribute --
						$delAttribute = $this->WispUser->deleteUserAttibute($user['WispUser']['UserID']);
						$count1 = '';
						if(isset($requestData['attributeName']))
						{
							$i = 0;
							$count1 = count($requestData['attributeName']);
							for($i=0;$i<$count1;$i++)
							{
								if(isset($requestData['attributeModifier']))
								{
									$attrValues = $requestData['attributeValues'][$i];
									if($requestData['attributeModifier'][$i] == '')
									{
										$attrValue = $attrValues;
									}
									else
									{
										$attrValue = $this->switchModifier($requestData['attributeModifier'][$i],$attrValues);
									}
								}
								$addattribute = $this->WispUser->addValue($user['WispUser']['UserID'], $requestData['attributeName'][$i], $requestData['attributeoperator'][$i], $attrValue,$requestData['attributeModifier'][$i]);
							}
						}
						
					$this->Session->setFlash(__('Wisp user is updated succefully!', true), 'flash_success');
					} else {
					  $this->Session->setFlash(__('Wisp user is not saved!', true), 'flash_failure');
					}
				}	
			}
			else
			{
					// for random generat user name.
					// -- loop for number of choosen number --
					$numberCount = $requestData['WispUser']['Number'];
					$prefix = $requestData['WispUser']['Prefix'];
					for($abc=0;$abc<$numberCount;$abc++)
					{
						list($user,$pass) = $this->randomUserName($prefix);
						$requestData['WispUser']['Username'] = $user;
						$requestData['WispUser']['Password'] = $pass;
						
						$addUser = $this->WispUser->insertUsername($requestData['WispUser']['Username']);
						foreach($addUser as $userId)
						{
							$requestData['WispUser']['UserId'] = $userId[0]['id'];
						}
					// -- add group --
						if(isset($requestData['groupId']))
						{
							foreach($requestData['groupId'] as $groupID)
							{
								$addUserGroup = $this->WispUser->insertUserGroup($requestData['WispUser']['UserId'], $groupID);
							}
						}
						// -- end of add group --
						// -- password is add in user_attributes table --
						$insertValue = $this->WispUser->addValue($requestData['WispUser']['UserId'],'User-Password', '2', $requestData['WispUser']['Password']);
						// -- add attribute --
					$count1 = '';
					if(isset($requestData['attributeName']))
					{
						$i = $abc;
						$count1 = count($requestData['attributeName']);
						for($i=0;$i<$count1;$i++)
						{
							if(isset($requestData['attributeModifier']))
							{
								$attrValues = $requestData['attributeValues'][$i];
								if($requestData['attributeModifier'][$i] == '')
								{
									$attrValue = $attrValues;
								}
								else
								{
									$attrValue = $this->switchModifier($requestData['attributeModifier'][$i]);

								}
							}
						$addattribute = $this->WispUser->addValue($requestData['WispUser']['UserId'], $requestData['attributeName'][$i], $requestData['attributeoperator'][$i], $attrValue);
						}
					}
					$this->WispUser->insertRec($requestData);
					
						//$newDataArr[] = $requestData;
					} // end of for loop
					$this->Session->setFlash(__('Wisp user is saved succefully!', true), 'flash_success');
			}	
		}
		
		// -- select all records form wisp_userdata table --
		$user = $this->WispUser->findById($id);
		// -- fetch userNmae --
		$username = $this->WispUser->selectById($user['WispUser']['UserID']);
		$user['WispUser']['Username'] = $username[0]['users']['Username'];
		// -- fetch Value as password --
		$getvalue = $this->WispUser->getValue($user['WispUser']['UserID']);
		$user['WispUser']['Password'] = $getvalue[0]['user_attributes']['Value'];
		$this->set('user', $user);
		// fetch user groups
		$userGroups = $this->WispUser->selectUserGroups($user['WispUser']['UserID']);
		$this->set('userGroups', $userGroups);
				
		// fetcing user attribute
		$userAttrib = $this->WispUser->selectUserAttributes($user['WispUser']['UserID']);
		
		$this->set('userAttrib', $userAttrib);
		
		
		// -- for fetching location from table --
		$locationData = $this->WispUser->selectLocation();
		
		foreach($locationData as $loc)
		{
			$location[$loc['wisp_locations']['ID']] = $loc['wisp_locations']['Name'];
		}
		$this->set('location', $location);
		
		// -- update records --
		$userData[] = array();
	}
	
	/* delete function 
	 * @param $id
	 */	
	public function remove($id)
	{
		$userId = $this->WispUser->fetchUserId($id);
		//echo "<pre>";print_r($userId[0]['wisp_userdata']['UserID']);exit;
		$UserAttributes = $this->WispUser->deleteUserAttributes($userId[0]['wisp_userdata']['UserID']);
		$Users = $this->WispUser->deleteUsers($userId[0]['wisp_userdata']['UserID']);
		if($this->WispUser->delete($id))
		{
			$this->redirect('/wispUsers/index');
			$this->Session->setFlash(__('Wisp user is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Wisp user is not removed!', true), 'flash_failure');
		}
	}
	
}