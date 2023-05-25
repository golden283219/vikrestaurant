<?php
/** 
 * @package   	VikRestaurants
 * @subpackage 	com_vikrestaurants
 * @author    	Matteo Galletti - e4j
 * @copyright 	Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * VikRestaurants component helper.
 *
 * @since 1.0
 */
abstract class VikRestaurants
{
	/**
	 * Returns the restaurant name.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getRestaurantName()
	{
		return self::getFieldFromConfig('restname', 'vrGetRestaurantName');
	}
	
	/**
	 * Returns a list of admin e-mails.
	 *
	 * @return 	array
	 */
	public static function getAdminMailList()
	{
		// get all e-mails
		$admin_mail_list = VREFactory::getConfig()->getString('adminemail');

		if (!strlen($admin_mail_list))
		{
			return array();
		}

		return array_map('trim', explode(',', $admin_mail_list));
	}
	
	/**
	 * Returns the admin e-mail.
	 * If not specified, the one set in the global
	 * configuration of the CMS will be used
	 *
	 * @return 	string
	 */
	public static function getAdminMail()
	{
		// get all e-mails
		$mails = self::getAdminMailList();

		if ($mails)
		{
			// returns first e-mail available
			return $mails[0];
		}

		// use owner e-mail
		return JFactory::getApplication()->get('mailfrom');
	}
	
	/**
	 * Returns the sender mail.
	 *
	 * @return 	string
	 */
	public static function getSenderMail()
	{
		// get sender from config
		$sender = VREFactory::getConfig()->getString('senderemail');

		if (empty($sender))
		{
			// missing sender, use the default one
			$sender = self::getAdminMail();
		}

		return $sender;
	}
	
	/**
	 * Returns an array containing the e-mail sending rules.
	 * The array contains the rules for these entities: customer, operator, admin.
	 *
	 * @return 	array
	 */
	public static function getSendMailWhen()
	{
		/**
		 * @deprecated 	1.9  Use VREConfig instead.
		 */
		return array(
			'customer' => intval(self::getFieldFromConfig('mailcustwhen', 'vrGetSendMailCustomerWhen')),
			'operator' => intval(self::getFieldFromConfig('mailoperwhen', 'vrGetSendMailOperatorWhen')),
			'admin'    => intval(self::getFieldFromConfig('mailadminwhen', 'vrGetSendMailAdminWhen')),
		);
	}

	/**
	 * Returns the file name of the e-mail template for the customers.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getMailTemplateName()
	{
		return self::getFieldFromConfig('mailtmpl', 'vrGetMailTemplateName');
	}

	/**
	 * Returns the file name of the e-mail template for the administrators.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getMailAdminTemplateName()
	{
		return self::getFieldFromConfig('adminmailtmpl', 'vrGetMailAdminTemplateName');
	}

	/**
	 * Returns the file name of the e-mail template sent after a cancellation.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getMailCancellationTemplateName()
	{
		return self::getFieldFromConfig('cancmailtmpl', 'vrGetMailCancellationTemplateName');
	}
	
	/**
	 * Returns the company logo name.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getCompanyLogoPath()
	{
		return self::getFieldFromConfig('companylogo', 'vrGetCompanyLogo');
	}

	/**
	 * Checks whether the restaurant section is enabled.
	 *
	 * @return 	boolean
	 */
	public static function isRestaurantEnabled()
	{
		return VREFactory::getConfig()->getBool('enablerestaurant', false);
	}

	/**
	 * Checks whether the take-away section is enabled.
	 *
	 * @return 	boolean
	 */
	public static function isTakeAwayEnabled()
	{
		return VREFactory::getConfig()->getBool('enabletakeaway', false);
	}
	
	/**
	 * Returns the date format.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getDateFormat()
	{
		return self::getFieldFromConfig('dateformat', 'vrGetDateFormat');
	}
	
	/**
	 * Returns the time format.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTimeFormat()
	{
		return self::getFieldFromConfig('timeformat', 'vrGetTimeFormat');
	}
	
	/**
	 * Checks whether the component should support multi-lingual contents.
	 *
	 * @return 	boolean
	 */
	public static function isMultilanguage()
	{
		return VREFactory::getConfig()->getBool('multilanguage', false);
	}
	
	/**
	 * Returns the currency symbol.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getCurrencySymb()
	{
		return self::getFieldFromConfig('currencysymb', 'vrGetCurrencySymb');
	}
	
	/**
	 * Returns the global currency name. It must be a value
	 * allowed by the ISO 4217.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getCurrencyName()
	{
		return self::getFieldFromConfig('currencyname', 'vrGetCurrencyName');
	}
	
	/**
	 * Returns the position of the currency symbol:
	 * - [1] after the price
	 * - [2] before the price
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getCurrencySymbPosition()
	{
		return (int) self::getFieldFromConfig('symbpos', 'vrGetCurrencySymbPosition');
	}

	/**
	 * Returns the decimal separator to use for prices.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getCurrencyDecimalSeparator()
	{
		return self::getFieldFromConfig('currdecimalsep', 'vrGetCurrencyDecimalSeparator');
	}

	/**
	 * Returns the thousands separator to use for prices.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9	 Use VREConfig instead.
	 */
	public static function getCurrencyThousandsSeparator()
	{
		return self::getFieldFromConfig('currthousandssep', 'vrGetCurrencyThousandsSeparator');
	}

	/**
	 * Returns the number of decimals to use for prices.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getCurrencyDecimalDigits()
	{
		return intval(self::getFieldFromConfig('currdecimaldig', 'vrGetCurrencyDecimalDigits'));
	}
	
	/**
	 * Checks if the restaurant performs a continuous opening time
	 * or whether it works with shifts.
	 *
	 * @return 	boolean
	 */
	public static function isContinuosOpeningTime()
	{
		return VREFactory::getConfig()->getUint('opentimemode') == 0;
	}
	
	/**
	 * Returns the minute intervals.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getMinuteIntervals()
	{
		return intval(self::getFieldFromConfig('minuteintervals', 'vrGetMinuteIntervals'));
	}
	
	/**
	 * Returns the average time of stay for reservations.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getAverageTimeStay()
	{
		return intval(self::getFieldFromConfig('averagetimestay', 'vrGetAverageTimeStay'));
	}
	
	/**
	 * Returns bookings restrictions in minutes.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getBookingMinutesRestriction()
	{
		return intval(self::getFieldFromConfig('bookrestr', 'vrGetBookingMinutesRestriction'));
	}
	
	/**
	 * Checks whether jQuery should be loaded in the front-end.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isLoadJQuery()
	{
		return intval(self::getFieldFromConfig( 'loadjquery', 'vrGetLoadJQuery'));
	}

	/**
	 * Returns the Google Maps API Key.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getGoogleMapsApiKey()
	{
		return self::getFieldFromConfig('googleapikey', 'vrGetGoogleMapsApiKey');
	}

	/**
	 * Checks whether the specified API library is enabled.
	 * The configuration currently supports the following APIs:
	 * 
	 * - Places API       places
	 * - Directions API   directions
	 * - Maps Static API  staticmap
	 *
	 * @param 	mixed 	 $api  The API library to check. If not specified,
	 * 						   the default API Key should be checked.
	 *
	 * @return 	boolean  True if enabled, false otherwise.
	 *
	 * @since 	1.8
	 */
	public static function isGoogleMapsApiEnabled($api = null)
	{
		$config = VREFactory::getConfig();

		// return FALSE in case the API Key is missing
		if (!$config->get('googleapikey'))
		{
			return false;
		}

		if (!$api)
		{
			// nothing else to check, return TRUE
			return true;
		}

		// check if the specified API library is enabled
		return $config->getBool('googleapi' . strtolower($api));
	}

	/**
	 * Checks whether the review system is enabled.
	 *
	 * @return 	boolean
	 */
	public static function isReviewsEnabled()
	{
		return VREFactory::getConfig()->getBool('enablereviews');
	}
	
	/**
	 * Checks whether the review system (for take-away) is enabled.
	 *
	 * @return 	boolean
	 */
	public static function isTakeAwayReviewsEnabled()
	{
		return self::isReviewsEnabled() && VREFactory::getConfig()->getBool('revtakeaway');
	}
	
	/**
	 * Checks if the comment is mandatory when leaving a review.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isReviewsCommentRequired()
	{
		return (bool) self::getFieldFromConfig('revcommentreq', 'vrGetReviewsCommentRequired');
	}
	
	/**
	 * Returns the minimum length for a review comment.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getReviewsCommentMinLength()
	{
		return intval(self::getFieldFromConfig('revminlength', 'vrGetReviewsCommentMinLength'));
	}
	
	/**
	 * Returns the maximum length for a review comment.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getReviewsCommentMaxLength()
	{
		return intval(self::getFieldFromConfig('revmaxlength', 'vrGetReviewsCommentMaxLength'));
	}
	
	/**
	 * Returns total number of reviews to display at once.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getReviewsListLimit()
	{
		return intval(self::getFieldFromConfig('revlimlist', 'vrGetReviewsListLimit'));
	}
	
	/**
	 * Checks whether the reviews are automatically published or if they
	 * need a manual approval by the administrator before to be visible.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isReviewsAutoPublished()
	{
		return (bool) self::getFieldFromConfig('revautopublished', 'vrGetReviewsAutoPublished');
	}

	/**
	 * Checks whether it is possible to filter the reviews by language.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isReviewsLangFilter()
	{
		return (bool) self::getFieldFromConfig('revlangfilter', 'vrGetReviewsLangFilter');
	}
	
	/**
	 * Returns the rule to allow the customers to leave reviews.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getReviewsLeaveMode()
	{
		return intval(self::getFieldFromConfig('revleavemode', 'vrGetReviewsLeaveMode'));
	}
	
	/**
	 * Returns the restaurant opening hour.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getFromOpeningHour()
	{
		return intval(self::getFieldFromConfig('hourfrom', 'vrGetFromOpeningHour'));
	}
	
	/**
	 * Returns the restaurant closing hour.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getToOpeningHour()
	{
		return intval(self::getFieldFromConfig('hourto', 'vrGetToOpeningHour'));
	}
	
	/**
	 * Returns the minimum number of allowed people for bookings.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getMinimumPeople()
	{
		return intval(self::getFieldFromConfig('minimumpeople', 'vrGetMinimumPeople'));
	}
	
	/**
	 * Returns the maximum number of allowed people for bookings.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getMaximumPeople()
	{
		return intval(self::getFieldFromConfig('maximumpeople', 'vrGetMaximumPeople'));
	}
	
	/**
	 * Checks whether the "Large Party" option should be displayed when
	 * creating the people dropdown.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isShowLargePartyLabel()
	{
		return (bool) self::getFieldFromConfig('largepartylbl', 'vrGetLargePartyLabel');
	}
	
	/**
	 * Returns the URL to reach when clicking the "Large Party" option.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getLargePartyURL()
	{
		return self::getFieldFromConfig('largepartyurl', 'vrGetLargePartyURL');
	}
	
	/**
	 * Returns the reservation requirements for the selection of the tables.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getReservationRequirements()
	{
		return intval(self::getFieldFromConfig('reservationreq', 'vrGetReservationRequirements'));
	}

	/**
	 * Returns the global taxes ratio.
	 *
	 * @return 	float
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTaxesRatio()
	{
		return floatval(self::getFieldFromConfig('taxesratio', 'vrGetTaxesRatio'));
	}

	/**
	 * Checks whether the taxes should be used.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isTaxesUsable()
	{
		return (int) self::getFieldFromConfig('usetaxes', 'vrGetUseTaxes');	
	}
	
	/**
	 * Returns the default deposit to leave per reservation.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getDepositPerReservation()
	{
		return intval(self::getFieldFromConfig('resdeposit', 'vrGetDepositReservation'));
	}
	
	/**
	 * Returns whether the deposit should be left per person.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getDepositPerPerson()
	{
		return (bool) self::getFieldFromConfig('costperperson', 'vrGetDepositPerPerson');
	}
	
	/**
	 * Checks whether the customers are able to choose menus while
	 * booking a table.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getChooseMenu()
	{
		return (bool) self::getFieldFromConfig('choosemenu', 'vrGetChooseMenu');
	}
	
	/**
	 * Returns the number of minutes for which a table should be locked.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTablesLockedTime()
	{
		return intval(self::getFieldFromConfig('tablocktime', 'vrGetTablesLockedTime'));
	}

	/**
	 * Returns the login requirements.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getLoginRequirements()
	{
		return intval(self::getFieldFromConfig('loginreq', 'vrGetLoginRequirements'));
	}

	/**
	 * Checks whether the user can register new accounts through VikRestaurants.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isRegistrationEnabled()
	{
		return intval(self::getFieldFromConfig('enablereg', 'vrGetEnableRegistration'));
	}

	/**
	 * Returns the default status assumed by a reservation.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getDefaultStatus()
	{
		return self::getFieldFromConfig('defstatus', 'vrGetDefaultStatus');
	}
	
	/**
	 * Checks whether the users are able to cancel reservations.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isCancellationEnabled()
	{
		return (bool) self::getFieldFromConfig('enablecanc', 'vrGetEnableCancellation');
	}

	/**
	 * Checks whether the users are forced to explain a reason when 
	 * trying to cancel a reservation.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getCancellationReason()
	{
		return intval(self::getFieldFromConfig('cancreason', 'vrGetCancellationReason'));
	}
	
	/**
	 * Returns the number of days ahead for which the users are allowed
	 * to cancel confirmed reservations.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getCancelBeforeTime()
	{
		return intval(self::getFieldFromConfig('canctime', 'vrGetCancelBeforeTime'));
	}

	/**
	 * Returns the method how the coupon code should be applied.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getApplyCouponType()
	{
		return intval(self::getFieldFromConfig('applycoupon', 'vrGetApplyCoupon'));
	}

	/**
	 * Checks whether dashboard should display a restaurant overview.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 1.9  Use VikRestaurants::isRestaurantOnDashboard() instead.
	 */
	public static function isOnDashboard()
	{
		return static::isRestaurantOnDashboard();
	}

	/**
	 * Checks whether dashboard should display a restaurant overview.
	 *
	 * @return 	boolean
	 *
	 * @since 	1.8.3
	 */
	public static function isRestaurantOnDashboard()
	{
		return self::isRestaurantEnabled()
			&& VREFactory::getConfig()->getBool('ondashboard')
			&& JFactory::getUser()->authorise('core.access.reservations', 'com_vikrestaurants');
	}

	/**
	 * Checks whether dashboard should display a take-away overview.
	 *
	 * @return 	boolean
	 *
	 * @since 	1.8.3
	 */
	public static function isTakeAwayOnDashboard()
	{
		return self::isTakeAwayEnabled()
			&& JFactory::getUser()->authorise('core.access.tkorders', 'com_vikrestaurants');
	}
	
	/**
	 * Returns the interval in seconds used to auto-refresh the dashboard.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getDashRefreshTime()
	{
		return intval(self::getFieldFromConfig('refreshdash', 'vrGetDashboardRefreshTime'));
	}

	/**
	 * Checks whether the credits footer is visible in the back-end.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isFooterVisible()
	{
		return (bool) self::getFieldFromConfig('showfooter', 'vrGetShowFooter');
	}
	
	/**
	 * Checks whether the reservations are currently allowed.
	 *
	 * @return 	boolean
	 */
	public static function isReservationsAllowed()
	{
		return self::isReservationsAllowedOn(static::now());
	}
	
	/**
	 * Checks whether the reservations are allowed on the specified date.
	 *
	 * @param 	integer  $timestamp  The timestamp of the date to check.
	 *
	 * @return 	boolean
	 */
	public static function isReservationsAllowedOn($timestamp)
	{
		return VREFactory::getConfig()->getInt('stopuntil') <= $timestamp;
	}

	/**
	 * Checks whether the take-away orders are currently allowed.
	 *
	 * @return 	boolean
	 */
	public static function isTakeAwayReservationsAllowed()
	{
		return self::isTakeAwayReservationsAllowedOn(static::now());
	}
	
	/**
	 * Checks whether the take-away orders are allowed on the specified date.
	 *
	 * @param 	integer  $timestamp  The timestamp of the date to check.
	 *
	 * @return 	boolean
	 */
	public static function isTakeAwayReservationsAllowedOn($timestamp)
	{
		/**
		 * Convert date string to UNIX timestamp.
		 *
		 * @since 1.8.2
		 */
		if (!preg_match("/^\d+$/", $timestamp))
		{
			$timestamp = static::createTimestamp($timestamp);
		}

		return VREFactory::getConfig()->getInt('tkstopuntil') <= $timestamp;
	}
	
	/**
	 * Returns the minimum cost needed to accept a take-away order.
	 *
	 * @param 	float  $areaCost  An optional delivery area cost.
	 * @param 	array  $args      An associative array with the searched query.
	 *
	 * @return 	float  The minimum area cost that will be used.
	 */
	public static function getTakeAwayMinimumCostPerOrder($areaCost = 0, array $args = null)
	{
		$config = VREFactory::getConfig();

		// get highest minimum required cost between config and delivery area
		$mincost = max(array($config->getFloat('mincostperorder'), (float) $areaCost));

		// search if we have a valid array
		if (!$args)
		{
			// create search array according to the details held by the cart instance
			$args = static::getCartSearchArray();
		}

		// init special days manager
		$sdManager = new VRESpecialDaysManager('takeaway');
		// set checkin date
		$sdManager->setStartDate($args['date']);
		// set checkin time
		$sdManager->setCheckinTime($args['hourmin']);
		// get first available special day
		$sd = $sdManager->getFirst();

		/**
		 * When the special day applies a minimum cost per order higher
		 * than 0, overwrite the default cost with the new one.
		 *
		 * @since 1.8.3
		 */
		if ($sd && $sd->minCostOrder)
		{
			$mincost = $sd->minCostOrder;
		}

		$dispatcher = VREFactory::getEventDispatcher();

		/**
		 * Plugins can use this hook to override the minimum cost 
		 * at runtime. The highest returned amount will be always used.
		 *
		 * In case the plugins returned something, the global cost will
		 * be always ignored, even if higher.
		 *
		 * @param 	float  $mincost  The minimum cost based on configuration and delivery area.
		 * @param 	array  $args     An associative array containing the order query.
		 *
		 * @return 	float  The minimum cost to use.
		 *
		 * @since 	1.8.3
		 */
		$return = $dispatcher->trigger('onCalculateOrderMinCost', array($mincost, $args));

		// filter the array in order to exclude all empty values
		$return = array_values(array_filter($return));
		
		// check whether the plugins returned something
		if ($return)
		{
			// override cost with the highest returned value
			$mincost = max($return);
		}

		return $mincost;
	}
	
	/**
	 * Returns the minutes intervals for the take-away times.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayMinuteInterval()
	{
		return intval(self::getFieldFromConfig('tkminint', 'vrGetTakeawayMinuteInterval'));
	}
	
	/**
	 * Returns the minutes needed to evaluate the ASAP option.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayAsapAfter()
	{
		return intval(self::getFieldFromConfig('asapafter', 'vrGetTakeawayAsapAfter'));
	}
	
	/**
	 * Returns the maximum number of meals that can be prepared per interval.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayMealsPerInterval()
	{
		return intval(self::getFieldFromConfig('mealsperint', 'vrGetTakeawayMealsPerInterval'));
	}

	/**
	 * Returns the maximum number of meals that can be ordered at once.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayMaxMeals()
	{
		return intval(self::getFieldFromConfig('tkmaxitems', 'vrGetTakeawayMaxMeals'));
	}

	/**
	 * Returns the maximum number of characters to display for
	 * the description of the take-away products.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayProductsDescriptionLength()
	{
		return intval(self::getFieldFromConfig('tkproddesclength', 'vrGetTakeawayProductsDescLength'));
	}

	/**
	 * Returns the rule to decide when displaying the "toppings" overlay.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayUseOverlay()
	{
		return intval(self::getFieldFromConfig('tkuseoverlay', 'vrGetTakeawayUseOverlay'));
	}

	/**
	 * Checks whether the user are allowed to pick a different date for bookings.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isTakeAwayDateAllowed()
	{
		return (bool) self::getFieldFromConfig('tkallowdate', 'vrGetTakeawayDateAllowed');
	}

	/**
	 * Checks whether the customers are not allowed to pre-order.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isTakeAwayLiveOrders()
	{
		return (bool) self::getFieldFromConfig('tkwhenopen', 'vrGetTakeawayLiveOrders');
	}

	/**
	 * Checks whether the restaurant offers the delivery service.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isTakeAwayDeliveryServiceEnabled()
	{
		return (bool) self::getFieldFromConfig('deliveryservice', 'vrGetTakeawayDeliveryService');
	}

	/**
	 * Returns the delivery service charge to be used.
	 *
	 * @param 	mixed 	$total  The current total net. If not specified, only
	 * 							the configuration delivery amount will be returned.
	 * @param 	mixed 	$area   Either an array or an object containing the delivery
	 * 							area details. If specified, the area charge will be
	 * 							added to the delivery charge.
	 *
	 * @return 	float 	The delivery service charge.
	 *
	 * @since 	1.2
	 */
	public static function getTakeAwayDeliveryServiceAddPrice($total = null, $area = null)
	{
		$config = VREFactory::getConfig();

		// get delivery charge
		$charge = $config->getFloat('dsprice');

		if (is_null($total))
		{
			// backward compatibility, just return the default value
			return $charge;
		}

		// get percentage or total
		$percentot = $config->getUint('dspercentot');

		if ($percentot == 1)
		{
			// percentage amount, calculate charge on total net
			$charge = $total * $charge / 100.0;
		}

		if (!is_null($area))
		{
			$area = (object) $area;

			// sum area charge to delivery charge
			$charge += $area->charge;
		}

		/**
		 * Always round the calculated amount to 2 decimals, in order
		 * to avoid roundings when saving the amount in the database.
		 *
		 * @since 1.8
		 */
		return round($charge, 2);
	}
	
	/**
	 * Checks whether the delivery charge is a fixed amount or a percentage
	 * to apply to the total cost.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayDeliveryServicePercentOrTotal()
	{
		return intval(self::getFieldFromConfig('dspercentot', 'vrGetTakeawayDelSerPercentOrTotal'));
	}

	/**
	 * Returns the pickup service charge to be used.
	 *
	 * @param 	mixed 	$total  The current total net. If not specified, only
	 * 							the configuration pickup amount will be returned.
	 *
	 * @return 	float 	The pickup service charge.
	 *
	 * @since 	1.2
	 */
	public static function getTakeAwayPickupAddPrice($total = null)
	{
		$config = VREFactory::getConfig();

		// get pickup charge
		$charge = $config->getFloat('pickupprice');

		if (is_null($total))
		{
			// backward compatibility, just return the default value
			return $charge;
		}

		// get percentage or total
		$percentot = $config->getUint('pickuppercentot');

		if ($percentot == 1)
		{
			// percentage amount, calculate charge on total net
			$charge = $total * $charge / 100.0;
		}

		/**
		 * Always round the calculated amount to 2 decimals, in order
		 * to avoid roundings when saving the amount in the database.
		 *
		 * @since 1.8
		 */
		return round($charge, 2);
	}
	
	/**
	 * Checks whether the pickup charge is a fixed amount or a percentage
	 * to apply to the total cost.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayPickupPercentOrTotal()
	{
		return intval(self::getFieldFromConfig('pickuppercentot', 'vrGetTakeawayPickupPercentOrTotal'));
	}
	
	/**
	 * Returns the threshold (total cost) for which the delivery charge should be ignored.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayFreeDeliveryService()
	{
		return floatval(self::getFieldFromConfig('freedelivery', 'vrGetTakeawayFreeDeliveryService'));
	}

	/**
	 * Checks whether the delivery charge should be applied or not.
	 *
	 * @param 	mixed 	 $cart  The cart instance.
	 *
	 * @return 	boolean  True in case of free delivery, false otherwise.
	 *
	 * @since 	1.8.3
	 */
	public static function isTakeAwayFreeDeliveryService($cart = null)
	{
		if (!$cart)
		{
			// recover cart instance if not specified
			$cart = TakeAwayCart::getInstance();
		}

		$config = VREFactory::getConfig();

		// fetch threshold from configuration
		$threshold = $config->getFloat('freedelivery');
		
		/**
		 * Use the total net only in case the taxes are not inclusive.
		 * In case of inclusive taxes we should use the grand total
		 * instead, just to make the things clearer.
		 *
		 * @since 1.8.3
		 */
		$total = $cart->getTotalCost();

		// prepare search arguments
		$args = static::getCartSearchArray($cart);

		$dispatcher = VREFactory::getEventDispatcher();

		/**
		 * Plugins can use this hook to override the threshold used
		 * to offer free deliveries at runtime.
		 * The highest returned amount will be always used.
		 *
		 * In case the plugins returned something, the global threshold will
		 * be always ignored, even if higher.
		 *
		 * @param 	float         $threshold  The free delivery threshold.
		 * @param 	TakeAwayCart  $cart       The cart instance.
		 * @param 	array  		  $args       An associative array containing the order query.
		 *
		 * @return 	float  The threshold to use.
		 *
		 * @since 	1.8.3
		 */
		$return = $dispatcher->numbers('onCalculateFreeDeliveryThreshold', array($threshold, $cart, $args));
		
		// check whether the plugins returned something
		if ($return)
		{
			// override threshold with the highest returned value
			$threshold = max($return);
		}

		// check whether the specified threshold is higher
		// then the cart total cost
		return $threshold <= $total;
	}

	/**
	 * Returns the Item ID to use within the take-away confirmation page.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Without replacement.
	 */
	public static function getTakeAwayConfirmItemID()
	{
		return intval(self::getFieldFromConfig('tkconfitemid', 'vrGetTakeAwayConfirmItemID'));
	}
	
	/**
	 * Returns the number of minutes for which the orders should be locked.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayOrdersLockedTime()
	{
		return intval(self::getFieldFromConfig('tklocktime', 'vrGetTakeawayOrdersLockedTime'));
	}
	
	/**
	 * Returns some notes to display within the head of the take-away menus list.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayNotes()
	{
		return self::getFieldFromConfig('tknote', 'vrGetTakeawayNotes');
	}
	
	/**
	 * Returns the global taxes ratio.
	 *
	 * @return 	float
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayTaxesRatio()
	{
		return floatval(self::getFieldFromConfig('tktaxesratio', 'vrGetTakeawayTaxesRatio'));
	}
	
	/**
	 * Checks whether the taxes are visible
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isTakeAwayTaxesVisible()
	{
		return (bool) self::getFieldFromConfig('tkshowtaxes', 'vrGetTakeawayShowTaxes');
	}

	/**
	 * Checks whether the taxes should be used.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isTakeAwayTaxesUsable()
	{
		return intval(self::getFieldFromConfig('tkusetaxes', 'vrGetTakeawayUseTaxes'));	
	}

	/**
	 * Returns the login requirements for the take-away process.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayLoginRequirements()
	{
		return intval(self::getFieldFromConfig('tkloginreq', 'vrGetTakeAwayLoginRequirements'));
	}

	/**
	 * Checks whether the users are allowed to create new accounts
	 * during the booking process for a take-away order.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isTakeAwayRegistrationEnabled()
	{
		return (bool) self::getFieldFromConfig('tkenablereg', 'vrGetTakeAwayEnableRegistration');
	}

	/**
	 * Returns the default status assumed by the take-away orders.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayDefaultStatus()
	{
		return self::getFieldFromConfig('tkdefstatus', 'vrGetTakeAwayDefaultStatus');
	}
	
	/**
	 * Checks whether the users are allowed to cancel take-away orders.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isTakeAwayCancellationEnabled()
	{
		return (bool) self::getFieldFromConfig('tkenablecanc', 'vrGetTakeAwayEnableCancellation');
	}

	/**
	 * Checks whether the users are forced to explain a reason when 
	 * trying to cancel a reservation.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayCancellationReason()
	{
		return intval(self::getFieldFromConfig('tkcancreason', 'vrGetTakeAwayCancellationReason'));
	}
	
	/**
	 * Returns the number of days ahead for which the users are allowed
	 * to cancel confirmed orders.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeAwayCancelBeforeTime()
	{
		return intval(self::getFieldFromConfig('tkcanctime', 'vrGetTakeAwayCancelBeforeTime'));
	}

	/**
	 * Returns a list of restaurant origin addresses.
	 *
	 * @return 	array
	 */
	public static function getTakeAwayOriginAddresses()
	{
		return VREFactory::getConfig()->getArray('tkaddrorigins', array());
	}
	
	/**
	 * Returns an array containing the e-mail sending rules (take-away).
	 * The array contains the rules for these entities: customer, operator, admin.
	 *
	 * @return 	array
	 */
	public static function getTakeawaySendMailWhen()
	{
		return array(
			'customer' => intval(self::getFieldFromConfig('tkmailcustwhen', 'vrTKSendMailCustWhen')),
			'operator' => intval(self::getFieldFromConfig('tkmailoperwhen', 'vrTKSendMailOperWhen')),
			'admin'    => intval(self::getFieldFromConfig('tkmailadminwhen', 'vrTKSendMailAdminWhen')),
		);
	}

	/**
	 * Returns the file name of the e-mail template for the customers (take-away).
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeawayMailTemplateName()
	{
		return self::getFieldFromConfig('tkmailtmpl', 'vrGetTakeawayMailTemplateName');
	}

	/**
	 * Returns the file name of the e-mail template for the administrators (take-away).
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeawayMailAdminTemplateName()
	{
		return self::getFieldFromConfig('tkadminmailtmpl', 'vrGetTakeawayMailAdminTemplateName');
	}

	/**
	 * Returns the file name of the e-mail template for cancellations (take-away).
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeawayMailCancellationTemplateName()
	{
		return self::getFieldFromConfig('tkcancmailtmpl', 'vrGetTakeawayMailCancellationTemplateName');
	}

	/**
	 * Returns the file name of the e-mail template for the reviews (take-away).
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeawayMailReviewTemplateName()
	{
		return self::getFieldFromConfig('tkreviewmailtmpl', 'vrGetTakeawayMailReviewTemplateName');
	}

	/**
	 * Returns the a list of closing days.
	 *
	 * @return 	array
	 */
	public static function getClosingDays()
	{
		$_str = VREFactory::getConfig()->get('closingdays', '');
		
		if (!strlen($_str))
		{
			return array();
		}

		$list = explode(';;', $_str);

		foreach ($list as &$cd)
		{
			$_app = explode(':', $cd);

			$cd = array(
				'ts'   => $_app[0],
				'date' => date(self::getDateFormat(), $_app[0]),
				'freq' => $_app[1],
			);
		}

		return $list;
	}
	
	/**
	 * Returns driver to use for SMS APIs.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getSmsApi()
	{
		return self::getFieldFromConfig('smsapi', 'vrGetSmsApi');
	}
	
	/**
	 * Returns the rule for sending SMS.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getSmsApiWhen()
	{
		return intval(self::getFieldFromConfig('smsapiwhen', 'vrGetSmsApiWhen'));
	}
	
	/**
	 * Returns the entity the should receive SMS.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getSmsApiTo()
	{
		return intval(self::getFieldFromConfig('smsapito', 'vrGetSmsApiTo'));
	}
	
	/**
	 * Returns the administrator phone number.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getSmsApiAdminPhoneNumber()
	{
		return self::getFieldFromConfig('smsapiadminphone', 'vrGetSmsApiAdminPhoneNumber');
	}
	
	/**
	 * Returns the SMS API driver fields.
	 *
	 * @return 	array
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getSmsApiFields()
	{
		return VREFactory::getConfig()->getArray('smsapifields', array());
	}
	
	/**
	 * Returns the default text to use for customers.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getSmsDefaultCustomersText()
	{
		return self::getFieldFromConfig('smstextcust', 'vrGetSmsCustomersText');
	}
	
	/**
	 * Returns the list of columns to display in the reservations list.
	 *
	 * @return 	array
	 */
	public static function getListableFields()
	{
		$str = VREFactory::getConfig()->get('listablecols');
		
		if (empty($str))
		{
			return array();
		}
		
		return explode(',', $str);
	}
	
	/**
	 * Returns the list of columns to display in the take-away orders list.
	 *
	 * @return 	array
	 */
	public static function getTakeAwayListableFields()
	{
		$str = VREFactory::getConfig()->get('tklistablecols');
		
		if (empty($str))
		{
			return array();
		}
		
		return explode(',', $str);
	}

	/**
	 * Checks whether to support the take-away stocks.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9
	 */
	public static function isTakeAwayStockEnabled()
	{
		return (bool) self::getFieldFromConfig('tkenablestock', 'vrGetEnableTakeAwayStock');
	}

	/**
	 * Returns the file name of the e-mail template for stocks notifications.
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getTakeawayStockMailTemplateName()
	{
		return self::getFieldFromConfig('tkstockmailtmpl', 'vrGetTakeawayStockMailTemplateName');
	}

	/**
	 * Returns the texts to display in the print order view.
	 *
	 * @return 	array
	 */
	public static function getPrintOrdersText()
	{
		$text = VREFactory::getConfig()->getArray('printorderstext', array());

		// merge saved text with default array
		return array_merge(
			array(
				'header' => '',
				'footer' => '',
			),
			$text
		);
	}

