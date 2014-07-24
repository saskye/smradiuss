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
 * Realm Members
 *
 * @class RealmMembersController
 *
 * @brief This class manages the realms for members.
 */
class RealmMembersController extends AppController
{

	/**
	 * @method index
	 * @param $realmID
	 * This method is used to show realms members list with pagination.
	 */
	public function index($realmID)
	{
		if (isset($realmID)) {
			// Getting list with pagination.
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('RealmID' => $realmID)
			);
			$realmMembers = $this->paginate();
			$realmMembersData =array();

			// Generating final array.
			foreach ($realmMembers as $realmMember) {
				$clientData = $this->RealmMember->getClientNameById($realmMember['RealmMember']['ClientID']);

				if (isset($clientData['Client']['Name'])) {
					$realmMember['RealmMember']['clientName'] = $clientData['Client']['Name'];
				}
				$realmMembersData[] = $realmMember;
			}
			$realmMember = $realmMembersData;
			// Send to view page.
			$this->set('realmMember', $realmMember);
			$this->set('realmID', $realmID);
		} else {
			$this->redirect('/realm_members/index');
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * @param $realmID
	 * This method is used to remove realms members.
	 */
	public function remove($id, $realmID)
	{
		if (isset($id)) {
			if ($this->RealmMember->delete($id)) {
				$this->redirect('/realm_members/index/'.$realmID);
				$this->Session->setFlash(__('Realm member is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm member is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/realm_members/index'.$realmID);
		}
	}
}

// vim: ts=4
