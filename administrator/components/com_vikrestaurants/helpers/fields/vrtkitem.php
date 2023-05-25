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

jimport('joomla.form.formfield');

/**
 * Take-away item SQL list.
 *
 * @since 1.6
 */
class JFormFieldVrtkitem extends JFormField
{
	/**
	 * Renders the field input.
	 *
	 * @return 	string
	 */	
	function getInput()
	{
		$rows = array();
		
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('m.id', 'id_menu'))
			->select($dbo->qn('m.title', 'menu_title'))
			->select($dbo->qn('e.id', 'id'))
			->select($dbo->qn('e.name', 'name'))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus', 'm') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('e.id_takeaway_menu'))
			->order($dbo->qn('m.ordering') . ' ASC')
			->order($dbo->qn('e.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $r)
			{
				if (!isset($rows[$r->id_menu]))
				{
					$menu = new stdClass;
					$menu->id    = $r->id_menu;
					$menu->title = $r->menu_title;
					$menu->items = array();

					$rows[$r->id_menu] = $menu;
				}

				if (!empty($r->id))
				{
					$item = new stdClass;
					$item->id   = $r->id;
					$item->name = $r->name;

					$rows[$r->id_menu]->items[] = $item;
				}
			}
		}

		$html = '<select class="form-select inputbox' . ($this->required ? ' required' : '') . '" name="' . $this->name . '"' . ($this->multiple ? ' multiple' : '') . '>';

		foreach ($rows as $menu)
		{
			$html .= '<optgroup label="' . $menu->title . '">';

			foreach ($menu->items as $item)
			{
				if ($this->multiple)
				{
					$selected = in_array($item->id, (array) $this->value);
				}
				else
				{
					$selected = $this->value == $item->id;
				}

				$html .= '<option value="' . $item->id . '"' . ($selected ? " selected=\"selected\"" : "") . '>' . $item->name . '</option>';
			}

			$html .= '</optgroup>';
		}

		$html .='</select>';

		return $html;
	}
}
