<?php
/**
 * Copyright (c) 2014, AllWorldIT
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */



/*
 * wisp location member
 */
class WispLocationMembersController extends AppController
{
	/* index function
	 * @param $LocationID
	 * Used to show members location with pagination.
	 *
	 */
	public function index($LocationID)
	{
		$this->WispLocationMember->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT,'conditions' => array('LocationID' => $LocationID));
		// Assigning fetched data to variable.
		$wispLocationMember = $this->paginate();
		$memberData = array();

		// Generating final array.
		foreach($wispLocationMember as $wMember)
		{
			$userNameData= $this->WispLocationMember->selectUsername($wMember['WispLocationMember']['UserID']);
			foreach($userNameData as $uData)
			{
				if(isset($uData['users']['Username']))
				{
					$wMember['WispLocationMember']['userName'] = $userNameData[0]['users']['Username'];
				}
				$memberData[] = $wMember;
			}
		}
		$wispLocationMember = $memberData;
		$this->set('wispLocationMember', $wispLocationMember);
		$this->set('LocationID', $LocationID);
	}

	/* remove function
	 * @param $id, $LocationID
	 * used to delete members locations.
	 *
	 */
	public function remove($id, $LocationID)
	{
		// Deleting
		$deleteMember = $this->WispLocationMember->deleteMembers($id);
		// Redirecting to index.
		$this->redirect('/WispLocation_Members/index/'.$LocationID);
		$this->Session->setFlash(__('Wisp locations member is removed succefully!', true), 'flash_success');
	}
}

// vim: ts=4
