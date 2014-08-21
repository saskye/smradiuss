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



App::uses('PaginatorComponent', 'Controller/Component');
App::uses('Hash', 'Utility');

/**
 * @class AWITPaginatorComponent
 * Overrides PaginatorComponent to provide page statistics called via AWITController
 * to expose getPaginationPages for page stats
 *
 * This component is used to handle automatic model data pagination. The primary way to use this
 * component is to call the paginate() method. There is a convenience wrapper on Controller as well.
 *
 * ### Configuring pagination
 *
 * You configure pagination using the PaginatorComponent::$settings. This allows you to configure
 * the default pagination behavior in general or for a specific model. General settings are used when there
 * are no specific model configuration, or the model you are paginating does not have specific settings.
 *
 * {{{
 *	$this->Paginator->settings = array(
 *		'limit' => 20,
 *		'maxLimit' => 100
 *	);
 * }}}
 *
 * The above settings will be used to paginate any model. You can configure model specific settings by
 * keying the settings with the model name.
 *
 * {{{
 *	$this->Paginator->settings = array(
 *		'Post' => array(
 *			'limit' => 20,
 *			'maxLimit' => 100
 *		),
 *		'Comment' => array( ... )
 *	);
 * }}}
 *
 * This would allow you to have different pagination settings for `Comment` and `Post` models.
 *
 * #### Paginating with custom finders
 *
 * You can paginate with any find type defined on your model using the `findType` option.
 *
 * {{{
 * $this->Paginator->settings = array(
 *		'Post' => array(
 *			'findType' => 'popular'
 *		)
 * );
 * }}}
 *
 * Would paginate using the `find('popular')` method.
 *
 * @package       Cake.Controller.Component
 * @link http://book.cakephp.org/2.0/en/core-libraries/components/pagination.html
 */
class AWITPaginatorComponent extends PaginatorComponent {



	/**
	 * @method getPaginationPages
	 * Clone of paginate() which returns an array for use with RESTFul pagination
	 *
	 * @param Model|string $object Model to paginate (e.g: model instance, or 'Model', or 'Model.InnerModel')
	 * @param string|array $scope Additional find conditions to use while paginating
	 * @param array $whitelist List of allowed fields for ordering. This allows you to prevent ordering
	 *   on non-indexed, or undesirable columns. See PaginatorComponent::validateSort() for additional details
	 *   on how the whitelisting and sort field validation works.
	 * @return array Model query results
	 * @throws MissingModelException
	 * @throws NotFoundException
	 */
	public function getPaginationPages($object = null, $scope = array(), $whitelist = array()) {
		if (is_array($object)) {
			$whitelist = $scope;
			$scope = $object;
			$object = null;
		}

		$object = $this->_getObject($object);

		if (!is_object($object)) {
			throw new MissingModelException($object);
		}

		$options = $this->mergeOptions($object->alias);
		$options = $this->validateSort($object, $options, $whitelist);
		$options = $this->checkLimit($options);

		$conditions = $fields = $order = $limit = $page = $recursive = null;

		if (!isset($options['conditions'])) {
			$options['conditions'] = array();
		}

		$type = 'all';

		if (isset($options[0])) {
			$type = $options[0];
			unset($options[0]);
		}

		extract($options);

		if (is_array($scope) && !empty($scope)) {
			$conditions = array_merge($conditions, $scope);
		} elseif (is_string($scope)) {
			$conditions = array($conditions, $scope);
		}
		if ($recursive === null) {
			$recursive = $object->recursive;
		}

		$extra = array_diff_key($options, compact(
			'conditions', 'fields', 'order', 'limit', 'page', 'recursive'
		));

		if (!empty($extra['findType'])) {
			$type = $extra['findType'];
			unset($extra['findType']);
		}

		if ($type !== 'all') {
			$extra['type'] = $type;
		}

		if (intval($page) < 1) {
			$page = 1;
		}
		$page = $options['page'] = (int)$page;

		if ($object->hasMethod('paginate')) {
			$results = $object->paginate(
				$conditions, $fields, $order, $limit, $page, $recursive, $extra
			);
		} else {
			$parameters = compact('conditions', 'fields', 'order', 'limit', 'page');
			if ($recursive != $object->recursive) {
				$parameters['recursive'] = $recursive;
			}
			$results = $object->find($type, array_merge($parameters, $extra));
		}
		$defaults = $this->getDefaults($object->alias);
		unset($defaults[0]);

		if (!$results) {
			$count = 0;
		} elseif ($object->hasMethod('paginateCount')) {
			$count = $object->paginateCount($conditions, $recursive, $extra);
		} else {
			$parameters = compact('conditions');
			if ($recursive != $object->recursive) {
				$parameters['recursive'] = $recursive;
			}
			$count = $object->find('count', array_merge($parameters, $extra));
		}
		$pageCount = intval(ceil($count / $limit));
		$requestedPage = $page;
		$page = max(min($page, $pageCount), 1);

		$paging = array(
			'page' => $page,
			'current' => count($results),
			'count' => $count,
			'prevPage' => ($page > 1),
			'nextPage' => ($count > ($page * $limit)),
			'pageCount' => $pageCount,
			'order' => $order,
			'limit' => $limit,
			'options' => Hash::diff($options, $defaults),
			'paramType' => $options['paramType']
		);

		return $paging;

	}



}
