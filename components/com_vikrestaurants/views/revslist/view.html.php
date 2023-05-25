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
 * VikRestaurants product reviews list view.
 *
 * @since 1.7
 */
class VikRestaurantsViewrevslist extends JViewVRE
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

		// compose request
		$request = new stdClass;
		$request->limit      = VikRestaurants::getReviewsListLimit();
		$request->limitstart = $input->get('limitstart', 0, 'uint');
		$request->sortby     = $input->get('sortby', '', 'string');
		$request->filterstar = $input->get('filterstar', '', 'string');
		$request->filterlang = $input->get('filterlang', '', 'string');
		$request->id_tk_prod = $input->get('id_tk_prod', 0, 'uint');

		// parse request
		if ($request->sortby < 1 || $request->sortby > 3)
		{
			// default ordering
			$request->sortby = 1;
		}

		if ($request->filterstar < 0 || $request->filterstar > 5)
		{
			// default stars filter
			$request->filterstar = 0;
		}

		// get product details

		$q = $dbo->getQuery(true)
			->select($dbo->qn('e.id'))
			->select($dbo->qn('e.name'))
			->select($dbo->qn('e.description'))
			->select($dbo->qn('e.img_path'))
			->select($dbo->qn('m.id', 'mid'))
			->select($dbo->qn('m.title', 'mtitle'))
			->select($dbo->qn('m.description', 'mdescription'))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus', 'm'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('e.id_takeaway_menu'))
			->where($dbo->qn('m.published') . ' = 1')
			->where($dbo->qn('e.published') . ' = 1')
			->where($dbo->qn('e.id') . ' = ' . $request->id_tk_prod);
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			// product not found, raise error
			throw new Exception(JText::_('VRTKCARTROWNOTFOUND'), 404);
		}

		$tmp = $dbo->loadObject();

		$item = new stdClass;

		$item->id          = $tmp->id;
		$item->name        = $tmp->name;
		$item->description = $tmp->description;
		$item->img_path    = $tmp->img_path;

		$item->menu = new stdClass;
		$item->menu->id          = $tmp->mid;
		$item->menu->title       = $tmp->mtitle;
		$item->menu->description = $tmp->mdescription;

		// apply menu translation
		VikRestaurants::translateTakeawayMenus($item->menu);

		// apply product translation
		VikRestaurants::translateTakeawayProducts($item);

		// prepare reviews handler

		VRELoader::import('library.reviews.handler');

		$reviewsHandler = new ReviewsHandler();

		if ($request->sortby == 1)
		{
			// from latest to oldest
			$reviewsHandler->setOrdering('timestamp', 2);
		}
		else if ($request->sortby == 2)
		{
			// from oldest to latest
			$reviewsHandler->setOrdering('timestamp', 1);
		}
		else if ($request->sortby == 3)
		{
			// from most rated to worst rated
			$reviewsHandler->setOrdering('rating', 2)
				->addOrdering('verified', 2)
				->addOrdering('timestamp', 2);
		}

		// set up filters
		$reviewsHandler->takeaway()
			->setLimit($request->limitstart, $request->limit)
			->setRatingFilter($request->filterstar)
			->setLangTag($request->filterlang)
			->allowEmptyComment();

		// build empty review
		$data = new stdClass;
		$data->name    = '';
		$data->email   = '';
		$data->title   = '';
		$data->rating  = 0;
		$data->comment = '';

		// look for review date saved in the user state
		$this->injectUserStateData($data, 'vre.review.data');
		
		/**
		 * An object containing the details of
		 * the selected product.
		 *
		 * @var object
		 */
		$this->item = &$item;

		/**
		 * The handler used to fetch the reviews.
		 *
		 * @var ReviewsHandler
		 */
		$this->reviewsHandler = &$reviewsHandler;

		/**
		 * An object containing the requested information.
		 *
		 * @var object
		 */
		$this->request = &$request;

		/**
		 * In case the user submitted a review and the
		 * system wasn't able to save it, the specified
		 * details can be found here.
		 *
		 * @var object
		 */
		$this->data = &$data;
		
		// display the template
		parent::display($tpl);
	}
}
