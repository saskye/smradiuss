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



// Load AppHelper class.
App::uses('AppHelper', 'View/Helper');



/**
 * @class AccessHelper
 * @brief This class manages user permission.
 */
class AccessHelper extends AppHelper
{
	/**
	 * @var $helpers
	 * This variable is used for include session helper.
	 */
	var $helpers = array("Session");


	/**
	 * @var $Access
	 * This variable is used for include access component.
	 */
	var $Access;


	/**
	 * @var $Auth
	 * This variable is used for include auth component.
	 */
	var $Auth;


	/**
	 * @var $user
	 * This variable is used for get auth user info.
	 */
	var $user;



	/**
	 * @method beforeRender
	 * beforeRender is called before the view file is rendered.
	 * @param $viewFile
	 */
	function beforeRender($viewFile)
	{
		App::import('Component', 'Access');
		$this->Access = new AccessComponent(new ComponentCollection());
		App::import('Component', 'Auth');
		$this->Auth = new AuthComponent(new ComponentCollection());
		$this->Auth->Session = $this->Session;
		$this->user = $this->Auth->user();
	}



	/**
	 * @method check
	 * This method check user permission.
	 * @param $aro
	 * @param $aco
	 */
	function check($aro, $aco)
	{
		return $this->Access->checkHelper($aro, $aco);
	}
}



// vim: ts=4
?>
