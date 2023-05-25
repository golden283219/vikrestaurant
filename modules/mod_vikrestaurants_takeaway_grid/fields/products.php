<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_grid
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield.list');

/**
 * Form field override to display a custom header.
 *
 * @since 1.0
 */
class JFormFieldProducts extends JFormFieldList
{
	/**
	 * The type of the field.
	 * MUST be equals to the definition in the XML file.
	 *
	 * @var string 
	 */
	protected $type = 'products';

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.4.2
	 */
	public function getOptions()
	{
		$options = [];

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('m.id', 'idMenu'));
		$q->select($dbo->qn('m.title', 'menuTitle'));
		$q->from($dbo->qn('#__vikrestaurants_takeaway_menus', 'm'));

		$q->select($dbo->qn('e.id', 'id'));
		$q->select($dbo->qn('e.name', 'name'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('e.id_takeaway_menu') . ' = ' . $dbo->qn('m.id'));

		$q->select($dbo->qn('o.id', 'idOption'));
		$q->select($dbo->qn('o.name', 'optionName'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('o.id_takeaway_menu_entry') . ' = ' . $dbo->qn('e.id'));

		/**
		 * Display also the unpublished products and menus as they are skipped
		 * within the method used to retrieve the selected products.
		 *
		 * @since 1.2
		 */

		$q->order($dbo->qn('m.ordering') . ' ASC');
		$q->order($dbo->qn('e.ordering') . ' ASC');
		$q->order($dbo->qn('o.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{	
			foreach ($dbo->loadObjectList() as $prod)
			{
				if (!empty($prod->id))
				{
					$options[] = JHtml::_(
						'select.option',
						$prod->id . '-' . (int) $prod->idOption,
						$prod->menuTitle . ' - ' . $prod->name . ($prod->optionName ? ' - ' . $prod->optionName : '')
					);
				}
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
