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
 * VikRestaurants review management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewmanagereview extends JViewVRE
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
		$type = $ids ? 'edit' : '';
		
		// set the toolbar
		$this->addToolBar($type);
		
		$review = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('r.*')
				->from($dbo->qn('#__vikrestaurants_reviews', 'r'))
				->where($dbo->qn('r.id') . ' = ' . $ids[0]);

			if ($input->get('layout') === 'modal')
			{
				$q->select($dbo->qn('c.image', 'customerImage'));
				$q->leftjoin($dbo->qn('#__vikrestaurants_users', 'c') . ' ON ' . $dbo->qn('r.jid') . ' = ' . $dbo->qn('c.jid'));
			}

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$review = $dbo->loadObject();
			}
		}

		if ($input->get('layout') === 'modal')
		{
			if (!$review)
			{
				throw new RuntimeException('Review not found', 404);
			}

			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('e.name', 'e.img_path', 'e.description')))
				->select($dbo->qn('m.title', 'menuTitle'))
				->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e'))
				->from($dbo->qn('#__vikrestaurants_takeaway_menus', 'm'))
				->where($dbo->qn('e.id') . ' = ' . $review->id_takeaway_product)
				->where($dbo->qn('e.id_takeaway_menu') . ' = ' . $dbo->qn('m.id'));

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if (!$dbo->getNumRows())
			{
				throw new RuntimeException(sprintf('Product [%d] not found', $review->id_takeaway_product), 404);
			}

			$this->product = $dbo->loadObject();

			/**
			 * Added support for 'modal' layout.
			 *
			 * @since 1.8
			 */
			$this->setLayout('modal');
		}
		else
		{
			if (empty($review))
			{
				$review = (object) $this->getBlankItem();
			}

			// use review data stored in user state
			$this->injectUserStateData($review, 'vre.review.data');

			// get products
			$menus = array();

			$q = $dbo->getQuery(true)
				->select($dbo->qn('m.id', 'id_menu'))
				->select($dbo->qn('m.title', 'menu_title'))
				->select($dbo->qn('e.id', 'id_product'))
				->select($dbo->qn('e.name', 'product_name'))
				->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e'))
				->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus', 'm') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('e.id_takeaway_menu'))
				->order(array(
					$dbo->qn('m.ordering') . ' ASC',
					$dbo->qn('e.ordering') . ' ASC',
				));

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				foreach ($dbo->loadObjectList() as $r)
				{
					if (!isset($menus[$r->id_menu]))
					{
						$menu = new stdClass;
						$menu->id    = $r->id_menu;
						$menu->title = $r->menu_title;
						$menu->items = array();

						$menus[$r->id_menu] = $menu;
					}

					$item = new stdClass;
					$item->id   = $r->id_product;
					$item->name = $r->product_name;
					
					$menus[$r->id_menu]->items[] = $item;
				}
			}

			$juser = null;

			if ($review->jid > 0)
			{
				$juser = new JUser($review->jid);
			}

			$this->menus  = &$menus;
			$this->juser  = &$juser;
		}

		$this->review = &$review;

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
	protected function getBlankItem()
	{
		return array(
			'id'                  => 0,
			'title'               => '',
			'name'                => '',
			'email'               => '',
			'jid'                 => 0,
			'comment'             => '',
			'published'           => 0,
			'verified'            => 0,
			'timestamp'           => '',
			'rating'              => 5,
			'langtag'             => VikRestaurants::getDefaultLanguage(),
			'id_takeaway_product' => 0,
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITREVIEW'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWREVIEW'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('review.save', JText::_('VRSAVE'));
			JToolbarHelper::save('review.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('review.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('review.cancel', JText::_('VRCANCEL'));
	}
}
