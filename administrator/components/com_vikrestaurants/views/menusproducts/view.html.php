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
 * VikRestaurants menus products view.
 *
 * @since 1.4
 */
class VikRestaurantsViewmenusproducts extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('menusproducts', 'ordering', 1);
		
		$filters = array();
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.products.keysearch', 'keysearch', '', 'string');
		$filters['id_menu']   = $app->getUserStateFromRequest('vre.products.id_menu', 'id_menu', 0, 'uint');
		$filters['status']    = $app->getUserStateFromRequest('vre.products.status', 'status', 0, 'uint');
		$filters['tag']       = $app->getUserStateFromRequest('vre.products.tag', 'tag', '', 'string');

		// set the toolbar
		$this->addToolBar($filters['status']);
		
		$rows = array();

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS `p`.*')
			->from($dbo->qn('#__vikrestaurants_section_product', 'p'))
			->where(1)
			->order($dbo->qn('p.' . $ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['keysearch'])
		{
			$q->where($dbo->qn('p.name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"));
		}
		
		switch ($filters['status'])
		{
			// published
			case 1:
				$q->where(array(
					$dbo->qn('p.hidden') . ' = 0',
					$dbo->qn('p.published') . ' = 1',
				));
				break;

			// unpublished
			case 2:
				$q->where(array(
					$dbo->qn('p.hidden') . ' = 0',
					$dbo->qn('p.published') . ' = 0',
				));
				break;

			// hidden
			case 3:
				$q->where($dbo->qn('p.hidden') . ' = 1');
				// always unset menus filtering
				$filters['id_menu'] = 0;
				break;

			// all except for hidden
			default:
				$q->where($dbo->qn('p.hidden') . ' = 0');
		}

		if ($filters['id_menu'])
		{
			$q->leftjoin($dbo->qn('#__vikrestaurants_section_product_assoc', 'a') . ' ON ' . $dbo->qn('a.id_product') . ' = ' . $dbo->qn('p.id'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_menus_section', 's') . ' ON ' . $dbo->qn('a.id_section') . ' = ' . $dbo->qn('s.id'));
			$q->where($dbo->qn('s.id_menu') . ' = ' . $filters['id_menu']);
		}

		if ($filters['tag'])
		{
			$q->andWhere(array(
				// only one tag
				$dbo->qn('p.tags') . ' = ' . $dbo->q($filters['tag']),
				// tag in the middle
				$dbo->qn('p.tags') . ' LIKE ' . $dbo->q("%,{$filters['tag']},%"),
				// first tag available
				$dbo->qn('p.tags') . ' LIKE ' . $dbo->q("{$filters['tag']},%"),
				// last tag available
				$dbo->qn('p.tags') . ' LIKE ' . $dbo->q("%,{$filters['tag']}"),
			), 'OR');
		}
			
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

		$def_lang = VikRestaurants::getDefaultLanguage();

		foreach ($rows as &$prod)
		{
			$prod['languages'] = array($def_lang);

			$q = $dbo->getQuery(true)
				->select($dbo->qn('tag'))
				->from($dbo->qn('#__vikrestaurants_lang_section_product'))
				->where($dbo->qn('id_product') . ' = ' . $prod['id']);
			
			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// merge default language with translation (filter to obtain a list with unique elements)
				$prod['languages'] = array_unique(array_merge($prod['languages'], $dbo->loadColumn()));
			}
		}

		// get menus
		
		$menus = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id', 'value'))
			->select($dbo->qn('name', 'text'))
			->from($dbo->qn('#__vikrestaurants_menus'))
			->order($dbo->qn('name') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$menus = $dbo->loadObjectList();
		}
		
		$new_type = OrderingManager::getSwitchColumnType('menusproducts', $ordering['column'], $ordering['type'], array(1, 2));
		$ordering = array($ordering['column'] => $new_type);
		
		$this->rows     = &$rows;
		$this->menus    = &$menus;
		$this->navbut   = &$navbut;
		$this->filters  = &$filters;
		$this->ordering = &$ordering;
		
		// display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @param 	integer  $status  The status filter set.
	 *
	 * @return 	void
	 */
	private function addToolBar($status = 0)
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWMENUSPRODUCTS'), 'vikrestaurants');

		$user = JFactory::getUser();
		
		if ($user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('menusproduct.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('menusproduct.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
			
			// status not "hidden"
			if ($status != 3)
			{
				JToolbarHelper::publishList('menusproduct.publish', JText::_('VRPUBLISH'));
				JToolbarHelper::spacer();
				
				JToolbarHelper::unpublishList('menusproduct.unpublish', JText::_('VRUNPUBLISH'));
				JToolbarHelper::divider();
			}
		}

		if ($user->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'menusproduct.delete', JText::_('VRDELETE'));	
		}
	}

	/**
	 * Checks for advanced filters set in the request.
	 *
	 * @return 	boolean  True if active, otherwise false.
	 *
	 * @since 	1.8
	 */
	protected function hasFilters()
	{
		return ($this->filters['status'] || $this->filters['id_menu'] || $this->filters['tag']);
	}
}
