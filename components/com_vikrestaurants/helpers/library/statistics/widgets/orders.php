<?php
/** 
 * @package     VikRestaurants
 * @subpackage  com_vikrestaurants
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Widget class used to fetch a list of orders.
 * The widgets supports the following lists:
 * - latest
 * - incoming
 * - current
 *
 * Displays a table of take-away orders.
 *
 * @since 1.8
 */
class VREStatisticsWidgetOrders extends VREStatisticsWidget
{
	/**
	 * @override
	 * Returns the form parameters required to the widget.
	 *
	 * @return 	array
	 */
	public function getForm()
	{
		return array(
			/**
			 * The maximum number of items to load.
			 *
			 * @var select
			 */
			'items' => array(
				'type'     => 'select',
				'label'    => JText::_('VRMANAGETKRES22'),
				'default'  => 10,
				'options'  => array(
					5,
					10,
					15,
					20,
					30,
					50,
				),
			),

			/**
			 * Flag used to check whether the latest table
			 * should be displayed or not.
			 *
			 * @var select
			 */
			'latest' => array(
				'type'    => 'checkbox',
				'label'   => JText::_('VRE_STATS_WIDGET_RESERVATIONS_LATEST_FIELD'),
				'help'    => JText::_('VRE_STATS_WIDGET_RESERVATIONS_LATEST_FIELD_HELP'),
				'default' => true, 
			),

			/**
			 * Flag used to check whether the incoming table
			 * should be displayed or not.
			 *
			 * @var select
			 */
			'incoming' => array(
				'type'    => 'checkbox',
				'label'   => JText::_('VRE_STATS_WIDGET_RESERVATIONS_INCOMING_FIELD'),
				'help'    => JText::_('VRE_STATS_WIDGET_RESERVATIONS_INCOMING_FIELD_HELP'),
				'default' => true, 
			),

			/**
			 * Flag used to check whether the current table
			 * should be displayed or not.
			 *
			 * @var select
			 */
			'current' => array(
				'type'    => 'checkbox',
				'label'   => JText::_('VRE_STATS_WIDGET_RESERVATIONS_CURRENT_FIELD'),
				'help'    => JText::_('VRE_STATS_WIDGET_RESERVATIONS_CURRENT_FIELD_HELP'),
				'default' => true, 
			),
		);
	}

	/**
	 * @override
	 * Checks whether the specified group is supported
	 * by the widget. Children classes can override this
	 * method to drop the support for a specific group.
	 *
	 * This widget supports only the "takeaway" group.
	 *
	 * @param 	string 	 $group  The group to check.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public function isSupported($group)
	{
		return $group == 'takeaway' ? true : false;
	}

	/**
	 * @override
	 * Loads the dataset(s) that will be recovered asynchronously
	 * for being displayed within the widget.
	 *
	 * It is possible to return an array of records to be passed
	 * to a chart or directly the HTML to replace.
	 *
	 * @return 	mixed
	 */
	public function getData()
	{
		$dbo    = JFactory::getDbo();
		$config = VREFactory::getConfig();

		// get number of items to display
		$limit = $this->getOption('items', 10);

		// get average time of stay
		$avg = VREFactory::getConfig()->getUint('tkminint');
		$now = VikRestaurants::now();

		$data = array();

		// if we are in the front-end, make sure the
		// user is an operator (throws exception)
		if (JFactory::getApplication()->isClient('site'))
		{
			// import operator user helper
			VRELoader::import('library.operator.user');
			// Load operator details. In case the user is
			// not an operator, an exception will be thrown
			$operator = VREOperatorUser::getInstance();
		}
		else
		{
			$operator = null;
		}

		// check if we should fetch the latest orders
		if ($this->getOption('latest'))
		{
			$data['latest'] = array();

			$q = $dbo->getQuery(true)
				->select($dbo->qn('r.id'))
				->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r'))
				->order($dbo->qn('r.id') . ' DESC');

			// apply restrictions depending on the current operator
			$this->applyOperatorRestrictions($q, $operator);

			$dbo->setQuery($q, 0, $limit);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$data['latest'] = $dbo->loadColumn();
			}
		}

