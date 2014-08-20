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
 * Application level Controller
 *
 * @brief This file is application-wide controller file.
 *
 * You can put all application-wide controller-related methods here.
 */

App::uses('AWITController', 'Controller');
App::uses('AWITPaginatorComponent', 'Controller/Component');
App::uses('AWITJsonView', 'Lib/View');

/**
 * Application Controller
 *
 * @class AppController
 * @brief Add your application-wide methods in the class below, your controllers
 *		  will inherit them.
 * @package app.Controller
 */
class AppController extends AWITController
{
	/**
	 * @var $components
	 * Components loaded for all Controllers
	 */
	public $components = array(
		'DebugKit.Toolbar', 'Session','Cookie', 'AWITPaginator',
		'RequestHandler' => array(
			'viewClassMap' => array(
				'json' => 'AWITJson',
			)
		)
	);



	/**
	 * @method indexapi
	 * Method to handle all REST responses to list data
	 *
	 * @param $id ID to search against
	 */
	public function indexapi($id = '')
	{
		// Call default list function
		$this->index($id);

		// Catching viewVars
		$data = $this->viewVars;

		// Processing REST requests
		$this->set(compact('data'));
		$this->set('_serialize', array('data'));
	}



	/**
	 * @method pages
	 * Return pagination statistics for REST pagination
	 *
	 * @param $id ID to search against
	 */
	public function pages($id = '') {
		$this->index($id);
		$pages =  $this->getPaginationPages();

		$this->set(compact('pages'));
		$this->set('_serialize', 'pages');
	}
}

// vim: ts=4