	/**
	 * Checks whether the API Framework is enabled.
	 *
	 * @return 	boolean
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function isApiFrameworkEnabled()
	{
		return (bool) self::getFieldFromConfig('apifw', 'vrGetApiFrameworkEnabled');
	}

	/**
	 * Returns the maximum number of failure attempts before banning a user.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getApiFrameworkMaxFailureAttempts()
	{
		return intval(self::getFieldFromConfig('apimaxfail', 'vrGetApiFrameworkMaxFailureAttempts'));
	}

	/**
	 * Returns the logging mode for API events.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getApiFrameworkLogMode()
	{
		return intval(self::getFieldFromConfig('apilogmode', 'vrGetApiFrameworkLogMode'));
	}

	/**
	 * Returns the interval for which auto-flush stored logs.
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	public static function getApiFrameworkLogFlush()
	{
		return intval(self::getFieldFromConfig('apilogflush', 'vrGetApiFrameworkLogFlush'));
	}

	/**
	 * Returns the confirmation message that will be asked while deleting an item.
	 * In case the confirmation message is disabled, an empty string will be returned.
	 *
	 * @return 	string
	 *
	 * @since 	1.8
	 */
	public static function getConfirmSystemMessage()
	{
		if (VREFactory::getConfig()->getBool('askconfirm', true))
		{
			return JText::_('VRSYSTEMCONFIRMATIONMSG');
		}

		return '';
	}

	/**
	 * Returns the audio file that will be used to play a
	 * notification sound every time a new reservation/order
	 * comes in.
	 *
	 * It is possible to use a different audio simply by uploading
	 * that file within the admin/assets/audio/ folder. The most
	 * recent file will be always used.
	 *
	 * @return 	string 	The file URI.
	 *
	 * @since 	1.8
	 */
	public static function getNotificationSound()
	{
		// get all files placed within audio folder
		$files = glob(VREADMIN . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . '*');

		// take only audio files (exclude default one too)
		$files = array_values(array_filter($files, function($f)
		{
			if (preg_match("/[\/\\\\]notification\.mp3$/i", $f))
			{
				// ignore default file
				return false;
			}

			// keep only the most common audio files
			return preg_match("/\.(mp3|mp4|wav|ogg|aac|flac)$/i", $f);
		}));

		if (!$files)
		{
			// no additional audio files, use the default one
			return VREASSETS_ADMIN_URI . 'audio/notification.mp3';
		}

		// sort files from the most recent to the oldest
		usort($files, function($a, $b)
		{
			// sort by descending creation date
			return filemtime($b) - filemtime($a);
		});

		// return most recent file
		return VREASSETS_ADMIN_URI . 'audio/' . basename($files[0]);
	}
	
	/**
	 * Accesses a configuration setting.
	 *
	 * @param 	string 	$param  The setting name.
	 *
	 * @return 	mixed
	 *
	 * @deprecated 	1.9  Use VREConfig instead.
	 */
	private static function getFieldFromConfig($param)
	{
		return VREFactory::getConfig()->get($param, '', 'string');
	}
	
	/**
	 * Loads the cart dependencies.
	 *
	 * @return 	void
	 */
	public static function loadCartLibrary()
	{
		VRELoader::import('library.cart.cart');
		
		VRELoader::import('library.cart.item');
		VRELoader::import('library.cart.itemgroup');
		VRELoader::import('library.cart.topping');
		
		VRELoader::import('library.cart.deals');
		VRELoader::import('library.cart.discount');
	}

	/**
	 * Loads the deals dependencies.
	 *
	 * @return 	void
	 */
	public static function loadDealsLibrary()
	{
		VRELoader::import('library.deals.handler');
	}

	/**
	 * Loads the banking dependencies.
	 *
	 * @param 	array 	$files  A list of dependencies to include.
	 * 							Loads all if empty.
	 *
	 * @return 	void
	 */
	public static function loadBankingLibrary($files = array())
	{
		if (!count($files) || in_array('creditcard', $files))
		{
			VRELoader::import('library.banking.creditcard');
		}
	}

	/**
	 * Loads the crypto dependencies.
	 *
	 * @return 	void
	 */
	public static function loadCryptLibrary()
	{
		VRELoader::import('library.crypt.cipher');
	}

	/**
	 * Loads the APIs Framework dependencies.
	 *
	 * @return 	void
	 */
	public static function loadFrameworkApis()
	{
		VRELoader::import('library.apislib.autoload');
		VRELoader::import('library.apislib.framework');
		VRELoader::import('library.apislib.login');
	}

