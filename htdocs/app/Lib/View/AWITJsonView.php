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



App::uses('JsonView', 'View');

/**
 * @class AWITJsonView
 * A view class that is used for AWIT specific JSON responses.
 *
 */
class AWITJsonView extends JsonView {

	/**
	 * @method _serialize
	 * Serialize view vars
	 *
	 * @param array $serialize The viewVars that need to be serialized
	 * @return string The serialized data
	 */
	protected function _serialize($serialize) {
		if (is_array($serialize)) {
			$data = array();
			foreach ($serialize as $alias => $key) {
				if (is_numeric($alias)) {
					$alias = $key;
				}
				if (!is_array($key) && array_key_exists($key, $this->viewVars)) {
					$data[$alias] = $this->viewVars[$key];
				}
			}
			$data = !empty($data) ? $data : null;
		} else {
			$data = isset($this->viewVars[$serialize]) ? $this->viewVars[$serialize] : null;
		}

		$status = 'success';
		$message = '';
		$code = '';

		// Handle 'Not Found' errors e.g. pagination request beyond bounds
		if (isset($data['name'])) {
			if (stripos('Not Found', $data['name']) > -1) {
				$status = 'fail';
				$message = $data['name'];
			}
		}

		// Handle http error codes '404' etc. errors e.g. missing controller
		if (isset($data['code'])) {
			$status = 'fail';
			$code = $data['code'];
			$message = $data['code'];

			if (isset($data['message'])) {
				$message = $data['message'];
			} else if (isset($data['name'])) {
				$message = $data['name'];
			}
		}

		// Handle pre defined properly structured AWIT Json variables
		if (isset($data['status'])) {
			$status = $data['status'];
		}

		if (isset($data['message'])) {
			$message = $data['message'];
		}

		if (isset($data['data'])) {
			$data = $data['data'];
		}


		$result = array();
		$result['status'] = $status;

		if ($status == 'success') {
			$result['data'] = $data;
		} else if ($status == 'fail') {
			$result['message'] = $message;
			if (!empty($code)) {
				$result['code'] = $code;
			}
		}

		if (version_compare(PHP_VERSION, '5.4.0', '>=') && Configure::read('debug')) {
			return json_encode($result, JSON_PRETTY_PRINT);
		}

		return json_encode($result);
	}

}
