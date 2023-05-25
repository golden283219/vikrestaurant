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
 * Widget class used to display an overview of the dishes
 * to prepare for the kitchen.
 *
 * @since 1.8
 */
class VREStatisticsWidgetKitchen extends VREStatisticsWidget
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
			 * Flag used to choose whether to show or not the
			 * outgoing courses next to the wall of bills.
			 *
			 * @var checkbox
			 */
			'outgoing' => array(
				'type'    => 'checkbox',
				'label'   => JText::_('VRE_STATS_WIDGET_KITCHEN_OUTGOING_COURSES'),
				'default' => 1,
			),

			/**
			 * Flag used to choose whether to show or not tables
			 * that haven't ordered yet anything.
			 *
			 * @var checkbox
			 *
			 * @since 1.8.1
			 */
			'emptybill' => array(
				'type'    => 'checkbox',
				'label'   => JText::_('VRE_STATS_WIDGET_KITCHEN_EMPTY_BILL'),
				'help'    => JText::_('VRE_STATS_WIDGET_KITCHEN_EMPTY_BILL_HELP'),
				'default' => 1,
			),
		);
	}

	/**
	 * @override
	 * Checks whether the specified group is supported
	 * by the widget. Children classes can override this
	 * method to drop the support for a specific group.
	 *
	 * This widget supports only the "restaurant" group.
	 *
	 * @param 	string 	 $group  The group to check.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public function isSupported($group)
	{
		return $group == 'restaurant' ? true : false;
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

		$now = VikRestaurants::now();

		$start = strtotime('00:00:00', $now);
		$end   = strtotime('23:59:59', $now);

		$data = array();

		$data['filters'] = array();
		$data['filters']['outgoing']  = $this->getOption('outgoing');
		$data['filters']['emptybill'] = $this->getOption('emptybill');

		$data['reservations'] = array();
		$data['waitinglist']  = array();

		$q = $dbo->getQuery(true);

		if ($data['filters']['emptybill'])
		{
			// base the query on the reservation in order to retrieve also
			// the tables with empty bills
			$q->from($dbo->qn('#__vikrestaurants_reservation', 'r'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_res_prod_assoc', 'p') . ' ON ' . $dbo->qn('p.id_reservation') . ' = ' . $dbo->qn('r.id'));
			
		}
		else
		{
			// base the query on the ordered products in order to retrieve
			// only the tables that already ordered something
			$q->from($dbo->qn('#__vikrestaurants_res_prod_assoc', 'p'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_reservation', 'r') . ' ON ' . $dbo->qn('p.id_reservation') . ' = ' . $dbo->qn('r.id'));
		}
		
		$q->select('p.*')
			->select($dbo->qn('i.tags'))
			->select($dbo->qn('r.id_table'))
			->select($dbo->qn('r.id', 'rid'))
			->select($dbo->qn('t.id_room'))
			->select($dbo->qn('t.name', 'table_name'))
			->select($dbo->qn('rm.name', 'room_name'))
			->select(sprintf(
				'CONCAT_WS(\' \', %s, %s) AS %s',
				$dbo->qn('o.firstname'),
				$dbo->qn('o.lastname'),
				$dbo->qn('operator_name')
			))
			->leftjoin($dbo->qn('#__vikrestaurants_section_product', 'i') . ' ON ' . $dbo->qn('p.id_product') . ' = ' . $dbo->qn('i.id'))
			->leftjoin($dbo->qn('#__vikrestaurants_table', 't') . ' ON ' . $dbo->qn('r.id_table') . ' = ' . $dbo->qn('t.id'))
			->leftjoin($dbo->qn('#__vikrestaurants_room', 'rm') . ' ON ' . $dbo->qn('t.id_room') . ' = ' . $dbo->qn('rm.id'))
			->leftjoin($dbo->qn('#__vikrestaurants_operator', 'o') . ' ON ' . $dbo->qn('r.id_operator') . ' = ' . $dbo->qn('o.id'))
			// take only the reservations in the nearly hours
			->where(array(
				'(' . $dbo->qn('r.checkin_ts') . ' - 3600 * 2) <= ' . $now,
				'(' . $dbo->qn('r.checkin_ts') . ' + 3600 * 3) >= ' . $now,
				$dbo->qn('p.status') . ' IS NULL',
			))
			// or take the reservations with dishes under preparation
			->orWhere(array(
				$dbo->qn('p.status') . ' IS NOT NULL',
				$dbo->qn('p.status') . ' IN (0, 1)',
			), 'AND')
			// than make sure the reservation is within the current day,
			// the status is CONFIRMED and the bill is still open
			->andWhere(array(
				$dbo->qn('r.id_parent') . ' = 0',
				$dbo->qn('r.status') . ' = ' . $dbo->q('CONFIRMED'),
				$dbo->qn('r.bill_closed') . ' = 0',
				$dbo->qn('r.checkin_ts') . ' BETWEEN ' . $start . ' AND ' . $end,
			), 'AND')
			->order($dbo->qn('r.checkin_ts') . ' ASC')
			->order($dbo->qn('p.id') . ' ASC');

		if ($operator && $operator->get('rooms'))
		{
			// take only the supported rooms (already comma-separated)
			$q->andWhere($dbo->qn('t.id_room') . ' IN (' . $operator->get('rooms') . ')');
		}

		// check if the operator can see all the reservations
		if ($operator && !$operator->canSeeAll())
		{
			// check if the operator can self-assign reservations
			if ($operator->canAssign())
			{
				// retrieve reservations assigned to this operator and reservations
				// free of assignments
				$q->andWhere($dbo->qn('r.id_operator') . ' IN (0, ' . (int) $operator->get('id') . ')');
			}
			else
			{
				// retrieve only the reservations assigned to the operator
				$q->andWhere($dbo->qn('r.id_operator') . ' = ' . (int) $operator->get('id'));
			}
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// group dishes by reservation ID
			foreach ($dbo->loadObjectList() as $dish)
			{
				// in case of an operator, make sure it can access the dish
				if (!$operator || $operator->canSeeProduct($dish->tags))
				{
					if ($dish->status != 1)
					{
						// push dish within the reservations pool
						$pool = &$data['reservations'];
					}
					else
					{
						// push dish within the waiting list
						$pool = &$data['waitinglist'];
					}

					// create reservation record if not set
					if (!isset($pool[$dish->rid]))
					{
						$res = new stdClass;
						$res->id          = $dish->rid;
						$res->operator    = $dish->operator_name;
						$res->lastUpdate  = 0;
						$res->elapsedTime = 0;
						$res->dishes      = array();

						$res->table = new stdClass;
						$res->table->id   = $dish->id_table;
						$res->table->name = $dish->table_name;

						$res->room = new stdClass;
						$res->room->id   = $dish->id_room;
						$res->room->name = $dish->room_name;

						$pool[$dish->rid] = $res;
					}
				
					// add dish to reservation, if any
					if ($dish->id)
					{
						// get status code
						$dish->code = JHtml::_('vikrestaurants.rescode', $dish->rescode, 3, $dish->id);

						// check if the reservation code owns a creation date time
						if ($dish->code && !empty($dish->code->createdon))
						{
							// take the last update time
							$res->lastUpdate = max(array($res->lastUpdate, $dish->code->createdon));

							if ($res->lastUpdate)
							{
								$res->elapsedTime = floor(($now - $res->lastUpdate) / 60);
							}
						}

						$pool[$dish->rid]->dishes[] = $dish;
					}
				}
			}
		}

		// include a reference of this widget
		$data['widget'] = $this;

		// return overview layout
		return JLayoutHelper::render('statistics.widgets.kitchen.wall', $data);
	}
}
