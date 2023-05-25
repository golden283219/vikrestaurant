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

VRELoader::import('library.mvc.controllers.admin');

/**
 * VikRestaurants configuration controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerConfiguration extends VREControllerAdmin
{
	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return 	boolean
	 */
	public function save()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();

		// check user permissions
		if (!$user->authorise('core.access.config', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to access the configuration
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			// go to dashboard
			$this->setRedirect('index.php?option=com_vikrestaurants');
			// $this->cancel();

			return false;
		}
		
		$args = array();
		$args['firstconfig']      = 0;
		$args['enablerestaurant'] = $input->get('enablerestaurant', 0, 'int');
		$args['enabletakeaway']   = $input->get('enabletakeaway', 0, 'int');
		$args['restname']         = $input->get('restname', '', 'string');
		$args['adminemail']       = $input->get('adminemail', '', 'string');
		$args['senderemail']      = $input->get('senderemail', '', 'string');
		$args['companylogo']      = $input->get('companylogo', '', 'string');
		$args['dateformat']       = $input->get('dateformat', '', 'string');
		$args['timeformat']       = $input->get('timeformat', '', 'string');
		$args['multilanguage']    = $input->get('multilanguage', 0, 'int');
		$args['currencysymb']     = $input->get('currencysymb', '', 'string');
		$args['currencyname']     = $input->get('currencyname', '', 'string');
		$args['symbpos']          = $input->get('symbpos', 0, 'int');
		$args['currdecimalsep']   = $input->get('currdecimalsep', '', 'string');
		$args['currthousandssep'] = $input->get('currthousandssep', '', 'string');
		$args['currdecimaldig']   = $input->get('currdecimaldig', 0, 'int');
		$args['reservationreq']   = $input->get('reservationreq', 0, 'int');
		$args['opentimemode']     = $input->get('opentimemode', 0, 'int');
		$args['hourfrom']         = $input->get('hourfrom', 0, 'int');
		$args['hourto']           = $input->get('hourto', 0, 'int');
		$args['minuteintervals']  = $input->get('minuteintervals', 0, 'int');
		$args['averagetimestay']  = $input->get('averagetimestay', 0, 'int');
		$args['bookrestr']        = $input->get('bookrestr', 0, 'int');
		$args['mindate']          = $input->get('mindate', 0, 'uint');
		$args['maxdate']          = $input->get('maxdate', 0, 'uint');
		$args['minimumpeople']    = $input->get('minpeople', 0, 'int');
		$args['maximumpeople']    = $input->get('maxpeople', 0, 'int');
		$args['largepartylbl']    = $input->get('largepartylbl', 0, 'int');
		$args['largepartyurl']    = $input->get('largepartyurl', '', 'string');
		$args['safedistance']     = $input->get('safedistance', 0, 'uint');
		$args['safefactor']       = $input->get('safefactor', 1, 'float');
		$args['askdeposit']       = $input->get('askdeposit', 0, 'uint');
		$args['resdeposit']       = abs($input->get('resdeposit', 0, 'float'));
		$args['costperperson']    = $input->get('costperperson', 0, 'int');
		$args['choosemenu']       = $input->get('choosemenu', 0, 'int');
		$args['orderfood']        = $input->get('orderfood', 0, 'uint');
		$args['editfood']         = $input->get('editfood', 0, 'uint');
		$args['tablocktime']      = $input->get('tablocktime', 0, 'int');
		$args['phoneprefix']      = $input->get('phoneprefix', 0, 'int');
		$args['loadjquery']       = $input->get('loadjquery', 0, 'int');
		$args['uiradio']          = $input->get('uiradio', '', 'string');
		$args['showfooter']       = $input->get('showfooter', 0, 'int');
		$args['loginreq']         = $input->get('loginreq', 0, 'int');
		$args['enablereg']        = $input->get('enablereg', 0, 'int');
		$args['defstatus']        = $input->get('defstatus', '', 'string');
		$args['selfconfirm']      = $input->get('selfconfirm', 0, 'uint');
		$args['ondashboard']      = $input->get('ondashboard', 0, 'int');
		$args['refreshdash']      = $input->get('refreshdash', 0, 'uint');
		$args['enablecanc']       = $input->get('enablecanc', 0, 'int');
		$args['cancreason']       = $input->get('cancreason', 0, 'uint');
		$args['canctime']         = $input->get('canctime', 0, 'uint');
		$args['cancmins']         = $input->get('cancmins', 0, 'uint');
		$args['applycoupon']      = $input->get('applycoupon', 0, 'int');
		$args['taxesratio']       = $input->get('taxesratio', 0.0, 'float');
		$args['usetaxes']         = $input->get('usetaxes', 0, 'int');
		$args['listablecols']     = $input->get('listablecols', array(), 'string');
		$args['listablecf']       = $input->get('listablecf', array(), 'array');
		$args['mailcustwhen']     = $input->get('mailcustwhen', 1, 'int');
		$args['mailoperwhen']     = $input->get('mailoperwhen', 1, 'int');
		$args['mailadminwhen']    = $input->get('mailadminwhen', 2, 'int');
		$args['mailtmpl']         = $input->get('mailtmpl', '', 'string');
		$args['adminmailtmpl']    = $input->get('adminmailtmpl', '', 'string');
		$args['cancmailtmpl']     = $input->get('cancmailtmpl', '', 'string');

		$args['googleapikey']        = $input->get('googleapikey', '', 'string');
		$args['googleapiplaces']     = $input->get('googleapiplaces', 0, 'uint');
		$args['googleapidirections'] = $input->get('googleapidirections', 0, 'uint');
		$args['googleapistaticmap']  = $input->get('googleapistaticmap', 0, 'uint');

		$args['gdpr']       = $input->get('gdpr', 0, 'uint');
		$args['policylink'] = $input->get('policylink', '', 'string');
		
		$cd_arr = $input->get('closing_days', array(), 'string');
		
		$args['mincostperorder']  = $input->get('mincostperorder', 0.0, 'float');
		$args['tkminint']         = $input->get('tkminint', 0, 'int');
		$args['asapafter']        = $input->get('asapafter', 0, 'int');
		$args['mealsperint']      = $input->get('mealsperint', 0, 'int');
		$args['tkordperint']      = $input->get('tkordperint', 0, 'uint');
		$args['tkordmaxser']      = $input->get('tkordmaxser', 0, 'uint');
		$args['deliveryservice']  = $input->get('deliveryservice', 0, 'int');
		$args['tkdefaultservice'] = $input->get('tkdefaultservice', '', 'string');
		$args['dsprice']          = $input->get('dsprice', 0.0, 'float');
		$args['dspercentot']      = $input->get('dspercentot', 0, 'int');
		$args['pickupprice']      = $input->get('pickupprice', 0.0, 'float');
		$args['pickuppercentot']  = $input->get('pickuppercentot', 0, 'int');
		$args['freedelivery']     = $input->get('freedelivery', 0.0, 'float');
		$args['tklocktime']       = $input->get('tklocktime', 0, 'int');
		$args['tkshowimages']     = $input->get('tkshowimages', 0, 'uint');
		$args['tkshowtimes']      = $input->get('tkshowtimes', 0, 'uint');
		$args['tknote']           = $input->get('tknote', '', 'raw');
		$args['tktaxesratio']     = $input->get('tktaxesratio', 0.0, 'float');
		$args['tkshowtaxes']      = $input->get('tkshowtaxes', 0, 'int');
		$args['tkusetaxes']       = $input->get('tkusetaxes', 0, 'int');
		$args['tkloginreq']       = $input->get('tkloginreq', 0, 'int');
		$args['tkenablereg']      = $input->get('tkenablereg', 0, 'int');
		$args['tkdefstatus']      = $input->get('tkdefstatus', '', 'string');
		$args['tkselfconfirm']    = $input->get('tkselfconfirm', 0, 'uint');
		$args['tkenablecanc']     = $input->get('tkenablecanc', 0, 'int');
		$args['tkcancreason']     = $input->get('tkcancreason', 0, 'uint');
		$args['tkcanctime']       = $input->get('tkcanctime', 0, 'uint');
		$args['tkcancmins']       = $input->get('tkcancmins', 0, 'uint');
		$args['tkmaxitems']       = $input->get('tkmaxitems', 0, 'int');
		$args['tkmealsbackslots'] = $input->get('tkmealsbackslots', 0, 'uint');
		$args['tkallowdate']      = $input->get('tkallowdate', 0, 'int');
		$args['tkmindate']        = $input->get('tkmindate', 0, 'uint');
		$args['tkmaxdate']        = $input->get('tkmaxdate', 0, 'uint');
		$args['tkwhenopen']       = $input->get('tkwhenopen', 0, 'uint');
		$args['tkpreorder']       = $input->get('tkpreorder', 0, 'uint');
		$args['tkuseoverlay']     = $input->get('tkuseoverlay', 0, 'int');
		$args['tkproddesclength'] = $input->get('tkproddesclength', 0, 'uint');
		$args['tkmailcustwhen']   = $input->get('tkmailcustwhen', 1, 'int');
		$args['tkmailoperwhen']   = $input->get('tkmailoperwhen', 1, 'int');
		$args['tkmailadminwhen']  = $input->get('tkmailadminwhen', 2, 'int');
		$args['tkmailtmpl']       = $input->get('tkmailtmpl', '', 'string');
		$args['tkadminmailtmpl']  = $input->get('tkadminmailtmpl', '', 'string');
		$args['tkcancmailtmpl']   = $input->get('tkcancmailtmpl', '', 'string');
		$args['tkreviewmailtmpl'] = $input->get('tkreviewmailtmpl', '', 'string');
		$args['tklistablecols']   = $input->get('tklistablecols', array(), 'string');
		$args['tklistablecf']     = $input->get('tklistablecf', array(), 'array');
		$args['tkenablegratuity'] = $input->get('tkenablegratuity', 0, 'uint');
		$args['tkdefgratuity']    = $input->get('tkdefgrat_amount', 0, 'float') . ':' . $input->get('tkdefgrat_percentot', 1, 'uint');
		$args['tkenablestock']    = $input->get('tkenablestock', 0, 'int');
		$args['tkstockmailtmpl']  = $input->get('tkstockmailtmpl', '', 'string');
		$args['tkaddrorigins']    = $input->get('tkaddrorigins', array(), 'string');

		$args['enablereviews']    = $input->get('enablereviews', 0, 'int');
		$args['revtakeaway']      = $input->get('revtakeaway', 0, 'int');
		$args['revleavemode']     = $input->get('revleavemode', 0, 'int');
		$args['revcommentreq']    = $input->get('revcommentreq', 0, 'int');
		$args['revminlength']     = max(array(0, 		$input->get('revminlength', 0, 'int')));
		$args['revmaxlength']     = min(array(2048, 	$input->get('revmaxlength', 0, 'int')));
		$args['revlimlist']       = max(array(1, 		$input->get('revlimlist', 5, 'int')));
		$args['revlangfilter']    = $input->get('revlangfilter', 0, 'int');
		$args['revautopublished'] = $input->get('revautopublished', 0, 'int');

		$args['apifw']       = $input->get('apifw', 0, 'uint');
		$args['apilogmode']  = $input->get('apilogmode', 0, 'uint');
		$args['apilogflush'] = $input->get('apilogflush', 0, 'uint');
		$args['apimaxfail']  = max(array(1, $input->get('apimaxfail', 0, 'uint')));
		
		$args['smsapi']           = $input->get('smsapi', '', 'string');
		$args['smsapiwhen']       = $input->get('smsapiwhen', 0, 'int');
		$args['smsapito']         = $input->get('smsapito', 0, 'int');
		$args['smsapiadminphone'] = $input->get('smsapiadminphone', '', 'string');
		$args['smsapifields']     = '';
		$args['smstmplcust']      = array();
		$args['smstmpladmin']     = array();

		$sms_cust_tmpl 	= $input->get('smstmplcust', array(), 'array');
		$sms_admin_tmpl = $input->get('smstmpladmin', array(), 'array');
		
		$languages = VikRestaurants::getKnownLanguages();
		
		for ($i = 0; $i < count($languages); $i++)
		{
			$args['smstmplcust'][$languages[$i]]   = $sms_cust_tmpl[0][$i];
			$args['smstmpltkcust'][$languages[$i]] = $sms_cust_tmpl[1][$i];
		}

		$args['smstmplcust']   = json_encode($args['smstmplcust']);
		$args['smstmpltkcust'] = json_encode($args['smstmpltkcust']);
		
		$args['smstmpladmin']   = $sms_admin_tmpl[0];
		$args['smstmpltkadmin'] = $sms_admin_tmpl[1];

		try
		{
			// get SMS driver configuration
			$smsconfig = VREApplication::getInstance()->getSmsConfig($args['smsapi']);

			$args['smsapifields'] = array();

			foreach ($smsconfig as $k => $p)
			{
				$args['smsapifields'][$k] = $input->get('sms_' . $k, '', 'string');
			}

			$args['smsapifields'] = json_encode($args['smsapifields']);
		}
		catch (Exception $e)
		{
			// SMS driver not supported
		}

		// validation
		
		if ($args['hourfrom'] < 0 || $args['hourfrom'] > 23)
		{
			$args['hourfrom'] = VikRestaurants::getFromOpeningHour();
		}
		
		if ($args['hourto'] < 0 || $args['hourto'] > 23)
		{
			$args['hourto'] = VikRestaurants::getToOpeningHour(true);
		}
		
		if ($args['averagetimestay'] < 5)
		{
			$args['averagetimestay'] = VikRestaurants::getAverageTimeStay();
		}
		
		if ($args['bookrestr'] < 0)
		{
			$args['bookrestr'] = 0;
		}
		
		if ($args['minimumpeople'] < 1)
		{
			$args['minimumpeople'] = VikRestaurants::getMinimumPeople();
		}
		
		if ($args['maximumpeople'] < 1) {
			$args['maximumpeople'] = VikRestaurants::getMaximumPeople();
		}

		if ($args['minimumpeople'] > $args['maximumpeople'])
		{
			$args['maximumpeople'] = $args['minimumpeople'];
		}
		
		if ($args['askdeposit'] == 0)
		{
			// turn off deposit cost
			$args['resdeposit'] = 0;
		}
		
		if ($args['tablocktime'] < 5)
		{
			$args['tablocktime'] = VikRestaurants::getTablesLockedTime();
		}
		
		if ($args['tklocktime'] < 1)
		{
			$args['tklocktime'] = VikRestaurants::getTakeAwayOrdersLockedTime();
		}

		if ($args['deliveryservice'] == 1 && $args['tkdefaultservice'] != 'delivery')
		{
			$args['tkdefaultservice'] = 'delivery';
		}
		else if ($args['deliveryservice'] == 0 && $args['tkdefaultservice'] != 'pickup')
		{
			$args['tkdefaultservice'] = 'pickup';
		}

		if ($args['defstatus'] == 'CONFIRMED')
		{
			$args['selfconfirm'] = 0;
		}

		if ($args['tkdefstatus'] == 'CONFIRMED')
		{
			$args['tkselfconfirm'] = 0;
		}

		if (!$args['tkallowdate'])
		{
			// turn off min/max dates in case the date selection is disabled
			$args['tkmindate'] = 0;
			$args['tkmaxdate'] = 0;
		}

		if ($args['tkwhenopen'])
		{
			// turn off pre-order in case of live orders
			$args['tkpreorder'] = 0;
		}
		
		// default restaurant reservations listable columns
		$listable_cols = array();

		foreach ($args['listablecols'] as $k => $v)
		{
			$tmp = explode(':', $v);

			if ($tmp[1] == 1)
			{
				$listable_cols[] = $tmp[0];
			} 
		}

		$args['listablecols'] = implode(',', $listable_cols);

		// restaurant reservations listable custom fields
		$listable_cols = array();

		foreach ($args['listablecf'] as $k => $v)
		{
			$tmp = explode(':', $v);

			if ($tmp[1] == 1)
			{
				$listable_cols[] = $tmp[0];
			} 
		}

		$args['listablecf'] = json_encode($listable_cols);
		
		// default take-away orders listable columns
		$listable_cols = array();

		foreach ($args['tklistablecols'] as $k => $v)
		{
			$tmp = explode(':', $v);

			if ($tmp[1] == 1)
			{
				$listable_cols[] = $tmp[0];
			} 
		}

		$args['tklistablecols'] = implode(',', $listable_cols);

		// take-away orders listable custom fields
		$listable_cols = array();

		foreach ($args['tklistablecf'] as $k => $v)
		{
			$tmp = explode(':', $v);

			if ($tmp[1] == 1)
			{
				$listable_cols[] = $tmp[0];
			} 
		}

		$args['tklistablecf'] = json_encode($listable_cols);
		
		// closing days
		$args['closingdays'] = array();

		foreach ($cd_arr as $cd)
		{
			list($date, $freq) = explode(':', $cd);

			$args['closingdays'][] = VikRestaurants::createTimestamp($date, 0, 0) . ':' . $freq;
		}

		$args['closingdays'] = implode(';;', $args['closingdays']);

		// origins
		$args['tkaddrorigins'] = json_encode(array_filter($args['tkaddrorigins']));

		// get record table
		$config = JTableVRE::getInstance('configuration', 'VRETable');

		// Save all configuration.
		// Do not care of any errors.
		$changed = $config->saveAll($args);

		if ($changed)
		{
			// display generic successful message
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));
		}

		// redirect to configuration page
		$this->cancel();

		return true;
	}

	/**
	 * Redirects the users to the configuration page.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrestaurants&view=editconfig');
	}

	/**
	 * Redirects the users to the dashboard page.
	 *
	 * @return 	void
	 */
	public function dashboard()
	{
		$this->setRedirect('index.php?option=com_vikrestaurants');
	}

	/**
	 * AJAX end-point to load the configuration fields
	 * of the requested SMS API driver.
	 *
	 * @return 	void
	 */
	public function smsapifields()
	{	
		$input = JFactory::getApplication()->input;
		
		$driver = $input->getString('driver');
		
		try
		{
			// access driver config through platform handler
			$form = VREApplication::getInstance()->getSmsConfig($driver);
		}
		catch (Exception $e)
		{
			// raise AJAX error, driver not found
			UIErrorFactory::raiseError(404, JText::_('VRSMSESTIMATEERR1'));
		}
		
		$params = array();

		// retrieve SMS driver configuration
		$params = VikRestaurants::getSmsApiFields();
		
		// build display data
		$data = array(
			'fields' => $form,
			'params' => $params,
		);

		// render payment form
		$html = JLayoutHelper::render('smsapi.fields', $data);
		
		echo json_encode(array($html));
		die;
	}
	
	/**
	 * AJAX end-point to estimate the remaining balance of
	 * the current SMS driver.
	 *
	 * @return 	void
	 */
	public function smsapicredit()
	{
		$input = JFactory::getApplication()->input;
		
		$driver = $input->getString('driver');
		
		try
		{
			// access driver instance through platform handler
			$api = VREApplication::getInstance()->getSmsInstance($driver);
		}
		catch (Exception $e)
		{
			// raise AJAX error, driver not found
			UIErrorFactory::raiseError(404, JText::_('VRSMSESTIMATEERR1'));
		}
		
		$phone = $input->get('phone', '', 'string');

		if (empty($phone))
		{
			// use admin phone number
			$phone = VikRestaurants::getSmsApiAdminPhoneNumber();
		}
		
		// make sure the driver support an estimation feature
		if (!method_exists($api, 'estimate'))
		{
			// raise AJAX error, estimate not supported
			UIErrorFactory::raiseError(405, JText::_('VRSMSESTIMATEERR2'));
		}
		
		// try to estimate
		$result = $api->estimate($phone, 'Sample');
		
		if ($result->errorCode != 0)
		{
			// raise AJAX error, unable to estimate
			UIErrorFactory::raiseError(500, JText::_('VRSMSESTIMATEERR3'));
		}
		
		// return the plain user credit
		echo $result->userCredit;
		die;
	}

	/**
	 * Tries to render a preview of the selected e-mail template.
	 *
	 * @return 	void
	 */
	public function mailpreview()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();

		$id    = $input->get('id', 0, 'uint');
		$group = $input->get('group', '', 'string');
		$alias = $input->get('alias', '', 'string');
		$file  = $input->get('file', '', 'string');
		$lang  = $input->get('langtag', '', 'string');

		// load mail factory
		VRELoader::import('library.mail.factory');

		// build base arguments
		$args = array($group, $alias);

		// fetch arguments based on specified group and alias
		if ($group == 'restaurant')
		{
			// restaurant group supports only reservations
			if (!$id)
			{
				// find latest reservation
				$q = $dbo->getQuery(true)
					->select($dbo->qn('id'))
					->from($dbo->qn('#__vikrestaurants_reservation'))
					->where($dbo->qn('closure') . ' = 0')
					->order($dbo->qn('id') . ' DESC');

				$dbo->setQuery($q, 0, 1);
				$dbo->execute();

				if (!$dbo->getNumRows())
				{
					throw new Exception('Before to see a preview of the e-mail template, you have to create at least a reservation first.', 400);
				}

				$id = (int) $dbo->loadResult();
			}

			// inject reservation ID within the arguments
			$args[] = $id;
		}
		else
		{
			// take-away group owns different types of providers
			if ($alias == 'stock')
			{
				// do nothing for stock template
			}
			else if ($alias == 'review')
			{
				if (!$id)
				{
					// find latest review
					$q = $dbo->getQuery(true)
						->select($dbo->qn('id'))
						->from($dbo->qn('#__vikrestaurants_reviews'))
						->order($dbo->qn('id') . ' DESC');

					$dbo->setQuery($q, 0, 1);
					$dbo->execute();

					if (!$dbo->getNumRows())
					{
						throw new Exception('Before to see a preview of the e-mail template, you have to create at least a review first.', 400);
					}

					$id = (int) $dbo->loadResult();
				}

				// inject review ID within the arguments
				$args[] = $id;
			}
			else
			{
				if (!$id)
				{
					// find latest order
					$q = $dbo->getQuery(true)
						->select($dbo->qn('id'))
						->from($dbo->qn('#__vikrestaurants_takeaway_reservation'))
						->order($dbo->qn('id') . ' DESC');

					$dbo->setQuery($q, 0, 1);
					$dbo->execute();

					if (!$dbo->getNumRows())
					{
						throw new Exception('Before to see a preview of the e-mail template, you have to create at least an order first.', 400);
					}

					$id = (int) $dbo->loadResult();
				}

				// inject reservation ID within the arguments
				$args[] = $id;
			}
		}

		if ($lang)
		{
			// force language tag too
			$args[] = $lang;
		}

		if ($alias == 'cancellation')
		{
			// we should include a sample cancellation reason
			// text to make it visible for styling
			$args[] = array(
				'cancellation_reason' => 'The cancellation reason will be printed here in case the system supports it.',
			);
		}
		else if ($alias == 'stock')
		{
			// pass test attributes to retrieve some junk data
			$args[] = array(
				'test'   => true,
				'start'  => $input->getUint('start'),
				'offset' => $input->getUint('offset'),
			);
		}

		// instantiate provider by using the fetched arguments
		$mail = call_user_func_array(array('VREMailFactory', 'getInstance'), $args);

		// overwrite template file
		$mail->setFile($file);

		// get mail subject (page title)
		$title = $mail->getSubject();

		// render mail template (page body)
		$tmpl = $mail->getHtml();

		// include style to prevent body from having margins
		$tmpl = '<style>body{margin:0;padding:0;}</style>' . $tmpl;

		$data = array(
			'title' => $title,
			'body'  => $tmpl,
		);

		// display resulting template
		$base = VREBASE . DIRECTORY_SEPARATOR . 'layouts';
		echo JLayoutHelper::render('document.blankpage', $data, $base);
		exit;
	}
}
