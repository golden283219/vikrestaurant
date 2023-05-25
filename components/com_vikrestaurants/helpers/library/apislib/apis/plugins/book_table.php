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
 * Event used to book a table for the searched date, time and number of people.
 *
 * @since 1.7
 */
class BookTable extends EventAPIs
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
			$args = array();
			$args['date']     = $input->getString('date');
			$args['hourmin']  = $input->getString('hourmin');
			$args['people']   = $input->getUint('people');
			$args['id_table'] = $input->getInt('id_table', 0);
		}

		// get current framework instance
		$apis = FrameworkAPIs::getInstance();
		
		try
		{
			/**
			 * Trigger TableAvailable plugin to verify the table availability.
			 *
			 * @see TableAvailable
			 */
			$json = $apis->dispatch('table_available', $args);
		}
		catch (Exception $e)
		{
			// register response here
			$response->setContent($e->getMessage());
			// propagate exception
			throw $e;
		}

		// decode response
		$res = json_decode($json);

		if (!isset($res->status) || !$res->status || isset($res->errcode))
		{
			/* we got a json like:
			{
				status: 0
			}

			or:
			{
				errcode: 500,
				error: "something wrong"
			}
			*/

			if (isset($res->status))
			{
				// table not available, register response
				$response->setStatus(1)->setContent($res->message);
				// propagate the same message we got
				echo $json;
			}
			else
			{
				// register error
				$response->setContent($res->error);
			}

			return;
		}

		// table is available
		$response->setStatus(1);

		// extract hour and minutes
		list($args['hour'], $args['min']) = explode(':', $args['hourmin']);
			
		$args['id_table'] = $res->table;

		// always load tables from the back-end
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$order = JTableVRE::getInstance('reservation', 'VRETable');

		$data = array();
		// fetch check-in timestamp
		$data['checkin_ts'] = VikRestaurants::createTimestamp($args['date'], $args['hour'], $args['min']);
		// set number of people
		$data['people'] = $args['people'];
		// assign table
		$data['id_table'] = $args['id_table'];
		// auto-confirm reservation
		$data['status'] = 'CONFIRMED';
		// use current language tag
		$data['langtag'] = JFactory::getLanguage()->getTag();

		// fill customer details
		$this->assignCustomer($data, $args);

		$obj = new stdClass;

		// save reservation
		if ($order->save($data) && $order->id)
		{
			$obj->status = 1;
			$obj->oid    = $order->id;
			$obj->date   = $args['date'];
			$obj->time   = $args['hourmin'];
			$obj->people = $args['people'];
			$obj->table  = $args['id_table'];

			/**
			 * Register the details of the saved reservation.
			 *
			 * @since 1.8
			 */
			$obj->details = $order->getProperties();
		}
		else
		{
			// get string error
			$error = $customer->getError(null, true);

			$obj->status  = 0;
			$obj->message = $error ? $error : JText::_('VRNEWQUICKRESNOTCREATED');
		}

		/**
		 * Let the application framework safely output the response.
		 *
		 * @since 1.8.4
		 */
		return $obj;
	}

	/**
	 * @override
	 * Returns the title of the event.
	 *
	 * @return 	string 	The title of the event.
	 */
	public function getTitle()
	{
		return 'Book a Table';
	}

	/**
	 * @override
	 * Returns the description of the plugin.
	 *
	 * @return 	string
	 */
	public function getDescription()
	{
		/**
		 * Read the description HTML from a layout.
		 *
		 * @since 1.8
		 */
		return JLayoutHelper::render('apis.plugins.book_table', array('plugin' => $this));
	}

	/**
	 * Recovers the customer data from the request.
	 *
	 * @param 	array 	&$data  The reservation details.
	 * @param   array   $args   The event arguments.
	 *
	 * @return 	void
	 */
	private function assignCustomer(array &$data, array $args)
	{
		$dbo = JFactory::getDbo();

		if (isset($args['purchaser']))
		{
			$purchaser = $args['purchaser'];
		}
		else
		{
			$input = JFactory::getApplication()->input;

			$purchaser = $input->get('purchaser', array(), 'array');
		}

		if (isset($purchaser['name']))
		{
			$data['purchaser_nominative'] = $purchaser['name'];
		}
		else
		{
			$data['purchaser_nominative'] = '';
		}

		if (isset($purchaser['mail']))
		{
			$data['purchaser_mail'] = $purchaser['mail'];
		}
		else
		{
			$data['purchaser_mail'] = '';
		}

		if (isset($purchaser['phone']))
		{
			$data['purchaser_phone'] = preg_replace("/[^0-9\s+]+/", '', $purchaser['phone']);
		}
		else
		{
			$data['purchaser_phone'] = '';
		}

		if (isset($purchaser['country']))
		{
			$data['purchaser_country'] = $purchaser['country'];
		}
		else
		{
			$data['purchaser_country'] = VRCustomFields::getDefaultCountryCode();
		}

		if (isset($purchaser['prefix']))
		{
			$data['purchaser_prefix'] = $purchaser['prefix'];
		}
		else
		{
			// get selected country
			$country = JHtml::_('vrehtml.countries.withcode', $data['purchaser_country']);

			if ($country)
			{
				$data['purchaser_prefix'] = $country->dial;
			}
			else
			{
				$data['purchaser_prefix'] = '';
			}
		}

		/**
		 * Assign user according to the specified e-mail.
		 *
		 * @since 1.8
		 */
		if ($data['purchaser_mail'])
		{
			$q = $dbo->getQuery(true)
				->select($dbo->qn('id'))
				->from($dbo->qn('#__vikrestaurants_users'))
				->where($dbo->qn('billing_mail') . ' = ' . $dbo->q($data['purchaser_mail']));

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$data['id_user'] = $dbo->loadResult();
			}
		}
	}
}
