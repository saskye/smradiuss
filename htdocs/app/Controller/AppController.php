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
 *	will inherit them.
 * @package app.Controller
 */
class AppController extends AWITController
{
	/**
	 * @var $helpers
	 * This variable is used for include other helper file.
	 */
	var $helpers = array('Html', 'Form', 'Session', 'Access');


	/**
	 * @var $components
	 * Components loaded for all Controllers
	 */
	public $components = array(
		'Session','Cookie', 'AWITPaginator',
		'RequestHandler' => array(
			'viewClassMap' => array(
				'json' => 'AWITJson',
			)
		),
		'Auth' => array(
			'loginRedirect' => array(
				'controller' => 'users',
				'action' => 'index'
			),
			'logoutRedirect' => array(
				'controller' => 'webui_users',
				'action' => 'login'
			),
			'Acl',
			'Access'
		)
	);



	/**
	 * @method pages
	 * Return pagination statistics for REST pagination
	 *
	 * @param $id ID to search against
	 */
	public function pages() {

		// Calling index with supplied args
		$args = func_get_args();
		$reflectionMethod = new ReflectionMethod(get_class($this), 'index');
		$parameters = $reflectionMethod->getParameters();
		$paramArray = array();

		$paramIndex = 0;
		foreach ($parameters as $param) {
			// Catching param names and their values to be extracted when child method is called
			$paramArray[$param->name] = $args[$paramIndex];
			$paramIndex++;
		}

		// Calling the Controller's index method with parameters
		$reflectionMethod->invoke($this, $paramArray);

		// Retrieving pagination statistics
		$pages =  $this->getPaginationPages();

		// Reset view vars
		$this->viewVars = array();

		// Exporting stats to view
		$this->set(compact('pages'));
		$this->set('_serialize', 'pages');
	}



	/**
	 * @method beforeFilter
	 * This method executes method that we need to be executed before any other action.
	 */
	function beforeFilter()
	{
		if ($this->Session->check('User.ID') != true) {
			$this->Auth->allow('login');
			$this->Auth->loginAction = array('controller' => 'webui_users', 'action' => 'login');
		}
	}



	/**
	 * @method beforeRender
	 * This method gets called before the view is rendered and
	 * serializes all viewVars to provide transparent REST responses
	 */
	public function beforeRender() {
		// Catching viewVars
		$data = $this->viewVars;

		// Processing REST requests
		$this->set(compact('data'));
		$this->set('_serialize', array('data'));
	}




}

// vim: ts=4
