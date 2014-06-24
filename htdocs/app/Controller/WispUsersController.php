<?php
/*
 * Wisp Users
 */

class WispUsersController extends AppController
{
	/* index function
	 * Showing users list with pagination.
	 *
	 */
	public function index()
	{
		$this->WispUser->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		// Assigning paginated data to var.
		$wispUser = $this->paginate();
		$wispUserData = array();

		// Adding username and disabled to above array and generating final array.
		foreach($wispUser as $wUsers)
		{
			$userData = $this->WispUser->selectById($wUsers['WispUser']['UserID']);

			$wUsers['WispUser']['Username'] = $userData[0]['users']['Username'];
			$wUsers['WispUser']['Disabled'] = $userData[0]['users']['Disabled'];
			$wispUserData[] = $wUsers;
		}

		$wispUser = $wispUserData;
		// setting data to use it on view page.
		$this->set('wispUser', $wispUser);
	}

	/* add function
	 * Used to add users to database
	 *
	 */
	public function add()
	{
		// Fetching groups from table.
		$groupItems = $this->WispUser->selectGroup();
		$grouparr = $location = array();
		foreach($groupItems as $val)
		{
			$grouparr[$val['groups']['ID']] = $val['groups']['Name'];
		}
		$this->set('grouparr', $grouparr);
		// Fetching locations from table
		$locationData = $this->WispUser->selectLocation();

		foreach($locationData as $loc)
		{
			$location[$loc['wisp_locations']['ID']] = $loc['wisp_locations']['Name'];
		}
		$this->set('location', $location);

		$userData[] = array();
		// Checking submission.
		if ($this->request->is('post'))
		{
			$requestData = $this->WispUser->set($this->request->data);
			// Checking wisp number field is set or not
			if(!$requestData['WispUser']['Number'])
			{
				// Validationg submitted data.
				if($this->WispUser->validates())
				{
					$addUser = $this->WispUser->insertUsername($requestData['WispUser']['Username']);
					foreach($addUser as $userId)
					{
						$requestData['WispUser']['UserId'] = $userId[0]['id'];
					}

					// Password attribute is inserted to table.
					$insertValue = $this->WispUser->addValue($requestData['WispUser']['UserId'],'User-Password', '2', $requestData['WispUser']['Password'],'');

					// Inserting groups
					if(isset($requestData['groupId']))
					{
						foreach($requestData['groupId'] as $groupID)
						{
							$addUserGroup = $this->WispUser->insertUserGroup($requestData['WispUser']['UserId'], $groupID);
						}
					}
					//end of group insertion.

					// Inserting attributes.
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
					// end of attribute insertion

					// Inserting data in wisp_userdata table
					$this->WispUser->insertRec($requestData);
					$this->Session->setFlash(__('Wisp user is saved succefully!', true), 'flash_success');
					} else {
			  			$this->Session->setFlash(__('Wisp user is not saved!', true), 'flash_failure');
					}
				}
				// This sectiopn work,if number fields is inserted from add many tab option.
				else
				{
					$numberCount = $requestData['WispUser']['Number'];
					$prefix = $requestData['WispUser']['Prefix'];
					// loop to add $numberCount number of users to system.
					for($abc=0;$abc<$numberCount;$abc++)
					{
						// generating random username and password
						list($user,$pass) = $this->randomUserName($prefix);
						$requestData['WispUser']['Username'] = $user;
						$requestData['WispUser']['Password'] = $pass;
						// Saving username
						$addUser = $this->WispUser->insertUsername($requestData['WispUser']['Username']);
						foreach($addUser as $userId)
						{
							$requestData['WispUser']['UserId'] = $userId[0]['id'];
						}
						//Inserting groups
						if(isset($requestData['groupId']))
						{
							foreach($requestData['groupId'] as $groupID)
							{
								$addUserGroup = $this->WispUser->insertUserGroup($requestData['WispUser']['UserId'], $groupID);
							}
						}
						// End of group insertion

						// Inserting password to user_attributes table.
						$insertValue = $this->WispUser->addValue($requestData['WispUser']['UserId'],'User-Password', '2', $requestData['WispUser']['Password'],'');

					// Inserting attributes
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
									// calling switch modifier function and getting attribute value after processing.
									$attrValue = $this->switchModifier($requestData['attributeModifier'][$i],$attrValues);
								}
							}
							// Saving attribute value to database.
							$addattribute = $this->WispUser->addValue($requestData['WispUser']['UserId'], $requestData['attributeName'][$i], $requestData['attributeoperator'][$i], $attrValue,$requestData['attributeModifier'][$i]);
						}
					}
					// Saving user data to db.
					$this->WispUser->insertRec($requestData);

					} // end of for loop
					$this->Session->setFlash(__('Wisp user is saved succefully!', true), 'flash_success');

				} // end of else
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
	 * @param $prefix
	 * Function user to generate random username and password
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
		}
		$thisUsername = $string;

		// Add prefix to string
		if ($prefix!='')
		{
			$thisUsername = $prefix.$string;
		}

		// Check if username used
		$userName = $this->WispUser->getUserName($thisUsername);

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
	 * used to edit users data , group, attributes etc.
	 *
	 */
	public function edit($id)
	{
		// Select all records form wisp_userdata table --
		$user = $this->WispUser->findById($id);
		// Fetch username
		$username = $this->WispUser->selectById($user['WispUser']['UserID']);
		$user['WispUser']['Username'] = $username[0]['users']['Username'];
		// Fetch Value as password.
		$getvalue = $this->WispUser->getValue($user['WispUser']['UserID']);
		$user['WispUser']['Password'] = $getvalue[0]['user_attributes']['Value'];
		$this->set('user', $user);
		// Fetch user groups
		$userGroups = $this->WispUser->selectUserGroups($user['WispUser']['UserID']);
		$this->set('userGroups', $userGroups);
		// Fetcing user attribute
		$userAttrib = $this->WispUser->selectUserAttributes($user['WispUser']['UserID']);

		$this->set('userAttrib', $userAttrib);

		// Fetching locations
		$location = $grouparr = array();
		$locationData = $this->WispUser->selectLocation();

		foreach($locationData as $loc)
		{
			$location[$loc['wisp_locations']['ID']] = $loc['wisp_locations']['Name'];
		}
		$this->set('location', $location);

		// Fetching all groups to fill select control.
		$groupItems = $this->WispUser->selectGroup();
		foreach($groupItems as $val)
		{
			$grouparr[$val['groups']['ID']] = $val['groups']['Name'];
		}
		$this->set('grouparr', $grouparr);

		// Update records
		$userData[] = array();

		// Checking submission.
		if ($this->request->is('post'))
		{
			$requestData = $this->WispUser->set($this->request->data);
			// Condition to check username on submission or not.
			if($requestData['hiddenUserName'] ==$requestData['WispUser']['Username'])
			{
				$editUser = $this->WispUser->updateUsername($user['WispUser']['UserID'],$requestData['WispUser']['Username']);

				// Update password
				$editValue = $this->WispUser->updateValue($user['WispUser']['UserID'],$requestData['WispUser']['Password']);
				$this->WispUser->updateRec($requestData, $user['WispUser']['UserID']);

				// Update group
				$delGroup = $this->WispUser->deleteUserGroup($user['WispUser']['UserID']);

				if(isset($requestData['groupId']))
				{
					foreach($requestData['groupId'] as $groupID)
					{
						$addUserGroup = $this->WispUser->insertUserGroup($user['WispUser']['UserID'], $groupID);
					}
				}
				// end of group updation.

				// Update attribute
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
					// Update username
					$editUser = $this->WispUser->updateUsername($user['WispUser']['UserID'],$requestData['WispUser']['Username']);

					// Update password
					$editValue = $this->WispUser->updateValue($user['WispUser']['UserID'],$requestData['WispUser']['Password']);
					// Update other records
					$this->WispUser->updateRec($requestData, $user['WispUser']['UserID']);

					// update groups
					$delGroup = $this->WispUser->deleteUserGroup($user['WispUser']['UserID']);

					if(isset($requestData['groupId']))
					{
						foreach($requestData['groupId'] as $groupID)
						{
							$addUserGroup = $this->WispUser->insertUserGroup($user['WispUser']['UserID'], $groupID);
						}
					}
					// end of group updation

					// Update attribute
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

		//Fetching records form wisp_userdata table
		$user = $this->WispUser->findById($id);
		// Fetch userName
		$username = $this->WispUser->selectById($user['WispUser']['UserID']);
		$user['WispUser']['Username'] = $username[0]['users']['Username'];
		// Fetch password
		$getvalue = $this->WispUser->getValue($user['WispUser']['UserID']);
		$user['WispUser']['Password'] = $getvalue[0]['user_attributes']['Value'];
		$this->set('user', $user);
		// Fetch user groups data
		$userGroups = $this->WispUser->selectUserGroups($user['WispUser']['UserID']);
		$this->set('userGroups', $userGroups);
		// Fetcing user attribute data.
		$userAttrib = $this->WispUser->selectUserAttributes($user['WispUser']['UserID']);

		$this->set('userAttrib', $userAttrib);

		// Fetching all location to fill select control.
		$locationData = $this->WispUser->selectLocation();

		foreach($locationData as $loc)
		{
			$location[$loc['wisp_locations']['ID']] = $loc['wisp_locations']['Name'];
		}
		$this->set('location', $location);

		$userData[] = array();
	}

	/* delete function
	 * @param $id
	 * used to delete record from all table referencing user.
	 *
	 */
	public function remove($id)
	{
		// Fetching user data and assigning to var.
		$userId = $this->WispUser->fetchUserId($id);

		// Deleting user attributes.
		$UserAttributes = $this->WispUser->deleteUserAttributes($userId[0]['wisp_userdata']['UserID']);
		// Deleting record from topup, users and group table.
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

// vim: ts=4
