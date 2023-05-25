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

jimport('joomla.application.component.controlleradmin');

/**
 * Extends the JControllerAdmin methods.
 *
 * @since 	1.8
 */
class VREControllerAdmin extends JControllerAdmin
{
	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 */
	public function saveOrderAjax()
	{
		$input = JFactory::getApplication()->input;

		// retrieve order from request
		$order   = $input->get('order', array(), 'array');
		$filters = $input->get('filters', array(), 'array');

		// init table
		$table = JTableVRE::getInstance($this->getControllerName(), 'VRETable');

		// iterate specified records
		foreach ($order as $id => $ordering)
		{
			$data = array(
				'id'       => (int) $id,
				'ordering' => (int) $ordering,
			);

			// update ordering
			$table->save($data);
		}

		$where = '';

		if ($filters)
		{
			// create ordering conditions
			$dbo = JFactory::getDbo();

			$where = array();

			foreach ($filters as $k => $v)
			{
				// insert condition only if the table owns the specified property
				if (property_exists($table, $k))
				{
					$where[] = $dbo->qn($k) . ' = ' . $dbo->q($v);
				}
			}

			// join the conditions with AND glue
			$where = implode(' AND ', $where);
		}

		// rearrange table global ordering
		$table->reorder($where);

		// stop the process
		exit;
	}

	/**
	 * Returns the name of the controller.
	 *
	 * @return 	string
	 */
	public function getControllerName()
	{
		if (preg_match("/Controller(.*?)$/i", get_class($this), $match))
		{
			return strtolower($match[1]);
		}

		return null;
	}
}
