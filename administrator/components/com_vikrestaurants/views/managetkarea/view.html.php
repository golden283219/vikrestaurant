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
 * VikRestaurants take-away delivery area management view.
 *
 * @since 1.7
 */
class VikRestaurantsViewmanagetkarea extends JViewVRE
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
		
		$area = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_takeaway_delivery_area'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$area = $dbo->loadObject();

				// decode stringified columns
				$area->content    = (object) json_decode($area->content);
				$area->attributes = (object) json_decode($area->attributes);
			}
		}

		if (empty($area))
		{
			$area = (object) $this->getBlankItem();
		}

		// use area data stored in user state
		$this->injectUserStateData($area, 'vre.tkarea.data');

		VikRestaurants::loadGraphics2D();
		$shapes = VikRestaurants::getAllDeliveryAreas($published = true);
		
		$this->area   = &$area;
		$this->shapes = &$shapes;

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
			'id'         => 0,
			'name'       => '',
			'type'       => 0,
			'charge'     => 0.0,
			'min_cost'   => 0.0,
			'published'  => 1,
			'content'    => new stdClass,
			'attributes' => new stdClass,
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITTKAREA'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWTKAREA'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('tkarea.save', JText::_('VRSAVE'));
			JToolbarHelper::save('tkarea.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('tkarea.savenew', JText::_('VRSAVEANDNEW'));
		}

		if ($type == 'edit' && $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2copy('tkarea.savecopy');
		}
		
		JToolbarHelper::cancel('tkarea.cancel', JText::_('VRCANCEL'));
	}
}
