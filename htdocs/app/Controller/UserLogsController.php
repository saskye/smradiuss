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
 * User Logs
 *
 * @class UserLogsController
 *
 * @brief This class manages the user's logs.
 */
class UserLogsController extends AppController
{

	/**
	 * @method index
	 * @param $userId
	 * This method is used to show user logs list with pagination.
	 */
	public function index($userId)
	{
		if (isset($userId)) {
			// Creating current month & year date.
			$current = date('Y-m').'-01';
			// Fetched data form topups table.
			$userLog = $this->UserLog->selectTopup($userId,$current);
			$this->set('userLog', $userLog);
			$this->set('userId', $userId);

			// For searching topups month and year wise.
			if ($this->request->is('post')) {
				// Reading submitted data to variable.
				$data = $this->request->data;
				// Setting data to model.
				$this->UserLog->set($data);
				$logDate = $data['UserLog']['yearData']."-".$data['UserLog']['dayData']."-01";
				// Selected user log record from paramete logdate.
			    $userLog = $this->UserLog->selectTopup($userId,$logDate);
				$this->set('userLog', $userLog);
			}

			// Fetch data form accounting table.
			$username = $this->UserLog->selectUser($userId);
			$userName = $username[0]['users']['Username'];

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
