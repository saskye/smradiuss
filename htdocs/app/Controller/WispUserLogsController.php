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
 * Wisp User Logs
 *
 * @class WispUserLogsController
 *
 * @brief This class manages the wisp user's log.
 */
class WispUserLogsController extends AppController
{

	/**
	 * @method index
	 * @param $userId
	 * This method is used to load wisp user logs data list.
	 */
	public function index($userId)
	{
		if (isset($userId)) {
			// Creating current month & year date.
			$current = date('Y-m').'-01';
			// Fetched data form topups table.
			$userLog = $this->WispUserLog->SelectRec($userId,$current);
			$this->set('userLog', $userLog);
			$this->set('userId', $userId);

			// For searching topups month and year wise.
			if ($this->request->is('post')) {
				// Reading submitted data to variable.
				$data = $this->request->data;
				// Setting data to model.
				$this->WispUserLog->set($data);
				$logDate = $data['WispUserLog']['yearData']."-".$data['WispUserLog']['dayData']."-01";
				// Select user log record.
			    $userLog = $this->WispUserLog->SelectRec($userId,$logDate);
				$this->set('userLog', $userLog);
			}

			// Fetch data form accounting table.
			$username = $this->WispUserLog->SelectAcc($userId);
			$userName = $username['User']['Username'];
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('Username' => $userName)
			);
			$userAcc  = $this->paginate();
			$this->set('userAcc', $userAcc);
		}
	}
}

// vim: ts=4
