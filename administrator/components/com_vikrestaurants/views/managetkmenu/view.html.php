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
 * VikRestaurants take-away menu management view.
 *
 * @since 1.2
 */
class VikRestaurantsViewmanagetkmenu extends JViewVRE
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
		
		$ids  = $input->get('cid', array(), 'uint');
		$type = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		$menu = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_takeaway_menus'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);
			
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$menu = $dbo->loadObject();

				$menu->entries = array();

				$q = $dbo->getQuery(true)
					->select(array(
						$dbo->qn('e.id', 'id_entry'),
						$dbo->qn('e.name', 'entry_name'),
						$dbo->qn('e.alias', 'entry_alias'),
						$dbo->qn('e.description', 'entry_description'),
						$dbo->qn('e.price', 'entry_price'),
						$dbo->qn('e.img_path', 'entry_image'),
						$dbo->qn('e.img_extra', 'entry_image_extra'),
						$dbo->qn('e.ready', 'entry_ready'),
						$dbo->qn('e.published', 'entry_published'),
					))
					->select(array(
						$dbo->qn('o.id', 'id_option'),
						$dbo->qn('o.name', 'option_name'),
						$dbo->qn('o.inc_price', 'option_price'),
					))
					->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e'))
					->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('e.id') . ' = ' . $dbo->qn('o.id_takeaway_menu_entry'))
					->where($dbo->qn('e.id_takeaway_menu') . ' = ' . $menu->id)
					->order($dbo->qn('e.ordering') . ' ASC')
					->order($dbo->qn('o.ordering') . ' ASC');
				
				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					foreach ($dbo->loadObjectList() as $row)
					{
						if (!isset($menu->entries[$row->id_entry]))
						{
							$entry = new stdClass;
							$entry->id          = $row->id_entry;
							$entry->name        = $row->entry_name;
							$entry->alias       = $row->entry_alias;
							$entry->description = $row->entry_description;
							$entry->price       = $row->entry_price;
							$entry->image       = $row->entry_image ? (array) $row->entry_image : array();
							$entry->ready       = $row->entry_ready;
							$entry->published   = $row->entry_published;
							$entry->options     = array();
							$entry->attributes  = array();

							if ($row->entry_image_extra)
							{
								// try to decode the images
								$extra = (array) json_decode($row->entry_image_extra);

								// merge main image with extra images
								$entry->image = array_merge($entry->image, $extra);
							}

							// retrieve attributes
							$q = $dbo->getQuery(true)
								->select($dbo->qn('id_attribute'))
								->from($dbo->qn('#__vikrestaurants_takeaway_menus_attr_assoc'))
								->where($dbo->qn('id_menuentry') . ' = ' . $entry->id);

							$dbo->setQuery($q);
							$dbo->execute();

							if ($dbo->getNumRows())
							{
								$entry->attributes = $dbo->loadColumn();
							}

							$menu->entries[$row->id_entry] = $entry;
						}

						if (!empty($row->id_option))
						{
							$option = new stdClass;
							$option->id    = $row->id_option;
							$option->name  = $row->option_name;
							$option->price = $row->option_price;

							$menu->entries[$row->id_entry]->options[] = $option;
						}
					}

					// do not use array keys
					$menu->entries = array_values($menu->entries);
				}
			}
		}
		
		if (empty($menu))
		{
			$menu = (object) $this->getBlankItem();
		}

		// use menu data stored in user state
		$this->injectUserStateData($menu, 'vre.tkmenu.data');
		
		// get all attributes
		$attributes = array();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_attribute'))
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();
		
		if ($dbo->getNumRows())
		{
			$attributes = $dbo->loadObjectList();
		}
		
		$this->menu 	  = &$menu;
		$this->attributes = &$attributes;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @return 	array 	A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem($id_menu = null)
	{
		return array(
			'id'           => 0,
			'title'        => '',
			'alias'        => '',
			'description'  => '',
			'published'    => 0,
			'publish_up'   => null,
			'publish_down' => null,
			'taxes_type'   => 0,
			'taxes_amount' => 0.0,
			'entries'      => array(),
		);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @param 	string  $type  The view type ('edit' or 'new').
	 *
	 * @return 	void
	 */
	private function addToolBar($type)
	{
		// add menu title and some buttons to the page
		if ($type == 'edit')
		{
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITTKMENU'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWTKMENU'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('tkmenu.save', JText::_('VRSAVE'));
			JToolbarHelper::save('tkmenu.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('tkmenu.savenew', JText::_('VRSAVEANDNEW'));
		}
		
		JToolbarHelper::cancel('tkmenu.cancel', JText::_('VRCANCEL'));
	}
}