		// check if we should fetch the incoming orders
		if ($this->getOption('incoming'))
		{
			$data['incoming'] = array();

			// go 2 hours in the past
			$prev_2_hours = strtotime('-2 hours', $now);

			// shows the orders close to the next shift
			$q = $dbo->getQuery(true)
				->select('r.*')
				->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r'))
				// check if the order is incoming
				->where($dbo->qn('r.checkin_ts') . ' >= ' . $prev_2_hours)
				->where($dbo->qn('r.checkin_ts') . ' - ' . ($avg * 60) . ' > ' . $now)
				// hide in case the order has been already carried on
				->where($dbo->qn('r.current') . ' IS NULL')
				// take only confirmed orders
				->where($dbo->qn('r.status') . ' = ' . $dbo->q('CONFIRMED'))
				->order(sprintf(
					'IFNULL(%s, %s) ASC',
					$dbo->qn('r.preparation_ts'),
					$dbo->qn('r.checkin_ts')
				));

			// apply restrictions depending on the current operator
			$this->applyOperatorRestrictions($q, $operator);

			$dbo->setQuery($q, 0, $limit);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$data['incoming'] = $dbo->loadColumn();
			}
		}

		// check if we should fetch the current orders
		if ($this->getOption('current'))
		{
			$data['current'] = array();

			// shows the order that should be currently prepared
			$q = $dbo->getQuery(true)
				->select('r.*')
				->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r'))
				// check if the order is incoming
				->where($dbo->qn('r.checkin_ts') . ' - ' . ($avg * 60) . ' <= ' . $now)
				->where($dbo->qn('r.checkin_ts') . ' + ' . ($avg * 60) . ' > ' . $now)
				// make sure the order hasn't been already completed
				->where($dbo->qn('r.current') . ' IS NULL')
				// otherwise take if it was manually attached to the current widget
				->orWhere($dbo->qn('r.current') . ' = 1')
				// take only confirmed orders
				->andWhere($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'))
				->order(sprintf(
					'IFNULL(%s, %s) ASC',
					$dbo->qn('r.preparation_ts'),
					$dbo->qn('r.checkin_ts')
				));

			// apply restrictions depending on the current operator
			$this->applyOperatorRestrictions($q, $operator);

			$dbo->setQuery($q, 0, $limit);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$data['current'] = $dbo->loadColumn();
			}
		}

		// get current language tag
		$langtag = JFactory::getLanguage()->getTag();

		foreach ($data as $k => $ids)
		{
			$list = array();

			// iterate all IDs
			foreach ($ids as $id)
			{
				// Load order.
				// If the same order was already loaded,
				// the cached record will be used.
				$order = VREOrderFactory::getOrder($id, $langtag);

				// push order in list
				$list[] = $order;
			}

			// define args for layout file
			$args = array(
				'orders' => $list,
				'widget' => $this,
			);

			// replace orders list with HTML layout
			$data[$k] = JLayoutHelper::render('statistics.widgets.orders.' . $k, $args);
		}

		return $data;
	}

	/**
	 * Applies any restrictions to avoid accessing orders that
	 * shouldn't be seen by the specified operator.
	 *
	 * @param 	mixed 	&$query    The query builder.
	 * @param 	mixed 	$operator  The operator instance.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.2
	 */
	protected function applyOperatorRestrictions(&$query, $operator)
	{
		if (!$operator)
		{
			// not an operator, go ahead
			return;
		}

		$dbo = JFactory::getDbo();

		// check if the operator can see all the orders
		if (!$operator->canSeeAll())
		{
			// retrieve only the orders assigned to the operator
			$query->where($dbo->qn('r.id_operator') . ' = ' . (int) $operator->get('id'));
		}
	}
}
