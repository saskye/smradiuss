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



App::uses('ExceptionRenderer', 'Error');

/**
 * @class AWITExceptionRenderer
 * Overrides ExceptionRenderer to provide REST responses via AWITErrorController
 */
class AWITExceptionRenderer extends ExceptionRenderer {



	/**
	 * @method _getController
	 *
	 * Get the controller instance to handle the exception.
	 * Override this method in subclasses to customize the controller used.
	 * This method returns the built in `CakeErrorController` normally, or if an error is repeated
	 * a bare controller will be used.
	 *
	 * @param Exception $exception The exception to get a controller for.
	 * @return Controller
	 */
	protected function _getController($exception) {
		App::uses('AppController', 'Controller');
		App::uses('AWITErrorController', 'Controller');
		if (!$request = Router::getRequest(true)) {
			$request = new CakeRequest();
		}
		$response = new CakeResponse();

		if (method_exists($exception, 'responseHeader')) {
			$response->header($exception->responseHeader());
		}

		if (class_exists('AppController')) {
			try {
				$controller = new AWITErrorController($request, $response);
				$controller->startupProcess();
			} catch (Exception $e) {
				if (!empty($controller) && $controller->Components->enabled('RequestHandler')) {
					$controller->RequestHandler->startup($controller);
				}
			}
		}
		if (empty($controller)) {
			$controller = new Controller($request, $response);
			$controller->viewPath = 'Errors';
		}
		return $controller;
	}



	/**
	 * @method error400
	 * Overrides to provide REST properly formatted responses
	 * Convenience method to display a 400 series page.
	 *
	 * @param Exception $error
	 * @return void
	 */
	public function error400($error) {
		echo "AWITExceptionRenderer: Method: [{$this->method}]";
		$message = $error->getMessage();
		if (!Configure::read('debug') && $error instanceof CakeException) {
			$message = __d('cake', 'Not Found');
		}
		$url = $this->controller->request->here();
		$this->controller->response->statusCode($error->getCode());
		$this->controller->set(array(
			'name' => h($message),
			'code' => h($error->getCode()),
			'url' => h($url),
			'error' => $error,
			'status' => 'fail',
			'_serialize' => array('status', 'name', 'code')
		));
		$this->_outputMessage('error400');
	}



	/**
	 * @method error500
	 * Overrides to provide properly formatted REST responses

	 * Convenience method to display a 500 page.
	 *
	 * @param Exception $error
	 * @return void
	 */
	public function error500($error) {
		$message = $error->getMessage();
		if (!Configure::read('debug')) {
			$message = __d('cake', 'An Internal Error Has Occurred.');
		}
		$url = $this->controller->request->here();
		$code = ($error->getCode() > 500 && $error->getCode() < 506) ? $error->getCode() : 500;
		$this->controller->response->statusCode($code);
		$this->controller->set(array(
			'name' => h($message),
			'code' => h($error->getCode()),
			'message' => h($url),
			'error' => $error,
			'_serialize' => array('name', 'code')
		));
		$this->_outputMessage('error500');
	}



	/**
	 * @method pdoError
	 * Overrides to provide peroperly formatted REST responses
	 * Convenience method to display a PDOException.
	 *
	 * @param PDOException $error
	 * @return void
	 */
	public function pdoError(PDOException $error) {
		$url = $this->controller->request->here();
		$code = 500;
		$this->controller->response->statusCode($code);
		$this->controller->set(array(
			'code' => $code,
			'url' => h($url),
			'name' => h($error->getMessage()),
			'error' => $error,
			'_serialize' => array('code', 'url', 'name', 'error')
		));
		$this->_outputMessage($this->template);
	}



}
