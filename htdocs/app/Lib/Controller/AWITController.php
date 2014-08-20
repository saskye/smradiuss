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



App::uses('Controller', 'Controller');
App::uses('CakeEventListener', 'Event');
App::uses('AWITClassRegistry', 'Utility');
App::uses('AWITPaginatorComponent', 'Controller/Component');

/**
 * @class AWITController
 * Overrides the Cake Controller to provide
 *  - REST functionality
 *  - page statistics via the AWITPaginator
 *  - Reference to current Controller for Models that need this
 *  - Skips redirects for REST/Json requests
 *
 *
 * Application controller class for organization of business logic.
 * Provides basic functionality, such as rendering views inside layouts,
 * automatic model availability, redirection, callbacks, and more.
 *
 * Controllers should provide a number of 'action' methods. These are public methods on the controller
 * that are not prefixed with a '_' and not part of Controller. Each action serves as an endpoint for
 * performing a specific action on a resource or collection of resources. For example: adding or editing a new
 * object, or listing a set of objects.
 *
 * You can access request parameters, using `$this->request`. The request object contains all the POST, GET and FILES
 * that were part of the request.
 *
 * After performing the required actions, controllers are responsible for creating a response. This usually
 * takes the form of a generated View, or possibly a redirection to another controller action. In either case
 * `$this->response` allows you to manipulate all aspects of the response.
 *
 * Controllers are created by Dispatcher based on request parameters and routing. By default controllers and actions
 * use conventional names. For example `/posts/index` maps to `PostsController::index()`. You can re-map URLs
 * using Router::connect().
 *
 * @package       Cake.Controller
 * @property      AclComponent $Acl
 * @property      AuthComponent $Auth
 * @property      CookieComponent $Cookie
 * @property      EmailComponent $Email
 * @property      PaginatorComponent $Paginator
 * @property      RequestHandlerComponent $RequestHandler
 * @property      SecurityComponent $Security
 * @property      SessionComponent $Session
 * @link          http://book.cakephp.org/2.0/en/controllers.html
 */
class AWITController extends Controller implements CakeEventListener {



	/**
	 * @method loadModel
	 * Overrides constructor loadModel in order to pass the Controller
	 * to the model being loaded such that the currently executing
	 * Controller can be manipulated in order to provide the appropriate
	 * REST responses without any change to existing Controllers
	 *
	 * @param string $modelClass Name of model class to load
	 * @param integer|string $id Initial ID the instanced model class should have
	 * @return boolean True if the model was found
	 * @throws MissingModelException if the model class cannot be found.
	 */
	public function loadModel($modelClass = null, $id = null) {
		if ($modelClass === null) {
			$modelClass = $this->modelClass;
		}

		$this->uses = ($this->uses) ? (array)$this->uses : array();
		if (!in_array($modelClass, $this->uses, true)) {
			$this->uses[] = $modelClass;
		}

		list($plugin, $modelClass) = pluginSplit($modelClass, true);

		// Passing Constroller to App Models
		$this->{$modelClass} = AWITClassRegistry::init(
			array(
				'class' => $plugin . $modelClass,
				'alias' => $modelClass, 'id' => $id
			),
			false,
			$this
		);

		if (!$this->{$modelClass}) {
			throw new MissingModelException($modelClass);
		}
		return true;
	}


	/**
	 * @method redirect
	 * Redirects to given $url, after turning off $this->autoRender.
	 * Script execution is halted after the redirect.
	 * Overriding redirect to support JSON responses
	 *
	 * @param string|array $url A string or array-based URL pointing to another location within the app,
	 *     or an absolute URL
	 * @param integer $status Optional HTTP status code (eg: 404)
	 * @param boolean $exit If true, exit() will be called after the redirect
	 * @return void
	 * @link http://book.cakephp.org/2.0/en/controllers.html#Controller::redirect
	 */
	public function redirect($url, $status = null, $exit = true) {
		// Skipping redirects for JSON/REST requests
		if ($this->request->accepts('application/json')) {
			return false;
		}

		// Continue redirection as per normal
		return parent::redirect($url, $status, $exit);
	}



	/**
	 * @method __get
	 * Provides backwards compatibility access to the request object properties.
	 * Also provides the params alias.
	 *
	 * @param string $name The name of the requested value
	 * @return mixed The requested value for valid variables/aliases else null
	 */
	public function __get($name) {
		switch ($name) {
			case 'base':
			case 'here':
			case 'webroot':
			case 'data':
				return $this->request->{$name};
			case 'action':
				return isset($this->request->params['action']) ? $this->request->params['action'] : '';
			case 'params':
				return $this->request;
			case 'paginate':
				return $this->Components->load('AWITPaginator')->settings;
		}

		if (isset($this->{$name})) {
			return $this->{$name};
		}

		return null;
	}


	/**
	 * @method __set
	 * Provides backwards compatibility access for setting values to the request object.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value) {
		switch ($name) {
			case 'base':
			case 'here':
			case 'webroot':
			case 'data':
				$this->request->{$name} = $value;
				return;
			case 'action':
				$this->request->params['action'] = $value;
				return;
			case 'params':
				$this->request->params = $value;
				return;
			case 'paginate':
				$this->Components->load('AWITPaginator')->settings = $value;
				return;
		}
		$this->{$name} = $value;
	}



	/**
	 * @method getPaginationPages
	 * Handles automatic pagination of model records.
	 *
	 * @param Model|string $object Model to paginate (e.g: model instance, or 'Model', or 'Model.InnerModel')
	 * @param string|array $scope Conditions to use while paginating
	 * @param array $whitelist List of allowed options for paging
	 * @return array Model query results
	 * @link http://book.cakephp.org/2.0/en/controllers.html#Controller::paginate
	 */
	public function getPaginationPages($object = null, $scope = array(), $whitelist = array()) {
		return $this->Components->load('AWITPaginator', $this->paginate)->getPaginationPages($object, $scope, $whitelist);
	}


}
