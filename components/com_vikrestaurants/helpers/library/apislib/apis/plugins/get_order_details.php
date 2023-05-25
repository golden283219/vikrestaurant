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

/**
 * Event used to obtain the details of the requested
 * restaurant reservation or take-away order.
 *
 * @since 1.7
 */
class GetOrderDetails extends EventAPIs
{
	/**
	 * The custom action that the event have to perform.
	 * This method should not contain any exit or die function, 
	 * otherwise the event won't be stopped properly.
	 *
	 * All the information to return, should be echoed instead.
	 *
	 * @param 	array 		  $args 	 The provided arguments for the event.
	 * @param 	ResponseAPIs  $response  The response object for admin.
	 *
	 * @return 	mixed         The response to output or the error message (ErrorAPIs).
	 */
	protected function doAction(array $args, ResponseAPIs &$response)
	{
		if (!$args)
		{
			$input = JFactory::getApplication()->input;
			
			// no payload found, recover arguments from request
			$args['id']      = $input->getUint('id', 0);
			$args['type']    = $input->getUint('type', 0);
			$args['langtag'] = $input->getString('langtag', null);
			$args['layout']  = $input->get('layout', array(), 'array');
		}

		// wrap arguments in a registry
		$args = new JRegistry($args);

		// set default language tag, if not specified
		$args->def('langtag', JFactory::getLanguage()->getTag());

		$response->setStatus(1);

		try
		{
			/**
			 * Take the language specified from the request.
			 * When missing, use the default one.
			 *
			 * In addition, pass an option to preload all
			 * information of the order/reservation.
			 *
			 * @since 1.8.4
			 */
			$langtag = $args->get('langtag');
			$options = array('preload' => true);

			if ($args->get('type') == 0)
			{
				// get restaurant reservation details
				$order = VREOrderFactory::getReservation((int) $args->get('id'), $langtag, $options);

				// register response
				$response->setContent('Restaurant reservation [' . $order->id . '] : ' . $order->sid);
			}
			else
			{
				// get take-away order details
				$order = VREOrderFactory::getOrder((int) $args->get('id'), $langtag, $options);

				// register response
				$response->setContent('Take-Away order [' . $order->id . '] : ' . $order->sid);

			}
		}
		catch (Exception $e)
		{
			// order not found, register response
			$response->setStatus(0)->setContent($e->getMessage());

			// rethrow exception
			throw $e;
		}

		/**
		 * Evaluate to use an apposite layout instead of the default one
		 * used by the e-mails. In case the event arguments specifies a
		 * a preferred layout (or maybe more), it will be included within
		 * the response in place of the default one.
		 *
		 * @since 1.8.3
		 */

		// get specified layouts
		$layouts = array_unique((array) $args->get('layout', array()));

		// prepare layout data
		$data = array(
			'order' => $order,
			'args'  => $args,
		);

		if ($layouts)
		{
			// the client specified a list of layouts
			$order->template = array();

			// iterate each layout and invoke the related file to
			// render the HTML of the layout
			foreach ($layouts as $layout)
			{
				$order->template[$layout] = JLayoutHelper::render('api.order.' . $layout, $data);
			}
		}
		else
		{
			// No specified layout, use default one.
			// In this case, the template will be a string instead of an array.
			$order->template = JLayoutHelper::render('api.order.html', $data);
		}

		// return response to client

		$obj = new stdClass;
		$obj->status       = 1;
		$obj->orderDetails = $order;

		/**
		 * Let the application framework safely output the response.
		 *
		 * @since 1.8.4
		 */
		return $obj;
	}

	/**
	 * @override
	 * Returns the description of the plugin.
	 *
	 * @return 	string
	 */
	public function getDescription()
	{
		// recover list of supported layouts
		$layouts = glob(implode(DIRECTORY_SEPARATOR, array(VREBASE, 'layouts', 'api', 'order', '*.php')));

		// create select options
		$layouts = array_map(function($layout)
		{
			// strip folder path and file extension
			$layout = preg_replace("/\.php$/i", '', basename($layout));

			return JHtml::_('select.option', $layout, ucwords($layout));
		}, $layouts);

		$displayData = array(
			'plugin'  => $this,
			'layouts' => $layouts,
		);

		/**
		 * Read the description HTML from a layout.
		 *
		 * @since 1.8
		 */
		return JLayoutHelper::render('apis.plugins.get_order_details', $displayData);
	}
}
