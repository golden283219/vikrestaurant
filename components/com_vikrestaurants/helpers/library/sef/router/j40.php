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

use Joomla\CMS\Component\Router\RouterBase;

VRELoader::import('library.sef.router');

/**
 * Routing class for com_vikrestaurants component.
 * Compatible with Joomla 4.0 or higher.
 *
 * @since  1.8
 */
class VikRestaurantsRouter extends RouterBase
{
	/**
	 * Use trait for router helping functions.
	 *
	 * @since 1.8.3
	 */
	use VRESefRouter;

	/**
	 * The current language tag.
	 *
	 * @var string
	 */
	protected $langtag;

	/**
	 * Class constructor.
	 *
	 * @param   JApplicationCms  $app   Application-object that the router should use.
	 * @param   JMenu            $menu  Menu-object that the router should use.
	 */
	public function __construct($app = null, $menu = null)
	{
		// invoke parent constructor
		parent::__construct($app, $menu);

		$this->langtag = JFactory::getLanguage()->getTag();

		VRELoader::import('library.sef.helper');
	}

	/**
	 * Prepare-method for URLs.
	 * This method is meant to validate and complete the URL parameters.
	 * For example it can add the Itemid or set a language parameter.
	 * This method is executed on each URL, regardless of SEF mode switched
	 * on or not.
	 *
	 * @param   array  $query  An associative array of URL arguments.
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since 	1.8.3
	 */
	public function preprocess($query)
	{
		$dbo = JFactory::getDbo();

		$active = $this->menu->getActive();

		if (!isset($query['view']))
		{
			// view not set, do not go ahead
			return $query;
		}

		// confirm reservation

		if ($query['view'] == 'confirmres')
		{
			// try to obtain the proper Itemid that belong to the confirmres view
			$itemid = $this->getProperItemID('confirmres');

			if ($itemid)
			{
				// overwrite the Itemid set in the query in order
				// to rewrite the base URI
				$query['Itemid'] = $itemid;
			}
		}

		// confirm take-away order

		else if ($query['view'] == 'takeawayconfirm')
		{
			// try to obtain the proper Itemid that belong to the takeawayconfirm view
			$itemid = $this->getProperItemID('takeawayconfirm');

			if ($itemid)
			{
				// overwrite the Itemid set in the query in order
				// to rewrite the base URI
				$query['Itemid'] = $itemid;
			}
		}

		// order | allorders

		else if (in_array($query['view'], array('order', 'reservation', 'allorders', 'orderdishes')))
		{
			// try to obtain the proper Itemid that belongs to the allorders view
			$itemid = $this->getProperItemID('allorders');

			if (!$itemid)
			{
				// fallback to obtain the proper Itemid that belong to the order view
				$itemid = $this->getProperItemID($query['view']);
			}

			if ($itemid)
			{
				// overwrite the Itemid set in the query in order
				// to rewrite the base URI
				$query['Itemid'] = $itemid;
			}
		}

		// take-away menus list

		else if ($query['view'] == 'takeaway')
		{
			if (isset($query['takeaway_menu']))
			{
				// arguments used to check if the active menu item
				// matches the values set in query string
				$args = array(
					'view'          => 'takeaway',
					'takeaway_menu' => $query['takeaway_menu'],
				);

				/**
				 * Make sure the ID of the item is not set within the query of the menu item.
				 * This because the link may be a self redirect, causing duplicated aliases.
				 * For example, if we have something like:
				 * /take-away/pizzas/
				 * we need to avoid pushing the alias of the menu.
				 */
				if (!$this->matchItemArguments($active, $args))
				{
					/**
					 * Try to look for a specific menu item for the specified take-away menu.
					 *
					 * @since 1.8.5
					 */
					$itemid = $this->getProperItemID('takeaway', array('takeaway_menu' => $query['takeaway_menu']));

					if (!$itemid)
					{
						/**
						 * Obtain the proper Itemid that belong to the take-away list view.
						 * Exclude any menu item that reports the takeaway_menu filter.
						 *
						 * @since 1.8.5
						 */
						$itemid = $this->getProperItemID('takeaway', [], ['takeaway_menu']);
					}

					if ($itemid)
					{
						// overwrite the Itemid set in the query in order
						// to rewrite the base URI
						$query['Itemid'] = $itemid;
					}
				}
				else
				{
					// force Item ID to use the ID of the current menu item
					$query['Itemid'] = $active->id;
				}
			}
			else
			{
				/**
				 * Obtain the proper Itemid that belong to the take-away list view.
				 * Exclude any menu item that reports the takeaway_menu filter.
				 *
				 * @since 1.8.5
				 */
				$itemid = $this->getProperItemID('takeaway', [], ['takeaway_menu']);

				if ($itemid)
				{
					// overwrite the Itemid set in the query in order
					// to rewrite the base URI
					$query['Itemid'] = $itemid;
				}
			}
		}

		// take-away item details

		else if ($query['view'] == 'takeawayitem')
		{
			if (isset($query['takeaway_item']))
			{
				// arguments used to check if the active menu item
				// matches the values set in query string
				$args = array(
					'view'          => 'takeawayitem',
					'takeaway_item' => $query['takeaway_item'],
				);

				/**
				 * Make sure the ID of the item is not set within the query of the menu item.
				 * This because the link may be a self redirect, causing duplicated aliases.
				 * For example, if we have something like:
				 * /take-away/pizza-margherita/
				 * we need to avoid pushing the alias of the item.
				 */
				if (!$this->matchItemArguments($active, $args))
				{
					// try to obtain the proper Itemid that belong directly to the item details view
					$itemid = $this->getProperItemID('takeawayitem', array('takeaway_item' => $query['takeaway_item']));

					if (!$itemid)
					{
						/**
						 * Try to look for a specific menu item for the take-away menu
						 * to which the item belongs.
						 *
						 * @since 1.8.5
						 */
						$q = $dbo->getQuery(true)
							->select($dbo->qn('id_takeaway_menu'))
							->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry'))
							->where($dbo->qn('id') . ' = ' . (int) $query['takeaway_item']);

						$dbo->setQuery($q, 0, 1);
						$dbo->execute();

						if ($dbo->getNumRows())
						{
							$id_menu = (int) $dbo->loadResult();

							// try to look for a specific menu item for the menu to which the item belong
							$itemid = $this->getProperItemID('takeaway', array('takeaway_menu' => $id_menu));
						}

						if (!$itemid)
						{
							/**
							 * Obtain the proper Itemid that belong to the take-away list view.
							 * Exclude any menu item that reports the takeaway_menu filter.
							 *
							 * @since 1.8.5
							 */
							$itemid = $this->getProperItemID('takeaway', [], ['takeaway_menu']);
						}
					}

					if ($itemid)
					{
						// overwrite the Itemid set in the query in order
						// to rewrite the base URI
						$query['Itemid'] = $itemid;
					}
				}
			}
		}

		// menu details

		else if ($query['view'] == 'menudetails')
		{
			if (isset($query['id']))
			{
				// arguments used to check if the active menu item
				// matches the values set in query string
				$args = array(
					'view' => 'menudetails',
					'id'   => $query['id'],
				);

				/**
				 * Make sure the ID of the item is not set within the query of the menu item.
				 * This because the link may be a self redirect, causing duplicated aliases.
				 * For example, if we have something like:
				 * /list/menu/
				 * we need to avoid pushing the alias of the item.
				 */
				if (!$this->matchItemArguments($active, $args))
				{
					// try to obtain the proper Itemid that belong directly to the item details view
					$itemid = $this->getProperItemID('menudetails', array('id' => $query['id']));

					if (!$itemid)
					{
						// try to obtain the proper Itemid that contains the specified menu
						$itemid = $this->getProperItemID('menuslist', array('id_menus' => array($query['id'])));

						if (!$itemid)
						{
							// fallback obtain the proper Itemid that belong to the take-away list view
							$itemid = $this->getProperItemID('menuslist');
						}
					}

					if ($itemid)
					{
						// overwrite the Itemid set in the query in order
						// to rewrite the base URI
						$query['Itemid'] = $itemid;
					}
				}
			}
		}

		// fallback

		else
		{
			// check whether the requested view owns an Item ID
			$itemid = $this->getProperItemID($query['view']);

			if ($itemid)
			{
				// set new Item ID
				$query['Itemid'] = $itemid;
			}
		}

		return $query;
	}

