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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * VikRestaurants order history view.
 *
 * @since 1.8
 */
class VikRestaurantsVieworderhistory extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();

		// force blank component layout
		$input->set('tmpl', 'component');
		
		$filters['id']    = $input->get('id', 0, 'uint');
		$filters['group'] = $input->get('group', 1, 'uint');

		$lim 	= 10; // always use only 10 records, due to the size of the blocks
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS l.*')
			->select($dbo->qn('o.code'))
			->select($dbo->qn('o.firstname'))
			->select($dbo->qn('o.lastname'))
			->from($dbo->qn('#__vikrestaurants_operator_log', 'l'))
			->leftjoin($dbo->qn('#__vikrestaurants_operator', 'o') . ' ON ' . $dbo->qn('o.id') . ' = ' . $dbo->qn('l.id_operator'))
			->where($dbo->qn('l.id_reservation') . ' = ' . $filters['id'])
			->where($dbo->qn('l.group') . ' = ' . $filters['group'])
			->order($dbo->qn('l.createdon') . ' DESC');

		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();

		// assert limit used for list query
		$this->assertListQuery($lim0, $lim);

		if ($dbo->getNumRows())
		{
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
			$navbut = '<table align="center"><tr><td>' . $pageNav->getListFooter() . '</td></tr></table>';
		}

		// load reservation logs

		$paylog = array();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('payment_log'));

		if ($filters['group'] == 1)
		{
			$q->from($dbo->qn('#__vikrestaurants_reservation'));
		}
		else
		{
			$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation'));
		}

		$q->where($dbo->qn('id') . ' = ' . $filters['id']);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$buffer = $dbo->loadResult();

			// check if we have some logs
			if (preg_match_all("/-+\s+\|([a-zA-Z0-9\/\-\.: ]+)\|\s+-+/", $buffer, $match))
			{
				// split payment logs
				$chunks = preg_split("/-+\s+\|([a-zA-Z0-9\/\-\.: ]+)\|\s+-+/", $buffer);
				// pop first empty string
				array_shift($chunks);

				for ($i = 0; $i < count($chunks); $i++)
				{
					// create log record with date and content
					$tmp = array();
					$tmp['createdon'] = $match[1][$i];
					$tmp['log']       = trim(@$chunks[$i]);

					if (!preg_match("/<pre(.*?)>/", $tmp['log']))
					{
						// wrap log within <pre>
						$tmp['log'] = '<pre>' . $tmp['log'] . '</pre>';
					}

					$paylog[] = $tmp;
				}
			}
		}
		
		$this->rows 	= &$rows;
		$this->payLog 	= &$paylog;
		$this->navbut 	= &$navbut;
		$this->filters 	= &$filters;
		
		// display the template (default.php)
		parent::display($tpl);
	}
}