	/**
	 * Flushes older API logs.
	 *
	 * @return 	void
	 */
	public static function flushApiLogs()
	{
		$factor = self::getApiFrameworkLogFlush();
		$now 	= static::now();

		if ($factor > 0)
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_api_login_logs'))
				->where('(' . $dbo->qn('createdon') . ' + 86400 * ' . $factor . ') < ' . $now);
			
			$dbo->setQuery($q);
			$dbo->execute();
		}
	}

	/**
	 * In case the group owns people that don't belong to the same
	 * family, the system will multiply the number of people by the
	 * configuration factor, so that the required distances can be
	 * maintained between the people.
	 *
	 * @param 	mixed 	 $people  The number of selected people. If not specified, this
	 * 							  value will be recovered from the request as 'people'.
	 * @param 	mixed 	 $family  True whether the customer agreed that all the members
	 * 							  belong to the same family. If not specified, this value
	 * 							  will be recovered from the request as 'family'.
	 *
	 * @return 	integer  The resulting number of people to apply to
	 * 					 the availability search.
	 *
	 * @since 	1.8
	 */
	public static function getPeopleSafeDistance($people = null, $family = null)
	{
		$input = JFactory::getApplication()->input;

		if (is_null($people))
		{
			// recover people from request
			$people = $input->get('people', 1, 'uint');
		}

		$config = VREFactory::getConfig();

		// make sure safe distance is supported
		if (!$config->getBool('safedistance'))
		{
			// do not alter number of people
			return $people;
		}

		if (is_null($family))
		{
			// recover family from request
			$family = $input->get('family', false, 'bool');
		}

		if ($family)
		{
			// all family members, use the specified number of people
			return $people;
		}

		// multiply the number of people by the specified factor
		return ceil($people * max(array(1, $config->getFloat('safefactor'))));
	}
	
	/**
	 * Checks whether the user can cancel an order.
	 *
	 * @param 	object   $order  The order instance.
	 * @param 	integer  $type   The order type (0 for restaurant, 1 for take-away).
	 *
	 * @return  boolean  True if possible, false otherwise.
	 */
	public static function canUserCancelOrder($order, $type = null)
	{
		if (is_null($type))
		{
			// fetch type from order class
			$type = preg_match("/Restaurant$/i", get_class($order)) ? 0 : 1;
		}

		// make sure the order is confirmed
		if ($order->status != 'CONFIRMED')
		{
			// order not confirmed
			return false;
		}

		$config = VREFactory::getConfig();

		if ($type == 0)
		{
			// check restaurant cancellation
			$enabled = $config->getBool('enablecanc');
			$mindays = $config->getUint('canctime');
			$maxmins = $config->getUint('cancmins');
		}
		else
		{
			// check take-away cancellation
			$enabled = $config->getBool('tkenablecanc');
			$mindays = $config->getUint('tkcanctime');
			$maxmins = $config->getUint('tkcancmins');
		}

		if (!$enabled)
		{
			// do not go ahead in case the cancellation is disabled
			return false;
		}

		// get current time
		$now = static::now();

		/**
		 * Check whether it is still possible to cancel the reservation
		 * by comparing the creation date time with the maximum number of minutes.
		 * In example, it could be possible to cancel a reservation only within
		 * 5 minutes since the purchase date time.
		 *
		 * @since 1.8
		 */
		if ($maxmins > 0)
		{
			// sum maximum number of minutes to creation date time
			$creation = strtotime('+' . $maxmins . ' minutes', $order->created_on);

			if ($now > $creation)
			{
				// the current time exceeded the specified limit
				return false;
			}
		}

		// sum minimum required days to current date and time
		$checkin = strtotime('+' . $mindays . ' days', $now);

		if ($checkin >= $order->checkin_ts)
		{
			// not enough time to complete the cancellation, the check-in
			// is too close to the current date and time
			return false;
		}

		// build plugin event
		$event = 'onCheck' . ($type == 0 ? 'Reservation' : 'Order') . 'Cancellation';

		/**
		 * This event can be used to apply additional conditions to the 
		 * cancellation restrictions. When this event is triggered, the
		 * system already validated the standard conditions and the
		 * cancellation has been approved for the usage.
		 *
		 * The method might be built as:
		 * - onCheckReservationCancellation  for restaurant reservations;
		 * - onCheckOrderCancellation 		 for take-away orders.
		 *
		 * @param 	mixed 	 $order  The order/reservation to check.
		 *
		 * @return 	boolean  Return false to deny the cancellation.
		 *
		 * @since 	1.8
		 */
		$res = VREFactory::getEventDispatcher()->trigger($event, array($order));

		// check if at least a plugin returned FALSE to prevent the cancellation
		return !in_array(false, $res, true);
	}

	/**
	 * Checks whether the user can approve its own order.
	 *
	 * @param 	object   $order  The order instance.
	 * @param 	integer  $type   The order type (0 for restaurant, 1 for take-away).
	 *
	 * @return  boolean  True if possible, false otherwise.
	 *
	 * @since 	1.8
	 */
	public static function canUserApproveOrder($order, $type = null)
	{
		if (is_null($type))
		{
			// fetch type from order class
			$type = preg_match("/Restaurant$/i", get_class($order)) ? 0 : 1;
		}

		// make sure the order is pending
		if ($order->status != 'PENDING')
		{
			// order not pending
			return false;
		}

		$config = VREFactory::getConfig();

		// check if the order has been assigned to a payment
		if ($order->id_payment > 0)
		{
			// get payment details
			$payment = VikRestaurants::hasPayment(null, $order->id_payment);

			/**
			 * Check if the payment allows the self-confirmation.
			 *
			 * @since 1.8.1
			 */
			$enabled = $payment && $payment->selfconfirm;
		}
		// otherwise check parameter globally
		else
		{
			if ($type == 0)
			{
				// check restaurant self-confirmation
				$enabled = $config->getBool('selfconfirm');
			}
			else
			{
				// check take-away self-confirmation
				$enabled = $config->getBool('tkselfconfirm');
			}
		}

		if (!$enabled)
		{
			// do not go ahead in case the self-confirmation is disabled
			return false;
		}

		// build plugin event
		$event = 'onCheck' . ($type == 0 ? 'Reservation' : 'Order') . 'SelfConfirmation';

		/**
		 * This event can be used to apply additional conditions to the 
		 * self-confirmation restrictions. When this event is triggered, the
		 * system already validated the standard conditions and the
		 * confirmation has been approved for the usage.
		 *
		 * The method might be built as:
		 * - onCheckReservationSelfCancellation  for restaurant reservations;
		 * - onCheckOrderSelfCancellation 		 for take-away orders.
		 *
		 * @param 	mixed 	 $order  The order/reservation to check.
		 *
		 * @return 	boolean  Return false to deny the confirmation.
		 *
		 * @since 	1.8
		 */
		$res = VREFactory::getEventDispatcher()->trigger($event, array($order));

		// check if at least a plugin returned FALSE to prevent the confirmation
		return !in_array(false, $res, true);
	}

	/**
	 * Checks whether the user is allowed to order food.
	 *
	 * @param 	object   $order   The order instance.
	 * @param 	mixed 	 &$error  This parameter can be used to retrieve
	 * 							  the reason of the failure.
	 *
	 * @return  boolean  True if possible, false otherwise.
	 *
	 * @since 	1.8
	 */
	public static function canUserOrderFood($order, &$error = null)
	{
		$config = VREFactory::getConfig();

		// get ordering flag
		// 0: never
		// 1: at the restaurant
		// 2: always
		$flag = $config->getUint('orderfood');

		if ($flag == 0)
		{
			// food ordering not allowed
			return false;
		}

		// make sure the order is confirmed
		if ($order->status != 'CONFIRMED')
		{
			// order not confirmed
			$error = JText::_('VREORDERFOOD_DISABLED_STATUS');
			
			return false;
		}

		$now = static::now();

		// make sure the group arrived at the restaurant
		if ($flag == 1 && !$order->arrived && $order->checkin_ts > $now)
		{
			// not yet arrived
			$error = JText::_('VREORDERFOOD_DISABLED_ARRIVED');

			return false;
		}

		// calculate time threshold
		$checkout = strtotime('+3 hours', $order->checkout);

		// allow ordering as long as the bill is open and
		// didn't pass more than 3 hours since the check-out
		if ($order->bill_closed == 1 || $checkout < $now)
		{
			// ordering no more allowed
			$error = JText::_('VREORDERFOOD_DISABLED_BILLCLOSED');

			return false;
		}

		/**
		 * This event can be used to apply additional conditions to the 
		 * default restrictions. When this event is triggered, the
		 * system already validated the standard conditions and the
		 * food ordering has been approved for the usage.
		 *
		 * @param 	mixed 	 $order   The restaurant reservation to check.
		 * @param 	mixed 	 &$error  It is possible to include here the reason
		 * 							  of the failure.
		 *
		 * @return 	boolean  Return false to deny the food ordering.
		 *
		 * @since 	1.8
		 */
		$res = VREFactory::getEventDispatcher()->trigger('onCheckRestaurantFoodOrdering', array($order, &$error));

		// check if at least a plugin returned FALSE to prevent the food ordering
		return !in_array(false, $res, true);
	}

	/**
	 * Prepares the document related to the specified view.
	 * Used also to implement OPEN GRAPH protocol and to include
	 * global meta data.
	 *
	 * @param 	mixed 	$page 	The view object.
	 *
	 * @return 	void
	 */
	public static function prepareContent($page)
	{
		VRELoader::import('library.view.contents');

		$handler = VREViewContents::getInstance($page);

		/**
		 * Set the browser page title.
		 *
		 * @since 1.8
		 */
		$handler->setPageTitle();

		// show the page heading (if not provided, an empty string will be returned)
		$handler->getPageHeading(true);

		// set the META description of the page
		$handler->setMetaDescription();

		// set the META keywords of the page
		$handler->setMetaKeywords();

		// set the META robots of the page
		$handler->setMetaRobots();

		// create OPEN GRAPH protocol
		$handler->buildOpenGraph();

		// create MICRODATA
		$handler->buildMicrodata();
	}
	
	/**
	 * Checks whether a user is logged-in.
	 *
	 * @return 	boolean
	 *
	 * @deprecated  1.9  Use JUser::guest instead.
	 */
	public static function userIsLogged()
	{
		return !JFactory::getUser()->guest;
	}
	
	/**
	 * Helper method used to print formatted prices according to the global configuration.
	 *
	 * @param 	float 	 $price  The price to format.
	 * @param 	string 	 $symb 	 The currency symbol. If not provided the default one will be used.
	 * @param 	integer  $pos 	 The currency position (1 = after price, 2 = before price).
	 * 							 If not provided, the default one will be used.
	 *
	 * @return 	string 	 The formatted price.
	 *
	 * @deprecated 	1.9  Use VRECurrency::format() instead.
	 */
	public static function printPriceCurrencySymb($price, $curr_symb = null, $pos = null)
	{
		// get currency instance
		$currency = VREFactory::getCurrency();

		$options = array();

		if ($curr_symb)
		{
			$options['symbol'] = $curr_symb;
		}

		if ($pos)
		{
			$options['position'] = (int) $pos;
		}

		// format currency
		return $currency->format($price, $options);
	}
	
	/**
	 * Loads global CSS and JS resources.
	 *
	 * @return 	void
	 */
	public static function load_css_js()
	{
		$vik = VREApplication::getInstance();

		// since jQuery is a required dependency, the framework should be 
		// invoked even if jQuery is disabled
		$vik->loadFramework('jquery.framework');
		
		if (self::isLoadJQuery())
		{
			$vik->addScript(VREASSETS_URI . 'js/jquery.min.js');
		}
		
		$vik->addScript(VREASSETS_URI . 'js/jquery-ui.min.js');
		$vik->addScript(VREASSETS_URI . 'js/vikrestaurants.js');

		$vik->addStyleSheet(VREASSETS_URI . 'css/jquery-ui.min.css');
		$vik->addStyleSheet(VREASSETS_URI . 'css/vikrestaurants.css');
		$vik->addStyleSheet(VREASSETS_URI . 'css/select.css');
		$vik->addStyleSheet(VREASSETS_URI . 'css/vikrestaurants-mobile.css');

		// custom
		if (is_file(VREBASE . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'vre-custom.css'))
		{
			$vik->addStyleSheet(VREASSETS_URI . 'css/vre-custom.css');
		}

		/**
		 * Loads utils.
		 *
		 * @since 1.8
		 */
		JHtml::_('vrehtml.assets.utils');

		/**
		 * Always instantiate the currency object.
		 *
		 * @since 1.7.4
		 */
		self::load_currency_js();
	}
	
	/**
	 * Loads Fanycbox jQuery add-on.
	 *
	 * @return 	void
	 *
	 * @deprecated 	1.9  Use JHtml::('vrehtml.assets.fancybox') instead.
	 */
	public static function load_fancybox()
	{
		JHtml::_('vrehtml.assets.fancybox');
	}

	/**
	 * Loads Select2 jQuery add-on.
	 *
	 * @return 	void
	 *
	 * @deprecated 	1.9  Use JHtml::('vrehtml.assets.select2') instead.
	 */
	public static function load_complex_select()
	{
		JHtml::_('vrehtml.assets.select2');
	}

	/**
	 * Loads Google Maps JS script.
	 *
	 * @return 	void
	 *
	 * @deprecated 	1.9  Use JHtml::('vrehtml.assets.googlemaps') instead.
	 */
	public static function load_googlemaps()
	{
		JHtml::_('vrehtml.assets.googlemaps');
	}

	/**
	 * Loads FontAwesome.
	 *
	 * @return 	void
	 *
	 * @deprecated 	1.9  Use JHtml::('vrehtml.assets.fontawesome') instead.
	 */
	public static function load_font_awesome()
	{
		JHtml::_('vrehtml.assets.fontawesome');
	}

	/**
	 * Configures the Currency JS object.
	 *
	 * @return 	void
	 *
	 * @since 	1.7.4
	 *
	 * @deprecated 	1.9  User JHtml::_('vrehtml.assets.currency') instead
	 */
	public static function load_currency_js()
	{
		JHtml::_('vrehtml.assets.currency');
	}

	/**
	 * Returns the current login URL.
	 *
	 * @param 	string 	 $url
	 * @param 	boolean  $xhtml
	 *
	 * @return 	string
	 */
	public static function getLoginReturnURL($url = '', $xhtml = false)
	{
		if (empty($url))
		{
			// get current URL
			return JUri::getInstance()->toString();
		}
		
		// route specified URL
		return VREApplication::getInstance()->routeForExternalUse($url, $xhtml);
	}
	
	/**
	 * Checks whether the requested arguments are valid to
	 * register a table booking.
	 *
	 * @param 	array 	 $args  An associative array containing the checkin
	 * 						    date, time and people.
	 *
	 * @return 	integer  The error code, otherwise 0 on success.
	 */
	public static function isRequestReservationValid($args)
	{
		$config = VREFactory::getConfig();

		if (empty($args['date']))
		{
			// missing date
			return 1;
		}
		
		if (empty($args['hourmin']))
		{
			// missing time
			return 2;
		}
		else
		{
			$tmp = explode(':', $args['hourmin']);

			if (count($tmp) != 2)
			{
				// invalid time string (HH:mm)
				return 2;
			}
			
			$args['hour'] = intval($tmp[0]);
			$args['min']  = intval($tmp[1]);

			/**
			 * Do not check anymore whether the specified minutes
			 * are a valid interval. This because the same check
			 * is already performed by isHourBetweenShifts() method,
			 * which makes sure that the selected time is an existing
			 * time slot.
			 *
			 * @since 1.8
			 */
			
			if (!self::isHourBetweenShifts($args, 1))
			{
				// the selected time is not part of a shift
				return 3;
			}
		}
		
		if (empty($args['people']) || $args['people'] < self::getMinimumPeople() || $args['people'] > self::getMaximumPeople())
		{
			// the selected number of people is not allowed
			return 4;
		}
		
		// check date

		/**
		 * Workaround used to adjust the current time to the specific
		 * timezone for those websites that are not able to change the
		 * server configuration.
		 *
		 * @since 1.7.4
		 */
		$now = static::now();
		
		$_date = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		
		if ($now > $_date)
		{
			// the selected check-in is in the past
			return 5;
		}

		// get first available checkin
		$next = strtotime('+' . self::getBookingMinutesRestriction() . ' minutes', $now);

		/**
		 * Get minimum date available.
		 *
		 * @since 1.8
		 */
		$minDate = $config->getUint('mindate');

		if ($minDate)
		{
			// increase current date by the specified number of days
			$tmp = strtotime('+' . $minDate . ' days 00:00:00', $now);
			// take highest timestamp between min date and asap
			$next = max(array($next, $tmp));
		}
		
		if ($next > $_date)
		{
			// the check-in time is not in the past but it is
			// before the first allowed time
			return 6;
		}

		/**
		 * Get maximum date available.
		 *
		 * @since 1.8
		 */
		$maxDate = $config->getUint('maxDate');

		if ($maxDate)
		{
			// increase current date by the specified number of days
			$maxDate = strtotime('+' . $maxDate . ' days 23:59:59', $now);
			
			if ($_date > $maxDate)
			{
				// the check-in time exceeds the maximum limit
				return 7;
			}
		}
		
		// valid request
		return 0;
	}
	
	/**
	 * Returns the error message related to the specified code.
	 *
	 * @param 	integer  $code  The error code.
	 *
	 * @return 	string 	 The error message.
	 *
	 * @see 	isRequestReservationValid()
	 */
	public static function getResponseFromReservationRequest($code)
	{
		$config = VREFactory::getConfig();

		/**
		 * Take maximum number of minutes between booking
		 * minutes restrictions and minimum check-in date.
		 *
		 * @since 1.8
		 */
		$asap = $config->getUint('bookrestr');
		$min  = $config->getUint('mindate') * 1440;

		$asap = max(array($asap, $min));

		$lookup = array( 
			'', 
			'VRRESERVATIONREQUESTMSG1', 
			'VRRESERVATIONREQUESTMSG2',
			'VRRESERVATIONREQUESTMSG3',
			'VRRESERVATIONREQUESTMSG4',
			'VRRESERVATIONREQUESTMSG5',
			/**
			 * Format Booking Minutes Restriction to the closest units.
			 *
			 * @since 1.7.4
			 */
			JText::sprintf(
				'VRRESERVATIONREQUESTMSG6',
				self::minutesToStr($asap)
			),
			/**
			 * Format Maximum Date restriction.
			 *
			 * @since 1.8
			 */
			JText::sprintf(
				'VRRESERVATIONREQUESTMSG7',
				self::minutesToStr($config->get('maxdate') * 1440)
			),
		);
		
		return $lookup[$code];
	}

	/**
	 * Returns the current time adjusted to the global timezone.
	 * Proxy for timestamp() method without passing any arguments.
	 *
	 * @return 	integer  The current time.
	 *
	 * @since 	1.7.4
	 */
	public static function now()
	{
		return self::timestamp();
	}

	/**
	 * Adjusts the given timestamp to the global timezone.
	 *
	 * @param 	integer  $ts  The timestamp to adjust.
	 *
	 * @return 	integer  The current time.
	 *
	 * @since 	1.7.4
	 */
	public static function timestamp($ts = null)
	{
		// create timezone instance
		$timezone = new DateTimeZone(JFactory::getConfig()->get('offset', 'UTC'));

		if (is_null($ts))
		{
			// get current time based on server configuration
			$date = new JDate();
		}
		else
		{
			// instantiate date object using the given timestamp
			$date = new JDate($ts);
		}

		// adjust to global timezone
		$date->setTimezone($timezone);

		// convert adjusted datetime to timestamp (based on server timezone)
		return strtotime($date->format('Y-m-d H:i:s', true));
	}

	/**
	 * Checks if the given time is in the past.
	 *
	 * @param 	mixed 	 $date 	A timestamp, a date string or an array of arguments.
	 *
	 * @return 	boolean  True if in the past, otherwise false.
	 *
	 * @since 	1.7.4
	 */
	public static function isTimePast($date)
	{
		if (is_integer($date))
		{
			// always convert timestamp to a supported date format
			$date = date('Y-m-d H:i', $date);
			// instantiate date object based on local timezone (this will adjust the date to UTC)
			$date = JDate::getInstance($date, JFactory::getConfig()->get('offset', 'UTC'));
		}
		else if (is_array($date))
		{
			// create timestamp from filters
			$ts = self::createTimestamp(@$date['date'], @$date['hour'], @$date['min']);

			// always convert timestamp to a supported date format
			$date = date('Y-m-d H:i', $ts);

			// instantiate date object based on local timezone (this will adjust the date to UTC)
			$date = JDate::getInstance($date, JFactory::getConfig()->get('offset', 'UTC'));
		}
		else
		{
			// instantiate date object based on UTC timezone, as it is supposed
			// to receive dates that were stored within the database
			$date = JDate::getInstance($date);
		}

		// check if the given date is in the past
		return $date->getTimestamp() <= JDate::getInstance()->getTimestamp();
	}
	
	/**
	 * Returns a human-readable string to check how time passed
	 * (or needs to pass) since the specified timestamp.
	 * The function supports conversion in minutes, hours, days and weeks.
	 *
	 * For example: 2 min. ago (past version) or in 2 min. (future version).
	 *
	 * In case the difference between the timestamp and the current time is
	 * longer than 2 weeks, it will be displayed the formatted date as fallback.
	 * 
	 * @param 	string 	 $dt_f 	 The date format as fallback.
	 * @param 	integer  $ts 	 The UNIX timestamp to check.
	 * @param 	boolean  $local  True to convert the provided timestamp
	 * 							 to the local timezone.
	 *
	 * @return 	string 	 The formatted string.
	 */
	public static function formatTimestamp($dt_f, $ts, $local = true)
	{
		/**
		 * Added $local parameter to adjust the specified timezone
		 * to the local offset if needed.
		 *
		 * @since 1.7.4
		 */
		if ($local)
		{
			// use current local time
			$now = self::now();
		}
		else
		{
			// use current server time
			$now = time();
		}

		if (abs($now - $ts) < 60)
		{
			return JText::_('VRDFNOW');
		}
		
		$diff = ($now - $ts);
		
		$minutes = abs($diff) / 60;
		
		if ($minutes < 60)
		{
			$minutes = floor($minutes);

			return JText::sprintf('VRDFMINS' . ($diff > 0 ? 'AGO' : 'AFT'), $minutes);
		}
		
		$hours = $minutes / 60;

		if ($hours < 24)
		{
			$hours = floor($hours);
			
			if ($hours == 1)
			{
				return JText::_('VRDFHOUR' . ($diff > 0 ? 'AGO' : 'AFT'));
			}

			return JText::sprintf('VRDFHOURS' . ($diff > 0 ? 'AGO' : 'AFT'), $hours);
		}
		
		$days = $hours / 24;

		if ($days < 7)
		{
			$days = floor($days);

			if ($days == 1)
			{
				return JText::_('VRDFDAY' . ($diff > 0 ? 'AGO' : 'AFT'));
			}

			return JText::sprintf('VRDFDAYS' . ($diff > 0 ? 'AGO' : 'AFT'), $days);
		}
		
		$weeks = $days / 7;

		if ($weeks < 3)
		{
			$weeks = floor($weeks);

			if ($weeks == 1)
			{
				return JText::_('VRDFWEEK' . ($diff > 0 ? 'AGO' : 'AFT'));
			}

			return JText::sprintf('VRDFWEEKS' . ($diff > 0 ? 'AGO' : 'AFT'), $weeks);
		}

		if (!$dt_f)
		{
			// do not return anything in case of missing format
			return '';
		}
		
		/**
		 * Adjust date time to local timezone.
		 *
		 * @since 1.7.4
		 */
		return JHtml::_('date', $ts, $dt_f, date_default_timezone_get());
	}

	/**
	 * Helper method to format the specified minutes to the closest unit.
	 * For example, 150 minutes will be formatted as "1 hour & 30 min.".
	 *
	 * @param 	string 	 $minutes 	The minutes amount.
	 *
	 * @return 	string 	 The formatted string.
	 */
	public static function minutesToStr($minutes)
	{
		$min_str   = array( JText::_('VRSHORTCUTMINUTE') );

		/**
		 * Try using the front-end language key in case
		 * the VRSHORTCUTMINUTE text is not translated.
		 *
		 * @since 1.8
		 */
		if ($min_str[0] == 'VRSHORTCUTMINUTE')
		{
			$min_str[0] = JText::_('VRMINSHORT');
		}
		
		$hours_str = array( JText::_('VRFORMATHOUR') , JText::_('VRFORMATHOURS') );
		$days_str  = array( JText::_('VRFORMATDAY')  , JText::_('VRFORMATDAYS')  );
		$weeks_str = array( JText::_('VRFORMATWEEK') , JText::_('VRFORMATWEEKS') );
		
		$comma_char = JText::_('VRFORMATCOMMASEP');
		$and_char 	= JText::_('VRFORMATANDSEP');
		
		$is_negative = $minutes < 0 ? 1 : 0;
		$minutes 	 = abs($minutes);
		
		$format = "";

		while ($minutes >= 60)
		{
			$app_str = "";

			if ($minutes >= 10080)
			{
				// weeks
				$val = intval($minutes / 10080);

				// if greater than 1 -> multiple, otherwise single
				$app_str = $val . ' ' . $weeks_str[(int) ($val > 1)];
				$minutes = $minutes % 10080;
			} 
			else if ($minutes >= 1440)
			{
				// days
				$val = intval($minutes / 1440);

				// if greater than 1 -> multiple, otherwise single
				$app_str = $val . ' ' . $days_str[(int) ($val > 1)];
				$minutes = $minutes % 1440;
			}
			else
			{
				// hours
				$val = intval($minutes / 60);

				// if greater than 1 -> multiple, otherwise single
				$app_str = $val . ' ' . $hours_str[(int) ($val > 1)];
				$minutes = $minutes % 60;
			}
			
			$sep = '';
			
			if ($minutes > 0)
			{
				$sep = $comma_char;
			}
			else if ($minutes == 0)
			{
				$sep = " $and_char";
			}
			
			$format .= (!empty($format) ? $sep . ' ' : '') . $app_str;
		}
		
		if ($minutes > 0)
		{
			$format .= (!empty($format) ? " $and_char " : '') . $minutes . ' ' . $min_str[0];
		}
		
		if ($is_negative)
		{
			$format = '-' . $format;
		}
			
		return $format;
	}

	/**
	 * Counts the total number of people at the specified date time.
	 *
	 * @param 	integer  $ts
	 *
	 * @return 	integer
	 *
	 * @deprecated 	1.9 Use VREAvailabilitySearch instead.
	 */
	public static function getPeopleAt($ts)
	{
		$dbo = JFactory::getDbo();
		
		$avg = self::getAverageTimeStay()*60;
		
		$q = "SELECT SUM(`r`.`people`) FROM `#__vikrestaurants_reservation` AS `r` 
		WHERE (
			( `r`.`checkin_ts` < $ts AND $ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
			( `r`.`checkin_ts` < $ts+$avg AND $ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
			( `r`.`checkin_ts` < $ts AND $ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
			( `r`.`checkin_ts` > $ts AND $ts+$avg > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
			( `r`.`checkin_ts` = $ts AND $ts+$avg = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
		);";

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadResult();
		}
		
		return 0;
	}

	/**
	 * Fetches the CSS gradient to use in proportion with the specified
	 * reservations statuses (CONFIRMED or PENDING).
	 *
	 * @param 	array 	$list       A map of status codes.
	 * @param 	string 	$direction  The gradient direction.
	 *
	 * @return 	string 	the CSS rules.
	 *
	 * @since 	1.7
	 */
	public static function getCssGradientFromStatuses($list = array(), $direction = 'right')
	{
		// make sure we have more than one status
		if (count(array_keys($list)) <= 1)
		{
			return false;
		}

		arsort($list);

		$total_count = 0;

		foreach ($list as $status => $count)
		{
			$total_count += $count;
		}

		$rgb_css = array();

		foreach ($list as $status => $count)
		{
			 $rgba = array();

			 if ($status == 'CONFIRMED')
			 {
			 	$rgba = array(56, 200, 112, 1);
			 }
			 else
			 {
			 	$rgba = array(233, 184, 44, 1);
			 }

			 $perc = $count * 100 / $total_count;

			 $rgb_css[] = 'rgba(' . $rgba[0] . ',' . $rgba[1] . ',' . $rgba[2] . ',' . $rgba[3] . ') ' . $perc . '%';
		}

		$rgb_css = implode(',', $rgb_css);

		return "background: -webkit-linear-gradient($direction,$rgb_css);"
			 . "background: -o-linear-gradient($direction,$rgb_css);"
			 . "background: -moz-linear-gradient($direction,$rgb_css);"
			 . "background: linear-gradient(to $direction,$rgb_css);";
	}
	
	/**
	 * Checks whether the menu selected during the 
	 * booking process are valid.
	 *
	 * @param 	array 	 $args 	An associative array with the search arguments.
	 *
	 * @return 	boolean  True if valid, false otherwise.
	 *
	 * @since 	1.5
	 */
	public static function validateSelectedMenus($args)
	{
		// Get menus available for the selected date and time.
		// Obtain only the menus that can effectively be chosen.
		$menus = self::getAllAvailableMenusOn($args, $choosable = true);
		
		if (count($menus) == 0)
		{
			// no menus selection, request valid
			return true;
		}
		
		$total = 0;

		// iterate selected menus
		foreach ($args['menus'] as $id => $quantity)
		{
			$ok = false;
			
			// find whether the menu is available
			for ($i = 0; $i < count($menus) && !$ok; $i++)
			{
				if ($id == $menus[$i]->id)
				{
					// menu found, increase total quantity
					$total += $quantity;

					$ok = true;
				}
			}
		}
		
		// make sure the selected total is OK
		return $total == $args['people'];
	}
	
	/**
	 * Creates the UNIX timestamp related to the specified date, hour and minutes.
	 *
	 * @param 	string 	 $date  The date in the configuration format.
	 * @param 	integer  $hour  The time hours.
	 * @param 	integer  $min   The time minutes.
	 *
	 * @return 	integer  The UNIX timestamp, otherwise -1 on failure.
	 */
	public static function createTimestamp($date, $hour = 0, $min = 0)
	{
		// get date format
		$date_format = VREFactory::getConfig()->get('dateformat');

		if (JFactory::getDbo()->getNullDate() == $date)
		{
			return -1;
		}

		// second char of $date_format can be only ['/', '.', '-']
		$df_separator = $date_format[1];

		$formats = explode($df_separator, $date_format);
		$d_exp   = explode($df_separator, $date);
		
		if (count($d_exp) != 3)
		{
			return -1;
		}
		
		$_attr = array();

		for ($i = 0, $n = count($formats); $i < $n; $i++)
		{
			$_attr[$formats[$i]] = $d_exp[$i];
		}

		/**
		 * Use 59 seconds in case we are asking for
		 * the end of the day.
		 *
		 * @since 1.8
		 */
		if ($hour == 23 && $min == 59)
		{
			$sec = 59;
		}
		else
		{
			$sec = 0;
		}
		
		return mktime((int) $hour, (int) $min, $sec, $_attr['m'], $_attr['d'], $_attr['Y']);
	}
	
	/**
	 * Checks whether the specified checkin arguments are
	 * part of an existing working shift.
	 *
	 * @param 	array 	 $args    An associative array of checkin arguments.
	 * @param 	integer  $group   The group to check (1: restaurant, 2: takeaway).
	 * @param 	boolean  $strict  True to make sure that the selected time is a valid
	 * 							  time slot for bookings. Otherwise the method will check
	 * 							  whether the specified time stays between a shift.
	 * 
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public static function isHourBetweenShifts($args, $group = 1, $strict = true)
	{
		/**
		 * Obtain list of available times and make
		 * sure the selected time is there.
		 *
		 * @since 1.8
		 */
		$times = JHtml::_('vikrestaurants.times', $group, $args['date']);

		$tmp = explode(':', $args['hourmin']);

		// calculate time in minutes
		$hm = (int) $tmp[0] * 60 + (int) $tmp[1];

		// iterate all working shifts
		foreach ($times as $shift)
		{
			// reset previous slot
			$prev = null;

			// iterate all times in shift
			foreach ($shift as $time)
			{
				$tmp = explode(':', $time->value);

				// calculate shift time in minutes
				$hm2 = (int) $tmp[0] * 60 + (int) $tmp[1];

				// strict mode on?
				if ($strict)
				{
					// make sure the time is exactly the same
					if ($hm == $hm2)
					{
						// the time is supported
						return true;
					}
				}
				else
				{
					// check if the specified time stays between this slot
					// and the previous one
					if ($prev !== null && $prev <= $hm && $hm <= $hm2)
					{
						// the time is supported
						return true;
					}
					
					// update previous slot
					$prev = $hm2;
				}
			}
		}
		
		// time not supported
		return false;
	}
	
	/**
	 * Finds the first available hour.
	 *
	 * @return 	string
	 */
	public static function getFirstAvailableHour()
	{
		if (self::isContinuosOpeningTime())
		{
			return self::getFromOpeningHour() . ':0';
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('MIN(' .  $dbo->qn('from') . ') AS ' . $dbo->qn('from'))
			->from($dbo->qn('#__vikrestaurants_shifts'));

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$from = $dbo->loadResult();

			$h = floor($from / 60);
			$m = $from % 60;

			return $h . ':' . $m;
		} 
		
		return -1;
	}

	/**
	 * Returns the closest available time for the given day.
	 *
	 * Considering the shifts below:
	 * 12:00 - 14:00
	 * 18:00 - 23:00
	 *
	 * In case the current time is 13:35, the closest available time
	 * would be 13:xx where xx is the minute rounded to the previous shift.
	 * For example, in case of 30 minutes of interval, the time would be 13:30.
	 *
	 * In case the current time is 16:45, the closest available time
	 * would be 18:00, the first time available in the future.
	 *
	 * @param 	mixed 	 &$day 	  The timestamp to use. Null to use the current day.
	 * 							  In case the timestamp is specified, it must be adjusted
	 * 							  to the local timezone.
	 * @param 	boolean  $next 	  True whether the closest time should always be in the future.
	 * @param 	integer  $client  The section to check (1: restaurant, 2: takeaway).
	 *
	 * @return 	mixed 	 The hour and minutes string in case of success, using the format h:m.
	 * 					 False in case of failure.
	 *
	 * @since 	1.7.4
	 */
	public static function getClosestTime(&$day = null, $next = false, $client = 1)
	{
		$shifts = array();

		$config = VREFactory::getConfig();

		if (is_null($day))
		{
			// get current local time
			$day = self::now();

			if ($next && JFactory::getApplication()->isClient('site'))
			{
				if ($client == 1)
				{
					// In case of site client, we need to increase the current time
					// by the specified "Booking Minutes Restriction" setting.
					$day = strtotime('+' . self::getBookingMinutesRestriction() . ' minutes', $day);
				}

				/**
				 * Get minimum date available.
				 *
				 * @since 1.8
				 */
				$minDate = $config->getUint($client == 1 ? 'mindate' : 'tkmindate');

				if ($minDate)
				{
					// increase current date by the specified number of days
					$tmp = strtotime('+' . $minDate . ' days 00:00:00', self::now());
					// take highest timestamp between min date and asap
					$day = max(array($day, $tmp));
				}
			}
		}
		else if (is_string($day))
		{
			// get current time
			$now = self::now();

			/**
			 * Check whether the specified day is equals to the current one,
			 * in order to use the proper time in the future. 
			 *
			 * @since 1.8.3
			 */
			if (date('Y-m-d', $now) == $day)
			{
				// find current hour and minutes
				list($hour, $min) = explode(':', date('H:i', self::now()));
			}
			else
			{
				// then use midnight for a different date
				$hour = $min = 0;
			}

			// create timestamp for given string by using current hour and minutes
			$day = VikRestaurants::createTimestamp($day, $hour, $min);
		}

		// use filters to consider current local time
		$filters = array(
			'date' => date(self::getDateFormat(), $day),
			'hour' => date('H', $day),
			'min'  => date('i', $day),
		);

		if ($client == 1)
		{
			$min_int = self::getMinuteIntervals();
		}
		else
		{
			$min_int = self::getTakeAwayMinuteInterval();
		}

		// Always round to the previous interval.
		// e.g. 11:29 -> 29 % 30 = 29 - 29 = 11:00
		// e.g. 12:45 -> 45 % 30 = 45 - 15 = 12:30
		// e.g. 13:57 -> 57 % 15 = 57 - 8  = 13:45
		$filters['min'] -= $filters['min'] % $min_int;

		if ($next === true)
		{
			// increase the minutes by the interval amount in order
			// to retrieve always the closest future time
			$filters['min'] += $min_int;

			// increase the hours by the number of exceeding minutes
			$filters['hour'] += floor($filters['min'] / 60);

			// always round the minutes
			$filters['min'] = $filters['min'] % 60;
		}

		// get the working shifts available for the specified day
		$shifts = JHtml::_('vikrestaurants.times', $client, $day);

		$tmp = $filters['hour'] * 60 + $filters['min'];

		// iterate all the working shifts
		foreach ($shifts as $shift)
		{
			/**
			 * Search for the closest time slot because
			 * the fetched time might not exist, as the
			 * minute intervals could not correspond to
			 * the shift intervals.
			 *
			 * @since 1.8
			 */
			foreach ($shift as $timeSlot)
			{
				// convert time to minutes
				$hm = JHtml::_('vikrestaurants.time2min', $timeSlot);

				if ($hm >= $tmp)
				{
					return $timeSlot->value;
				}
			}
		}

		// impossible to evaluate the closest time, return false
		return false;
	}

	/**
	 * Returns the closest available time for the given day (take-away section only).
	 *
	 * Considering the shifts below:
	 * 12:00 - 14:00
	 * 18:00 - 23:00
	 *
	 * In case the current time is 13:35, the closest available time
	 * would be 13:xx where xx is the minute rounded to the previous shift.
	 * For example, in case of 30 minutes of interval, the time would be 13:30.
	 *
	 * In case the current time is 16:45, the closest available time
	 * would be 18:00, the first time available in the future.
	 *
	 * @param 	mixed 	 &$day 	The timestamp to use. Null to use the current day.
	 * 							In case the timestamp is specified, it must be adjusted
	 * 							to the local timezone.
	 * @param 	boolean  $next 	True whether the closest time should always be in the future.
	 *
	 * @return 	mixed 	 The hour and minutes string in case of success, using the format h:m.
	 * 					 False in case of failure.
	 *
	 * @since 	1.8
	 */
	public static function getClosestTimeTakeAway(&$day = null, $next = false)
	{
		// get closest time for take-away section
		return self::getClosestTime($day, $next, 2);
	}

	/**
	 * Validates the selected time against the available ones.
	 * In case of invalid time, the first one will be used.
	 *
	 * @param 	string 	 &$time   The select time string.
	 * @param 	mixed 	 $shifts  The list of available times. In case a string was
	 * 							  passed, it will be used as date to retrieve all the
	 * 							  available time slots.
	 *
	 * @return 	boolean  True if valid, false otherwise.
	 *
	 * @since 	1.8
	 */
	public static function validateTakeAwayTime(&$time, $shifts)
	{
		if (is_string($shifts))
		{
			// obtain all the available times for pick-up and delivery
			$shifts = JHtml::_('vikrestaurants.takeawaytimes', $shifts);
		}

		if ($time)
		{
			// convert time to minutes
			$hm = JHtml::_('vikrestaurants.time2min', $time);
		}
		else
		{
			$hm = 0;
		}

		foreach ($shifts as $shift)
		{
			foreach ($shift as $slot)
			{
				// convert time to minutes
				$hm2 = JHtml::_('vikrestaurants.time2min', $slot);

				if (empty($slot->disable) && $hm == $hm2)
				{
					// time is valid
					return true;
				}
			}
		}

		if ($shifts)
		{
			// use first time available in case there is no selected time
			$sh = reset($shifts);
			$time = $sh[0]->value;
		}

		return false;
	}
	
	/**
	 * Checks whether the specified minutes are a valid interval
	 * for restaurant reservations.
	 *
	 * @param 	integer  $minute
	 *
	 * @return 	boolean  True if valid, false otherwise.
	 *
	 * @deprecated 1.9 	Use isHourBetweenShifts() instead.
	 */
	public static function isMinuteAnInterval($minute)
	{
		$min = self::getMinuteIntervals();

		for ($i = 0; $i < 60; $i += $min)
		{
			if ($i == $minute)
			{
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Checks whether the specified minutes are a valid interval
	 * for take-away orders.
	 *
	 * @param 	integer  $minute
	 *
	 * @return 	boolean  True if valid, false otherwise.
	 *
	 * @since 	1.2
	 *
	 * @deprecated 1.9 	Use isHourBetweenShifts() instead.
	 */
	public static function isTakeAwayMinuteAnInterval($minute)
	{
		$min = self::getTakeAwayMinuteInterval();

		for ($i = 0; $i < 60; $i += $min)
		{
			if ($i == $minute)
			{
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Checks whether the requested arguments are valid to
	 * register a take-away order.
	 *
	 * @param 	array 	 $args  An associative array containing the checkin
	 * 						    date, time and delivery.
	 *
	 * @return 	integer  The error code, otherwise 0 on success.
	 *
	 * @since 	1.2
	 */
	public static function isRequestTakeAwayOrderValid($args)
	{
		if (empty($args['date']))
		{
			// missing date
			return 1;
		}
		
		if (empty($args['hourmin']))
		{
			// missing time
			return 2;
		}
		else
		{
			$tmp = explode(':', $args['hourmin']);

			if (count($tmp) != 2)
			{
				// invalid time string (HH:mm)
				return 2;
			}
			
			$args['hour'] = intval($tmp[0]);
			$args['min']  = intval($tmp[1]);

			/**
			 * Do not check anymore whether the specified minutes
			 * are a valid interval. This because the same check
			 * is already performed by isHourBetweenShifts() method,
			 * which makes sure that the selected time is an existing
			 * time slot.
			 *
			 * @since 1.8
			 */
			
			if (!self::isHourBetweenShifts($args, 2))
			{
				// the selected time is not part of a shift
				return 3;
			}
		}

		// init special days manager
		$sdManager = new VRESpecialDaysManager('takeaway');
		// set checkin date
		$sdManager->setStartDate($args['date']);
		// set checkin time
		$sdManager->setCheckinTime($args['hourmin']);
		// get first available special day
		$sd = $sdManager->getFirst();

		if ($sd)
		{
			// set up delivery/pickup service configuration
			$delivery = $sd->delivery;
			$pickup   = $sd->pickup;
		}
		else
		{
			// get delivery service flag from configuration
			$service = VREFactory::getConfig()->getUint('deliveryservice');
			
			// set up delivery/pickup service configuration
			$delivery = $service == 1 || $service == 2;
			$pickup   = $service == 0 || $service == 2;
		}

		/**
		 * Make sure the selected service is supported.
		 *
		 * @since 1.8
		 */
		if ($args['delivery'])
		{
			// delivery service selected
			if (!$delivery)
			{
				// delivery service is not supported
				return 4;
			}
		}
		else
		{
			// pickup service selected
			if (!$pickup)
			{
				// pickup service is not supported
				return 4;
			}
		}
		
		return 0;
	}
	
	/**
	 * Returns the error message related to the specified code.
	 *
	 * @param 	integer  $code  The error code.
	 *
	 * @return 	string 	 The error message.
	 *
	 * @since 	1.2
	 *
	 * @see 	isRequestTakeAwayOrderValid()
	 */
	public static function getResponseFromTakeAwayOrderRequest($code)
	{
		$lookup = array( 
			'', 
			'VRTKORDERREQUESTMSG1', 
			'VRTKORDERREQUESTMSG2',
			'VRTKORDERREQUESTMSG3',
			'VRTKSERVICENOTALLOWEDERR',
		 );
		
		return $lookup[$code];
	}

	/**
	 * Returns a search array based on the query held
	 * by the cart instance. The returned array will 
	 * contain the selected check-in date and time as
	 * well as the type of service.
	 *
	 * @param 	mixed  $cart  The cart instance.
	 *
	 * @return 	array
	 *
	 * @since 	1.8.3
	 */
	public static function getCartSearchArray($cart = null)
	{
		if (!$cart)
		{
			// get cart instance
			$cart = TakeAwayCart::getInstance();
		}

		$config = VREFactory::getConfig();

		// create return array
		$args = array();
		$args['date']     = date($config->get('dateformat'), $cart->getCheckinTimestamp());
		$args['hourmin']  = $cart->getCheckinTime();
		$args['delivery'] = $cart->getService() == 1;

		if (!preg_match("/:/", $args['hourmin']))
		{
			// use a valid time for BC
			$args['hourmin'] = '0:0';
		}

		// extract hours and minutes
		list($args['hour'], $args['min']) = explode(':', $args['hourmin']);

		return $args;
	}
	
	/**
	 * Applies the taxes to the total cost.
	 *
	 * @param 	float  $total
	 * @param 	float  $taxes
	 *
	 * @return 	float
	 *
	 * @deprecated 1.9 	Use TakeAwayCart::getTaxes() instead.
	 */
	public static function getTotalCostWithTaxes($total, $taxes)
	{
		return floatval($total + $total * $taxes / 100);
	}
	
	/**
	 * Returns a list of working shifts.
	 *
	 * @param 	integer  $group  1 for restaurant, 2 for take-away.
	 * 
	 * @return 	array
	 *
	 * @deprecated 1.9	Use JHtml::_('vikrestaurants.shifts') instead.
	 */
	public static function getWorkingShifts($group = 0)
	{
		if (!self::isContinuosOpeningTime())
		{	
			$dbo = JFactory::getDbo();
			$q = "SELECT `id`, `name`, `showlabel`, `label`, `from`, `to`, FLOOR(`from`/60) AS `from_hour`, (`from`%60) AS `from_min`, FLOOR(`to`/60) AS `to_hour`, (`to`%60) AS `to_min` 
			FROM `#__vikrestaurants_shifts` " . ($group != 0 ? "WHERE `group`=" . $group : "") . " ORDER BY `from` ASC, `id` ASC;";
			
			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				return $dbo->loadAssocList();
			}
		}
		
		return array();
	}
	
	/**
	 * Returns the first availabel date and time.
	 *
	 * @param 	array 	$shifts
	 * @param 	array 	$continuos
	 *
	 * @return 	array
	 *
	 * @deprecated 1.9 	Use VikRestaurants::getClosestTime() instead.
	 */
	public static function getFirstAvailableDateTime($shifts = array(), $continuos = array())
	{	
		$hour = intval(date('H'))+1;
		$min = 0;
		
		$df = self::getDateFormat();
		
		$sel = array(
			"date" => date($df), "hourmin" => ($hour).':0', "people" => 2
		);
		
		if( count($shifts) == 0 ) {
			$shifts = array( array( "from_hour" => $continuos[0], "from_min" => 0, "to_hour" => $continuos[1], "to_min" => 0 ) );
		}
		
		$found = false;
		
		while( $hour < 24 && !$found ) {
			for( $i = 0; $i < count($shifts) && !$found; $i++ ) {
				if( $shifts[$i]['from_hour'] <= $hour && $hour <= $shifts[$i]['to_hour'] ) {
					$found = true;
					$min = $shifts[$i]['from_min'];
				}
			}
			
			if( !$found ) {
				$hour++;
			}
		}
		
		if( !$found ) {
			$hour = $shifts[0]['from_hour'];
			$min = $shifts[0]['from_min'];
			$sel['date'] = getdate();
			$sel['date'] = date( $df, mktime(0, 0, 0, $sel['date']['mon'], $sel['date']['mday']+1, $sel['date']['year']) );
		}
		
		$sel['hourmin'] = $hour.":".$min;
		
		return $sel;
		
	}
	
	/**
	 * Validates the user arguments before registering an account.
	 *
	 * @param 	array 	 &$args   The user arguments.
	 *
	 * @return 	boolean  True if valid, false otherwise.
	 */
	public static function checkUserArguments(&$args, $ignore = false)
	{
		if (empty($args['firstname']) && empty($args['lastname']))
		{
			// at least one of these 2 arguments cannot be empty
			return false;
		}

		/**
		 * In case the username is not provided, take the specified
		 * e-mail address. In this way, developers can override the 
		 * layout of the login to get rid of the "username" field.
		 *
		 * @since 1.8
		 */
		if (empty($args['username']))
		{
			$args['username'] = $args['email'];
		}

		if (empty($args['password']))
		{
			// password cannot be empty
			return false;
		}

		/**
		 * Validate password only if confirmation is provided.
		 *
		 * @since 1.8
		 */
		if (isset($args['confpassword']) && strcmp($args['password'], $args['confpassword']))
		{
			// password do not match
			return false;
		}

		if (empty($args['email']) || !self::validateUserEmail($args['email']))
		{
			// e-mail is empty or invalid
			return false;
		}

		/**
		 * Compare the e-mail with the confirmation only if provided.
		 *
		 * @since 1.8
		 */
		if (isset($args['confemail']) && strcasecmp($args['email'], $args['confemail']))
		{
			// e-mail do not match
			return false;
		}

		// valid arguments
		return true;
	}
	
	/**
	 * Validates an e-mail address.
	 *
	 * @param 	string 	 $email  The e-mail address to validate.
	 *
	 * @return 	boolean  True if valid, false otherwise.
	 */
	public static function validateUserEmail($email = '')
	{
		$isValid = true;
		$atIndex = strrpos($email, "@");

		if (is_bool($atIndex) && !$atIndex)
		{
			return false;
		}
		
		$domain 	= substr($email, $atIndex +1);
		$local  	= substr($email, 0, $atIndex);
		$localLen 	= strlen($local);
		$domainLen 	= strlen($domain);

		if ($localLen < 1 || $localLen > 64)
		{
			// local part length exceeded or too short
			return false;
		}

		if ($domainLen < 1 || $domainLen > 255)
		{
			// domain part length exceeded or too short
			return false;
		}
			
		if ($local[0] == '.' || $local[$localLen -1] == '.')
		{
			// local part starts or ends with '.'
			return false;
		}
				
		if (preg_match('/\\.\\./', $local))
		{
			// local part has two consecutive dots
			return false;
		}
					
		if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		{
			// character not valid in domain part
			return false;
		}
		
		if (preg_match('/\\.\\./', $domain))
		{
			// domain part has two consecutive dots
			return false;
		} 

		if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local)))
		{
			// character not valid in local part unless local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local)))
			{
				return false;
			}
		}

		if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A"))
		{
			// domain not found in DNS
			return false;
		}
		
		return true;
	}

	/**
	 * Registers a new Joomla User with the details
	 * specified in the given $args associative array.
	 *
	 * @param 	array 	 $args 	The user details.
	 * @param 	integer  $type 	The registration type (for employee [1] or for users [2]).
	 *
	 * @return 	mixed 	 The user ID on success, false on failure,
	 * 					 the string status during the activation.
	 */
	public static function createNewJoomlaUser($args)
	{
		$app = JFactory::getApplication();

		// load com_users site language
		JFactory::getLanguage()->load('com_users', JPATH_SITE, JFactory::getLanguage()->getTag(), true);
		
		// load UsersModelRegistration
		JModelLegacy::addIncludePath(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_users' . DIRECTORY_SEPARATOR . 'models');
		$model = JModelLegacy::getInstance('registration', 'UsersModel');

		// adapt data for model
		$args['name'] 		= trim($args['firstname'] . ' ' . $args['lastname']);
		$args['email1'] 	= $args['email'];
		$args['password1'] 	= $args['password'];
		$args['block'] 		= 0;

		// register user
		$return = $model->register($args);

		if ($return === false)
		{
			// impossible to save the user
			$app->enqueueMessage($model->getError(), 'error');
		}
		else if ($return === 'adminactivate')
		{
			// user saved, admin activation required
			$app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
		}
		else if ($return === 'useractivate')
		{
			// user saved, self activation required
			$app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
		}
		else
		{
			// user saved, can immediately login
			$app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));
		}

		return $return;
	}
	
	/**
	 * Returns the details of the operator assigned
	 * to the current logged-in user.
	 *
	 * @return 	mixed  The operator details on success, false otherwise.
	 *
	 * @since 	1.4
	 */
	public static function getOperator()
	{
		VRELoader::import('library.operator.user');
		
		try
		{
			// get operator instance
			$operator = VREOperatorUser::getInstance();
		}
		catch (Exception $e)
		{
			// user is not logged-in or the account
			// is not assigned to any operator
			return false;
		}

		/**
		 * Returns a VREOperatorUser instance.
		 *
		 * @since 1.8
		 */
		return $operator;
	}

	/**
	 * Removes the credit card details assigned to reservations
	 * with a check-in a day in the past.
	 *
	 * @return 	void
	 *
	 * @since 	1.7
	 */
	public static function removeExpiredCreditCards()
	{
		$session = JFactory::getSession();

		$now = VikRestaurants::now();

		// if the session token does not exist, get a time in the past
		$check = intval($session->get('cc-flush-check', $now - 3600, 'vr'));

		if ($check < $now)
		{
			$dbo = JFactory::getDbo();

			// update restaurant reservations
			$q = $dbo->getQuery(true)
				->update($dbo->qn('#__vikrestaurants_reservation'))
				->set($dbo->qn('cc_details') . ' = ' . $dbo->q(''))
				->where($dbo->qn('checkin_ts') . ' + 86400 < ' . $now);

			$dbo->setQuery($q);
			$dbo->execute();

			// update take-away orders
			$q->clear('update')->update($dbo->qn('#__vikrestaurants_takeaway_reservation'));

			$dbo->setQuery($q);
			$dbo->execute();

			// check only every 15 minutes
			$session->set('cc-flush-check', $now + 15 * 60, 'vr');
		}
	}
	
	/**
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryFindTable($args, $skip_session = false)
	{
		$in_ts = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		$avg = self::getAverageTimeStay($skip_session)*60;

		$table_published_where = '';
		if( !$skip_session ) {
			$table_published_where = 'AND `t`.`published`=1';
		}
	
		return "SELECT `t`.`id` AS `tid`, `t`.`name` AS `tname`, `t`.`min_capacity`, `t`.`max_capacity`, `t`.`multi_res`, `rm`.`id` AS `rid`, `rm`.`name` AS `rname` 
				FROM `#__vikrestaurants_table` AS `t`
				LEFT JOIN `#__vikrestaurants_room` AS `rm` ON `rm`.`id`=`t`.`id_room`
				WHERE `rm`.`published`=1 $table_published_where AND NOT EXISTS ( 
					SELECT `t`.`id` 
					FROM `#__vikrestaurants_reservation` AS `r` 
					WHERE `t`.`id` = `r`.`id_table` AND `r`.`status` <> 'REMOVED' AND `r`.`status` <> 'CANCELLED' AND ( 
						( `r`.`checkin_ts` < $in_ts AND $in_ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts+$avg AND $in_ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts AND $in_ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` > $in_ts AND $in_ts+$avg > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` = $in_ts AND $in_ts+$avg = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
					)
				) AND (
					SELECT COUNT(1) FROM `#__vikrestaurants_room_closure` AS `rc` 
					WHERE `rc`.`id_room`=`rm`.`id` AND `rc`.`start_ts`<=$in_ts AND $in_ts<`rc`.`end_ts` LIMIT 1
				)=0 AND 
				`t`.`min_capacity` <= {$args['people']} AND {$args['people']} <= `t`.`max_capacity` 
				GROUP BY `t`.`id`
				ORDER BY `t`.`multi_res` ASC, `t`.`max_capacity` ASC, `rm`.`ordering` ASC";
	}
	
	/**
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryFindTableMultiRes($args, $skip_session = false)
	{
		$in_ts = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		$avg = self::getAverageTimeStay($skip_session)*60;
		
		$l_m = $args['min']*60; // less minutes

		$table_published_where = '';
		if( !$skip_session ) {
			$table_published_where = 'AND `t`.`published`=1';
		}
	
		return "SELECT SUM(`r`.`people`) AS `curr_capacity`, `t`.`id` AS `tid`, `t`.`name` AS `tname`, 
		`t`.`min_capacity`, `t`.`max_capacity`, `t`.`multi_res`, `rm`.`id` AS `rid`, `rm`.`name` AS `rname` 
				FROM `#__vikrestaurants_reservation` AS `r`, `#__vikrestaurants_room` AS `rm`, `#__vikrestaurants_table` AS `t` 
				WHERE `t`.`id`=`r`.`id_table` $table_published_where AND `t`.`multi_res`=1 AND `t`.`id_room`=`rm`.`id` AND `rm`.`published`=1 AND (
					SELECT COUNT(1) FROM `#__vikrestaurants_room_closure` AS `rc` 
					WHERE `rc`.`id_room`=`rm`.`id` AND `rc`.`start_ts`<=$in_ts AND $in_ts<`rc`.`end_ts` LIMIT 1
				)=0 AND 
				`r`.`status` <> 'REMOVED' AND `r`.`status` <> 'CANCELLED' AND ( 
					( `r`.`checkin_ts` < $in_ts AND $in_ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
					( `r`.`checkin_ts` < $in_ts+$avg-$l_m AND $in_ts+$avg-$l_m < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
					( `r`.`checkin_ts` < $in_ts AND $in_ts+$avg-$l_m < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
					( `r`.`checkin_ts` > $in_ts AND $in_ts+$avg-$l_m > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
					( `r`.`checkin_ts` = $in_ts AND $in_ts+$avg-$l_m = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
				) 
				GROUP BY `t`.`id` 
				HAVING {$args['people']} >= `t`.`min_capacity` AND SUM(`r`.`people`)+{$args['people']} <= `t`.`max_capacity` 
				ORDER BY `rm`.`ordering` ASC";
	}

	/**
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryFindAvailableSharedTables($args, $skip_session = false)
	{
		$in_ts = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		$avg = self::getAverageTimeStay($skip_session)*60;
		
		$l_m = $args['min']*60; // less minutes

		$table_published_where = '';
		if( !$skip_session ) {
			$table_published_where = 'AND `t`.`published`=1';
		}

		return "SELECT `t`.`id` AS `tid`, `t`.`name` AS `tname`, `t`.`min_capacity`, `t`.`max_capacity`, `t`.`multi_res`, `rm`.`id` AS `rid`, `rm`.`name` AS `rname`, IFNULL(
				(
					SELECT SUM(`r`.`people`)
	    			FROM `#__vikrestaurants_reservation` AS `r`
    				WHERE `t`.`id`=`r`.`id_table` AND `r`.`status`<>'REMOVED' AND `r`.`status`<>'CANCELLED' AND ( 
						( `r`.`checkin_ts` < $in_ts AND $in_ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts+$avg-$l_m AND $in_ts+$avg-$l_m < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts AND $in_ts+$avg-$l_m < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` > $in_ts AND $in_ts+$avg-$l_m > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` = $in_ts AND $in_ts+$avg-$l_m = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
    				)
				), 0
			) AS `curr_capacity`
			FROM `#__vikrestaurants_table` AS `t`, `#__vikrestaurants_room` AS `rm` 
			WHERE `t`.`id_room`=`rm`.`id` $table_published_where AND `t`.`multi_res`=1 AND `rm`.`published`=1 AND ( 
				SELECT COUNT(1) 
    			FROM `#__vikrestaurants_room_closure` AS `rc` 
    			WHERE `rc`.`id_room`=`rm`.`id` AND `rc`.`start_ts`<=$in_ts AND $in_ts<`rc`.`end_ts` LIMIT 1
			)=0 
			GROUP BY `t`.`id` 
			HAVING {$args['people']} >= `t`.`min_capacity` AND `curr_capacity`+{$args['people']} <= `t`.`max_capacity` 
			ORDER BY `rm`.`ordering` ASC";
	}

	/**
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryFindTableMultiResWithID($args, $skip_session = false)
	{
		$in_ts = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		$avg = self::getAverageTimeStay($skip_session)*60;
		
		$l_m = $args['min']*60; // less minutes

		$table_published_where = '';
		if( !$skip_session ) {
			$table_published_where = 'AND `t`.`published`=1';
		}
	
		return "SELECT SUM(`r`.`people`) AS `curr_capacity`, `t`.`id` AS `tid`, `t`.`name` AS `tname`, `t`.`min_capacity`, `t`.`max_capacity`, `t`.`multi_res`, `rm`.`id` AS `rid`, `rm`.`name` AS `rname` 
				FROM `#__vikrestaurants_reservation` AS `r`, `#__vikrestaurants_room` AS `rm`, `#__vikrestaurants_table` AS `t` 
				WHERE `t`.`id`=`r`.`id_table` $table_published_where AND `t`.`id`={$args['table']} AND `t`.`multi_res`=1 AND `t`.`id_room`=`rm`.`id` AND `rm`.`published`=1 AND  (
					SELECT COUNT(1) FROM `#__vikrestaurants_room_closure` AS `rc` 
					WHERE `rc`.`id_room`=`rm`.`id` AND `rc`.`start_ts`<=$in_ts AND $in_ts<`rc`.`end_ts` LIMIT 1
				)=0 AND 
				`r`.`status` <> 'REMOVED' AND `r`.`status` <> 'CANCELLED' AND ( 
					( `r`.`checkin_ts` < $in_ts AND $in_ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts+$avg-$l_m AND $in_ts+$avg-$l_m < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts AND $in_ts+$avg-$l_m < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` > $in_ts AND $in_ts+$avg-$l_m > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` = $in_ts AND $in_ts+$avg-$l_m = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
				) 
				GROUP BY `t`.`id` 
				HAVING {$args['people']} >= `t`.`min_capacity` AND SUM(`r`.`people`)+{$args['people']} <= `t`.`max_capacity` 
				ORDER BY `rm`.`ordering` ASC";
	}

	/**
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryFindTableMultiResWithIDExcludingRes($args, $res_id, $skip_session = false)
	{
		$in_ts = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		$avg = self::getAverageTimeStay($skip_session)*60;
		
		$l_m = $args['min']*60; // less minutes

		$table_published_where = '';
		if( !$skip_session ) {
			$table_published_where = 'AND `t`.`published`=1';
		}
	
		return "SELECT SUM(`r`.`people`) AS `curr_capacity`, `t`.`id` AS `tid`, `t`.`name` AS `tname`, `t`.`min_capacity`, `t`.`max_capacity`, `t`.`multi_res`, `rm`.`id` AS `rid`, `rm`.`name` AS `rname` 
				FROM `#__vikrestaurants_reservation` AS `r`, `#__vikrestaurants_room` AS `rm`, `#__vikrestaurants_table` AS `t` 
				WHERE `t`.`id`=`r`.`id_table` $table_published_where AND `r`.`id`<>$res_id AND `t`.`id`={$args['table']} AND `t`.`multi_res`=1 AND `t`.`id_room`=`rm`.`id` AND `rm`.`published`=1 AND  (
					SELECT COUNT(1) FROM `#__vikrestaurants_room_closure` AS `rc` 
					WHERE `rc`.`id_room`=`rm`.`id` AND `rc`.`start_ts`<=$in_ts AND $in_ts<`rc`.`end_ts` LIMIT 1
				)=0 AND 
				`r`.`status` <> 'REMOVED' AND `r`.`status` <> 'CANCELLED' AND ( 
					( `r`.`checkin_ts` < $in_ts AND $in_ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts+$avg-$l_m AND $in_ts+$avg-$l_m < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts AND $in_ts+$avg-$l_m < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` > $in_ts AND $in_ts+$avg-$l_m > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` = $in_ts AND $in_ts+$avg-$l_m = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
				)
				GROUP BY `t`.`id` 
				HAVING {$args['people']} >= `t`.`min_capacity` AND SUM(`r`.`people`)+{$args['people']} <= `t`.`max_capacity` 
				ORDER BY `rm`.`ordering` ASC";
	}
	
	/**
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryAllReservationsOnDate($args)
	{
		$_d = self::getOpeningTimeDelimiters($args);
		
		return "SELECT `t`.`id` AS `idt`, `rm`.`id` AS `rid`, `rm`.`name`, `r`.`checkin_ts`, `r`.`stay_time`  
				FROM `#__vikrestaurants_table` AS `t`, `#__vikrestaurants_room` AS `rm`, `#__vikrestaurants_reservation` AS `r` 
				WHERE `rm`.`id`=`t`.`id_room` AND `r`.`id_table`=`t`.`id` AND {$_d[0]} <= `r`.`checkin_ts` AND `r`.`checkin_ts` <= {$_d[1]} 
				AND `t`.`multi_res`=0 AND `t`.`min_capacity` <= {$args['people']} AND {$args['people']} <= `t`.`max_capacity` 
				AND `r`.`status` <> 'REMOVED' AND `r`.`status` <> 'CANCELLED' 
				ORDER BY `idt` ASC, `r`.`checkin_ts` ASC";
	} 
	
	/**
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryCountOccurrencyTableMultiRes($args, $skip_session = false)
	{
		$in_ts = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		$avg = self::getAverageTimeStay($skip_session)*60;
		
		$l_m = $args['min']*60; // less minutes
	
		return "SELECT SUM(`r`.`people`) AS `curr_capacity`, `t`.`id`, `t`.`multi_res` 
				FROM `#__vikrestaurants_reservation` AS `r`, `#__vikrestaurants_table` AS `t` 
				WHERE `t`.`id` = `r`.`id_table` AND `t`.`multi_res` = 1 AND ( 
					( `r`.`checkin_ts` < $in_ts AND $in_ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts+$avg-$l_m AND $in_ts+$avg-$l_m < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts AND $in_ts+$avg-$l_m < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` > $in_ts AND $in_ts+$avg-$l_m > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` = $in_ts AND $in_ts+$avg-$l_m = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
				) 
				GROUP BY `t`.`id`";
	}
	
	/**
	 * Returns the details of the specified table, only if AVAILABLE
	 * for the specified search arguments.
	 *
	 * @param 	array    $args   A list of arguments (date, hourmin, people, table).
	 * @param 	boolean  $admin  True to exclude publishing filters. If not specified,
	 * 							 this value will be based on the current client.
	 *
	 * @return 	mixed 	 The table associative array if available, null otherwise.
	 *
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryTableJustReserved($args, $admin = null)
	{
		$dbo = JFactory::getDbo();

		if (is_null($admin))
		{
			// fetch client in case $admin was not specified
			$admin = JFactory::getApplication()->isClient('administrator');
		}

		// extract hour and min in case the "hourmin" attribute was specified
		if (!empty($args['hourmin']))
		{
			$tmp = explode(':', $args['hourmin']);

			$args['hour'] = (int) array_shift($tmp);
			$args['min']  = (int) array_shift($tmp);
		}

		$avg    = self::getAverageTimeStay();
		$in_ts  = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		$out_ts = self::createTimestamp($args['date'], $args['hour'], $args['min'] + $avg);

		$q = $dbo->getQuery(true);

		// SELECT table data
		$q->select($dbo->qn('t.id', `tid`));
		$q->select($dbo->qn('t.name', `tname`));
		$q->select($dbo->qn('t.min_capacity'));
		$q->select($dbo->qn('t.max_capacity'));
		$q->select($dbo->qn('t.multi_res'));

		// SELECT room data
		$q->select($dbo->qn('rm.id', 'rid'));
		$q->select($dbo->qn('rm.name', 'rname'));

		// load tables and rooms from DB
		$q->from($dbo->qn('#__vikrestaurants_table', 't'));
		$q->from($dbo->qn('#__vikrestaurants_room', 'rm'));

		// join the tables with the rooms
		$q->where($dbo->qn('t.id_room') . ' = ' . $dbo->qn('rm.id'));
		$q->where($dbo->qn('t.id') . ' = ' . (int) $args['table']);

		// exclude multi reservation table
		$q->where($dbo->qn('t.multi_res') . ' = 0');

		/**
		 * Use publishing restrictions only for front-end users.
		 *
		 * @since 1.7.4
		 */
		if (!$admin)
		{
			$q->where($dbo->qn('rm.published') . ' = 1');
			$q->where($dbo->qn('t.published') . ' = 1');
			$q->where((int) $args['people'] . ' BETWEEN ' . $dbo->qn('t.min_capacity') . ' AND ' . $dbo->qn('t.max_capacity'));

			/**
			 * Search for room closure.
			 *
			 * @since 1.8
			 */
			$closureQuery = $dbo->getQuery(true)
				->select('COUNT(1)')
				->from($dbo->qn('#__vikrestaurants_room_closure', 'rc'))
				->where($dbo->qn('rc.id_room') . ' = ' . $dbo->qn('rm.id'))
				->where($in_ts . ' BETWEEN ' . $dbo->qn('rc.start_ts') . ' AND ' . $dbo->qn('rc.end_ts'));

			$q->where('(' . $closureQuery . ') = 0');
		}

		// list of allowed statuses that represents a valid reservation
		$statuses = array(
			'PENDING',
			'CONFIRMED',
		);

		$availQuery = $dbo->getQuery(true)
			->select($dbo->qn('t.id')) 
			->from($dbo->qn('#__vikrestaurants_reservation', 'r')) 
			->where($dbo->qn('t.id') . ' = ' . $dbo->qn('r.id_table'))
			->where($dbo->qn('r.status') . ' IN (' . implode(',', array_map(array($dbo, 'q'), $statuses)) . ')')
			->andWhere(array(
				$dbo->qn('r.checkin_ts') . " < {$in_ts}  AND {$in_ts}  < " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . " * 60, {$avg})\n",
				$dbo->qn('r.checkin_ts') . " < {$out_ts} AND {$out_ts} < " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . " * 60, {$avg})\n",
				$dbo->qn('r.checkin_ts') . " < {$in_ts}  AND {$out_ts} < " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . " * 60, {$avg})\n",
				$dbo->qn('r.checkin_ts') . " > {$in_ts}  AND {$out_ts} > " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . " * 60, {$avg})\n",
				$dbo->qn('r.checkin_ts') . " = {$in_ts}  AND {$out_ts} = " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . " * 60, {$avg})\n",
			), 'OR');

		// exclude if reserved
		$q->where('NOT EXISTS(' . $availQuery . ')');
		
		// return "SELECT `t`.`id` AS `tid`, `t`.`name` AS `tname`, `t`.`min_capacity`, `t`.`max_capacity`, `t`.`multi_res`, `rm`.`id` AS `rid`, `rm`.`name` AS `rname`
		// 		FROM `#__vikrestaurants_table` AS `t`, `#__vikrestaurants_room` AS `rm` 
		// 		WHERE $table_published_where NOT EXISTS ( 
		// 			SELECT `t`.`id` 
		// 			FROM `#__vikrestaurants_reservation` AS `r` 
		// 			WHERE `t`.`id` = `r`.`id_table` AND `t`.`multi_res` = 0 AND `r`.`status` <> 'REMOVED' AND `r`.`status` <> 'CANCELLED' AND ( 
		// 				( `r`.`checkin_ts` < $in_ts AND $in_ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
		// 				( `r`.`checkin_ts` < $in_ts+$avg AND $in_ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
		// 				( `r`.`checkin_ts` < $in_ts AND $in_ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
		// 				( `r`.`checkin_ts` > $in_ts AND $in_ts+$avg > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
		// 				( `r`.`checkin_ts` = $in_ts AND $in_ts+$avg = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
		// 			) 
		// 		) AND `t`.`id_room` = `rm`.`id` AND `rm`.`published`=1 AND  (
		// 			SELECT COUNT(1) FROM `#__vikrestaurants_room_closure` AS `rc` 
		// 			WHERE `rc`.`id_room`=`rm`.`id` AND `rc`.`start_ts` <= $in_ts AND $in_ts < `rc`.`end_ts` LIMIT 1
		// 		)=0 AND 
		// 		`t`.`id` = {$args['table']}
		// 		ORDER BY `t`.`multi_res` ASC, `t`.`max_capacity` ASC, `rid` ASC";

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadAssoc();
		}

		return null;
	}

	/**
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryTableJustReservedExcludingResId($args, $res_id, $skip_session = false)
	{
		$in_ts = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		$avg = self::getAverageTimeStay($skip_session)*60;

		$table_published_where = '';
		if( !$skip_session ) {
			$table_published_where = '`t`.`published`=1 AND';
		}
		
		return "SELECT `t`.`id` AS `tid`, `t`.`name` AS `tname`, `t`.`min_capacity`, `t`.`max_capacity`, `t`.`multi_res`, `rm`.`id` AS `rid`, `rm`.`name` AS `rname` 
				FROM `#__vikrestaurants_table` AS `t`, `#__vikrestaurants_room` AS `rm` 
				WHERE $table_published_where NOT EXISTS ( 
					SELECT `t`.`id` 
					FROM `#__vikrestaurants_reservation` AS `r` 
					WHERE `r`.`id` <> $res_id AND `t`.`id` = `r`.`id_table` AND `t`.`multi_res` = 0 AND `r`.`status` <> 'REMOVED' AND `r`.`status` <> 'CANCELLED' AND ( 
						( `r`.`checkin_ts` < $in_ts AND $in_ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts+$avg AND $in_ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` < $in_ts AND $in_ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` > $in_ts AND $in_ts+$avg > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
						( `r`.`checkin_ts` = $in_ts AND $in_ts+$avg = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
					) 
				) AND `t`.`id_room` = `rm`.`id` AND `rm`.`published`=1 AND  (
					SELECT COUNT(1) FROM `#__vikrestaurants_room_closure` AS `rc` 
					WHERE `rc`.`id_room`=`rm`.`id` AND `rc`.`start_ts`<=$in_ts AND $in_ts<`rc`.`end_ts` LIMIT 1
				)=0 AND 
				`t`.`min_capacity` <= {$args['people']} AND {$args['people']} <= `t`.`max_capacity` AND `t`.`id`={$args['id_table']} 
				ORDER BY `t`.`multi_res` ASC, `t`.`max_capacity` ASC, `rid` ASC";
	}

	/**
	 * @deprecated 1.9 	Use VikRestaurants::removeRestaurantReservationsOutOfTime() instead.
	 */
	public static function getQueryRemoveAllReservationsOutOfTime($args)
	{
		return "SELECT `id` FROM `#__vikrestaurants_reservation` WHERE `status` = 'PENDING' AND `locked_until` < " . time();
	}

	/**
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryAllReservationsRelativeTo($args, $skip_session = false) {
		$in_ts = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		$avg = self::getAverageTimeStay($skip_session)*60;
		
		return "SELECT `r`.`id`, `r`.`checkin_ts`, `r`.`people`, `r`.`purchaser_nominative`, `r`.`purchaser_mail`,
			`p`.`name` AS `pname`, `p`.`charge` AS `pcharge`, `r`.`custom_f`, `r`.`notes`
			FROM `#__vikrestaurants_reservation` AS `r` 
			LEFT JOIN `#__vikrestaurants_gpayments` AS `p` ON `r`.`id_payment`=`p`.`id` 
			LEFT JOIN `#__vikrestaurants_table` AS `t` ON `r`.`id_table`=`t`.`id` 
			WHERE `r`.`id_table`={$args['table']} AND `t`.`min_capacity`<={$args['people']} AND {$args['people']}<=`t`.`max_capacity` AND ( 
				( `r`.`checkin_ts` < $in_ts AND $in_ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
				( `r`.`checkin_ts` < $in_ts+$avg AND $in_ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
				( `r`.`checkin_ts` < $in_ts AND $in_ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
				( `r`.`checkin_ts` > $in_ts AND $in_ts+$avg > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
				( `r`.`checkin_ts` = $in_ts AND $in_ts+$avg = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
			)";
	}
	
	/**
	 * @deprecated 1.9 	Use VREAvailabilitySearch instead.
	 */
	public static function getQueryAllReservationsRelativeToWithoutPayments($args, $skip_session = false) {
		$in_ts = self::createTimestamp($args['date'], $args['hour'], $args['min']);
		$avg = self::getAverageTimeStay($skip_session)*60;
		
		return "SELECT `r`.`id`, `r`.`checkin_ts`, `r`.`people`, `r`.`custom_f`, `r`.`notes` 
			FROM `#__vikrestaurants_reservation` AS `r`, `#__vikrestaurants_table` AS `t`
			WHERE `r`.`id_table`=`t`.`id` AND `r`.`id_table`={$args['table']} AND `t`.`min_capacity`<={$args['people']} AND {$args['people']}<=`t`.`max_capacity` AND ( 
				( `r`.`checkin_ts` < $in_ts AND $in_ts < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
				( `r`.`checkin_ts` < $in_ts+$avg AND $in_ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
				( `r`.`checkin_ts` < $in_ts AND $in_ts+$avg < `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
				( `r`.`checkin_ts` > $in_ts AND $in_ts+$avg > `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) OR 
				( `r`.`checkin_ts` = $in_ts AND $in_ts+$avg = `r`.`checkin_ts`+IF(`r`.`stay_time`>0, `r`.`stay_time`*60, $avg) ) 
			)";
	}
	
	/**
	 * @deprecated 1.9 	Without replacement.
	 */
	public static function getOpeningTimeDelimiters($args)
	{
		$_app = $args['hour']*60+$args['min'];
		
		$start = self::getFromOpeningHour();
		$end = self::getToOpeningHour();
		$start_min = $end_min = 0;
		if( !self::isContinuosOpeningTime() ) {
				
			$dbo = JFactory::getDbo();
			$q = 'SELECT * FROM `#__vikrestaurants_shifts` WHERE `from`<='.$_app.' AND `to`>='.$_app.' LIMIT 1;';
			$dbo->setQuery($q);
			$dbo->execute();
			if( $dbo->getNumRows() > 0 ) {
				$row = $dbo->loadAssoc();
				$start = intval($row["from"]/60);
				$end = intval($row["to"]/60);
				
				$start_min = $row["from"]%60;
				$end_min = $row["to"]%60;
			}
		}
		
		if( $end < $start ) {
			if( $args['hour'] >= 0 && $args['hour'] <= $end ) {
				$start = 0;
			} else {
				$end = 23;
			}
		}
		
		$_sd = max( array( self::createTimestamp($args['date'],0,0), self::createTimestamp($args['date'],$start,$start_min) ) );
		$_fd = min( array( self::createTimestamp($args['date'],23,59), self::createTimestamp($args['date'],$end,$end_min) ) );
		
		return array( $_sd, $_fd );
	}
	
	/**
	 * @deprecated 1.9 	Without replacement.
	 */
	public static function getAvailableHoursFromInterval($_s, $_f)
	{
		$_avg = self::getAverageTimeStay();
		$_itv = self::getMinuteIntervals();
		$_available = array();
		for( $t = $_s; $t <= $_f-$_avg*60; $t+=$_itv*60 ) {
			$_available[count($_available)] = $t;
		}
		
		return $_available;
	}

	/**
	 * Validates the specified coupon against the
	 * specified arguments.
	 *
	 * @param 	mixed 	$coupon  Either the coupon code or the object itself.
	 * @param 	array 	$args    An array of validation arguments.
	 *
	 * @return 	mixed 	The coupon object in case of success, null otherwise.
	 *
	 * @since 	1.8
	 */
	public static function getValidCoupon($coupon, $args)
	{
		$dbo = JFactory::getDbo();

		if (is_scalar($coupon))
		{
			// recover coupon code
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_coupons'))
				->where($dbo->qn('code') . ' = ' . $dbo->q($coupon));

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if (!$dbo->getNumRows())
			{
				// coupon not found
				return null;
			}

			$coupon = $dbo->loadObject();
		}
		else
		{
			// passed coupon details, cast to object
			$coupon = (object) $coupon;
		}

		// validate group
		if (isset($args['group']) && $args['group'] != $coupon->group)
		{
			// coupon group doesn't match
			return null;
		}

		// validate publishing date
		if (isset($args['date']) && $coupon->datevalid)
		{
			// get publishing dates
			list($start, $end) = explode('-', $coupon->datevalid);

			if (!preg_match("/^[\d]+$/", $args['date']))
			{
				// convert to timestamp
				$args['date'] = self::createTimestamp($args['date'], 0, 0);
			}

			if ($start > $args['date'] || $args['date'] > $end)
			{
				// date out of publishing range
				return null;
			}
		}

		// validate minimum value
		if (isset($args['total']) && (float) $args['total'] < $coupon->minvalue)
		{
			// minimum value not reached
			return null;
		}

		/**
		 * Make sure the number of usages didn't exceed the
		 * maximum threshold.
		 *
		 * @since 1.8
		 */
		if ($coupon->maxusages > 0 && $coupon->usages >= $coupon->maxusages)
		{
			// cannot redeem the coupon anymore
			return null;
		}

		/**
		 * Check if we should check whether the current user
		 * should be able to redeem the coupon one more time.
		 *
		 * @since 1.8
		 */
		if ($coupon->maxperuser > 0)
		{
			// get current user
			$user = JFactory::getUser();

			if ($user->guest)
			{
				// the coupon can be redeemed only by logged-in users
				return null;
			}

			// fetch reservations table
			$table = $coupon->group == 0 ? '#__vikrestaurants_reservation' : '#__vikrestaurants_takeaway_reservation';

			$q = $dbo->getQuery(true)
				->select('COUNT(1)')
				->from($dbo->qn($table, 'r'))
				->leftjoin($dbo->qn('#__vikrestaurants_users', 'u') . ' ON ' . $dbo->qn('r.id_user') . ' = ' . $dbo->qn('u.id'))
				->where($dbo->qn('r.coupon_str') . ' LIKE ' . $dbo->q($coupon->code . ';;%'))
				->andWhere(array(
					$dbo->qn('r.created_by') . ' = ' . $user->id,
					$dbo->qn('u.jid') . ' = ' . $user->id,
				), 'OR');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// compare the number of usages against the maximum limit
				if ((int) $dbo->loadResult() >= $coupon->maxperuser)
				{
					// the user already redeemed the coupon all the possible times
					return null;
				}
			}
		}

		/**
		 * This event can be used to apply additional conditions to the 
		 * coupon validation. When this event is triggered, the
		 * system already validated the standard conditions and the
		 * coupon has been approved for the usage.
		 *
		 * @param 	object 	 $coupon  The coupon code to check.
		 * @param 	array 	 $args    A configuration array.
		 *
		 * @return 	boolean  Return false to deny the coupon activation.
		 *
		 * @since 	1.8
		 */
		$res = VREFactory::getEventDispatcher()->trigger('onBeforeActivateCoupon', array($coupon, $args));

		// check if at least a plugin returned FALSE to prevent the coupon activation
		if (in_array(false, $res, true))
		{
			// a coupon decided to deny the activation
			return null;
		}

		// return coupon object
		return $coupon;
	}
	
	/**
	 * Checks whether the specified coupon can be used for
	 * a restaurant reservation and for the specified 
	 * number of participants.
	 *
	 * @param 	mixed 	 $coupon  Either the coupon code or the object itself.
	 * @param 	integer  $args    The number of selected people.
	 *
	 * @return 	mixed 	 The coupon object in case of success, null otherwise.
	 */
	public static function validateCoupon($coupon, $people)
	{
		// create validation arguments
		$args = array(
			// for restaurant only
			'group' => 0,
			// make sure the number of guests is allowed
			'total' => $people,
			// use the current date and time
			'date'  => static::now(),
		);

		// validate coupon code
		return self::getValidCoupon($coupon, $args);
	}
	
	/**
	 * Checks whether the specified coupon can be used for
	 * a take-away order and for the specified cart details.
	 *
	 * @param 	mixed 	$coupon  Either the coupon code or the object itself.
	 * @param 	object  $cart    The cart instance.
	 *
	 * @return 	mixed 	The coupon object in case of success, null otherwise.
	 *
	 * @since 	1.2
	 */
	public static function validateTakeawayCoupon($coupon, $cart)
	{
		// create validation arguments
		$args = array(
			// for take-away only
			'group' => 1,
			// make sure the number of guests is allowed
			'total' => $cart->getTotalCost(),
			// use the selected check-in date and time
			'date'  => $cart->getCheckinTimestamp(),
		);

		// validate coupon code
		return self::getValidCoupon($coupon, $args);
	}
	
	/**
	 * Checks whether a custom field is valid.
	 *
	 * @param 	array 	 $cf
	 * @param 	mixed 	 $val
	 * @param 	boolean  $is_delivery
	 *
	 * @return 	boolean
	 *
	 * @deprecated 1.9 	Use VRCustomFields::validateField() instead.
	 */
	public static function isCustomFieldValid($cf, $val, $is_delivery = false)
	{
		// take previous service set
		$prev = VRCustomFields::$deliveryService;
		// update delivery service
		VRCustomFields::$deliveryService = (bool) $is_delivery;
		// validate field
		$valid = VRCustomFields::validateField($cf, $val);
		// restore previous service
		VRCustomFields::$deliveryService = $prev;

		return $valid;
	}
	
	/**
	 * Helper method used to generate a serial code.
	 * In a remote case, this method may generate 2 identical codes.
	 * The probability to have 2 identical strings is:
	 * 1 / count($map)^$len
	 *
	 * @param 	integer  $length  The length of the serial code.
	 * @param 	string 	 $scope   The purpose of the serial code.
	 * @param 	array 	 $map 	  A map containing all the allowed tokens.
	 *
	 * @return 	string 	 The resulting serial code.
	 */
	public static function generateSerialCode($length = 12, $scope = null, $map = null)
	{
		$code = '';

		/**
		 * This event can be used to change the way the system generates
		 * a serial code. It is possible to edit the code or simply to
		 * alter the map of allowed tokens. In case the serial code
		 * didn't reach the specified length, the remaining characters
		 * will be generated according to the default algorithm.
		 *
		 * @param 	string 	 	 $code    The serial code.
		 * @param 	array|null 	 &$map    A map of allowed tokens.
		 * @param 	integer  	 $length  The length of the serial code.
		 * @param 	string|null  $scope   The purpose of the code.
		 *
		 * @return 	void
		 *
		 * @since 	1.8
		 */
		VREFactory::getEventDispatcher()->trigger('onGenerateSerialCode', array(&$code, &$map, $length, $scope));

		if (!is_scalar($code))
		{
			// reset code in case of invalid string
			$code = '';
		}

		// check if we already have a complete serial code
		if (strlen($code) >= $length)
		{
			// just return the specified number of characters
			return substr($code, 0, $length);
		}

		if (!$map)
		{
			// use default tokens if not specified/modified
			$map = array(
				'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
				'0123456789'
			);
		}
		else
		{
			// always treat as array
			$map = (array) $map;
		}
		
		// iterate until the specified length is reached
		for ($i = strlen($code); $i < $length; $i++)
		{
			// toss tokens block
			$_row = rand(0, count($map) - 1);
			// toss block character
			$_col = rand(0, strlen($map[$_row]) - 1);

			// append character to serial code
			$code .= (string) $map[$_row][$_col];
		}

		return $code;
	}
	
	/**
	 * @deprecated 1.9  Use array_merge() instead.
	 */
	public static function mergeArrays($a, $b)
	{
		return array_merge($a, $b);
	}

	/**
	 * @deprecated 1.9 	Use VRESpecialDaysManager instead.
	 */
	public static function getSpecialDays($args = '', $group = 1, $skip_session = false)
	{	
		$_h = $args['hour'];
		if( $_h == -1 ) {
			$_h = 0;
		}
		
		$_d = $args['date'];
		if( $_d == -1 ) {
			$_d = date( self::getDateFormat($skip_session), static::now() );
		}
		
		$_ts = self::createTimestamp($_d, $_h, $args['min'], $skip_session);
		
		$args['timestamp'] = $_ts;
		
		$q = "SELECT * FROM `#__vikrestaurants_specialdays` WHERE `group`=".$group." AND `start_ts` <= ".$_ts." AND ".$_ts." <= `end_ts` ORDER BY `priority` DESC;";
		
		return self::_get_special_days_($args, $q, $skip_session);
	}
	
	/**
	 * @deprecated 1.9 	Use VRESpecialDaysManager instead.
	 */
	public static function getSpecialDaysForDeposit($args = '', $group = 1, $skip_session = false)
	{
		$_h = $args['hour'];
		if( $_h == -1 ) {
			$_h = 0;
		}
		
		$_d = $args['date'];
		if( $_d == -1 ) {
			$_d = date( self::getDateFormat($skip_session), static::now() );
		}
		
		$_ts = self::createTimestamp($_d, $_h, $args['min'], $skip_session);
		
		$args['timestamp'] = $_ts;
		
		$q = "SELECT * FROM `#__vikrestaurants_specialdays` WHERE `group`=".$group." AND ((`start_ts` <= ".$_ts." AND ".$_ts." <= `end_ts`) OR `start_ts`=-1) ORDER BY `priority` DESC;";
		
		return self::_get_special_days_($args, $q, $skip_session);
		
	}
	
	/**
	 * @deprecated 1.9 	Use VRESpecialDaysManager instead.
	 */
	private static function _get_special_days_($args = '', $query = '', $skip_session = false)
	{
		if( empty($args) || empty($query) ) return;
		
		$dbo = JFactory::getDbo();
		
		$special_days = array();
		
		$q = $query;
		
		$current_days_index = intval( date( 'w', $args['timestamp'] ) );
		
		// working time shifts of 1.4 version
		$_hour_full = $args['hour']*60+$args['min'];
		
		$at_least_one_day = false;
		
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$sp_days = $dbo->loadAssocList();
			
			for( $i = 0, $n = count( $sp_days ); $i < $n; $i++ ) {
				$ok = false;
				if( strlen( $sp_days[$i]['days_filter'] ) == 0 || $args['date'] == -1 ) {
					$ok = true;
				}
				
				$_days = explode( ', ', $sp_days[$i]['days_filter'] );
				
				$_days_arr = array();
				for( $k = 0, $o = count($_days); $k < $o && !$ok; $k++ ) {
					if( $_days[$k] == $current_days_index ) {
						$ok = true;
					}
				}
				
				if( $ok ) {
					$at_least_one_day = true;
					
					$ok = false;
					if( strlen( $sp_days[$i]['working_shifts'] ) == 0 || $args['hour'] == -1 ) {
						$ok = true;
					}
					$shifts = explode( ', ', $sp_days[$i]['working_shifts'] );
				
					for( $j = 0, $m = count($shifts); $j < $m && !$ok; $j++ ) {
						$hm = explode( '-', $shifts[$j] );
						//if( $hm[0] <= $args['hour'] && $args['hour'] <= $hm[1] ) {
						if( $hm[0] <= $_hour_full && $_hour_full <= $hm[1] ) {
							$ok = true;
						} 
					}
		
					if( $ok ) {
						$special_days[count($special_days)] = $sp_days[$i];
						return $special_days; // limit to 1
					}
					
				}
				
			}
			
			if( $at_least_one_day ) {
				//return $special_days;
			}
		}
		
		return -1;
	}

	/**
	 * Returns the special days available for the specified date and time.
	 *
	 * @param 	array 	 $args   A list of arguments to fetch (date only).
	 * 							 In case the date is equals to -1, the current
	 * 							 day will be used.
	 * @param 	integer  $group
	 *
	 * @return 	mixed
	 *
	 * @deprecated 1.9 	Use VRESpecialDaysManager instead.
	 */
	public static function getSpecialDaysOnDate($args, $group = 1)
	{	
		// use current date in case of invalid date
		$_d = $args['date'] != -1 ? $args['date'] : date(self::getDateFormat(), static::now());
		
		// back to UNIX timestamp
		$_ts = self::createTimestamp($_d);
		
		// get day of the week for specified timestamp
		$week_day = intval(date('w', $_ts));

		$priority = null;

		$dbo = JFactory::getDbo();

		// get list of special days
		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_specialdays'))
			->where($dbo->qn('group') . ' = ' . (int) $group)
			->andWhere(array(
				$dbo->qn('start_ts') . ' = -1',
				$_ts . ' BETWEEN ' . $dbo->qn('start_ts') . ' AND ' . $dbo->qn('end_ts'),
			))
			->order($dbo->qn('priority') . ' DESC');

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// no active special days for the given date and time
			return array();
		}

		$special_days = array();
		
		foreach ($dbo->loadAssocList() as $sd)
		{		
			$ok = false;

			// check days filter
			if (strlen($sd['days_filter']) == 0 || $args['date'] == -1)
			{
				// no days filter or any specified date
				$ok = true;
			}
			else
			{
				// explode days from field
				$_days = explode(',', $sd['days_filter']);

				// make sure the day is in the list
				$ok = in_array($week_day, $_days);
			}

			if ($ok)
			{
				/**
				 * Make sure the returned special days own the same priority,
				 * as we cannot merge together LOW special days with HIGH
				 * special days.
				 *
				 * @since 1.7.4
				 */
				if ($priority === null || $priority == $sd['priority'])
				{
					$special_days[] = $sd;

					$priority = $sd['priority'];
				}

				/**
				 * @since 1.7.2 this method won't limit anymore the special days to 1
				 */
				// return $special_days; // limit to 1
			}
		}
		
		return $special_days;
	}

	/** 
	 * Returns a list of working shifts for the specified special days.
	 *
	 * @param 	array  $shifts        All the supported working shifts.
	 * @param 	array  $special_days  A list of special days.
	 *
	 * @return 	array  A list of resulting shifts.
	 *
	 * @see 	getSpecialDaysOnDate()
	 *
	 * @deprecated 1.9 	Use VRESpecialDaysManager instead.
	 */
	public static function getWorkingShiftsFromSpecialDays(array $shifts, $special_days)
	{
		if (!$special_days)
		{
			// return default working shifts
			return $shifts;
		}

		$_eval_shifts = array();

		// iterate special days
		foreach ($special_days as $sd)
		{
			// go ahead only if the special day has some working shifts
			if (!empty($sd['working_shifts']))
			{
				// extract shift IDs
				$list = explode(',', $sd['working_shifts']);

				// iterate list of shift IDs
				foreach ($list as $ws)
				{
					$found = false;

					// iterate shifts as long as we haven't found a matching ID
					for ($i = 0; $i < count($shifts) && !$found; $i++)
					{
						// check whether the special day working shift
						// matches the ID of the shift
						if ((int) $ws == $shifts[$i]['id'])
						{
							// register shift for its key in order to avoid duplicates
							$_eval_shifts[$ws] = $shifts[$i];
							$found = true;
						}
					}
				}
			}
		}

		if (!$_eval_shifts)
		{
			// return default shifts if empty
			return $shifts;
		}
		
		// return shifts by ignoring their keys
		return array_values($_eval_shifts);
	}

	/**
	 * Calculates the total amount to leave when trying
	 * book a table for the specified check-in.
	 *
	 * @param 	array 	$args 	An associative array containing the check-in details.
	 *
	 * @return 	float 	The total amount to leave.
	 *
	 * @since 	1.8
	 */
	public static function getTotalDeposit($args)
	{
		$config = VREFactory::getConfig();

		// instantiate special days manager
		$sdManager = new VRESpecialDaysManager('restaurant');

		// set date filter
		$sdManager->setStartDate($args['date']);
		// set time filter
		$sdManager->setCheckinTime($args['hourmin']);

		// get first special day available
		$sd = $sdManager->getFirst();

		if ($sd)
		{
			// calculate total deposit
			$total = $sd->getTotalDeposit($args['people']);
		}
		else
		{
			// fallback to global configuration

			// get default cost to leave
			$total = $config->getFloat('resdeposit');
			
			if ($config->getBool('costperperson'))
			{
				// multiply deposit per the number of guests
				$total *= $args['people'];
			}

			// get minimum number of people for deposit
			$ask = $config->getUint('askdeposit');

			if ($ask == 0 || $args['people'] < $ask)
			{
				// never apply deposit in case it is disabled or in
				// case the number of people is lower than the specified amount
				$total = 0;
			}
		}

		/**
		 * This event can be used to alter the total deposit at runtime.
		 * In example, it is possible to change the amount according
		 * to the selected table/room.
		 *
		 * Since multiple plugins might be attached to this event, the system
		 * will take the highest returned value.
		 *
		 * @param 	float  $total  The current total.
		 * @param 	array  $args   The searched arguments.
		 *
		 * @return 	float  The deposit that should be used.
		 *
		 * @since 	1.8
		 */
		$res = VREFactory::getEventDispatcher()->trigger('onCalculateTotalDeposit', array($total, $args));

		// check if at least a plugin returned something
		if (count($res))
		{
			// overwrite the total deposit with the highest fetched value
			$total = max($res);
		}

		// check if the coupon discount should be
		// applied to the deposit to leave (2)
		if ($config->getUint('applycoupon') == 2)
		{
			// get coupon from session, if any
			$coupon = JFactory::getSession()->get('vr_coupon_data', null);

			// check if a coupon was redeemed
			if ($coupon)
			{
				if ($coupon->percentot == 1)
				{
					// percentage discount
					$total -= $total * $coupon->value / 100;
				}
				else
				{
					// fixed discount
					$total -= $coupon->value;
				}
			}
		}

		// make sure the total is not lower than 0
		return max(array(0, $total));
	}

	/**
	 * Returns a list of available payments.
	 *
	 * @param 	integer  $group  The group to check (1: restaurant, 2: takeaway).
	 * @param 	mixed    $user   The user that requested the payment. If not specified,
	 * 							 the current user will be taken.
	 * @param 	mixed 	 $total  The total cost of the order. If not specified, it will
	 * 							 retrieved from the take-away cart.
	 *
	 * @return 	array 	 A list of payments.
	 *
	 * @since 	1.8
	 */
	public static function getAvailablePayments($group, $user = null, $total = null)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_gpayments'))
			->where($dbo->qn('published') . ' = 1')
			->where($dbo->qn('group') . ' <> ' . (($group % 2) + 1))
			->order($dbo->qn('ordering') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();
		
		if (!$dbo->getNumRows())
		{
			// no payments available
			return array();
		}

		$list = $dbo->loadObjectList();

		if (is_null($total))
		{
			if ($group == 1)
			{
				// ignore total for restaurant reservations
				$total = 0;
			}
			else
			{
				// recover total from cart
				$total = TakeAwayCart::getInstance()->getTotalCost();	
			}
		}
		else if (is_object($total))
		{
			// extract total cost from cart
			$total = $total->getTotalCost();
		}
		else
		{
			$total = (float) $total;
		}

		if (is_null($user))
		{
			// get current user
			$user = JFactory::getUser()->id;
		}
		else if (is_object($user))
		{
			// extract ID from object
			$user = $user->id;
		}
		else
		{
			$user = (int) $user;
		}

		$count = 0;

		/**
		 * The payment can be available only for trusted customer.
		 * In this case, we have to count the total number of orders
		 * made by the specified user, which must be equals or greater
		 * than the "trust" factor of the payment.
		 *
		 * @since 1.8
		 */
		if ($user > 0)
		{
			$q = $dbo->getQuery(true);
			$q->select('COUNT(1)');

			if ($group == 1)
			{
				$q->from($dbo->qn('#__vikrestaurants_reservation', 'r'));
			}
			else
			{
				$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r'));
			}

			$q->leftjoin($dbo->qn('#__vikrestaurants_users', 'c') . ' ON ' . $dbo->qn('c.id') . ' = ' . $dbo->qn('r.id_user'));

			$q->where($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'));
			$q->andWhere(array(
				$dbo->qn('r.created_by') . ' = ' . $user,
				$dbo->qn('c.jid') . ' = ' . $user,
			), 'OR');

			$dbo->setQuery($q);
			$dbo->execute();

			$count = (int) $dbo->loadResult();
		}

		$dispatcher = VREFactory::getEventDispatcher();

		foreach ($list as $p)
		{
			$ok = true;

			// validate payment cost restrictions
			if ($p->enablecost != 0)
			{
				if (($p->enablecost > 0 && $p->enablecost > $total) || ($p->enablecost < 0 && abs($p->enablecost) < $total))
				{
					// cost restrictions not verified
					$ok = false;
				}
			}

			// check if the customer is trusted
			if ($p->trust > $count)
			{
				// customer is not trusted
				$ok = false;
			}

			if ($ok)
			{
				/**
				 * Trigger event to let external plugins apply additional filters while 
				 * searching for a compatible payment gateway.
				 * The hook will be executed only in case all the other filters
				 * accepted the payment for the usage.
				 *
				 * @param 	object   $payment  The payment database record.
				 * @param 	integer  $group    The group to check (1: restaurant, 2: takeaway).
				 * @param 	integer  $user     The ID of the user.
				 * @param 	float 	 $total    The total cost of the order.
				 *
				 * @return 	boolean  True to accept the payment gateway, false to discard it.
				 *
				 * @since 	1.8.3
				 */
				$discard = $dispatcher->false('onSearchAvailablePayment', array($p, $group, $user, $total));

				// search for a plugin that returned false
				if ($discard)
				{
					// skip payment gateway
					$ok = false;
				}
			}

			if ($ok)
			{
				$payments[] = $p;
			}
		}

		// translate payments in case multi-lingual is supported
		VikRestaurants::translatePayments($payments);

		return $payments;
	}

	/**
	 * Checks whether there is at least a published
	 * payment gateway for the specified section.
	 *
	 * @param 	integer  $group   The group to check (1: restaurant, 2: takeaway).
	 * @param 	integer  $id      An optional ID to obtain the specified payment.
	 * @param 	boolean  $strict  False to include also unpublished payments.
	 *
	 * @return 	mixed    In case we are searching by ID, the payment details will be
	 * 					 returned (false if not exists). Otherwise, true/false depending
	 * 					 on the number of published payments.
	 *
	 * @since 	1.8
	 */
	public static function hasPayment($group = null, $id = null, $strict = true)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		if ($id)
		{
			// return payment details if we are searching by ID
			$q->select('*');
		}
		else
		{
			// ignore select because we just need to count the rows found
			$q->select(1);
		}

		$q->from($dbo->qn('#__vikrestaurants_gpayments'));
		$q->where(1);
		
		if ($strict)
		{
			$q->where($dbo->qn('published') . ' = 1');
		}

		if (!is_null($group))
		{
			$q->andWhere(array(
				$dbo->qn('group') . ' = 0',
				$dbo->qn('group') . ' = ' . (int) $group,
			), 'OR');
		}

		if (!is_null($id))
		{
			$q->where($dbo->qn('id') . ' = ' . (int) $id);
		}

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// no available payment
			return false;
		}

		// return payment details in case of specified ID
		return $id ? $dbo->loadObject() : true;
	}
	
	/**
	 * Returns a list of menus available for the restaurant.
	 *
	 * @param 	array 	 $args       The searched arguments. In case of missing
	 * 								 date, all the menus will be retrieved.
	 * @param 	boolean  $choosable  Whether to include only the menus that can be selected.
	 *
	 * @return 	array 	 A list of choosable menus.
	 *
	 * @since 	1.5
	 */
	public static function getAllAvailableMenusOn(array $args = array(), $choosable = false)
	{
		// Check if we have a closure. In case the date was
		// not passed, the system will ignore the closure.
		$closed = self::isClosingDayIgnoringDate($args);

		$ids = array();

		$sdList = null;

		// flag used to check whether all the customers of
		// the group are allowed to choose different menus
		$freedom = true;

		/**
		 * Do not use special days if the date was not specified.
		 * This allows us to retrieve all the menus.
		 *
		 * @since 1.8
		 */
		if (!empty($args['date']))
		{
			// instantiate special days manager
			$sdManager = new VRESpecialDaysManager('restaurant');

			// set date filter
			$sdManager->setStartDate($args['date']);

			if (!empty($args['hourmin']))
			{
				// set time filter
				$sdManager->setCheckinTime($args['hourmin']);
			}

			// get list of available special days
			$sdList = $sdManager->getList();
			
			// make sure any special days exist
			if ($sdList)
			{
				$overwrite_closure = false;

				foreach ($sdList as $sd)
				{
					// in case of closure, make sure the special day can overwrite it
					if (!$closed || $sd->ignoreClosingDays)
					{
						if ($sd->ignoreClosingDays)
						{
							// special day can overwrite closure
							$overwrite_closure = true;
						}

						// if we need to get only the choosable menus,
						// make sure the special day allows their selection
						if (!$choosable || $sd->chooseMenu)
						{
							// get available menus
							$ids = array_merge($ids, $sd->menus);

							// all the special days found must allow the freedom of choice
							$freedom = $freedom && $sd->choiceFreedom;
						}
					}
				}

				if (!$ids)
				{
					// no selected menus
					return array();
				}

				if ($overwrite_closure)
				{
					// overwrite closure
					$closed = false;
				}

				// avoid duplicates
				$ids = array_unique($ids);
			}
		}

		if ($closed)
		{
			// restaurant closed, return empty list
			return array();
		}
		
		$dbo = JFactory::getDbo();

		$menus = array();

		// recover menus
		$q = $dbo->getQuery(true)
			->select('*')
			->select((int) $freedom . ' AS ' . $dbo->qn('freechoose'))
			->from($dbo->qn('#__vikrestaurants_menus'))
			->where($dbo->qn('published') . ' = 1')
			->order($dbo->qn('ordering') . ' ASC');

		if ($ids)
		{
			// take only the menus fetched by the special day
			$q->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');
		}

		if ($choosable)
		{
			// take only the menus that can be chosen
			$q->where($dbo->qn('choosable') . ' = 1');
		}
		
		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// no menus found
			return array();
		}

		$menus = $dbo->loadObjectList();

		if ($sdList || empty($args['date']))
		{
			// directly return menus in case of special day
			// or in case we should not filter them
			return $menus;
		}

		$list = array();

		if (!empty($args['hourmin']))
		{
			// extract hours and minutes
			list($args['hour'], $args['min']) = explode(':', $args['hourmin']);
		}
		else
		{
			// use midnight in case the time was not specified
			$args['hour'] = $args['min'] = 0;
		}

		// calculate checkin week day
		$weekday = date('w', VikRestaurants::createTimestamp($args['date'], $args['hour'], $args['min']));
		// calculate time in minutes
		$time = (int) $args['hour'] * 60 + (int) $args['min'];

		// validate each menu against the selected date and time
		foreach ($menus as $m)
		{
			// make sure the menu is not published for special days only
			$ok = !$m->special_day;

			if (!empty($m->days_filter))
			{
				// split days
				$days = preg_split("/,\s*/", $m->days_filter);
	
				// make sure the checkin day is supported
				$ok = $ok && in_array($weekday, $days);
			}

			if (!empty($args['hourmin']) && !empty($m->working_shifts))
			{
				// split shifts
				$shifts = preg_split("/,\s*/", $m->working_shifts);
	
				$has = false;

				// iterate shifts
				for ($i = 0; $i < count($shifts) && $has == false; $i++)
				{
					// from shift ID to time
					$sh = JHtml::_('vikrestaurants.timeofshift', (int) $shifts[$i]);

					if ($sh && $sh->from <= $time && $time <= $sh->to)
					{
						$has = true;
					}
				}

				$ok = $ok && $has;
			}

			if ($ok)
			{
				// menu is ok, copy it
				$list[] = $m;
			}
		}

		return $list;
	}

	/**
	 * Checks whether the customers can choose the menus for the party
	 * in the specified date and time.
	 * This method ignores the closing days.
	 *
	 * @param 	array 	 $args  The searched arguments.
	 *
	 * @return 	boolean  True if choosable, false otherwise.
	 *
	 * @since 	1.5
	 */
	public static function isMenusChoosable($args)
	{
		// instantiate special days manager
		$sdManager = new VRESpecialDaysManager('restaurant');

		// set date filter
		$sdManager->setStartDate($args['date']);
		// set time filter
		$sdManager->setCheckinTime($args['hourmin']);

		// get list of available special days
		$sdList = $sdManager->getList();
		
		if ($sdList)
		{
			foreach ($sdList as $sd)
			{
				// checks whether it is possible to choose menus
				// with the configuration of the special day found
				if ($sd->chooseMenu)
				{
					return true;
				}
			}

			// none of the available special days allows
			// the menus selection
			return false;
		}

		// fallback to global configuration
		return VREFactory::getConfig()->getBool('choosemenu');
	}
	
	/**
	 * Returns a list of take-away menus available for
	 * the specified date and time.
	 * In case the returned list is empty, no menus are 
	 * available for the given check-in.
	 *
	 * @param 	array 	$args  An associative array containing the date and time.
	 *
	 * @return 	array   A list of available menus.
	 *
	 * @since 	1.6
	 */
	public static function getAllTakeawayMenusOn($args)
	{
		/**
		 * Manipulate $args in order to use the closest time
		 * in case we passed an invalid time.
		 *
		 * @since 1.7.5
		 */
		if (empty($args['hourmin']))
		{
			// always get a time in the future
			$args['hourmin'] = self::getClosestTimeTakeAway($args['date'], $next = true);

			if (!$args['hourmin'])
			{
				// unable to find a valid time for the given date
				return array();
			}
		}

		// Check if we have a closure. In case the date was
		// not passed, the system will ignore the closure.
		$closed = self::isClosingDayIgnoringDate($args);

		$ids = array();

		$sdList = null;

		// instantiate special days manager
		$sdManager = new VRESpecialDaysManager('takeaway');

		// set date filter
		$sdManager->setStartDate($args['date']);

		// set time filter
		$sdManager->setCheckinTime($args['hourmin']);

		// get list of available special days
		$sdList = $sdManager->getList();
		
		// make sure any special days exist
		if ($sdList)
		{
			$overwrite_closure = false;

			foreach ($sdList as $sd)
			{
				// in case of closure, make sure the special day can overwrite it
				if (!$closed || $sd->ignoreClosingDays)
				{
					if ($sd->ignoreClosingDays)
					{
						// special day can overwrite closure
						$overwrite_closure = true;
					}

					// get available menus
					$ids = array_merge($ids, $sd->menus);
				}
			}

			if (!$ids)
			{
				// no selected menus
				return array();
			}

			if ($overwrite_closure)
			{
				// overwrite closure
				$closed = false;
			}

			// avoid duplicates
			$ids = array_unique($ids);
		}

		if ($closed)
		{
			// restaurant closed, return empty list
			return array();
		}

		if (!$ids)
		{
			$dbo = JFactory::getDbo();

			// get all published menus
			$q = $dbo->getQuery(true)
				->select($dbo->qn('id'))
				->from($dbo->qn('#__vikrestaurants_takeaway_menus'))
				->where($dbo->qn('published') . ' = 1');

			list($h, $m) = explode(':', $args['hourmin']);

			if (is_int($args['date']))
			{
				// set hour and minutes to received timestamp
				$checkin = strtotime($h . ":" . $m, $args['date']);
			}
			else
			{
				// calculate check-in date time
				$checkin = VikRestaurants::createTimestamp($args['date'], $h, $m);
			}

			/**
			 * Take all the menus with a valid/empty start publishing.
			 *
			 * @since 1.8.3
			 */
			$q->andWhere(array(
				$dbo->qn('publish_up') . ' = -1',
				$dbo->qn('publish_up') . ' IS NULL',
				$dbo->qn('publish_up') . ' <= ' . $checkin, 
			));

			/**
			 * Take all the menus with a valid/empty finish publishing.
			 *
			 * @since 1.8.3
			 */
			$q->andWhere(array(
				$dbo->qn('publish_down') . ' = -1',
				$dbo->qn('publish_down') . ' IS NULL',
				$dbo->qn('publish_down') . ' >= ' . $checkin, 
			));

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$ids = $dbo->loadColumn();
			}
		}

		return $ids;
	}

	/**
	 * Checks whether the menus in the list are available
	 * for the purchase.
	 *
	 * @param 	array 	 &$menus 	 The menus list.
	 * @param 	integer  $checkin    The check-in timestamp.
	 * @param 	mixed 	 $available  A list of available menus.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public static function fetchMenusStatus(&$menus, $checkin = null, $available = null)
	{
		$config = VREFactory::getConfig();

		if (is_null($checkin))
		{
			// recover cart instance
			self::loadCartLibrary();
			$cart = TakeAwayCart::getInstance();

			// use check-in stored in cart
			$checkin = $cart->getCheckinTimestamp();
		}

		/**
		 * Convert UNIX timestamp to date string, in order
		 * to exclude the time.
		 *
		 * @since 1.8.2
		 */
		if (preg_match("/^\d+$/", $checkin))
		{
			$checkin = date($config->get('dateformat'), $checkin);
		}

		if (is_null($available))
		{
			// get all take-away menus available for the specified date
			$available = self::getAllTakeawayMenusOn(array('date' => $checkin));
		}

		// check whether the date selection is allowed
		$is_date_allowed = $config->getBool('tkallowdate');
		// in case the date selection is disabled, check whether pre-orders are enabled
		$is_live_orders = $is_date_allowed ? false : $config->getBool('tkwhenopen');
		// in case the pre-orders are disabled, check whether the restaurant is currently open
		$is_currently_avail = !$is_live_orders ? true : self::isTakeAwayCurrentlyAvailable();

		// check whether take-away orders are currently allowed
		$orders_allowed = self::isTakeAwayReservationsAllowedOn($checkin);

		if (!is_array($menus))
		{
			// always use an array
			$menus = array($menus);
			// remember that the argument was NOT an array
			$was_array = false;
		}
		else
		{
			// remember that the argument was already an array
			$was_array = true;
		}

		foreach ($menus as &$menu)
		{
			// check whether the menu products are available for purchase
			$menu->isActive = $orders_allowed && $is_currently_avail && in_array($menu->id, $available);

			if ($menu->isActive == false)
			{
				// menu not active, fetch reason
				if (!$orders_allowed)
				{
					// orders have been stopped for the current day (from dashboard)
					$menu->availError = JText::_('VRTKMENUNOTAVAILABLE3');
				}
				else if ($is_currently_avail)
				{
					// since the restaurant is open, the menu is not available
					// for the selected check-in date
					$menu->availError = JText::_('VRTKMENUNOTAVAILABLE'); 
				}
				else
				{
					// restaurant is currently closed
					$menu->availError = JText::_('VRTKMENUNOTAVAILABLE2'); 
				}
			}
		}

		if (!$was_array)
		{
			// revert to original value
			$menus = array_shift($menus);
		}
	}

	/**
	 * Checks whether there is a closing day for the given information.
	 * In case the array contains the date and it is equals to "-1", 
	 * the day will never be considered as closed.
	 *
	 * @param 	array 	 $args  The date information array.
	 *
	 * @return 	boolean  True if closing day, false otherwise.
	 *
	 * @see 	isClosingDay()
	 */
	public static function isClosingDayIgnoringDate(array $args)
	{
		if (empty($args['date']) || $args['date'] == -1)
		{
			return false;
		}
		
		return self::isClosingDay($args);
	}
	
	/**
	 * Checks whether there is a closing day for the given information.
	 *
	 * @param 	mixed   $args  Either an array containing the date information
	 * 						   or a UNIX timestamp. If not specified, the current
	 * 						   date and time will be used. In case of array, it is
	 * 						   possible to use the following attributes:
	 * 						   - date  a system-formatted date (mandatory);
	 *						   - hour  an hour in 24h format (optional);
	 * 						   - min   a minute (optional).
	 *
	 * @return 	boolan  True if closing day, false otherwise.
	 */
	public static function isClosingDay($args = array())
	{
		// get closing days
		$cd = self::getClosingDays();

		if (isset($args['date']))
		{
			if (!empty($args['hourmin']))
			{
				// extract hour and min
				list($hour, $min) = explode(':', $args['hourmin']);
			}
			else
			{
				// look for hour and min
				$hour = isset($args['hour']) ? $args['hour'] : 0;
				$min  = isset($args['min'])  ? $args['min']  : 0;
			}

			if ($hour == -1)
			{
				$hour = 0;
			}
			
			if (is_numeric($args['date']))
			{
				/**
				 * Set time to given timestamp.
				 *
				 * @since 1.8
				 */
				$ts = strtotime($hour . ':' . $min, $args['date']);
			}
			else
			{
				// create timestamp
				$ts = self::createTimestamp($args['date'], $hour, $min);
			}
		}
		else if (is_numeric($args))
		{
			/**
			 * Use the given timestamp.
			 *
			 * @since 1.8
			 */
			$ts = (int) $args;
		}
		else if (is_string($args))
		{
			/**
			 * Create timestamp from given date string.
			 *
			 * @since 1.8
			 */
			$ts = VikRestaurants::createTimestamp($args, 0, 0);
		}
		else
		{
			/**
			 * Use current date and time if not specified.
			 *
			 * @since 1.8
			 */
			$ts = self::now();
		}

		// get date information
		$date = getdate($ts);
		
		// iterate closing days
		foreach ($cd as $v)
		{
			// get closing date information
			$app = getdate($v['ts']);
			
			if ($v['freq'] == 0)
			{
				// no recurrence, make sure the day is exactly the same
				if ($date['year'] == $app['year'] && $date['mon'] == $app['mon'] && $date['mday'] == $app['mday'])
				{
					return true;
				}
			}
			else if ($v['freq'] == 1)
			{
				// weekly recurrence, make sure the day of the week is the same
				if ($date['wday'] == $app['wday'])
				{
					return true;
				}
			}
			else if ($v['freq'] == 2)
			{
				// monthly recurrence, make sure the day of the month is the same
				if ($date['mday'] == $app['mday'])
				{
					return true;
				}
			}
			else if ($v['freq'] == 3)
			{
				// yearly recurrence, make sure the day and the month are the same
				if ($date['mday'] == $app['mday'] && $date['mon'] == $app['mon'])
				{
					return true;
				}
			}
		}
		
		return false;
	}

	/**
	 * Checks whether it is possible to purchase products
	 * at the current date and time. This method should
	 * be used only in case the "Live Orders" setting
	 * is turned on.
	 *
	 * @return 	boolean
	 *
	 * @since 	1.7
	 */
	public static function isTakeAwayCurrentlyAvailable()
	{
		/**
		 * Consider current real time.
		 *
		 * @since 1.7.4
		 */	
		$date = getdate(self::now());

		$args = array(
			'date'    => $date[0],
			'hourmin' => (int) $date['hours'] . ':' . (int) $date['minutes'],
		);

		// Make sure the current date and time is included
		// within a valid working shift. Use non-strict method
		// in order to make sure the time is between the shift
		// opening-closing delimiters.
		return static::isHourBetweenShifts($args, 2, $strict = false);
	}

	/**
	 * Checks whether the specified product owns at least
	 * a topping group.
	 *
	 * @param 	integer  $id_entry   The product ID.
	 * @param 	integer  $id_option  The variation ID (optional).
	 *
	 * @return 	boolean  True if supports toppings, false otherwise.
	 *
	 * @since 	1.7
	 */
	public static function hasItemToppings($id_entry, $id_option = 0)
	{
		static $lookup = null;

		$id_entry  = (int) $id_entry;
		$id_option = (int) $id_option;

		// check if we already cached the toppings
		if ($lookup === null)
		{
			$lookup = array();
			
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn('id_entry'))
				->select($dbo->qn('id_variation'))
				->from($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc'));

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				foreach ($dbo->loadObjectList() as $group)
				{
					if (!isset($lookup[$group->id_entry]))
					{
						$lookup[$group->id_entry] = array();
					}

					// add variation only if not already exists
					if (!in_array($group->id_variation, $lookup[$group->id_entry]))
					{
						$lookup[$group->id_entry][] = $group->id_variation == -1 ? 0 : $group->id_variation;
					}
				}
			}
		}

		if (!isset($lookup[$id_entry]))
		{
			// no toppings available for the given product
			return false;
		}

		// search for a topping group available for all the variations
		if (in_array(0, $lookup[$id_entry]))
		{
			return true;
		}

		// otherwise search for a group assigned only to the specified variation
		return $id_option > 0 && in_array($id_option, $lookup[$id_entry]);
	}

	/**
	 * Returns the media upload settings.
	 *
	 * @return 	array
	 */
	public static function getMediaProperties()
	{
		$prop = VREFactory::getConfig()->getArray('mediaprop', null);

		if (!$prop)
		{
			$prop = array(
				'resize' 		=> 0,
				'resize_value' 	=> 512,				
				'thumb_value' 	=> 128,
			);
		}

		return $prop;
	}

	/**
	 * Updates the media upload settings.
	 *
	 * @param  	array 	&$prop
	 *
	 * @return 	void
	 */
	public static function storeMediaProperties(&$prop)
	{
		$dbo = JFactory::getDbo();

		if (!isset($prop['resize']))
		{
			$prop['resize'] = 0;
		}

		if (empty($prop['resize_value']))
		{
			$prop['resize_value'] = 512;
		}

		if (empty($prop['thumb_value']))
		{
			$prop['thumb_value'] = 128;
		}

		$config = VREFactory::getConfig();
		$config->set('mediaprop', $prop);
		$config->set('firstmediaconfig', 0);
	}

	/**
	 * Uploads a media file.
	 *
	 * @param 	string 	 $name       The media name.
	 * @param 	mixed 	 $prop       The upload settings.
	 * @param 	boolean  $overwrite  True to overwrite the existing media.
	 *
	 * @return 	array 	 A response.
	 *
	 * @uses 	uploadFile()
	 */
	public static function uploadMedia($name, $prop = null, $overwrite = false)
	{
		// upload as a normal file
		$resp = self::uploadFile($name, VREMEDIA . DIRECTORY_SEPARATOR, 'jpeg,jpg,png,gif,bmp', $overwrite);

		// import image cropper
		VRELoader::import('library.image.resizer');

		if ($resp->esit)
		{
			if ($prop === null)
			{
				// get media settings if not specified
				$prop = self::getMediaProperties();
			}
			
			if ($prop['resize'] == 1)
			{	
				// crop original image
				$crop_dest = str_replace($resp->name, '$_' . $resp->name, $resp->path);
				
				ImageResizer::proportionalImage($resp->path,  $crop_dest, $prop['resize_value'], $prop['resize_value']);
				copy($crop_dest, $resp->path);
				unlink($crop_dest);
			}

			// generate thumbnail
			$thumb_dest = VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $resp->name;
			ImageResizer::proportionalImage($resp->path, $thumb_dest,  $prop['thumb_value'],  $prop['thumb_value']);
		}

		return $resp;
	}

	/**
	 * Moves the given file within the specified destination.
	 *
	 * @param 	mixed 	 $name       Either the file object or the $_FILES name in
	 *                               which the file is located.
	 * @param 	string 	 $dest       The path (including filename) in which to move the uploaded file.
	 * @param 	string 	 $filters    Either a regex or a comma-separated list of supported extensions.
	 * @param 	boolean  $overwrite  True to overwrite the file if the destination is already occupied.
	 *                               Otherwise a progressive file name will be used.
	 *
	 * @return 	object 	 An object containing the information of the uploaded file. It is possible to
	 *                   check whether the file was uploaded by looking the "status" property. In case of
	 *                   errors, the "errno" property will return an error code to understand why the error
	 *                   occurred (1: unsupported file, 2: generic upload error).
	 */
	public static function uploadFile($name, $dest, $filters = '*', $overwrite = false)
	{
		if (is_string($name))
		{
			$file = JFactory::getApplication()->input->files->get($name, null, 'array');
		}
		else
		{
			$file = $name;
		}

		$dest = rtrim($dest, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		
		/**
		 * Added support for status property.
		 * The [esit] property will be temporarily
		 * left for backward compatibility.
		 *
		 * @since 1.8
		 *
		 * @deprecated 1.9  [esit] property will be removed.
		 */
		$obj = new stdClass;
		$obj->status = 0;
		$obj->esit   = 0;
		$obj->errno  = null;
		$obj->path   = '';
		
		if (isset($file) && strlen(trim($file['name'])) > 0)
		{
			jimport('joomla.filesystem.file');

			$filename = JFile::makeSafe(str_replace(' ', '-', $file['name']));
			$src = $file['tmp_name'];

			// use a different name if the file path is already occupied
			if (!$overwrite && file_exists($dest . $filename))
			{
				$j = 2;

				// split file name and file extension
				if (preg_match("/(.*?)(\.[a-z0-9]{2,})/i", $filename, $match))
				{
					$basename = $match[1];
					$file_ext = $match[2];
				}
				else
				{
					$basename = $filename;
					$file_ext = '';
				}

				// increase counter as long as the path is occupied
				while (file_exists($dest . $basename . '-' . $j . $file_ext))
				{
					$j++;
				}

				// construct file name
				$filename = $basename . '-' . $j . $file_ext;
			}

			// create file object
			$obj->path = $dest . $filename;
			$obj->src  = $src;
			$obj->name = $filename;

			// make sure the file is compatible
			if (self::isFileTypeCompatible($file, $filters))
			{
				// complete file upload
				if (JFile::upload($src, $obj->path, $use_streams = false, $allow_unsafe = true))
				{
					$obj->status = 1;
					$obj->esit = 1;
				}
				else
				{
					// unable to upload the file
					$obj->errno = 2;
				}
			}
			else
			{
				// file not supported
				$obj->errno = 1;
			}
		}

		return $obj;
	}

	/**
	 * Helper method used to check whether the given file name
	 * supports one of the given filters.
	 *
	 * @param 	mixed 	 $file     Either the file name or the uploaded file.
	 * @param 	string 	 $filters  Either a regex or a comma-separated list of supported extensions.
	 *                             The regex must be inclusive of 
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public static function isFileTypeCompatible($file, $filters)
	{
		// make sure the filters query is not empty
		if (strlen($filters) == 0)
		{
			// cannot assert whether the file could be accepted or not
			return false;
		}

		// check whether all the files are accepted
		if ($filters == '*')
		{
			return true;
		}

		// use the file MIME TYPE in case of array
		if (is_array($file))
		{
			$file = $file['type'];
		}

		/**
		 * Check if we are handling a regex.
		 *
		 * @since 1.8
		 */
		if (static::isRegex($filters))
		{
			return (bool) preg_match($filters, $file);
		}
		
		// fallback to comma-separated list
		$types = array_filter(preg_split("/\s*,\s*/", $filters));

		foreach ($types as $t)
		{
			// remove initial dot if specified
			$t = ltrim($t, '.');

			// check if the file ends with the given extension
			if (preg_match("/{$t}$/", $file))
			{
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Checks whether the given string is a structured PCRE regex.
	 * It simply makes sure that the string owns valid delimiters.
	 * A delimiter can be any non-alphanumeric, non-backslash,
	 * non-whitespace character.
	 *
	 * @param 	string   $str  The string to check.
	 *
	 * @return 	boolean  True if a regex, false otherwise.
	 *
	 * @since 	1.8
	 */
	public static function isRegex($str)
	{
		// first of all make sure the first character is a supported delimiter
		if (!preg_match("/^([!#$%&'*+,.\/:;=?@^_`|~\-(\[{<\"])/", $str, $match))
		{
			// no valid delimiter
			return false;
		}

		// get delimiter
		$d = $match[1];

		// lookup used to check if we should take a different ending delimiter
		$lookup = array(
			'{' => '}',
			'[' => ']',
			'(' => ')',
			'<' => '>',
		);

		if (isset($lookup[$d]))
		{
			$d = $lookup[$d];
		}

		// make sure the regex ends with the delimiter found
		return (bool) preg_match("/\\{$d}[gimsxU]*$/", $str);
	}
	
	/**
	 * Fetches the take-away order details.
	 *
	 * @param 	integer  $order_id
	 * @param 	string 	 $langtag
	 *
	 * @return 	mixed
	 *
	 * @deprecated 	1.9 Use VREOrderFactory::getReservation() instead.
	 */
	public static function fetchOrderDetails($order_id, $langtag = '')
	{
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `r`.*,`gp`.`name` AS `payment_name`,`gp`.`note` AS `payment_note`,`gp`.`prenote` AS `payment_prenote`,`gp`.`charge` AS `payment_charge`,
		`t`.`name` AS `table_name`,`t`.`id_room` AS `table_id_room`,`room`.`name` AS `room_name`,`room`.`description` AS `room_description`,
		`ma`.`id_menu`,`menu`.`name` AS `menu_name`,`ma`.`quantity` AS `menu_quantity`,
		`ju`.`name` AS `user_name`, `ju`.`username` AS `user_uname`, `ju`.`email` AS `user_email`
		FROM `#__vikrestaurants_reservation` AS `r` 
		LEFT JOIN `#__vikrestaurants_gpayments` AS `gp` ON `gp`.`id`=`r`.`id_payment` 
		LEFT JOIN `#__vikrestaurants_table` AS `t` ON `t`.`id`=`r`.`id_table` 
		LEFT JOIN `#__vikrestaurants_room` AS `room` ON `room`.`id`=`t`.`id_room` 
		LEFT JOIN `#__vikrestaurants_res_menus_assoc` AS `ma` ON `ma`.`id_reservation`=`r`.`id` 
		LEFT JOIN `#__vikrestaurants_menus` AS `menu` ON `menu`.`id`=`ma`.`id_menu` 
		LEFT JOIN `#__vikrestaurants_users` AS `u` ON `u`.`id`=`r`.`id_user`
		LEFT JOIN `#__users` AS `ju` ON `ju`.`id`=`u`.`jid` 
		WHERE `r`.`id` = " . intval($order_id);
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$rows = $dbo->loadAssocList();
			$order = $rows[0];
			$order['menus_list'] = array();

			if (!empty($rows[0]['menu_name']))
			{
				foreach ($rows as $r)
				{
					$order['menus_list'][] = array(
						'id'       => $r['id_menu'],
						'name'     => $r['menu_name'],
						'quantity' => $r['menu_quantity'],
					);
				}
			}

			// translations
			if (empty($langtag))
			{
				$langtag = $order['langtag'];

				if (empty($langtag))
				{
					$langtag = JFactory::getLanguage()->getTag();
				}
			}

			$order['langtag'] = $langtag;

			if (!empty($order['id_payment']) && $order['id_payment'] > 0)
			{
				$payments_translations = self::getTranslatedPayments(array($order['id_payment']), $order['langtag']);

				$order['payment_name']    = self::translate($order['id_payment'], $order, $payments_translations, 'payment_name', 'name');
				$order['payment_note']    = self::translate($order['id_payment'], $order, $payments_translations, 'payment_note', 'note');
				$order['payment_prenote'] = self::translate($order['id_payment'], $order, $payments_translations, 'payment_prenote', 'prenote');
			}

			return $order;
		}

		return false;
	}

	/**
	 * Returns a list of food assigned to the specified reservation.
	 *
	 * @param 	integer  $oid
	 * @param 	mixed 	 $dbo
	 *
	 * @return 	array
	 *
	 * @deprecated 	1.9 Use VREOrderFactory::getReservation() instead.
	 */
	public static function getFoodFromReservation($oid, $dbo = null)
	{
		if ($dbo === null)
		{
			$dbo = JFactory::getDbo();
		}

		$q = "SELECT `a`.* 
		FROM `#__vikrestaurants_res_prod_assoc` AS `a`
		WHERE `a`.`id_reservation` = $oid 
		ORDER BY `a`.`id` ASC";

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadAssocList();
		}

		return array();
	}

	/**
	 * Returns the plain e-mail template for admin notifications.
	 *
	 * @param 	array 	$order_details
	 *
	 * @return 	string
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function loadAdminEmailTemplate($order_details = array())
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('restaurant', 'admin', $order_details['id'])->getTemplate();
	}
	
	/**
	 * Sends the e-mail notification to the administrator.
	 *
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function sendAdminEmail($order_details)
	{
		VRELoader::import('library.mail.factory');

		$mail = VREMailFactory::getInstance('restaurant', 'admin', $order_details['id']);

		if ($mail->shouldSend())
		{
			return $mail->send();
		}
	}

	/**
	 * Parses the contents of e-mail template.
	 *
	 * @param 	string 	$tmpl
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */
	public static function parseAdminEmailTemplate($tmpl, $order_details)
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('restaurant', 'admin', $order_details['id'])->getHtml();
	}
	
	/**
	 * Returns the plain e-mail template for customer notifications.
	 *
	 * @param 	array 	$order_details
	 *
	 * @return 	string
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function loadEmailTemplate($order_details = array())
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('restaurant', 'customer', $order_details['id'])->getTemplate();
	}
	
	/**
	 * Sends the e-mail notification to the customer.
	 *
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function sendCustomerEmail($order_details)
	{
		VRELoader::import('library.mail.factory');

		$mail = VREMailFactory::getInstance('restaurant', 'customer', $order_details['id']);

		if ($mail->shouldSend())
		{
			return $mail->send();
		}
	}

	/**
	 * Parses the contents of e-mail template.
	 *
	 * @param 	string 	$tmpl
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */
	public static function parseEmailTemplate($tmpl, $order_details)
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('restaurant', 'customer', $order_details['id'])->getHtml();
	}

	/**
	 * Returns the plain e-mail template for customer cancellations.
	 *
	 * @param 	array 	$order_details
	 *
	 * @return 	string
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function loadCancellationEmailTemplate($order_details = array())
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('restaurant', 'cancellation', $order_details['id'])->getTemplate();
	}
	
	/**
	 * Sends the e-mail cancellation to the customer.
	 *
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function sendCancellationEmail($order_details)
	{
		VRELoader::import('library.mail.factory');

		$mail = VREMailFactory::getInstance('restaurant', 'cancellation', $order_details['id']);

		if ($mail->shouldSend())
		{
			return $mail->send();
		}
	}
	
	/**
	 * Parses the contents of e-mail template.
	 *
	 * @param 	string 	$tmpl
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */
	public static function parseCancellationEmailTemplate($tmpl, $order_details)
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('restaurant', 'cancellation', $order_details['id'])->getHtml();
	}

	/**
	 * Removes all the take-away orders out of time.
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9  Use VikRestaurants::removeTakeAwayOrdersOutOfTime() instead.
	 */
	public static function removeAllTakeAwayOrdersOutOfTime($dbo = null)
	{
		self::removeTakeAwayOrdersOutOfTime();
	}

	/**
	 * Sets the status to REMOVED for all the take-away orders
	 * that haven't been confirmed within the specified range of time.
	 *
	 * @param 	mixed    $id     Either an array or the ID of the orders
	 * 							 to remove. If not specified, all the expired
	 * 							 orders will be taken.
	 *
	 * @return 	integer  The total number of affected rows.
	 *
	 * @since 	1.8
	 *
	 * @uses 	removeRecordsOutOfTime()
	 */
	public static function removeTakeAwayOrdersOutOfTime($id = null)
	{
		return static::removeRecordsOutOfTime('#__vikrestaurants_takeaway_reservation', $id);
	}

	/**
	 * Sets the status to REMOVED for all the restaurant reservations
	 * that haven't been confirmed within the specified range of time.
	 *
	 * @param 	mixed    $id     Either an array or the ID of the reservations
	 * 							 to remove. If not specified, all the expired
	 * 							 reservations will be taken.
	 *
	 * @return 	integer  The total number of affected rows.
	 *
	 * @since 	1.8
	 *
	 * @uses 	removeRecordsOutOfTime()
	 */
	public static function removeRestaurantReservationsOutOfTime($id = null)
	{
		return static::removeRecordsOutOfTime('#__vikrestaurants_reservation', $id);
	}

	/**
	 * Sets the status to REMOVED for all the records that haven't
	 * been confirmed within the specified range of time.
	 *
	 * @param 	string 	 $table  The table of the records to update.
	 * @param 	mixed    $id     Either an array or the ID of the records
	 * 							 to remove. If not specified, all the 
	 * 							 expired records will be taken.
	 *
	 * @return 	integer  The total number of affected rows.
	 *
	 * @since 	1.8
	 */
	protected static function removeRecordsOutOfTime($table, $id = null)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);
		
		$q->update($dbo->qn($table));
		$q->set($dbo->qn('status') . ' = ' . $dbo->q('REMOVED'));
		$q->where($dbo->qn('status') . ' = ' . $dbo->q('PENDING'));
		$q->where($dbo->qn('locked_until') . ' < ' . time());

		if ($id)
		{
			// take only the specified reservations
			$q->where($dbo->qn('id') . ' IN (' . implode(',', array_map('intval', (array) $id)) . ')');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		return $dbo->getAffectedRows();
	}
	
	/**
	 * Fetches the take-away order details.
	 *
	 * @param 	integer  $order_id
	 * @param 	string 	 $langtag
	 *
	 * @return 	array
	 *
	 * @deprecated 	1.9 Use VREOrderFactory::getOrder() instead.
	 */
	public static function fetchTakeAwayOrderDetails($order_id, $langtag = '')
	{
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `r`.*,`gp`.`name` AS `payment_name`,`gp`.`note` AS `payment_note`,`gp`.`prenote` AS `payment_prenote`,`gp`.`charge` AS `payment_charge`, 
		`rp`.`id` AS `id_res_prod_assoc`, `rp`.`id_product` AS `id_product`,`rp`.`quantity` AS `product_quantity`,`rp`.`id_product_option` AS `id_product_option`,`rp`.`price` AS `product_price`,`rp`.`notes` AS `product_notes`,
		`entry`.`name` AS `product_name`,`option`.`name` AS `option_name`, `entry`.`id_takeaway_menu` as `id_tkmenu`,
		`group`.`id` AS `id_group`, `group`.`title` AS `group_title`, `topping`.`id` AS `id_topping`, `topping`.`name` AS `topping_name`,
		`ju`.`name` AS `user_name`, `ju`.`username` AS `user_uname`, `ju`.`email` AS `user_email`
		FROM `#__vikrestaurants_takeaway_reservation` AS `r` 
		LEFT JOIN `#__vikrestaurants_gpayments` AS `gp` ON `gp`.`id`=`r`.`id_payment` 
		LEFT JOIN `#__vikrestaurants_takeaway_res_prod_assoc` AS `rp` ON `r`.`id`=`rp`.`id_res` 
		LEFT JOIN `#__vikrestaurants_takeaway_res_prod_topping_assoc` AS `rpt` ON `rp`.`id`=`rpt`.`id_assoc` 
		LEFT JOIN `#__vikrestaurants_takeaway_menus_entry` AS `entry` ON `entry`.`id`=`rp`.`id_product` 
		LEFT JOIN `#__vikrestaurants_takeaway_menus_entry_option` AS `option` ON `option`.`id`=`rp`.`id_product_option` 
		LEFT JOIN `#__vikrestaurants_takeaway_entry_group_assoc` AS `group` ON `group`.`id`=`rpt`.`id_group` 
		LEFT JOIN `#__vikrestaurants_takeaway_topping` AS `topping` ON `topping`.`id`=`rpt`.`id_topping` 
		LEFT JOIN `#__vikrestaurants_users` AS `u` ON `u`.`id`=`r`.`id_user`
		LEFT JOIN `#__users` AS `ju` ON `ju`.`id`=`u`.`jid` 
		WHERE `r`.`id` = " . intval($order_id);
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$record = $dbo->loadAssocList();
			
			$order = $record[0];
			$order['items'] = array();
			
			$last_item = $last_group = -1;
			
			$entries_ids  = array();
			$options_ids  = array();
			$groups_ids   = array();
			$toppings_ids = array();
			
			foreach ($record as $r)
			{
				if ($last_item != $r['id_res_prod_assoc'] && !empty($r['id_res_prod_assoc']))
				{
					$order['items'][] = array(
						'id_assoc'        => $r['id_res_prod_assoc'],
						'id'              => $r['id_product'],
						'id_tkmenu'       => $r['id_tkmenu'],
						'id_option'       => $r['id_product_option'],
						'name'            => $r['product_name'],
						'option_name'     => $r['option_name'],
						'price'           => $r['product_price'],
						'quantity'        => $r['product_quantity'],
						'notes'           => $r['product_notes'],
						'toppings_groups' => array(),
					);
					
					$last_item  = $r['id_res_prod_assoc'];
					$last_group = -1;
					
					if (!in_array($r['id_product'], $entries_ids))
					{
						$entries_ids[] = $r['id_product'];
					}
					
					if (!in_array($r['id_product_option'], $options_ids))
					{
						$options_ids[] = $r['id_product_option'];
					}
				}
				
				if ($last_group != $r['id_group'])
				{
					if ($r['id_group'] > 0)
					{
						$order['items'][count($order['items'])-1]['toppings_groups'][] = array(
							'id'       => $r['id_group'],
							'title'    => $r['group_title'],
							'toppings' => array(),
						);
						
						if (!in_array($r['id_group'], $groups_ids))
						{
							$groups_ids[] = $r['id_group'];
						}
					}
						
					$last_group = $r['id_group'];
				}
				
				if ($r['id_topping'] > 0)
				{
					$order['items'][count($order['items']) - 1]['toppings_groups'][count($order['items'][count($order['items']) - 1]['toppings_groups']) - 1]['toppings'][] = array(
						'id'   => $r['id_topping'],
						'name' => $r['topping_name'],
					);
					
					if (!in_array($r['id_topping'], $toppings_ids))
					{
						$toppings_ids[] = $r['id_topping'];
					}
				}
			}

			// translations
			if (empty($langtag))
			{
				$langtag = $order['langtag'];
				
				if (empty($langtag))
				{
					$langtag = JFactory::getLanguage()->getTag();
				}
			}

			$order['langtag'] = $langtag;

			if (VikRestaurants::isMultilanguage())
			{
				// items

				$entries_translations  = self::getTranslatedTakeawayProducts($entries_ids, $order['langtag']);
				$options_translations  = self::getTranslatedTakeawayOptions($options_ids, $order['langtag']);
				$groups_translations   = self::getTranslatedTakeawayGroups($groups_ids, $order['langtag']);
				$toppings_translations = self::getTranslatedTakeawayToppings($toppings_ids, $order['langtag']);
				
				for ($i = 0; $i < count($order['items']); $i++)
				{
					$item =& $order['items'][$i];
					
					$item['name']        = self::translate($item['id'], $item, $entries_translations, "name", "name");
					$item['option_name'] = self::translate($item['id_option'], $item, $options_translations, "option_name", "name");
					
					for ($j = 0; $j < count($item['toppings_groups']); $j++)
					{
						$group =& $item['toppings_groups'][$j];
						
						$group['title'] = self::translate($group['id'], $group, $groups_translations, "title", "name");
						
						for ($k = 0; $k < count($group['toppings']); $k++)
						{
							$topping =& $group['toppings'][$k];
							
							$topping['name'] = self::translate($topping['id'], $topping, $toppings_translations, "name", "name");
						}
					}
				}

				// payment

				if (!empty($order['id_payment']) && $order['id_payment'] > 0)
				{
					$payments_translations = self::getTranslatedPayments(array($order['id_payment']), $order['langtag']);

					$order['payment_name'] = self::translate($order['id_payment'], $order, $payments_translations, 'payment_name', 'name');
					$order['payment_note'] = self::translate($order['id_payment'], $order, $payments_translations, 'payment_note', 'note');
					$order['payment_prenote'] = self::translate($order['id_payment'], $order, $payments_translations, 'payment_prenote', 'prenote');
				}

				// custom fields

				$custom_fields_original   = json_decode($order['custom_f'], true);
				$custom_fields_translated = array();

				foreach ($custom_fields_original as $k => $val)
				{
					$q = "SELECT `id` FROM `#__vikrestaurants_custfields` WHERE `name`= " . $dbo->q($k);
					
					$dbo->setQuery($q, 0, 1);
					$dbo->execute();
					
					if ($dbo->getNumRows())
					{
						$id = $dbo->loadResult();

						$translation = VikRestaurants::getTranslatedCustomFields(array($id), $order['langtag']);

						if (!empty($translation[$id]['name']))
						{
							$custom_fields_translated[$translation[$id]['name']] = $val;
						}
						else
						{
							$custom_fields_translated[$k] = $val;
						}
					}
				}

				$order['custom_f'] = json_encode($custom_fields_translated);
			}
			
			return $order;
		}

		return false;
	}

	/**
	 * Returns the plain e-mail template for admin notifications.
	 *
	 * @return 	string
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function loadTakeAwayAdminEmailTemplate()
	{
		defined('_VIKRESTAURANTSEXEC') or define('_VIKRESTAURANTSEXEC', '1');
		ob_start();
		include VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR . self::getTakeawayMailAdminTemplateName();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	/**
	 * Sends the e-mail notification to the admin.
	 *
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function sendAdminEmailTakeAway($order_details)
	{
		VRELoader::import('library.mail.factory');

		$mail = VREMailFactory::getInstance('takeaway', 'admin', $order_details['id']);

		if ($mail->shouldSend())
		{
			return $mail->send();
		}
	}
	
	/**
	 * Parses the contents of e-mail template.
	 *
	 * @param 	string 	$tmpl
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */
	public static function parseTakeAwayAdminEmailTemplate($tmpl, $order_details)
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('takeaway', 'admin', $order_details['id'])->getHtml();
	}
	
	/**
	 * Returns the plain e-mail template for customer notifications.
	 *
	 * @return 	string
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function loadTakeAwayEmailTemplate()
	{
		defined('_VIKRESTAURANTSEXEC') or define('_VIKRESTAURANTSEXEC', '1');
		ob_start();
		include VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR . self::getTakeawayMailTemplateName();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	/**
	 * Sends the e-mail notification to the customer.
	 *
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function sendCustomerEmailTakeAway($order_details)
	{
		VRELoader::import('library.mail.factory');

		$mail = VREMailFactory::getInstance('takeaway', 'customer', $order_details['id']);

		if ($mail->shouldSend())
		{
			return $mail->send();
		}
	}
	
	/**
	 * Parses the contents of e-mail template.
	 *
	 * @param 	string 	$tmpl
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */
	public static function parseTakeAwayEmailTemplate($tmpl, $order_details)
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('takeaway', 'customer', $order_details['id'])->getHtml();
	}

	/**
	 * Returns the plain e-mail template for customer cancellations.
	 *
	 * @return 	string
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function loadTakeAwayCancellationEmailTemplate()
	{
		defined('_VIKRESTAURANTSEXEC') or define('_VIKRESTAURANTSEXEC', '1');
		ob_start();
		include VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR . self::getTakeawayMailCancellationTemplateName();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	/**
	 * Sends the e-mail cancellation to the customer.
	 *
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function sendCancellationEmailTakeAway($order_details)
	{
		VRELoader::import('library.mail.factory');

		$mail = VREMailFactory::getInstance('takeaway', 'cancellation', $order_details['id']);

		if ($mail->shouldSend())
		{
			return $mail->send();
		}
	}
	
	/**
	 * Parses the contents of e-mail template.
	 *
	 * @param 	string 	$tmpl
	 * @param 	array 	$order_details
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */
	public static function parseTakeAwayCancellationEmailTemplate($tmpl, $order_details)
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('takeaway', 'cancellation', $order_details['id'])->getHtml();
	}

	/**
	 * Returns the details of the specified review.
	 *
	 * @param 	integer  $id        The review ID.
	 * @param 	string 	 $conf_key  The confirmation key.
	 *
	 * @return 	array
	 *
	 * @deprecated 	1.9 Use ReviewsHandler::getReview() instead.
	 */
	public static function fetchReview($id, $conf_key = '')
	{
		$handler = new ReviewsHandler();

		try
		{
			// get review
			$review = $handler->getReview($id, array('conf_key' => $conf_key));
		}
		catch (Exception $e)
		{
			return null;
		}

		// cast to to array for BC
		$review = (array) $review;

		// add columns alias for BC
		$review['takeaway_product_name']  = $review->productName;
		$review['takeaway_product_desc']  = $review->productDescription;
		$review['takeaway_product_image'] = $review->productImage;

		return $review;
	}

	/**
	 * Returns the plain e-mail template for review notifications.
	 *
	 * @return 	string
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */
	public static function loadTakeAwayReviewEmailTemplate()
	{
		defined('_VIKRESTAURANTSEXEC') or define('_VIKRESTAURANTSEXEC', '1');
		ob_start();
		include VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR . self::getTakeawayMailReviewTemplateName();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	/**
	 * Sends the e-mail review notification.
	 *
	 * @param 	array 	$review
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function sendReviewEmailTakeAway($review)
	{
		VRELoader::import('library.mail.factory');

		$mail = VREMailFactory::getInstance('takeaway', 'review', $review['id']);

		if ($mail->shouldSend())
		{
			return $mail->send();
		}
	}
	
	/**
	 * Parses the contents of e-mail template.
	 *
	 * @param 	string 	$tmpl
	 * @param 	array 	$review
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */
	public static function parseTakeAwayReviewEmailTemplate($tmpl, $review)
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('takeaway', 'review', $review['id'])->getHtml();
	}

	/**
	 * Sends a SMS notification to the customer/administrator according
	 * to the SMS APIs configuration settings.
	 *
	 * A notification e-mail is sent to the administrator in case the
	 * selected gateway fails while sending a message.
	 *
	 * @param 	string   $phone  The phone number of the customer. If not specified,
	 * 							 it will be recovered from the order detais.
	 * @param 	mixed 	 $order  Either the order details object or the order ID.
	 * @param 	integer  $group  The group to which the order belongs. Specify
	 * 							 0 for restaurant, 1 for take-away.
	 *
	 * @return 	boolean  True in case of successful notification, false otherwise.
	 *
	 * @since 	1.3
	 */
	public static function sendSmsAction($phone, $order, $group = 0)
	{
		$config = VREFactory::getConfig();

		/**
		 * Make sure the SMS can be sent for this group
		 * List of accepted statuses:
		 * - 0  Restaurant only;
		 * - 1  Take-Away only;
		 * - 2  Restaurant & Take-Away;
		 * - 3  Only manual.
		 */
		if (!in_array($config->getUint('smsapiwhen'), array($group, 2)))
		{
			// do not send automated messages for this group
			return false;
		}

		try
		{
			// get current SMS instance
			$smsapi = VREApplication::getInstance()->getSmsInstance();
		}
		catch (Exception $e)
		{
			// SMS framework not supported
			return false;
		}

		// retrieve order details in case an ID was passed
		if (is_scalar($order))
		{
			if ($group == 0)
			{
				// get restaurant reservation details
				$order = VREOrderFactory::getReservation($order);
			}
			else
			{
				// get take-away order details
				$order = VREOrderFactory::getOrder($order);
			}
		}

		if (!$order)
		{
			// invalid order
			return false;
		}

		$notified = 0;
		$errors   = array();
		$records  = array();

		// Make sure the customer can receive automated messages.
		// 0 for customer, 2 for customer & admin.
		if (in_array($config->getUint('smsapito'), array(0, 2)))
		{
			// get SMS notification message
			$message = VikRestaurants::getSmsCustomerTextMessage($order, $group);

			// missing phone number, try to use the one assigned to the order
			if (!$phone)
			{
				$phone = $order->purchase_phone;
			}

			if ($phone)
			{
				$records[] = array(
					'phone' => $phone,
					'text'  => $message,
				);
			}
		}

		// Make sure the administrator can receive automated messages.
		// 1 for admin, 2 for customer & admin.
		if (in_array($config->getUint('smsapito'), array(1, 2)))
		{
			// get SMS notification message
			$message = VikRestaurants::getSmsAdminTextMessage($order, $group);

			// get admin phone number
			$phone = $config->get('smsapiadminphone');

			if ($phone)
			{
				$records[] = array(
					'phone' => $phone,
					'text'  => $message,
				);
			}
		}

		// iterate messages to send
		foreach ($records as $sms)
		{
			// send message
			$response = $smsapi->sendMessage($sms['phone'], $sms['text']);

			// validate response
			if ($smsapi->validateResponse($response))
			{
				// successful notification
				$notified++;
			}
			else
			{
				// unable to send the notification, register error message
				$errors[] = $smsapi->getLog();
			}
		}

		if ($errors)
		{
			// send a notification e-mail to the administrator in case of error(s)
			self::sendAdminMailSmsFailed($errors);
		}
		
		return (bool) $notified;
	}

	/**
	 * Returns the notification message that should be sent via
	 * SMS to the customer of the given order/reservation.
	 *
	 * @param 	mixed    $order  The order/reservation details object.
	 * @param 	integer  $group  The section to notify (0: restaurant, 1: take-away).
	 *
	 * @return 	string   The parsed template message.
	 *
	 * @since 	1.3
	 */
	public static function getSmsCustomerTextMessage($order, $group = 0)
	{
		// use order lang tag
		$tag = $order->langtag;

		if (!$tag)
		{
			// no lang tag found, use the current one
			$tag = JFactory::getLanguage()->getTag();
		}

		if ($group == 0)
		{
			// load content for restaurant reservation
			$setting = 'smstmplcust';
		}
		else
		{
			// load content for take-away order
			$setting = 'smstmpltkcust';
		}

		// get JSON array from configuration
		$sms_map = VREFactory::getConfig()->getArray($setting);

		// make sure the SMS lookup specifies a template to
		// be used for the given language
		if (!empty($sms_map[$tag]))
		{
			// use template
			$sms = $sms_map[$tag];
		}
		else
		{
			// fallback to default template
			if ($group == 0)
			{
				// restaurant template
				$sms = JText::_('VRSMSMESSAGECUSTOMER');
			}
			else
			{
				// take-away template
				$sms = JText::_('VRSMSMESSAGETKCUSTOMER');
			}
		}

		// parse SMS template
		return self::parseContentSMS($order, $group, $sms);
	}

	/**
	 * Returns the notification message that should be sent via
	 * SMS to the administrator.
	 *
	 * @param 	mixed    $order  The order/reservation details object.
	 * @param 	integer  $group  The section to notify (0: restaurant, 1: take-away).
	 *
	 * @return 	string   The parsed template message.
	 *
	 * @since 	1.3
	 */
	public static function getSmsAdminTextMessage($order, $group = 0)
	{
		if ($group == 0)
		{
			// load content for restaurant reservation
			$setting = 'smstmpladmin';
		}
		else
		{
			// load content for take-away order
			$setting = 'smstmpltkadmin';
		}

		// get SMS template
		$sms = VREFactory::getConfig()->get($setting);
		
		if (empty($sms))
		{
			// fallback to default template
			if ($group == 0)
			{
				// restaurant template
				$sms = JText::_('VRSMSMESSAGEADMIN');
			}
			else
			{
				// take-away template
				$sms = JText::_('VRSMSMESSAGETKADMIN');
			}
		}
		
		// parse SMS template
		return self::parseContentSMS($order, $group, $sms);
	}
	
	/**
	 * Parses the SMS template to replace any placeholder with the related value.
	 *
	 * @param 	mixed    $order   The order/reservation details object.
	 * @param 	integer  $action  The section to notify (0: restaurant, 1: take-away).
	 * @param 	string   $sms     The template to parse.
	 *
	 * @return 	string   The parsed template message.
	 *
	 * @since 	1.3
	 */
	private static function parseContentSMS($order, $action = 0, $sms = '')
	{
		$config   = VREFactory::getConfig();
		$currency = VREFactory::getCurrency();

		$checkin_date_time  = date($config->get('dateformat') . ' ' . $config->get('timeformat'), $order->checkin_ts);
		$creation_date_time = date($config->get('dateformat') . ' ' . $config->get('timeformat'), $order->created_on);

		if ($action == 0)
		{
			// restaurant
			$sms = str_replace('{total_cost}', $currency->format($order->deposit), $sms);
			$sms = str_replace('{people}'    , $order->people                    , $sms);
		}
		else
		{
			// take-away
			$sms = str_replace('{total_cost}', $currency->format($order->total_to_pay), $sms);
		}

		// commons
		$sms = str_replace('{checkin}'   , $checkin_date_time          , $sms);
		$sms = str_replace('{created_on}', $creation_date_time         , $sms);
		$sms = str_replace('{company}'   , $config->get('restname')    , $sms);
		$sms = str_replace('{customer}'  , $order->purchaser_nominative, $sms);
		
		return $sms;
	}
	
	/**
	 * Sends a notification e-mail to the administrator(s) every
	 * time an error occurs while sending a SMS.
	 *
	 * @param 	mixed 	 $text   Either an array of messages or a string.
	 *
	 * @return 	boolean  True in case the notification was sent, false otherwise.
	 *
	 * @since 	1.3
	 */
	public static function sendAdminMailSmsFailed($text)
	{
		if (is_array($text))
		{
			// join messages, separated by an empty line
			$text = implode('<br /><br />', $text);
		}

		$config = VREFactory::getConfig();

		// get administrators e-mail
		$adminmails = self::getAdminMailList();
		// get sender e-mail address
		$sendermail = self::getSenderMail();
		// get restaurant name
		$fromname = $config->getString('restname');
		
		// fetch e-mail subject
		$subject = JText::sprintf('VRSMSFAILEDSUBJECT', $fromname);

		$vik = VREApplication::getInstance();

		$sent = false;
		
		// iterate e-mails to notify
		foreach ($adminmails as $recipient)
		{
			// send the e-mail notification
			$sent = $vik->sendMail($sendermail, $fromname, $recipient, $recipient, $subject, $text) || $sent;
		}

		return $sent;
	}

	/**
	 * Sends a notification e-mail to the administrator(s) every
	 * time an error occurs while trying to validate a payment.
	 *
	 * @param 	mixed 	 $text   Either an array of messages or a string.
	 *
	 * @return 	boolean  True in case the notification was sent, false otherwise.
	 *
	 * @since 	1.8
	 */
	public static function sendAdminMailPaymentFailed($text)
	{
		if (is_array($text))
		{
			// join messages, separated by an empty line
			$text = implode('<br /><br />', $text);
		}

		$config = VREFactory::getConfig();

		// get administrators e-mail
		$adminmails = self::getAdminMailList();
		// get sender e-mail address
		$sendermail = self::getSenderMail();
		// get restaurant name
		$fromname = $config->getString('restname');
		
		// fetch e-mail subject
		$subject = 'Invalid Payment Received - ' . $fromname;

		$vik = VREApplication::getInstance();

		$sent = false;
		
		// iterate e-mails to notify
		foreach ($adminmails as $recipient)
		{
			// send the e-mail notification
			$sent = $vik->sendMail($sendermail, $fromname, $recipient, $recipient, $subject, $text) || $sent;
		}

		return $sent;
	}
	
	/**
	 * Helper method used to refresh the deals that should be
	 * applied to the cart.
	 *
	 * @param 	TakeAwayCart  &$cart  The cart instance.
	 *
	 * @return 	boolean       True in case of deals, false otherwise.
	 *
	 * @since 	1.7
	 */
	public static function checkForDeals(&$cart)
	{
		self::loadDealsLibrary();
		// get all deals available for the current date and time
		$deals = DealsHandler::getAvailableFullDeals($cart);

		// prepare deals before application
		DealsHandler::beforeApply($cart);

		$applied = false;
		
		// apply deals only in case of active deals and added products
		if (count($deals) && $cart->getCartRealLength())
		{
			// iterate deals
			foreach ($deals as $deal)
			{
				/**
				 * Let the deals handler applies the offer.
				 *
				 * @since 1.8
				 */
				$applied = DealsHandler::apply($cart, $deal) || $applied;
			}
		}

		return $applied;
	}

	/**
	 * Resets the deals applied to the items within the cart.
	 * In addition checks whether there are some items that
	 * are no more available for the selected date and time.
	 *
	 * @param 	TakeAwayCart  &$cart 	The cart instance.
	 * @param 	mixed 		  $hourmin  The optional check-in time.
	 *
	 * @return 	void
	 *
	 * @since 	1.7
	 */
	public static function resetDealsInCart(&$cart, $hourmin = null)
	{
		$config = VREFactory::getConfig();

		$filters = array();
		$filters['date'] = date($config->get('dateformat'), $cart->getCheckinTimestamp());

		/**
		 * Try to recover check-in time from cart.
		 *
		 * @since 1.8
		 */
		if (is_null($hourmin))
		{
			$hourmin = $cart->getCheckinTime();
		}

		/**
		 * If specified, consider also the time when
		 * fetching the available menus.
		 *
		 * @since 1.8
		 */
		if ($hourmin)
		{
			$filters['hourmin'] = $hourmin;
		}

		// get list of available menus
		$menus = self::getAllTakeawayMenusOn($filters);
		
		$cart->deals()->emptyDiscounts();
		
		foreach ($cart->getItemsList() as $item)
		{
			// unset deal
			$item->setDealID(-1);
			$item->setPrice($item->getOriginalPrice());
			$item->setDealQuantity(0);
			$item->setRemovable(true);
			
			if (!in_array($item->getMenuID(), $menus))
			{
				// the item is not more available, unset it
				$item->setQuantity(0);
			}
		}
	}

	/**
	 * Calculate remaining availability in stock for the given product.
	 *
	 * @param 	integer  $eid 	 The product ID.
	 * @param 	integer  $oid 	 The variation ID (optional).
	 * @param 	integer  $index  The product of the database to ignore.
	 *
	 * @return 	integer  The remaining quantity (-1 if unlimited).
	 *
	 * @since 	1.7
	 */
	public static function getTakeawayItemRemainingInStock($eid, $oid = 0, $index = 0)
	{
		if (!self::isTakeAwayStockEnabled())
		{
			return -1;
		}

		$dbo = JFactory::getDbo();

		$eid = intval($eid);
		$oid = intval($oid);

		if ($oid > 0)
		{
			$q = $dbo->getQuery(true)
				->select($dbo->qn('stock_enabled'))
				->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option'))
				->where($dbo->qn('id') . ' = ' . $oid);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows() && !$dbo->loadResult())
			{
				// unset option ID as the stock should refer to the parent item
				$oid = 0;
			}
		}

		$where = '';
		
		if ($index > 0)
		{
			// exclude item stored in database
			$where = " AND `i`.`id` <> " . intval($index);
		}

		// build query used to retrieve items with low stocks
		$q = "SELECT
			IF(
				`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, 
				(
					IFNULL(
						(
							SELECT SUM(`so`.`items_available`) 
							FROM `#__vikrestaurants_takeaway_stock_override` AS `so` 
							WHERE `so`.`id_takeaway_entry`=`e`.`id` AND `so`.`id_takeaway_option` IS NULL
						), `e`.`items_in_stock`
					)
				), (
					IFNULL(
						(
							SELECT SUM(`so`.`items_available`) 
							FROM `#__vikrestaurants_takeaway_stock_override` AS `so` 
							WHERE `so`.`id_takeaway_entry` = `e`.`id` AND `so`.`id_takeaway_option` = `o`.`id`
						), `o`.`items_in_stock`
					)
				)
			) AS `products_in_stock`,

			IF(
				`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, 
				(
					IFNULL(
						(
							SELECT SUM(`i`.`quantity`)
							FROM `#__vikrestaurants_takeaway_reservation` AS `r` 
							LEFT JOIN `#__vikrestaurants_takeaway_res_prod_assoc` AS `i` ON `i`.`id_res` = `r`.`id`
							LEFT JOIN `#__vikrestaurants_takeaway_menus_entry_option` AS `io` ON `i`.`id_product_option` = `io`.`id`
							WHERE (`r`.`status` = 'CONFIRMED' OR `r`.`status` = 'PENDING') AND `i`.`id_product` = `e`.`id`
							AND (`o`.`id` IS NULL OR `io`.`stock_enabled` = 0)
							{$where}
						), 0
					)
				), (
					IFNULL(
						(
							SELECT SUM(`i`.`quantity`)
							FROM `#__vikrestaurants_takeaway_reservation` AS `r` 
							LEFT JOIN `#__vikrestaurants_takeaway_res_prod_assoc` AS `i` ON `i`.`id_res` = `r`.`id`
							WHERE (`r`.`status` = 'CONFIRMED' OR `r`.`status` = 'PENDING') AND `i`.`id_product` = `e`.`id` AND `i`.`id_product_option` = `o`.`id`
							{$where}
						), 0
					)
				)
			) AS `products_used`

			FROM
				`#__vikrestaurants_takeaway_menus_entry` AS `e`
			LEFT JOIN
				`#__vikrestaurants_takeaway_menus_entry_option` AS `o` ON `e`.`id` = `o`.`id_takeaway_menu_entry`
			LEFT JOIN
				`#__vikrestaurants_takeaway_menus` AS `m` ON `m`.`id`=`e`.`id_takeaway_menu` 
			WHERE
				`e`.`id` = {$eid}";

		if ($oid > 0)
		{
			$q .= " AND `o`.`id` = {$oid}";
		}
		else
		{
			// do not take option with self stock
			$q .= " AND (`o`.`id` IS NULL OR `o`.`stock_enabled` = 0)";
		}

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$row = $dbo->loadObject();

			return (int) $row->products_in_stock - (int) $row->products_used;
		}

		return 0;
	}

	/**
	 * Checks whether all the ordered products are still
	 * available for the purchase. In case the stock of one
	 * or more items is no more available, an error message
	 * will be enqueued and the missing units will be removed
	 * from the cart instance.
	 *
	 * @param 	TakeAwayCart  &$cart  The current user cart.
	 *
	 * @return 	boolean       False in case something has been removed
	 * 						  from the cart.
	 *
	 * @since 	1.7
	 */
	public static function checkCartStockAvailability(&$cart)
	{
		if (!self::isTakeAwayStockEnabled())
		{
			// do not go ahead in case the stock system is disabled
			return true;
		}

		$ok = true;

		// iterate ordered items
		foreach ($cart->getItemsList() as $item)
		{
			// find remaining units of the current item/variation
			$in_stock = self::getTakeawayItemRemainingInStock($item->getItemID(), $item->getVariationID(), -1);

			// get item/variation ordered units
			$stock_item_quantity = $cart->getQuantityItems($item->getItemID(), $item->getVariationID());
		
			if ($in_stock - $stock_item_quantity < 0)
			{
				// there are not enough units available, remove missing
				// ones from the user cart
				$removed_items = $stock_item_quantity - $in_stock;
				$item->remove($removed_items);

				if ($stock_item_quantity == $removed_items)
				{
					// no more items, all the units have been removed from the cart
					JFactory::getApplication()->enqueueMessage(JText::sprintf('VRTKSTOCKNOITEMS', $item->getFullName()), 'error');
				}
				else
				{
					// only some units have been removed from the cart
					JFactory::getApplication()->enqueueMessage(JText::sprintf('VRTKSTOCKREMOVEDITEMS', $item->getFullName(), $removed_items), 'warning');
				}

				$ok = false;
			}
		}

		return $ok;
	}

	/**
	 * Returns the plain e-mail template for stock notifications.
	 *
	 * @return 	string
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */
	public static function loadTakeAwayStockEmailTemplate()
	{
		defined('_VIKRESTAURANTSEXEC') or define('_VIKRESTAURANTSEXEC', '1');
		ob_start();
		include VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR.self::getTakeawayStockMailTemplateName();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Sends the e-mail stocks notification.
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */	
	public static function notifyAdminLowStocks()
	{
		VRELoader::import('library.mail.factory');

		$mail = VREMailFactory::getInstance('takeaway', 'stock');

		if ($mail->shouldSend())
		{
			return $mail->send();
		}
	}

	/**
	 * Parses the contents of e-mail template.
	 *
	 * @param 	string 	$tmpl
	 * @param 	array 	$list
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 	Use VREMailFactory instead.
	 */
	public static function parseTakeAwayStockEmailTemplate($tmpl, $list)
	{
		VRELoader::import('library.mail.factory');

		return VREMailFactory::getInstance('takeaway', 'stock')->getHtml();
	}

	/**
	 * Returns a full address string based on the specified delivery details.
	 * The address is built by following this structure:
	 * [ADDRESS] [ADDRESS_2], [ZIP] [CITY] [STATE], [COUNTRY]
	 *
	 * @param 	mixed 	$address   Either an object or an array containing the address details.
	 * @param 	array 	$excluded  A list of properties to exclude while creating the address.
	 *
	 * @return 	string 	The full address.
	 *
	 * @since 	1.7
	 */
	public static function deliveryAddressToStr($address, array $excluded = array())
	{
		// always treat the address as an array
		$address = (array) $address;

		$str = array();

		// route + street number
		$app = array();

		if (!empty($address['address']) && !in_array('address', $excluded))
		{
			$app[] = trim($address['address']);
		}

		// info address
		if (!empty($address['address_2']) && !in_array('address_2', $excluded))
		{
			$app[] = trim($address['address_2']);
		}

		// insert first block
		if ($app)
		{
			$str[] = implode(' ', $app);
		}

		// zip
		$app = array();

		if (!empty($address['zip']) && !in_array('zip', $excluded))
		{
			$app[] = trim($address['zip']);
		}

		// city
		if (!empty($address['city']) && !in_array('city', $excluded))
		{
			$app[] = trim($address['city']);
		}

		// state
		if (!empty($address['state']) && !in_array('state', $excluded))
		{
			$app[] = trim($address['state']);
		}

		// insert second block
		if ($app)
		{
			$str[] = implode(' ', $app);
		}

		// country name or country code
		if (!empty($address['country']) && !in_array('country', $excluded))
		{
			$str[] = !empty($address['country_name']) ? $address['country_name'] : $address['country'];
		}

		// join fetched address parts
		return implode(', ', $str);
	}

	/**
	 * Compares 2 addresses to check if they are equals.
	 *
	 * @param 	array 	 $addr 	The associative array containing the
	 * 							address details fetched by VikRestaurants.
	 * @param 	array 	 $resp 	The associative array containing the
	 * 							address details fetched by Google.
	 *
	 * @return 	boolean  True if equals, otherwise false.
	 *
	 * @since 	1.7.4
	 */
	public static function compareAddresses($addr, $resp)
	{
		/**
		 * When specified, try to calculate the distance between
		 * the coordinates and evaluate whether they are so close
		 * to be considered the same address.
		 *
		 * @since 1.8.3
		 */
		if (!empty($addr['latitude']) && !empty($addr['longitude']) && !empty($resp['lat']) && !empty($resp['lng']))
		{
			// load geometry helper
			self::loadGraphics2D();

			// create a circle with center at the address latitude and longitude
			// and with a radius of 1 meter
			$circle = new Circle(0.001, $addr['latitude'], $addr['longitude']);
			// create a point with coordinates equals to the searched address
			$point = new Point($resp['lat'], $resp['lng']);

			// check whether the 2 points have a distance equals or lower
			// than a meter, which means that we have the same address at
			// least for 99% of the times 
			$intersect = Geom::isPointInsideCircleOnEarth($circle, $point);

			if ($intersect)
			{
				// we have the same address
				return true;
			}
		}

		// create a relationship between the $addr
		// columns (array keys) and the $resp columns (array values)
		$lookup = array(
			'country' => 'country',
			'state'   => 'state',
			'city'    => 'city',
			'zip'     => 'zip',
			'address' => 'street',
		);

		// iterate lookup
		foreach ($lookup as $k_addr => $k_resp)
		{
			// get $addr value
			$val_1 = isset($addr[$k_addr]) ? $addr[$k_addr] : '';

			// get $resp value
			$val_2 = isset($resp[$k_resp]) ? $resp[$k_resp] : '';

			if (is_array($val_2))
			{
				// implode values
				$val_2 = implode(' ', array_values($val_2));
			}

			// replace any commas from the values
			$val_1 = preg_replace("/[,]/", '', $val_1);
			$val_2 = preg_replace("/[,]/", '', $val_2);

			// compare values
			if (strcasecmp(trim($val_1), trim($val_2)))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the details of the given customer.
	 *
	 * @param 	mixed  $id  The customer ID. If not specified,
	 * 						the customer assigned to the current
	 * 						user will be retrieved, if any.
	 *
	 * @return 	mixed  The customer object if exists, NULL otherwise
	 *
	 * @since 	1.4
	 */
	public static function getCustomer($id = null)
	{
		$jid = null;

		if (is_null($id))
		{
			// get current CMS user
			$user = JFactory::getUser();

			// make sure the user is not a guest
			if ($user->guest)
			{
				return null;
			}

			// get CMS user ID
			$jid = $user->id;
		}
		else
		{
			$id = (int) $id;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);
		
		// get customer details
		$q->select('c.*');
		$q->from($dbo->qn('#__vikrestaurants_users', 'c'));

		// get billing country name
		$q->select($dbo->qn('country.country_name'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_countries', 'country') . ' ON ' . $dbo->qn('country.country_2_code') . ' = ' . $dbo->qn('c.country_code'));

		// get CMS user details
		$q->select($dbo->qn('u.name', 'user_name'));
		$q->select($dbo->qn('u.username', 'user_username'));
		$q->select($dbo->qn('u.email', 'user_email'));
		$q->leftjoin($dbo->qn('#__users', 'u') . ' ON ' . $dbo->qn('u.id') . ' = ' . $dbo->qn('c.jid'));

		// get delivery locations
		$q->select($dbo->qn('d.id', 'delivery_id'));
		$q->select($dbo->qn('d.country', 'delivery_country'));
		$q->select($dbo->qn('d.state', 'delivery_state'));
		$q->select($dbo->qn('d.city', 'delivery_city'));
		$q->select($dbo->qn('d.address', 'delivery_address'));
		$q->select($dbo->qn('d.address_2', 'delivery_address_2'));
		$q->select($dbo->qn('d.zip', 'delivery_zip'));
		$q->select($dbo->qn('d.latitude', 'delivery_latitude'));
		$q->select($dbo->qn('d.longitude', 'delivery_longitude'));
		$q->select($dbo->qn('d.type', 'delivery_type'));
		$q->select($dbo->qn('d.note', 'delivery_note'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_user_delivery', 'd') . ' ON ' . $dbo->qn('c.id') . ' = ' . $dbo->qn('d.id_user'));

		// get location country name
		$q->select($dbo->qn('country2.country_name', 'delivery_country_name'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_countries', 'country2') . ' ON ' . $dbo->qn('country2.country_2_code') . ' = ' . $dbo->qn('d.country'));

		if (is_null($id))
		{
			// get customer by CMS user
			$q->where($dbo->qn('u.id') . ' = ' . $jid);
		}
		else
		{
			// get customer by ID
			$q->where($dbo->qn('c.id') . ' = ' . $id);
		}

		$q->order($dbo->qn('d.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// no matching customers
			return null;
		}

		$app = $dbo->loadObjectList();

		$customer = new stdClass;
		$customer->id                = $app[0]->id;
		$customer->billing_name      = $app[0]->billing_name;
		$customer->billing_mail      = $app[0]->billing_mail;
		$customer->billing_phone     = $app[0]->billing_phone;
		$customer->country_code      = $app[0]->country_code;
		$customer->country           = $app[0]->country_name;
		$customer->billing_state     = $app[0]->billing_state;
		$customer->billing_city      = $app[0]->billing_city;
		$customer->billing_address   = $app[0]->billing_address;
		$customer->billing_address_2 = $app[0]->billing_address_2;
		$customer->billing_zip       = $app[0]->billing_zip;
		$customer->company           = $app[0]->company;
		$customer->vatnum            = $app[0]->vatnum;
		$customer->ssn               = $app[0]->ssn;
		$customer->notes             = $app[0]->notes;
		$customer->image             = $app[0]->image;

		$customer->fields = new stdClass;
		$customer->fields->restaurant = (array) json_decode($app[0]->fields, true);
		$customer->fields->takeaway   = (array) json_decode($app[0]->tkfields, true); 

		$customer->user = new stdClass;
		$customer->user->id       = $app[0]->jid;
		$customer->user->name     = $app[0]->user_name;
		$customer->user->username = $app[0]->user_username;
		$customer->user->email    = $app[0]->user_email;

		$customer->locations = array();

		foreach ($app as $d)
		{
			if (!empty($d->delivery_address))
			{
				$addr = new stdClass;
				$addr->id          = $d->delivery_id;
				$addr->country     = $d->delivery_country;
				$addr->countryName = $d->delivery_country_name;
				$addr->state       = $d->delivery_state;
				$addr->city        = $d->delivery_city;
				$addr->address     = $d->delivery_address;
				$addr->address_2   = $d->delivery_address_2;
				$addr->zip         = $d->delivery_zip;
				$addr->type        = $d->delivery_type;
				$addr->note        = $d->delivery_note;
				$addr->latitude    = $d->delivery_latitude;
				$addr->longitude   = $d->delivery_longitude;

				$customer->locations[] = $addr;
			}
		}

		return $customer;
	}

	/**
	 * Extracts the first name and last name from the user address
	 * and pre-fill the custom fields, in case they are empty.
	 *
	 * @param 	array 	 $cf       A list of custom fields.
	 * @param 	array 	 &$fields  Where to inject the fetched data.
	 * @param 	boolean  $first    True whether the first name is usually
	 * 							   specified before the last name.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public static function extractNameFields(array $cf, array &$fields, $first = true)
	{
		$user = JFactory::getUser();

		if ($user->guest || !$user->name)
		{
			// user not logged-in, doesn't go ahead
			return;
		}

		// get all custom fields that can be used as nominative
		$tmp = VRCustomFields::findField(array('rule', VRCustomFields::NOMINATIVE), $cf);

		if (!$tmp)
		{
			// no fields found, doesn't go ahead
			return;
		}

		// check if we have only one field
		if (count($tmp) == 1)
		{
			// we have a generic nominative, use the full name
			$fields[$tmp[0]['name']] = $user->name;
		}
		else
		{
			// get name chunks
			$chunks = preg_split("/\s+/", $user->name);

			// extract last name from the list
			$lname = array_pop($chunks);
			// join remaining chunks into the first name
			$fname = implode(' ', $chunks);

			if (!$fname)
			{
				// first name missing, switch with last name because
				// the customers usually writes the first name instead
				// of the last name
				$fname = $lname;
				$lname = '';
			}

			if ($first)
			{
				// show first name and last name
				$fields[$tmp[0]['name']] = $fname;
				$fields[$tmp[1]['name']] = $lname;
			}
			else
			{
				// show last name and first name
				$fields[$tmp[0]['name']] = $lname;
				$fields[$tmp[1]['name']] = $fname;	
			}
		}
	}

	/**
	 * Fetches an associative array containing the value that each
	 * custom field of "address" type could assume. In case an address
	 * has been already validated (e.g. through the MAP module), the
	 * fetched parts will be retrieved and assigned to the related field
	 * in order to perfectly fit a valid address.
	 *
	 * @param 	array 	$cf       A list of custom fields.
	 * @param 	array 	&$fields  Where to inject the fetched data.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public static function extractAddressFields(array $cf, array &$fields)
	{
		// get latest searched address
		$delivery_address_object = JFactory::getSession()->get('delivery_address', null, 'vre');

		$components = array(
			'address' => '',
			'city'    => '',
			'zip'     => '',
		);

		if ($delivery_address_object && $delivery_address_object->address)
		{
			$parts = $delivery_address_object->address;

			// start from base address
			if (!empty($parts['street']))
			{
				$components['address'] = trim($parts['street']['name'] . ' ' . $parts['street']['number']);
			}

			// fetch ZIP code
			$components['zip'] = !empty($parts['zip']) ? $parts['zip'] : '';

			// fetch city
			$components['city'] = !empty($parts['city']) ? $parts['city'] : '';
			
			if (!$components['city'])
			{
				// try with the state
				$components['city'] = !empty($parts['state']) ? $parts['state'] : '';
			}

			if ($components['city'])
			{
				// find city field
				$cityField = VRCustomFields::findField(array('rule', VRCustomFields::CITY), $cf, $lim = 1);

				if ($cityField)
				{
					// register city value for custom field
					$fields[$cityField['name']] = $components['city'];
				}
				else
				{
					// city field not found, add city to base address
					$components['address'] .= ', ' . $components['city'];
				}
			}

			if ($components['zip'])
			{
				// find ZIP field
				$zipField = VRCustomFields::findField(array('rule', VRCustomFields::ZIP), $cf, $lim = 1);

				if ($zipField)
				{
					// register ZIP value for custom field
					$fields[$zipField['name']] = $components['zip'];
				}
				else
				{
					// ZIP field not found, add ZIP to base address
					$components['address'] .= ', ' . $components['zip'];
				}
			}

			if ($components['address'])
			{
				// find address field
				$addrField = VRCustomFields::findField(array('rule', VRCustomFields::ADDRESS), $cf, $lim = 1);

				if ($addrField)
				{
					// register address value for custom field
					$fields[$addrField['name']] = $components['address'];
				}
			}
		}
	}

	/**
	 * Checks whether the current user is able to leave a review
	 * for the specified take-away product.
	 *
	 * @param 	integer  $id_product  The product ID.
	 *
	 * @return 	boolean  True if possible, false otherwise.
	 *
	 * @since 	1.7
	 */
	public static function canLeaveTakeAwayReview($id_product)
	{
		$dbo  = JFactory::getDbo();
		$user = JFactory::getUser();

		// get leave review mode:
		// - 0 	anyone
		// - 1  registered user
		// - 2  verified purchase
		$mode = self::getReviewsLeaveMode();

		if ($mode > 0)
		{
			// user must be logged in
			if ($user->guest)
			{
				return false;
			}
		}

		$id_product = (int) $id_product;

		// check if the user already left a review
		if (self::isAlreadyTakeAwayReviewed($id_product, $user->id))
		{
			// the user already wrote a review
			return false;
		}

		if ($mode != 2)
		{
			return true;
		}

		// make sure the user is a verified purchaser by checking whether
		// the date of the purchase of this product exists and it is in the past
		if (self::isVerifiedTakeAwayReview($id_product, $user))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks whether the specified product has been
	 * already reviewed by the user.
	 * 
	 * @param 	integer  $id_product  The product to look for.
	 * @param 	integer  $id_user     The CMS user ID. If not provided, the current one
	 * 								  will be used.
	 *
	 * @return 	boolean  True if already rated, false otherwise.
	 *
	 * @since 	1.7
	 */
	public static function isAlreadyTakeAwayReviewed($id_product, $id_user = null)
	{
		$dbo = JFactory::getDbo();

		if ($id_user === null)
		{
			// take current user
			$id_user = JFactory::getUser()->id;
		}

		// get user IP address
		$ip_addr = JFactory::getApplication()->input->server->get('REMOTE_ADDR');

		$q = $dbo->getQuery(true)
			->select(1)
			->from($dbo->qn('#__vikrestaurants_reviews'))
			->where($dbo->qn('id_takeaway_product') . ' = ' . (int) $id_product);

		if ($id_user > 0)
		{
			// search by user ID
			$q->where($dbo->qn('jid') . ' = ' . (int) $id_user);
		}
		else
		{
			// search by IP address
			$q->where($dbo->qn('ipaddr') . ' = ' . $dbo->q($ip_addr));
		}

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		return (bool) $dbo->getNumRows();
	}

	/**
	 * Checks whether the specified user made a purchase in the past
	 * for the selected take-away product. Needed to check when
	 * the leave review mode is set to 2: verified purchaser.
	 *
	 * @param 	integer  $id_product  The product to look for.
	 * @param 	mixed 	 $user 		  Either the user id or an object. If not
	 * 								  specified, the current user will be taken.
	 *
	 * @return 	boolean  True if verified purchaser, false otherwise.
	 *
	 * @since 	1.7
	 */
	public static function isVerifiedTakeAwayReview($id_product, $user = null)
	{
		if (is_null($user))
		{
			// take current user
			$user = JFactory::getUser();	
		}
		else if (is_scalar($user))
		{
			// get specified user
			$user = JFactory::getUser($user);
		}

		if ($user->guest)
		{
			// guest user cannot be a verified purchaser
			return false;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select(1)
			->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_res_prod_assoc', 'i') . ' ON ' . $dbo->qn('r.id') . ' = ' . $dbo->qn('i.id_res'))
			->leftjoin($dbo->qn('#__vikrestaurants_users', 'u') . ' ON ' . $dbo->qn('r.id_user') . ' = ' . $dbo->qn('u.id'))
			->where($dbo->qn('u.jid') . ' = ' . $user->id)
			->where($dbo->qn('r.status') . ' = ' . $dbo->q('CONFIRMED'))
			->where($dbo->qn('i.id_product') . ' = ' . (int) $id_product)
			->where($dbo->qn('r.checkin_ts') . ' < ' . static::now());

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		return (bool) $dbo->getNumRows();
	}

	// ORDER STATUS

	/**
	 * Creates or updates the given order status.
	 * An order cannot have 2 or more statuses with the same code.
	 *
	 * @param 	integer  $oid 		The order ID.
	 * @param 	integer  $code_id 	The code ID.
	 * @param 	integer  $group 	The group type (1: restaurants, 2: take-away).
	 * @param 	mixed 	 $notes 	Some notes about the order status.
	 * 								If null, they won't be altered in case of update.
	 *
	 * @return 	integer  The ID of the order status.
	 *
	 * @since 	1.7
	 */
	public static function insertOrderStatus($oid, $code_id, $group, $notes = null)
	{
		if ($code_id <= 0)
		{
			return null;
		}

		$dbo = JFactory::getDbo();

		$oid 		= intval($oid);
		$code_id 	= intval($code_id);
		$group 		= ($group == 1 ? 1 : 2);

		// check if we have an order status with the specified code
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_order_status'))
			->where($dbo->qn('id_order') . ' = ' . $oid)
			->where($dbo->qn('id_rescode') . ' = ' . $code_id)
			->where($dbo->qn('group') . ' = ' . $group);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		$data = new stdClass;
		$data->id_order   = $oid;
		$data->id_rescode = $code_id;
		$data->createdby  = JFactory::getUser()->id;
		$data->createdon  = static::now();
		$data->group 	  = $group;
		
		if ($notes !== null)
		{
			$data->notes = $notes;
		}

		if ($dbo->getNumRows())
		{
			// update
			$data->id = $dbo->loadResult();

			$dbo->updateObject('#__vikrestaurants_order_status', $data, 'id');
		}
		else
		{
			// insert
			$dbo->insertObject('#__vikrestaurants_order_status', $data, 'id');
		}

		return $data->id;
	}

	/**
	 * Returns the order status history.
	 *
	 * @param 	integer  $oid
	 * @param 	string 	 $sid
	 * @param 	integer  $group
	 *
	 * @return 	array
	 */
	public static function getOrderStatusList($oid, $sid, $group)
	{
		$dbo = JFactory::getDbo();

		$oid 	= intval($oid);
		$group 	= ($group == 1 ? 1 : 2);

		$q = "SELECT `os`.*, `rc`.`code`, `rc`.`notes` AS `code_notes`, `rc`.`icon` 
		FROM `#__vikrestaurants_order_status` AS `os`
		LEFT JOIN `#__vikrestaurants_res_code` AS `rc` ON `os`.`id_rescode`=`rc`.`id` 
		WHERE `os`.`id_order`=$oid AND `os`.`group`=$group ORDER BY `os`.`createdon` ASC;";

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$status = $dbo->loadAssocList();

			$table = ($group == 1 ? '#__vikrestaurants_reservation' : '#__vikrestaurants_takeaway_reservation');

			$q = "SELECT `sid` FROM `$table` WHERE `id`=$oid";

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				if ($dbo->loadResult() == $sid)
				{
					return $status;
				}
			}
		}

		return null;
	}

	/**
	 * Loads the Graphics2D dependencies.
	 *
	 * @return 	void
	 *
	 * @since 	1.7
	 */
	public static function loadGraphics2D()
	{
		VRELoader::import('library.graphics2d.graphics2d');
	}

	/**
	 * Checks whether the system supports certain types of
	 * delivery areas.
	 *
	 * @param 	array 	 $types  A list of delivery types. If not specified,
	 * 							 all the types will be retrieved.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 *
	 * @since 	1.7
	 */
	public static function hasDeliveryAreas(array $types = array())
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select(1)
			->from($dbo->qn('#__vikrestaurants_takeaway_delivery_area'))
			->where($dbo->qn('published') . ' = 1');

		if (count($types))
		{
			$types = array_map('intval', $types);

			$q->where($dbo->qn('type') . ' IN (' . implode(',', $types) . ')');
		}

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		return (bool) $dbo->getNumRows();
	}

	/**
	 * Returns a list of delivery areas.
	 *
	 * @param 	boolean  $published  True to obtain only the published areas.
	 *
	 * @return 	array 	 A list of areas.
	 *
	 * @since 	1.7
	 */
	public static function getAllDeliveryAreas($published = false)
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_takeaway_delivery_area'))
			->order($dbo->qn('ordering') . ' ASC');

		if ($published)
		{
			$q->where($dbo->qn('published') . ' = 1');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		$areas = array();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadAssocList() as $r)
			{
				$r['content']    = json_decode($r['content']);
				$r['attributes'] = json_decode($r['attributes']);

				$elem = null;

				if ($r['type'] == 1)
				{
					// build polygon area
					$elem = self::parsePolygonDeliveryArea($r);
				}
				else if ($r['type'] == 2)
				{
					// build circle area
					$elem = self::parseCircleDeliveryArea($r);
				}
				else if ($r['type'] == 3)
				{
					// build ZIP area
					$elem = self::parseZipDeliveryArea($r);
				}
				else if ($r['type'] == 4)
				{
					// build CITY area
					$elem = self::parseCityDeliveryArea($r);
				}

				if ($elem !== null)
				{
					// add area only if supported
					$areas[] = $elem;
				}
			}
		}

		/**
		 * For site client only, filter the delivery areas according
		 * to the configuration of the special days.
		 *
		 * @since 1.8.2
		 */
		if ($app->isClient('site'))
		{
			// get cart instance
			VikRestaurants::loadCartLibrary();
			$cart = TakeAwayCart::getInstance();

			// get check-in date
			$date = $cart->getCheckinTimestamp();
			// get check-in time
			$time = $cart->getCheckinTime($first = true);

			// init special days manager
			$sdManager = new VRESpecialDaysManager('takeaway');
			// set checkin date
			$sdManager->setStartDate($date);

			if ($time)
			{
				// explode hour and minute
				list($hour, $min) = explode(':', $time);
				// set checkin time
				$sdManager->setCheckinTime($hour, $min);
			}

			// get special days
			$sdList = $sdManager->getList();

			$accepted = array();

			// iterate all special days found
			foreach ($sdList as $sd)
			{
				// add accepted areas
				$accepted = array_merge($accepted, $sd->deliveryAreas);
			}

			if ($accepted)
			{
				$tmp = $areas;

				// remove duplicated areas
				$accepted = array_unique($accepted);

				// filter the areas according to the available ones
				$areas = array_filter($areas, function($a) use($accepted)
				{
					// make sure the area is accepted
					return in_array($a['id'], $accepted);
				});

				if ($areas)
				{
					// reset array keys
					$areas = array_values($areas);
				}
				else
				{
					// No delivery areas found, probably the areas assigned
					// to the special day have been deleted. Take all the
					// global areas.
					$areas = $tmp;
				}
			}
		}

		return $areas;
	}

	/**
	 * Parses a Polygon delivery area.
	 *
	 * @param 	array 	$a  The delivery area to parse.
	 *
	 * @return 	array 	The parsed area.
	 *
	 * @since 	1.7
	 */
	public static function parsePolygonDeliveryArea($a)
	{
		$polygon = new Polygon();

		if (!is_array($a['content']))
		{
			return null;
		}

		// iterate all the added coordinates
		foreach ($a['content'] as $p)
		{
			// make sure we have valid coordinates
			if (isset($p->latitude) && isset($p->longitude))
			{
				// add a vertex to the polygon
				$polygon->addPoint(
					new Point(
						floatval($p->longitude),
						floatval($p->latitude)
					)
				);
			}
		}

		// make sure we have a valid polygon
		if ($polygon->getNumPoints() < 3)
		{
			return null;
		}

		// add polygon to the area
		$a['shape'] = $polygon;

		return $a;
	}

	/**
	 * Parses a Circle delivery area.
	 *
	 * @param 	array 	$a  The delivery area to parse.
	 *
	 * @return 	array 	The parsed area.
	 *
	 * @since 	1.7
	 */
	public static function parseCircleDeliveryArea($a)
	{
		// validate circle
		if (!isset($a['content']->center->latitude) || !isset($a['content']->center->longitude) || !isset($a['content']->radius))
		{
			return null;
		}

		// create circle shape
		$a['shape'] = new Circle(
			floatval($a['content']->radius),
			floatval($a['content']->center->longitude),
			floatval($a['content']->center->latitude)
		);

		return $a;
	}

	/**
	 * Parses a ZIP delivery area.
	 *
	 * @param 	array 	$a  The delivery area to parse.
	 *
	 * @return 	array 	The parsed area.
	 *
	 * @since 	1.7
	 */
	public static function parseZipDeliveryArea($a)
	{
		if (!is_array($a['content']))
		{
			return null;
		}

		$a['shape'] = array();

		// create ZIP intervals
		foreach ($a['content'] as $range)
		{
			if (isset($range->from) && isset($range->to))
			{
				$a['shape'][] = $range;
			}
		}

		if (!count($a['shape']))
		{
			return null;
		}

		return $a;
	}

	/**
	 * Parses a CITY delivery area.
	 *
	 * @param 	array 	$a  The delivery area to parse.
	 *
	 * @return 	array 	The parsed area.
	 *
	 * @since 	1.8.2
	 */
	public static function parseCityDeliveryArea($a)
	{
		if (!is_array($a['content']))
		{
			return null;
		}

		// assign content array to shape attribute
		$a['shape'] = $a['content'];

		if (!count($a['shape']))
		{
			return null;
		}

		return $a;
	}

	/**
	 * Returns the delivery area that matches the specified coordinates at best.
	 *
	 * @param 	mixed 	$lat   The latitude.
	 * @param 	mixed 	$lng   The longitude.
	 * @param 	mixed 	$zip   The ZIP code.
	 * @param 	mixed 	$city  The city name.
	 *
	 * @return 	mixed   The matching area on success, null otherwise.
	 *
	 * @since 	1.7
	 */
	public static function getDeliveryAreaFromCoordinates($lat = null, $lng = null, $zip = null, $city = null)
	{
		// load shapes helpers
		self::loadGraphics2D();

		// get all delivery areas (only published)
		$areas = self::getAllDeliveryAreas(true);

		foreach ($areas as $a)
		{
			if ($a['type'] == 1)
			{
				if ($lat !== null && $lng !== null)
				{	
					// check whether the specified coordinates belong to the polygon
					if (Geom::isPointInsidePolygon($a['shape'], new Point($lng, $lat), Geom::WINDING_NUMBER))
					{
						return $a;
					}
				}
			}
			else if ($a['type'] == 2)
			{
				if ($lat !== null && $lng !== null)
				{
					// check whether the specified coordinates belong to the circle
					if (Geom::isPointInsideCircleOnEarth($a['shape'], new Point($lng, $lat)))
					{
						return $a;
					}
				}
			}
			else if ($a['type'] == 3)
			{
				if ($zip !== null && strlen($zip))
				{	
					// check whether the specified ZIP is accepted by the area
					foreach ($a['shape'] as $range)
					{
						if ($range->from <= $zip && $zip <= $range->to)
						{
							return $a;
						}
					}
				}
			}
			else if ($a['type'] == 4)
			{
				/**
				 * Added support for "city" area type validation.
				 *
				 * @since 1.8.2
				 */
				if ($city !== null && strlen($city))
				{	
					// check whether the specified CITY is accepted by the area
					foreach ($a['shape'] as $tmp)
					{
						if (!strcasecmp($city, $tmp))
						{
							return $a;
						}
					}
				}
			}
		}

		return null;
	}

	// INVOICE

	public static function loadFrameworkPDF()
	{
		return VRELoader::import('pdf.tcpdf.tcpdf');
	}

	public static function getInvoiceObject()
	{
		$obj = self::getFieldFromConfig('invoiceobj', 'vrGetInvoiceObject', true);

		VRELoader::import('pdf.constraints');

		if (!strlen($obj))
		{
			$obj = new stdClass;

			$obj->params = new stdClass;
			$obj->params->number 	= 1;
			$obj->params->suffix 	= date('Y');
			$obj->params->datetype 	= 1;
			$obj->params->legalinfo = '';

			$obj->constraints = new VikRestaurantsConstraintsPDF;
		}
		else
		{
			$obj = json_decode($obj);
		}

		return $obj;
	}

	public static function buildInvoiceObject($prop)
	{
		$obj = self::getInvoiceObject();

		$obj->params->number 	= $prop['number'][0];
		$obj->params->suffix 	= $prop['number'][1];
		$obj->params->datetype 	= $prop['datetype'];
		$obj->params->legalinfo = $prop['legalinfo'];

		$obj->constraints->pageOrientation 	= $prop['pageorientation'];
		$obj->constraints->pageFormat 		= $prop['pageformat'];
		$obj->constraints->unit 			= $prop['unit'];
		$obj->constraints->imageScaleRatio 	= $prop['scale'];

		return $obj;
	}

	public static function storeInvoiceObject($obj) {
		$dbo = JFactory::getDbo();

		$q = "UPDATE `#__vikrestaurants_config` SET `setting`=".$dbo->quote(json_encode($obj))." WHERE `param`='invoiceobj' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();

		return $dbo->getAffectedRows();
	}

	public static function getOrderInvoice($oid, $group = 0, $dbo = null) {
		if( $dbo === null ) {
			$dbo = JFactory::getDbo();
		}

		$oid 	= intval($oid);
		$group 	= intval($group);

		$q = "SELECT * FROM `#__vikrestaurants_invoice` WHERE `id_order`=$oid AND `group`=$group LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();

		if( $dbo->getNumRows() ) {
			return $dbo->loadAssoc();
		}

		return null;
	}

	public static function generateInvoicePDF($order_details, $group, $obj = null) {
		if( $obj === null ) {
			$obj = self::getInvoiceObject();
		}

		$path_pdf = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikrestaurants'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'archive'.DIRECTORY_SEPARATOR.($group == 1 ? 'tk-' : '').$order_details['id']."-".$order_details['sid'].".pdf";
		
		if( file_exists($path_pdf) ) @unlink($path_pdf); // unlink pdf if exists
		
		$usepdffont = 'courier';
		if( file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikrestaurants'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'tcpdf'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.'dejavusans.php') ) {
			$usepdffont = 'dejavusans';    
		}

		if( !class_exists('TCPDF') ) {
			self::loadFrameworkPDF();
		}
		
		$pdf = new TCPDF($obj->constraints->pageOrientation, $obj->constraints->unit, $obj->constraints->pageFormat, true, 'UTF-8', false);
		$title = JText::_('VRTITLEPDFINVOICE');
		$pdf->SetTitle($title);
		if( true ) { // hide header always
			$pdf->SetPrintHeader(false);
		} else {
			//$pdf->SetHeaderData($companylogo, $companylogowidth, $title, '');
		}
		//
		//header and footer fonts
		$pdf->setHeaderFont(array($usepdffont, '', $obj->constraints->fontSizes->header));
		$pdf->setFooterFont(array($usepdffont, '', $obj->constraints->fontSizes->footer));
		//default monospaced font
		//$pdf->SetDefaultMonospacedFont('courier');
		//margins
		$pdf->SetMargins($obj->constraints->margins->left, $obj->constraints->margins->top, $obj->constraints->margins->right);
		$pdf->SetHeaderMargin($obj->constraints->margins->header);
		$pdf->SetFooterMargin($obj->constraints->margins->footer);
		//
		$pdf->SetAutoPageBreak(true, $obj->constraints->margins->bottom);
		$pdf->setImageScale($obj->constraints->imageScaleRatio);
		$pdf->SetFont($usepdffont, '', $obj->constraints->fontSizes->body);
		
		if( true ) { // hide footer always
			$pdf->SetPrintFooter(false);
		} else {
			// print footer
		}
		
		$pdf_tmpl = self::parseInvoiceTemplatePDF($order_details, $group, $obj);

		$pdf->addPage();
		$pdf->writeHTML($pdf_tmpl, true, false, true, false, '');
		
		$pdf->Output($path_pdf, 'F');
		
		return $path_pdf;
	}

	public static function parseInvoiceTemplatePDF($order_details, $group, $obj) {

		defined('_VIKRESTAURANTSEXEC') or define('_VIKRESTAURANTSEXEC', '1');
		ob_start();
		include JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikrestaurants'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.($group == 0 ? 'restaurant' : 'takeaway').'_invoice_tmpl.php';

		$tmpl = ob_get_contents();
		ob_end_clean();
		
		$date_format 	= self::getDateFormat(true);
		$time_format 	= self::getTimeFormat(true);
		$curr_symb 		= self::getCurrencySymb(true);
		$symb_pos 		= self::getCurrencySymbPosition(true);
		
		// COMPANY LOGO
		$logo_name = self::getCompanyLogoPath(true);
		
		$c_path =  '..'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikrestaurants'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.$logo_name;
		
		$logo_str = "";
		if( file_exists( $c_path ) && !empty($logo_name) ) { 
			$logo_str = '<img src="'.$c_path.'"/>';
		}
		$tmpl = str_replace( '{company_logo}', $logo_str, $tmpl );
		
		// COMPANY INFO
		$tmpl = str_replace( '{company_info}', nl2br($obj->params->legalinfo), $tmpl );
		
		// INVOICE DETAILS
		$tmpl = str_replace( '{invoice_number}', $obj->params->number, $tmpl );
		$tmpl = str_replace( '{invoice_suffix}', (strlen($obj->params->suffix) ? '/' : '').$obj->params->suffix, $tmpl );
		$tmpl = str_replace( '{invoice_order_number}', $order_details['id']."-".$order_details['sid'], $tmpl );
		
		$invoice_date = date($date_format); 
		if( $obj->params->datetype == 2 ) {
			$invoice_date = date($date_format, $order_details['created_on']);
		}

		if( isset($obj->params->customDate) && strlen($obj->params->customDate) ) {
			$invoice_date = $obj->params->customDate;
		}

		$tmpl = str_replace( '{invoice_date}', $invoice_date, $tmpl );

		// INVOICE ORDERS
		$order_lines = '';

		if( $group == 0 ) {

			$order_details['items'] = self::getFoodFromReservation($order_details['id']);

			foreach( $order_details['items'] as $item ) {

				$order_lines .= '<tr>
					<td width="65%"><strong>'.$item['name'].'</strong></td>
					<td width="15%">x'.$item['quantity'].'</td>
					<td width="25%">'.self::printPriceCurrencySymb($item['price'], $curr_symb, $symb_pos).'</td>
				</tr>';

			}

		} else {
			
			foreach( $order_details['items'] as $item ) {

				$order_lines .= '<tr>
					<td width="65%"><strong>'.$item['name'].(!empty($item['option_name']) ? ' - '.$item['option_name'] : '').'</strong></td>
					<td width="15%">x'.$item['quantity'].'</td>
					<td width="25%">'.self::printPriceCurrencySymb($item['price'], $curr_symb, $symb_pos).'</td>
				</tr>';

				$toppings_groups_str = '';

				foreach( $item['toppings_groups'] as $i => $group ) {
					$toppings_str = '';
					foreach( $group['toppings'] as $topping ) {
						$toppings_str .= (strlen($toppings_str) ? ', ' : '').'<i>'.$topping['name'].'</i>';
					}

					$toppings_groups_str .= (strlen($toppings_groups_str) ? '<br />' : '').'<strong>'.$group['title'].':</strong> '.$toppings_str;
					
				}

				$order_lines .= '<tr>
					<td width="100%" valign="top" colspan="3" style="padding-left: 20px;"><small>'.$toppings_groups_str.'</small></td>
				</tr>';

			}
			
		}

		$tmpl = str_replace( '{invoice_order_details}', $order_lines, $tmpl );
		
		// CUSTOMER INFO
		$custinfo = "";
		$custdata = json_decode($order_details['custom_f'], true);
		foreach($custdata as $kc => $vc) {
			if( !empty($vc) ) {
				$custinfo .= JText::_($kc).': '.$vc."<br/>\n";
			}
		}
		$tmpl = str_replace( '{customer_info}', $custinfo, $tmpl );
		
		// BILLING INFO
		$billinginfo = "";
		if( $order_details['customer'] !== null ) {
			$custobj = $order_details['customer'];
			if( !empty($custobj['company']) ) $billinginfo .= $custobj['company'].' ';
			if( !empty($custobj['vatnum']) ) $billinginfo .= $custobj['vatnum'];
			if( !empty($billinginfo) ) $billinginfo .= '<br/>';
			
			if( !empty($custobj['billing_state']) ) $billinginfo .= $custobj['billing_state'].', ';
			if( !empty($custobj['billing_city']) ) $billinginfo .= $custobj['billing_city'].' ';
			if( !empty($custobj['billing_zip']) ) $billinginfo .= $custobj['billing_zip'];
			if( !empty($billinginfo) ) $billinginfo .= '<br/>';
			
			if( !empty($custobj['billing_address']) ) $billinginfo .= $custobj['billing_address'];
			if( !empty($custobj['billing_address_2']) ) $billinginfo .= ", ".$custobj['billing_address_2'];
			if( !empty($billinginfo) ) $billinginfo .= '<br/>';
		}
		$tmpl = str_replace( '{billing_info}', $billinginfo, $tmpl );
		
		// TOTAL SUMMARY

		$net = $discount = $pay_ch = $delivery_ch = $taxes = $grand_total = $gratuity = 0;

		/**
		 * Added support for Gratuity/Tip.
		 *
		 * @since 1.7.4
		 */

		if ($group == 0)
		{
			$tax_ratio = self::getTaxesRatio(true);
			$use_taxes = self::isTaxesUsable(true);

			//

			$gratuity = $order_details['tip_amount'];

			$grand_total = $order_details['bill_value'] - $gratuity;

			if ($use_taxes == 0)
			{
				// included
				$net = $grand_total * 100.0 / ($tax_ratio + 100.0);
			}
			else
			{
				$net = $grand_total;
				
				// excluded
				$grand_total *= 1 + $tax_ratio / 100.0;
			}

			$taxes    = $grand_total - $net;
			$discount = $order_details['discount_val'];

			// do not increase NET by DISCOUNT as it is already
			// added while parsing the document
			// $net += $discount;

			$grand_total += $gratuity;

			// TODO (pay charge)
		}
		else
		{
			$net			= $order_details['total_to_pay'] - $order_details['taxes'] - $order_details['pay_charge'] - $order_details['delivery_charge'] - $order_details['tip_amount'];
			$discount 		= $order_details['discount_val'];
			$pay_ch			= $order_details['pay_charge'];
			$delivery_ch	= $order_details['delivery_charge'];
			$taxes			= $order_details['taxes'];
			$gratuity 		= $order_details['tip_amount'];
			$grand_total	= $order_details['total_to_pay'];
		}

		$tmpl = str_replace( '{invoice_totalnet}', self::printPriceCurrencySymb($net+$discount, $curr_symb, $symb_pos), $tmpl );
		$tmpl = str_replace( '{invoice_discountval}', self::printPriceCurrencySymb($discount, $curr_symb, $symb_pos), $tmpl );
		$tmpl = str_replace( '{invoice_totaltip}', self::printPriceCurrencySymb($gratuity, $curr_symb, $symb_pos), $tmpl );
		$tmpl = str_replace( '{invoice_paycharge}', self::printPriceCurrencySymb($pay_ch, $curr_symb, $symb_pos), $tmpl );
		$tmpl = str_replace( '{invoice_deliverycharge}', self::printPriceCurrencySymb($delivery_ch, $curr_symb, $symb_pos), $tmpl );
		$tmpl = str_replace( '{invoice_totaltax}', self::printPriceCurrencySymb($taxes, $curr_symb, $symb_pos), $tmpl );
		$tmpl = str_replace( '{invoice_grandtotal}', self::printPriceCurrencySymb($grand_total, $curr_symb, $symb_pos), $tmpl );
		
		return $tmpl;
	}

	public static function sendInvoiceMail($order_details, $pdf) {
		$admin_mail_list 	= self::getAdminMailList(true);
		$sendermail 		= self::getSenderMail(true);
		$fromname 			= self::getRestaurantName(true);
		
		$subject = JText::sprintf('VRINVMAILSUBJECT', $fromname, $order_details['id']."-".$order_details['sid']);
		$content = JText::sprintf('VRINVMAILCONTENT', $fromname, $order_details['id']."-".$order_details['sid']);

		$content = "########################################\n\n$content\n\n########################################\n\n";
		
		$attachments = array($pdf);
		
		$vik = VREApplication::getInstance();
		return $vik->sendMail($sendermail, $fromname, $order_details['purchaser_mail'], $admin_mail_list[0], $subject, nl2br($content), $attachments);
	}
	
	// LANGUAGE TRANSLATIONS

	/**
	 * Returns the default language of the website.
	 *
	 * @param 	string 	$client  The client to check (site or admin).
	 *
	 * @return 	string 	The default language tag.
	 *
	 * @since 	1.4
	 */
	public static function getDefaultLanguage($client = 'site')
	{
		// get default language for the specified client
		return JComponentHelper::getParams('com_languages')->get($client);
	}
	
	/**
	 * Loads the specified language.
	 *
	 * @param 	string 	$tag     The language tag to load.
	 * @param 	mixed 	$client  The base path of the language.
	 *
	 * @return 	void
	 *
	 * @since 	1.4
	 */
	public static function loadLanguage($tag, $client = null)
	{
		if (!empty($tag))
		{
			/**
			 * Added support for client argument to allow also
			 * the loading of back-end languages.
			 *
			 * @since 1.8
			 */
			if (is_null($client))
			{
				$client = JPATH_SITE;
			}

			$lang = JFactory::getLanguage();

			/**
			 * In case the extension doesn't support the specified language,
			 * Joomla loads by default the default en-GB version.
			 * So, we don't need to add a fallback.
			 */
			$lang->load('com_vikrestaurants', $client, $tag, true);

			/**
			 * Reload system language too.
			 *
			 * @since 1.8.1
			 */
			$lang->load('joomla', $client, $tag, true);
		}
	}
	
	/**
	 * Returns a list of installed languages.
	 *
	 * @return 	array
	 *
	 * @since 	1.4
	 */
	public static function getKnownLanguages()
	{
		// get default language
		$def_lang = self::getDefaultLanguage('site');

		// get installed languages
		$known_languages = VREApplication::getInstance()->getKnownLanguages();
		
		$languages = array();

		foreach ($known_languages as $k => $v)
		{
			if ($k == $def_lang)
			{
				// move default language in first position
				array_unshift($languages, $k);
			}
			else
			{
				// otherwise insert at the end
				array_push($languages, $k);
			}
		}
		
		return $languages;
	}

	/**
	 * Translates a list of rooms.
	 *
	 * @param 	array 	&$rooms  A list of rooms (objects or arrays).
	 * @param 	string  $lang    An optional language to use. If not
	 * 							 specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 *
	 * @uses 	translateRecords()
	 */
	public static function translateRooms(&$rooms, $lang = null)
	{
		self::translateRecords('room', $rooms, $lang);
	}

	/**
	 * Translates a list of menus.
	 *
	 * @param 	array 	&$menus  A list of menus (objects or arrays).
	 * @param 	string  $lang    An optional language to use. If not
	 * 							 specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 *
	 * @uses 	translateRecords()
	 */
	public static function translateMenus(&$menus, $lang = null)
	{
		self::translateRecords('menu', $menus, $lang);
	}

	/**
	 * Translates a list of products.
	 *
	 * @param 	array 	&$products  A list of products (objects or arrays).
	 * @param 	string  $lang       An optional language to use. If not
	 * 							    specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 *
	 * @uses 	translateRecords()
	 */
	public static function translateMenusProducts(&$products, $lang = null)
	{
		self::translateRecords('menusproduct', $products, $lang);
	}

	/**
	 * Translates a list of payments.
	 *
	 * @param 	array 	&$payments  A list of payments (objects or arrays).
	 * @param 	string  $lang       An optional language to use. If not
	 * 							    specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 *
	 * @uses 	translateRecords()
	 */
	public static function translatePayments(&$payments, $lang = null)
	{
		self::translateRecords('payment', $payments, $lang);
	}

	/**
	 * Translates a list of take-away menus.
	 *
	 * @param 	array 	&$menus  A list of menus (objects or arrays).
	 * @param 	string  $lang    An optional language to use. If not
	 * 							 specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 *
	 * @uses 	translateRecords()
	 */
	public static function translateTakeawayMenus(&$menus, $lang = null)
	{
		self::translateRecords('tkmenu', $menus, $lang);
	}

	/**
	 * Translates a list of take-away products.
	 *
	 * @param 	array 	&$items  A list of products (objects or arrays).
	 * @param 	string  $lang    An optional language to use. If not
	 * 							 specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 *
	 * @uses 	translateRecords()
	 */
	public static function translateTakeawayProducts(&$items, $lang = null)
	{
		self::translateRecords('tkentry', $items, $lang);
	}

	/**
	 * Translates a list of take-away product variations.
	 *
	 * @param 	array 	&$options  A list of variations (objects or arrays).
	 * @param 	string  $lang      An optional language to use. If not
	 * 							   specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 *
	 * @uses 	translateRecords()
	 */
	public static function translateTakeawayProductOptions(&$options, $lang = null)
	{
		self::translateRecords('tkentryoption', $options, $lang);
	}

	/**
	 * Translates a list of take-away attributes.
	 *
	 * @param 	array 	&$attributes  A list of attributes (objects or arrays).
	 * @param 	string  $lang         An optional language to use. If not
	 * 							      specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public static function translateTakeawayAttributes(&$attributes, $lang = null)
	{
		self::translateRecords('tkattr', $attributes, $lang);
	}

	/**
	 * Translates a list of take-away deals.
	 *
	 * @param 	array 	&$deals  A list of deals (objects or arrays).
	 * @param 	string  $lang    An optional language to use. If not
	 * 							 specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 *
	 * @uses 	translateRecords()
	 */
	public static function translateTakeawayDeals(&$deals, $lang = null)
	{
		self::translateRecords('tkdeal', $deals, $lang);
	}

	/**
	 * Translates a list of take-away toppings groups.
	 * All the assigned toppings will be translated too.
	 *
	 * @param 	array 	&$groups  A list of groups (objects or arrays).
	 * @param 	string  $lang     An optional language to use. If not
	 * 							  specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public static function translateTakeawayToppingsGroups(&$groups, $lang = null)
	{
		self::translateRecords('tkentrygroup', $groups, $lang);

		foreach ($groups as &$group)
		{
			if (isset($group->list))
			{
				$k = 'list';
			}
			else if (isset($group->toppings))
			{
				$k = 'toppings';
			}
			else
			{
				$k = null;
			}

			// use title as description if not specified
			$group->description = $group->description ? $group->description : $group->title;

			if ($k)
			{
				// translate toppings if specified
				foreach ($group->{$k} as &$toppings)
				{
					self::translateTakeawayToppings($toppings, $lang);
				}
			}
		}
	}

	/**
	 * Translates a list of take-away toppings.
	 *
	 * @param 	array 	&$toppings  A list of toppings (objects or arrays).
	 * @param 	string  $lang       An optional language to use. If not
	 * 							    specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public static function translateTakeawayToppings(&$toppings, $lang = null)
	{
		self::translateRecords('tktopping', $toppings, $lang);
	}

	/**
	 * Translates a configuration setting.
	 *
	 * @param 	string 	$param  The setting name to translate.
	 * @param 	string  $lang   An optional language to use. If not
	 * 							specified, the current one will be used.
	 *
	 * @return 	string  The translated setting.
	 *
	 * @since 	1.8
	 */
	public static function translateSetting($param, $lang = null)
	{
		$settings = array();
		$settings[$param] = VREFactory::getConfig()->get($param);

		// translate setting
		self::translateConfig($settings, $lang);

		// return single translation
		return $settings[$param];
	}

	/**
	 * Translates a list of configuration settings.
	 *
	 * @param 	array 	&$settings  A list of settings (objects or arrays).
	 * @param 	string  $lang       An optional language to use. If not
	 * 							    specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public static function translateConfig(&$settings, $lang = null)
	{
		$tmp = $settings;

		if (is_string(key($tmp)))
		{
			// adjust settings array
			$settings = array();

			foreach ($tmp as $k => $v)
			{
				$settings[] = array(
					'param'   => $k,
					'setting' => $v,
				);
			}
		}

		// translate settings
		self::translateRecords('config', $settings, $lang);

		// reset array
		reset($tmp);

		if (is_string(key($tmp)))
		{
			// back to associative array
			$tmp = $settings;
			$settings = array();

			foreach ($tmp as $r)
			{
				$settings[$r['param']] = $r['setting'];
			}
		}
	}

	/**
	 * Translates a list of generic translatable records.
	 *
	 * @param 	string 	$table     The translatable table name.
	 * @param 	array 	&$records  A list of records (objects or arrays).
	 * @param 	string  $lang      An optional language to use. If not
	 * 							   specified, the current one will be used.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public static function translateRecords($table, &$records, $lang = null)
	{
		// make sure multi-language is supported
		if (!$records || !static::isMultilanguage())
		{
			return false;
		}

		if (!$lang)
		{
			// get current language tag if not specified
			$lang = JFactory::getLanguage()->getTag();
		}

		// get translator
		$translator = VREFactory::getTranslator();

		// get translation table foreign key
		$fk = $translator->getTable($table)->getLinkedPrimaryKey();

		if (!is_array($records))
		{
			// always use an array
			$records = array($records);
			// remember that the argument was NOT an array
			$was_array = false;
		}
		else
		{
			// remember that the argument was already an array
			$was_array = true;
		}

		// extract IDs from records
		$ids = array();

		foreach ($records as $item)
		{
			$ids[] = is_object($item) ? $item->{$fk} : $item[$fk];
		}

		// preload table translations
		$tbLang = $translator->load($table, array_unique($ids), $lang);

		foreach ($records as &$item)
		{
			$id = is_object($item) ? $item->{$fk} : $item[$fk];

			// translate record for the given language
			$tx = $tbLang->getTranslation($id, $lang);

			if ($tx)
			{
				// get translations columns lookup
				$columns = $tbLang->getContentColumns($original = true);

				// iterate all the columns
				foreach ($columns as $colName)
				{
					// inject translation within the record
					if (is_object($item))
					{
						// treat record as object
						$item->{$colName} = $tx->{$colName};
					}
					else
					{
						// treat record as associative array
						$item[$colName] = $tx->{$colName};
					}
				}
			}
		}

		if (!$was_array)
		{
			// revert to original value
			$records = array_shift($records);
		}
	}
	
	/**
	 * Translates the menus.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedMenus($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_menu`, `name`, `description` FROM `#__vikrestaurants_lang_menus` WHERE `id_menu` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$menus = array();
			foreach( $dbo->loadAssocList() as $m ) {
				$menus[$m['id_menu']] = $m;
			}

			return $menus;
		}
		
		return false;
	}
	
	/**
	 * Translates the menu sections.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedSections($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_section`, `name`, `description` FROM `#__vikrestaurants_lang_menus_section` WHERE `id_section` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$sections = array();
			foreach( $dbo->loadAssocList() as $s ) {
				$sections[$s['id_section']] = $s;
			}

			return $sections;
		}
		
		return false;
	}
	
	/**
	 * Translates the products.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedProducts($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_product`, `name`, `description` FROM `#__vikrestaurants_lang_section_product` WHERE `id_product` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$products = array();
			foreach( $dbo->loadAssocList() as $p ) {
				if (!isset($products[$p['id_product']]))
				{
					$products[$p['id_product']] = $p;
				}
			}

			return $products;
		}
		
		return false;
	}
	
	/**
	 * Translates the product options.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedProductOptions($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_option`, `name` FROM `#__vikrestaurants_lang_section_product_option` WHERE `id_option` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$options = array();
			foreach( $dbo->loadAssocList() as $o ) {
				$options[$o['id_option']] = $o;
			}

			return $options;
		}
		
		return false;
	}
	
	/**
	 * Translates the take-away menus.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedTakeawayMenus($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_menu`, `name`, `description` FROM `#__vikrestaurants_lang_takeaway_menus` WHERE `id_menu` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$menus = array();
			foreach( $dbo->loadAssocList() as $m ) {
				$menus[$m['id_menu']] = $m;
			}

			return $menus;
		}
		
		return false;
	}
	
	/**
	 * Translates the take-away products.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedTakeawayProducts($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_entry`, `name`, `description` FROM `#__vikrestaurants_lang_takeaway_menus_entry` WHERE `id_entry` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$products = array();
			foreach( $dbo->loadAssocList() as $p ) {
				$products[$p['id_entry']] = $p;
			}
			
			return $products;
		}
		
		return false;
	}
	
	/**
	 * Translates the variations.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedTakeawayOptions($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_option`, `name` FROM `#__vikrestaurants_lang_takeaway_menus_entry_option` WHERE `id_option` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$options = array();
			foreach( $dbo->loadAssocList() as $o ) {
				$options[$o['id_option']] = $o;
			}

			return $options;
		}
		
		return false;
	}
	
	/**
	 * Translates the toppings groups.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedTakeawayGroups($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_group`, `name` FROM `#__vikrestaurants_lang_takeaway_menus_entry_topping_group` WHERE `id_group` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$groups = array();
			foreach( $dbo->loadAssocList() as $g ) {
				$groups[$g['id_group']] = $g;
			}

			return $groups;
		}
		
		return false;
	}
	
	/**
	 * Translates the toppings.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedTakeawayToppings($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_topping`, `name` FROM `#__vikrestaurants_lang_takeaway_topping` WHERE `id_topping` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$toppings = array();
			foreach( $dbo->loadAssocList() as $t ) {
				$toppings[$t['id_topping']] = $t;
			}

			return $toppings;
		}
		
		return false;
	}
	
	/**
	 * Translates the attributes.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedTakeawayAttributes($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_attribute`, `name` FROM `#__vikrestaurants_lang_takeaway_menus_attribute` WHERE `id_attribute` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$attributes = array();
			foreach( $dbo->loadAssocList() as $a ) {
				$attributes[$a['id_attribute']] = $a;
			}

			return $attributes;
		}
		
		return false;
	}
	
	/**
	 * Translates the deals.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedTakeawayDeals($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_deal`, `name`, `description` FROM `#__vikrestaurants_lang_takeaway_deal` WHERE `id_deal` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$deals = array();
			foreach( $dbo->loadAssocList() as $d ) {
				$deals[$d['id_deal']] = $d;
			}

			return $deals;
		}
		
		return false;
	}

	/**
	 * Translates the payments.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedPayments($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_payment`, `name`, `note`, `prenote` FROM `#__vikrestaurants_lang_payments` WHERE `id_payment` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$payments = array();
			foreach( $dbo->loadAssocList() as $p ) {
				$payments[$p['id_payment']] = $p;
			}

			return $payments;
		}
		
		return false;
	}

	/**
	 * Translates the custom fields.
	 *
	 * @param 	array 	$ids
	 * @param 	string 	$tag
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function getTranslatedCustomFields($ids = array(), $tag = '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		
		if (!self::isMultilanguage())
		{
			return false;
		}
		
		if (empty($tag))
		{
			$tag = JFactory::getLanguage()->getTag();
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id_customf`, `name`, `choose`, `poplink` FROM `#__vikrestaurants_lang_customf` WHERE `id_customf` IN (".implode(",", $ids).") AND `tag`=".$dbo->quote($tag).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$payments = array();
			foreach( $dbo->loadAssocList() as $p ) {
				$payments[$p['id_customf']] = $p;
			}

			return $payments;
		}
		
		return false;
	}
	
	/**
	 * Translates the contents.
	 *
	 * @param 	integer  $id
	 * @param 	array 	 $ori
	 * @param 	array 	 $new
	 * @param 	string 	 $key1
	 * @param 	string 	 $key2
	 *
	 * @return 	string
	 *
	 * @deprecated 	1.9 Use VRELanguageTranslator instead.
	 */
	public static function translate($id, $ori, $new, $key1, $key2)
	{
		if ($new === false || empty($new[$id][$key2]))
		{
			return $ori[$key1];
		}
		
		return $new[$id][$key2];
	}
	
	// OPERATORS LOGS
	
	/**
	 * Returns a list of e-mail addresses that belong to the
	 * operators that should receive notifications for the
	 * specified group.
	 *
	 * @param 	integer  $group  The group to check (0: both, 1: restaurant, 2: takeaway).
	 * @param 	mixed 	 $order  The details of the order.
	 *
	 * @return 	array 	 A list of e-mails.
	 *
	 * @since 	1.5
	 */
	public static function getNotificationOperatorsMails($group = 0, $order = null)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select('*')
			->from($dbo->qn('#__vikrestaurants_operator'))
			->where($dbo->qn('mail_notifications') . ' = 1')
			->where($dbo->qn('email') . '<> ""');

		if ($group > 0)
		{
			$q->where($dbo->qn('group') . ' IN (0, ' . (int) $group . ')');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			return array();
		}

		$rows = $dbo->loadObjectList();

		VRELoader::import('library.operator.user');

		$operators = array();
		
		/**
		 * Take only the operators that are able to access the
		 * room assigned to the specified order.
		 *
		 * @since 1.8
		 */
		foreach ($rows as $operator)
		{
			// instantiate operator
			$operator = new VREOperatorUser($operator);

			$add = true;

			if ($order)
			{
				// validate for restaurant group only
				if ($group == 1)
				{
					// include operator only in case it can access the room of the order
					$add = $operator->canAccessRoom($order->room->id);
				}
			}

			if ($add)
			{
				// include e-mail only
				$operators[] = $operator->get('email');
			}
		}

		return $operators;
	}
	
	/**
	 * Stores an operator log.
	 *
	 * @param 	integer  $id_operator
	 * @param 	integer  $id_order
	 * @param 	string 	 $log
	 * @param 	integer  $group
	 *
	 * @return 	mixed
	 *
	 * @deprecated 1.9 	Use VREOperatorLogger::store() instead.
	 */
	public static function storeOperatorLog($id_operator, $id_order, $log, $group)
	{
		if (empty($log) || empty($id_operator))
		{
			return 0;
		}

		// get operator logger
		$logger = VREOperatorLogger::getInstance();

		// register log
		return $logger->store($id_operator, $log, $group, $id_order);
	}
	
	/**
	 * Generates a log subject depending on the specified arguments.
	 *
	 * @param 	array    $operator
	 * @param 	integer  $id_order
	 * @param 	integer  $group
	 * @param 	integer  $action
	 *
	 * @return 	string
	 *
	 * @deprecated 1.9 	Use VREOperatorLogger::generate() instead.
	 */
	public static function generateOperatorLog($operator, $id_order, $group, $action)
	{
		$log = "";
		
		if ($group == self::OPERATOR_RESTAURANT_LOG)
		{	
			if ($action == self::OPERATOR_RESTAURANT_INSERT)
			{
				$log = JText::sprintf('VROPLOGRESTAURANTINSERT', $operator['code'], $id_order);
			}
			else if ($action == self::OPERATOR_RESTAURANT_UPDATE)
			{
				$log = JText::sprintf('VROPLOGRESTAURANTUPDATE', $operator['code'], $id_order);
			}
			else if ($action == self::OPERATOR_RESTAURANT_CONFIRMED)
			{
				$log = JText::sprintf('VROPLOGRESTAURANTCONFIRMED', $operator['code'], $id_order);
			}
			else if ($action == self::OPERATOR_RESTAURANT_TABLE_CHANGED)
			{
				$log = JText::sprintf('VROPLOGRESTAURANTTABLECHANGED', $operator['code'], $id_order);
			}
		}
		else if ($group == self::OPERATOR_TAKEAWAY_LOG)
		{	
			if ($action == self::OPERATOR_TAKEAWAY_INSERT)
			{
				$log = JText::sprintf('VROPLOGTAKEAWAYINSERT', $operator['code'], $id_order);
			}
			else if ($action == self::OPERATOR_TAKEAWAY_UPDATE)
			{
				$log = JText::sprintf('VROPLOGTAKEAWAYUPDATE', $operator['code'], $id_order);
			}
			else if ($action == self::OPERATOR_TAKEAWAY_CONFIRMED)
			{
				$log = JText::sprintf('VROPLOGTAKEAWAYCONFIRMED', $operator['code'], $id_order);
			}
		}
		
		return $log;
	}
	
	const OPERATOR_GENERIC_LOG    = 0;
	const OPERATOR_RESTAURANT_LOG = 1;
	const OPERATOR_TAKEAWAY_LOG   = 2;
	
	const OPERATOR_GENERIC_ACTION = 0;
	
	const OPERATOR_RESTAURANT_UNDEFINED     = 0;
	const OPERATOR_RESTAURANT_INSERT        = 1;
	const OPERATOR_RESTAURANT_UPDATE        = 2;
	const OPERATOR_RESTAURANT_CONFIRMED     = 3;
	const OPERATOR_RESTAURANT_TABLE_CHANGED = 4;
	
	const OPERATOR_TAKEAWAY_UNDEFINED = 0;
	const OPERATOR_TAKEAWAY_INSERT    = 1;
	const OPERATOR_TAKEAWAY_UPDATE    = 2;
	const OPERATOR_TAKEAWAY_CONFIRMED = 3;
	
	/* OPERATORS AREA */
	
	/**
	 * Returns the main menu of the operators area.
	 *
	 * @param 	array 	$operator
	 *
	 * @return 	string
	 *
	 * @deprecated 1.9  Use the layout directly.
	 */
	public static function getToolbarLiveMap($operator)
	{
		return JLayoutHelper::render('oversight.toolbar', array('operator' => $operator));
	}	
}
