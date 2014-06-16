<?php
/**
 * Wisp User Logs
 *
 */
class WispUserLogsController extends AppController
{
	/* index function 
	 * @param $userId
	 */	
	public function index($userId)
	{
		
		if (isset($userId))
		{
			// -- fetch data form topups table --
			$current = date('Y-m').'-01';
			$userLog = $this->WispUserLog->SelectRec($userId,$current);
			$this->set('userLog', $userLog);
			$this->set('userId', $userId);
			
			// -- for searching --
			if ($this->request->is('post'))
			{
				$data = $this->request->data;
				$this->WispUserLog->set($data);
				$logDate = $data['WispUserLog']['yearData']."-".$data['WispUserLog']['dayData']."-01";
			    $userLog = $this->WispUserLog->SelectRec($userId,$logDate);
				$this->set('userLog', $userLog);
			}
			
			// -- fetch data form accounting table --
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