	/**
	 * Build method for URLs.
	 * This method is meant to transform the query parameters into a more human
	 * readable form. It is only executed when SEF mode is switched on.
	 *
	 * @param   array  &$query  An array of URL arguments.
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	public function build(&$query)
	{
		$dbo = JFactory::getDbo();

		$active = $this->menu->getActive();
		
		$segments = array();

		if (!isset($query['view']) || !$this->isActive())
		{
			// view not set or router is disabled
			return $segments;
		}

		// order | allorders

		if (in_array($query['view'], array('order', 'reservation', 'allorders', 'orderdishes')))
		{
			// build URL for order details
			if (isset($query['ordnum']) && isset($query['ordkey']))
			{
				// prepend view name to differentiate orders and reservations
				switch ($query['view'])
				{
					case 'reservation':
					case 'orderdishes':
						$segments[] = JText::_('VRE_SEF_RESERVATION');
						break;

					case 'order':
						$segments[] = JText::_('VRE_SEF_ORDER');
						break;
				}

				// ordnum and ordkey must be set
				$segments[] = VRESefHelper::stringToAlias(intval($query['ordnum']) . "-" . $query['ordkey']);

				if ($query['view'] == 'orderdishes')
				{
					$segments[] = JText::_('VRE_SEF_ORDERDISHES');
				}

				// unset ord num and ord key
				unset($query['ordnum']);
				unset($query['ordkey']);
			}

			unset($query['view']);
		}

		// take-away menus list

		else if ($query['view'] == 'takeaway')
		{
			if (isset($query['takeaway_menu']))
			{
				// arguments used to check if the active menu item
				// matches the values set in query string
				$args = array(
					'view'          => 'takeaway',
					'takeaway_menu' => $query['takeaway_menu'],
				);

				/**
				 * Make sure the ID of the item is not set within the query of the menu item.
				 * This because the link may be a self redirect, causing duplicated aliases.
				 * For example, if we have something like:
				 * /take-away/pizzas/
				 * we need to avoid pushing the alias of the menu.
				 */
				if (!$this->matchItemArguments($active, $args))
				{
					// search for an item ID that matches our query
					$itemid = $this->getProperItemID('takeaway', ['takeaway_menu' => $query['takeaway_menu']]);

					if (!$itemid && $query['takeaway_menu'])
					{
						// recover menu alias
						$alias = VRESefHelper::getRecordAlias($query['takeaway_menu'], 'tkmenu', $this->langtag);

						if ($alias)
						{
							// alias found, push it within the segments array
							$segments[] = $alias;

							// unset item ID from query
							unset($query['takeaway_menu']);
						}
					}
					else
					{
						// empty menu ID, don't keep it in URL
						unset($query['takeaway_menu']);
					}
				}
				else
				{
					// remove take-away menu from query string
					unset($query['takeaway_menu']);
				}
			}

			unset($query['view']);
		}

		// take-away item details

		else if ($query['view'] == 'takeawayitem')
		{
			if (isset($query['takeaway_item']))
			{
				// arguments used to check if the active menu item
				// matches the values set in query string
				$args = array(
					'view'          => 'takeawayitem',
					'takeaway_item' => $query['takeaway_item'],
				);

				/**
				 * Make sure the ID of the item is not set within the query of the menu item.
				 * This because the link may be a self redirect, causing duplicated aliases.
				 * For example, if we have something like:
				 * /take-away/pizza-margherita/
				 * we need to avoid pushing the alias of the item.
				 */
				if (!$this->matchItemArguments($active, $args))
				{
					// try to obtain the proper Itemid that belong directly to the item details view
					$itemid = $this->getProperItemID('takeawayitem', array('takeaway_item' => $query['takeaway_item']));

					if (!$itemid)
					{
						// recover parent ID
						$q = $dbo->getQuery(true)
							->select($dbo->qn('id_takeaway_menu'))
							->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry'))
							->where($dbo->qn('id') . ' = ' . (int) $query['takeaway_item']);

						$dbo->setQuery($q, 0, 1);
						$dbo->execute();

						if ($dbo->getNumRows())
						{
							$id_menu = $dbo->loadResult();

							// search for an item ID that matches our query
							$itemid = $this->getProperItemID('takeaway', ['takeaway_menu' => $id_menu]);

							if (!$itemid)
							{
								// recover menu alias
								$alias = VRESefHelper::getRecordAlias($dbo->loadResult(), 'tkmenu', $this->langtag);

								if ($alias)
								{
									// alias found, push it within the segments array
									$segments[] = $alias;
								}
							}
						}

						// recover product alias
						$alias = VRESefHelper::getRecordAlias($query['takeaway_item'], 'tkentry', $this->langtag);

						if ($alias)
						{
							// alias found, push it within the segments array
							$segments[] = $alias;

							// unset item ID from query
							unset($query['takeaway_item']);
						}
					}
					else
					{
						// unset item ID from query
						unset($query['takeaway_item']);
					}
				}
				else
				{
					unset($query['takeaway_item']);
				}

				// check if we should route the variation too
				if (isset($query['id_option']))
				{
					if ($query['id_option'])
					{
						// recover variation alias
						$alias = VRESefHelper::getRecordAlias($query['id_option'], 'tkentryoption', $this->langtag);

						if ($alias)
						{
							// alias found, push it within the segments array
							$segments[] = $alias;

							// unset variation ID from query
							unset($query['id_option']);
						}
					}
					else
					{
						// empty ID, don't keep it within URL
						unset($query['id_option']);
					}
				}

				unset($query['view']);
			}
		}

		// menu details

		else if ($query['view'] == 'menudetails')
		{
			if (isset($query['id']))
			{
				// arguments used to check if the active menu item
				// matches the values set in query string
				$args = array(
					'view' => 'menudetails',
					'id'   => $query['id'],
				);

				/**
				 * Make sure the ID of the item is not set within the query of the menu item.
				 * This because the link may be a self redirect, causing duplicated aliases.
				 * For example, if we have something like:
				 * /list/menu/
				 * we need to avoid pushing the alias of the item.
				 */
				if (!$this->matchItemArguments($active, $args))
				{
					// try to obtain the proper Itemid that belong directly to the item details view
					$itemid = $this->getProperItemID('menudetails', array('id' => $query['id']));

					if (!$itemid)
					{
						// try to obtain the proper Itemid that contains the specified menu
						$itemid = $this->getProperItemID('menuslist', array('id_menus' => array($query['id'])));

						// recover product alias
						$alias = VRESefHelper::getRecordAlias($query['id'], 'menu', $this->langtag);

						if ($alias)
						{
							// alias found, push it within the segments array
							$segments[] = $alias;

							// unset item ID from query
							unset($query['id']);
						}
					}
					else
					{
						// unset item ID from query
						unset($query['id']);
					}
				}
				else
				{
					unset($query['id']);
				}

				unset($query['view']);
			}
		}

		// fallback

		else
		{
			// check whether the requested view owns an Item ID
			$itemid = $this->getProperItemID($query['view']);

			if ($itemid)
			{
				// unset the view from the query
				unset($query['view']);
			}
		}

		return $segments;
	}

	/**
	 * Parse method for URLs.
	 * This method is meant to transform the human readable URL back into
	 * query parameters. It is only executed when SEF mode is switched on.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 */
	public function parse(&$segments)
	{
		$total  	= count($segments);
		$active 	= $this->menu->getActive();		
		$query_view = empty($active->query['view']) ? '' : $active->query['view'];
		$vars 		= array();

		if (!$total || !$this->isActive())
		{
			// no vars or router is disabled
			return $vars;
		}

		// load site language because some keywords might be
		// translated through the component
		VikRestaurants::loadLanguage($this->langtag);

		// order dishes

		if ($total > 2 && $segments[2] == JText::_('VRE_SEF_ORDERDISHES'))
		{
			$vars['view'] = 'orderdishes';
			
			// make sure the order number and the order key are set
			$exp = explode("-", $segments[1]);

			if (count($exp) == 2)
			{
				$vars['ordnum'] = $exp[0];
				$vars['ordkey'] = $exp[1];
			}
		}

		// order details

		else if ($segments[0] == JText::_('VRE_SEF_ORDER'))
		{
			$vars['view'] = 'order';

			if ($total > 1)
			{
				// make sure the order number and the order key are set
				$exp = explode("-", $segments[1]);

				if (count($exp) == 2)
				{
					$vars['ordnum'] = $exp[0];
					$vars['ordkey'] = $exp[1];
				}
			}
		}

		// reservation details

		else if ($segments[0] == JText::_('VRE_SEF_RESERVATION'))
		{
			$vars['view'] = 'reservation';

			if ($total > 1)
			{
				// make sure the order number and the order key are set
				$exp = explode("-", $segments[1]);

				if (count($exp) == 2)
				{
					$vars['ordnum'] = $exp[0];
					$vars['ordkey'] = $exp[1];
				}
			}
		}

		// take-away menus list or item details

		else if ($query_view == 'takeaway')
		{
			if (!empty($active->query['takeaway_menu']))
			{
				// we are probably under a menu category
				$id_menu = $active->query['takeaway_menu'];

				// increase total to enter the second statement
				$total++;
			}
			else
			{
				$alias 	= $segments[0];

				// recover menu ID
				$id_menu = VRESefHelper::getRecordWithAlias($alias, 'tkmenu', $this->langtag);

				// remove menu from segment
				array_shift($segments);
			}

			if ($total == 1)
			{
				// take-away menus list, filtering by menu
				$vars['view'] = 'takeaway';

				if ($id_menu)
				{
					$vars['takeaway_menu'] = $id_menu;

					$itemid = $this->getProperItemID($vars['view']);

					if (!empty($itemid))
					{
						$vars['Itemid'] = $itemid;
					}
				}		
			}
			else
			{
				// take-away item details
				$vars['view'] = 'takeawayitem';

				$alias 	= $segments[0];

				// recover product ID
				$id_item = VRESefHelper::getRecordWithAlias($alias, 'tkentry', $this->langtag, $id_menu);

				if ($id_item)
				{
					$vars['takeaway_item'] = $id_item;

					$itemid = $this->getProperItemID($vars['view']);

					if (!empty($itemid))
					{
						$vars['Itemid'] = $itemid;
					}
				}

				if ($total == 3)
				{
					// decode option
					$alias 	= $segments[1];

					// recover option ID
					$id_option = VRESefHelper::getRecordWithAlias($alias, 'tkentryoption', $this->langtag, $id_item);

					if ($id_option)
					{
						$vars['id_option'] = $id_option;
					}
				}
			}
		}

		// take-away item details

		else if ($query_view == 'takeawayitem')
		{
			$vars['view'] = 'takeawayitem';

			// set product ID
			$vars['takeaway_item'] = $active->query['takeaway_item'];

			// decode option
			$alias 	= $segments[0];

			// recover option ID
			$id_option = VRESefHelper::getRecordWithAlias($alias, 'tkentryoption', $this->langtag, $active->query['takeaway_item']);

			if ($id_option)
			{
				$vars['id_option'] = $id_option;
			}
		}

		// menu details

		else if ($query_view == 'menuslist' || $query_view == 'menudetails')
		{
			$vars['view'] = 'menudetails';

			// decode menu
			$alias 	= $segments[0];

			// recover menu ID
			$id_menu = VRESefHelper::getRecordWithAlias($alias, 'menu', $this->langtag);

			if ($id_menu)
			{
				$vars['id'] = $id_menu;
			}
		}

		$segments = array();

		return $vars;
	}
}
