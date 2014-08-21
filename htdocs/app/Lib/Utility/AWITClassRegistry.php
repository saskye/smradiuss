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
 * Included libraries.
 */
App::uses('Model', 'Model');
App::uses('AppModel', 'Model');
App::uses('ConnectionManager', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * @class AWITClassRegistry
 * Overrides the ClassRegistry class to provide an init method to pass
 * the currently loaded Constructor to Model's (Plugins not supported yet)
 *
 * A repository for class objects, each registered with a key.
 * If you try to add an object with the same key twice, nothing will come of it.
 * If you need a second instance of an object, give it another key.
 *
 * @package       Cake.Utility
 */
class AWITClassRegistry extends ClassRegistry {



	/**
	 * @method init
	 *
	 * Loads a class, registers the object in the registry and returns instance of the object. ClassRegistry::init()
	 * is used as a factory for models, and handle correct injecting of settings, that assist in testing.
	 *
	 * Examples
	 * Simple Use: Get a Post model instance ```ClassRegistry::init('Post');```
	 *
	 * Expanded: ```array('class' => 'ClassName', 'alias' => 'AliasNameStoredInTheRegistry');```
	 *
	 * Model Classes can accept optional ```array('id' => $id, 'table' => $table, 'ds' => $ds, 'alias' => $alias);```
	 *
	 * When $class is a numeric keyed array, multiple class instances will be stored in the registry,
	 *  no instance of the object will be returned
	 * {{{
	 * array(
	 *		array('class' => 'ClassName', 'alias' => 'AliasNameStoredInTheRegistry'),
	 *		array('class' => 'ClassName', 'alias' => 'AliasNameStoredInTheRegistry'),
	 *		array('class' => 'ClassName', 'alias' => 'AliasNameStoredInTheRegistry')
	 * );
	 * }}}
	 * @param string|array $class as a string or a single key => value array instance will be created,
	 *  stored in the registry and returned.
	 * @param boolean $strict if set to true it will return false if the class was not found instead
	 *	of trying to create an AppModel
	 * @return object instance of ClassName.
	 * @throws CakeException when you try to construct an interface or abstract class.
	 */
	public static function init($class, $strict = false, $controller = null) {
		$_this = ClassRegistry::getInstance();

		if (is_array($class)) {
			$objects = $class;
			if (!isset($class[0])) {
				$objects = array($class);
			}
		} else {
			$objects = array(array('class' => $class));
		}
		$defaults = array();
		if (isset($_this->_config['Model'])) {
			$defaults = $_this->_config['Model'];
		}
		$count = count($objects);
		$availableDs = null;

		foreach ($objects as $settings) {
			if (is_numeric($settings)) {
				trigger_error(__d('cake_dev',
					'(ClassRegistry::init() Attempted to create instance of a class with a numeric name'), E_USER_WARNING);
				return false;
			}

			if (is_array($settings)) {
				$pluginPath = null;
				$settings = array_merge($defaults, $settings);
				$class = $settings['class'];

				list($plugin, $class) = pluginSplit($class);
				if ($plugin) {
					$pluginPath = $plugin . '.';
					$settings['plugin'] = $plugin;
				}

				if (empty($settings['alias'])) {
					$settings['alias'] = $class;
				}
				$alias = $settings['alias'];

				$model = $_this->_duplicate($alias, $class);
				if ($model) {
					$_this->map($alias, $class);
					return $model;
				}

				App::uses($plugin . 'AppModel', $pluginPath . 'Model');
				App::uses($class, $pluginPath . 'Model');

				if (class_exists($class) || interface_exists($class)) {
					$reflection = new ReflectionClass($class);
					if ($reflection->isAbstract() || $reflection->isInterface()) {
						throw new CakeException(__d('cake_dev',
							'Cannot create instance of %s, as it is abstract or is an interface', $class));
					}
					$testing = isset($settings['testing']) ? $settings['testing'] : false;
					if ($testing) {
						$settings['ds'] = 'test';
						$defaultProperties = $reflection->getDefaultProperties();
						if (isset($defaultProperties['useDbConfig'])) {
							$useDbConfig = $defaultProperties['useDbConfig'];
							if ($availableDs === null) {
								$availableDs = array_keys(ConnectionManager::enumConnectionObjects());
							}
							if (in_array('test_' . $useDbConfig, $availableDs)) {
								$useDbConfig = 'test_' . $useDbConfig;
							}
							if (strpos($useDbConfig, 'test') === 0) {
								$settings['ds'] = $useDbConfig;
							}
						}
					}
					if ($reflection->getConstructor()) {
						// Invoking constructor with a reference to the controller
						$instance = $reflection->newInstance($settings, null, null, $controller);
					} else {
						$instance = $reflection->newInstance();
					}
					if ($strict && !$instance instanceof Model) {
						$instance = null;
					}
				}
				if (!isset($instance)) {
					$appModel = 'AppModel';
					if ($strict) {
						return false;
					} elseif ($plugin && class_exists($plugin . 'AppModel')) {
						$appModel = $plugin . 'AppModel';
					}

					if (!empty($appModel)) {
						$settings['name'] = $class;
						$instance = new $appModel($settings);
					}

					if (!isset($instance)) {
						trigger_error(__d('cake_dev',
							'(ClassRegistry::init() could not create instance of %s', $class), E_USER_WARNING);
						return false;
					}
				}
				$_this->map($alias, $class);
			}
		}

		if ($count > 1) {
			return true;
		}
		return $instance;
	}

}
