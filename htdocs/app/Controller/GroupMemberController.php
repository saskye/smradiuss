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



/**
 * Group Member
 *
 * @class GroupMemberController
 *
 * @brief This class manage the groups for member.
 */
class GroupMemberController extends AppController
{
	/**
	 * @method index
	 * @param $groupID
	 * This method is used for fetching list of group members with pagination.
	 */
	public function index($groupID)
	{
		if (isset($groupID)) {
			$this->GroupMember->recursive = 0;
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('GroupID' => $groupID)
			);

			$GroupMember = $this->paginate();
			$UserNameData =array();

			// Preparing final array.
			foreach ($GroupMember as $groupMember) {
				$userName = $this->GroupMember->getUserNameById($groupMember['GroupMember']['UserID']);
				if (isset($userName['User']['Username'])) {
					$groupMember['GroupMember']['UserName'] = $userName['User']['Username'];
				}
				$UserNameData[] = $groupMember;
			}

			$GroupMember = $UserNameData;
			$this->set('GroupMember', $GroupMember);
			$this->set('groupID', $groupID);
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * @param $groupID
	 * This method is used to delete group member.
	 */
	public function remove($id, $groupID){
		if (isset($id)) {
			if ($this->GroupMember->delete($id)) {
				$this->redirect('/group_member/index/'.$groupID);
				$this->Session->setFlash(__('Removed from this group succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Not removed from this group!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/group_member/index'.$groupID);
		}
	}
}

// vim: ts=4
