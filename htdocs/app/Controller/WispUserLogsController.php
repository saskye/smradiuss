<?php
/**
 * Wisp User Logs
 *
 * @class WispUserLogsController
 *
 * @brief This class manage the wisp user's log.
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
		if (isset($userId))
		{
			// Creating current month & year date.
			$current = date('Y-m').'-01';
			// Fetched data form topups table.
			$userLog = $this->WispUserLog->SelectRec($userId,$current);
			$this->set('userLog', $userLog);
			$this->set('userId', $userId);

			// For searching topups month and year wise.
			if ($this->request->is('post'))
			{
				// Reading submitted data to variable.
				$data = $this->request->data;
				// Setting data to model.
				$this->WispUserLog->set($data);
				$logDate = $data['WispUserLog']['yearData']."-".$data['WispUserLog']['dayData']."-01";
				// Selected user log record from paramete logdate.
			    $userLog = $this->WispUserLog->SelectRec($userId,$logDate);
				$this->set('userLog', $userLog);
			}

			// Fetch data form accounting table.
			$username = $this->WispUserLog->SelectAcc($userId);
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
