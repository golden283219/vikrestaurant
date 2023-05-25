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
 * VikRestaurants menus product management view.
 *
 * @since 1.4
 */
class VikRestaurantsViewmanagemenusproduct extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return void
	 */
	function display($tpl = null)
	{	
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		
		$ids  = $input->getUint('cid', array());
		$type = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		$product = null;

		$status = 0;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_section_product'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$product = $dbo->loadObject();

				$product->tags = $product->tags ? explode(',', $product->tags) : array();

				$q = $dbo->getQuery(true)
					->select('*')
					->from($dbo->qn('#__vikrestaurants_section_product_option'))
					->where($dbo->qn('id_product') . ' = ' . $product->id)
					->order($dbo->qn('ordering') . ' ASC');
				
				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					$product->variations = $dbo->loadObjectList();
				}
				else
				{
					$product->variations = array();
				}
			}
		}
		
		if (empty($product))
		{
			$product = (object) $this->getBlankItem($app->getUserState('vre.products.status', 0));
		}

		// use product data stored in user state
		$this->injectUserStateData($product, 'vre.menusproduct.data');
		
		$this->product = &$product;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @param 	integer  $status  The status to pre-select.
	 *
	 * @return 	array 	 A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem($status = 0)
	{
		return array(
			'id'          => 0,
			'name'        => '',
			'description' => '',
			'price'       => 0.0,
			'published'   => $status == 1 ? 1 : 0,
			'hidden'      => $status == 3 ? 1 : 0,
			'image'       => '',
			'tags'        => array(),
			'variations'  => array(),
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITMENUSPRODUCT'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWMENUSPRODUCT'), 'vikrestaurants');
		}

		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('menusproduct.save', JText::_('VRSAVE'));
			JToolbarHelper::save('menusproduct.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('menusproduct.savenew', JText::_('VRSAVEANDNEW'));
		}
		
		JToolbarHelper::cancel('menusproduct.cancel', JText::_('VRCANCEL'));
	}
}
