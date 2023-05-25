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
 * VikRestaurants APIs base framework.
 * This class is used to run all the installed plugins in a given directory.
 * The classname of the plugins must follow the standard below:
 * e.g. File = plugin.php   		Class = Plugin
 * e.g. File = plugin_name.php   	Class = PluginName
 *
 * All the events are runnable only if the user is correctly authenticated.
 *
 * @see 	APIs 		This class extends the base framework handler.
 * @see 	JFactory 	Joomla Factory class to retrieve the database resource.
 * @see 	VREFactory 	Custom Factory class to retrieve the software configuration.
 * @see 	UserAPIs
 * @see 	ResponseAPIs
 * @see 	ErrorAPIs
 * @see 	EventAPIs
 *
 * @since  	1.7
 */
class FrameworkAPIs extends APIs
{
	/**
	 * Class constructor.
	 * @protected This class can be accessed only through the static getInstance() method.
	 *
	 * In case the framework is not accessible, it will be disabled.
	 *
	 * @param 	string 	$path  The dir path containing all the plugins.
	 *
	 * @see APIs::getInstance()
	 */
	protected function __construct($path = null)
	{
		parent::__construct($path);

		// make sure the APIs framework is enabled
		$enabled = VREFactory::getConfig()->getBool('apifw');

		if (!$enabled)
		{
			// disable APIs
			$this->disable();
		}

		if (JFactory::getApplication()->isClient('site'))
		{
			// always load tables from the back-end
			JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		}
	}

	/**
	 * Authenticate the provided user and connect it on success.
	 * The credentials of the user are stored in the database.
	 * @usedby 	APIs::connect()
	 *
	 * This method can raise the following internal errors:
	 * - 103 = The username and password do not match
	 * - 104 = This account is blocked
	 * - 105 = The source IP is not authorised
	 *
	 * @param 	UserAPIs  $user 	The object of the user.
	 *
	 * @return 	integer   The ID of the user on success, otherwise false.
	 *
	 * @uses 	APIs::setError()  Set the error raised.
	 */
	protected function doConnection(UserAPIs $user)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		// get login that matches with the credentials provided
		$q->select('*')
			->from($dbo->qn('#__vikrestaurants_api_login'))
			->where($dbo->qn('username') . ' = ' . $dbo->q($user->getUsername()))
			->where('BINARY ' . $dbo->qn('password') . ' = ' . $dbo->q($user->getPassword()));

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			// set error : credentials not correct
			$this->setError(103, 'Authentication Error! The username and password do not match.');
			return false;
		}

		// load login
		$login = $dbo->loadAssoc();

		// check if login account is still active
		if (!$login['active'])
		{
			// set error : login blocked
			$this->setError(104, 'Authentication Error! This account is blocked.');
			return false;
		}

		// check if user IP address is in the list of the allowed IPs
		// if there are no IPs specified, all addresses are allowed
		if (strlen($login['ips']))
		{
			$ip_list = json_decode($login['ips'], true);

			if (count($ip_list) && !in_array($user->getSourceIp(), $ip_list))
			{
				// set error : ip address not allowed
				$this->setError(105, 'Authentication Error! The source IP is not authorised.');
				return false;
			}
		}

		return $login['id'];
	}

	/**
	 * Register the provided event and response.
	 * This log is registered in the database and it is visible only from the administrator.
	 * @usedby 	APIs::connect()
	 * @usedby 	APIs::trigger()
	 *
	 * @param 	EventAPIs 	  $event 	 The event requested.
	 * @param 	ResponseAPIs  $response  The response caught or raised.
	 *
	 * @return 	boolean       True if the event has been registered, otherwise false.
	 *
	 * @uses 	APIs::isConnected() Check if the user is connected.
	 * @uses 	APIs::getUser() 	Get the current user.
	 */
	protected function registerEvent(EventAPIs $event = null, ResponseAPIs $response = null)
	{
		$log     = '';
		$status  = 2;
		$id_user = $this->isConnected() ? $this->getUser()->id() : -1;
		$ip      = $this->isConnected() ? $this->getUser()->getSourceIp() : null;

		// if the event is not empty : register it
		if ($event !== null)
		{
			$log .= 'Event: ' . $event->getName() . "\n";
		}

		// if the response is not empty : register it and evaluate the status
		if ($response !== null)
		{
			$log .= $response->getContent();

			$status = $response->isVerified() ? 1 : 0;
		}

		if (empty($log))
		{
			// if the evaluated log is still empty
			if ($id_user > 0)
			{
				// try to register the details of the user
				$log = 'User [' . $this->getUser()->getUsername() . '] login @ ' . date('Y-m-d H:i:s', $ts);
			}
			else
			{
				// otherwise register a "unrecognised" response
				$log = 'Unable to recognize the response';
			}

		}

		// prepare log data
		$data = array(
			'id'       => 0,
			'id_login' => $id_user,
			'status'   => $status,
			'content'  => $log,
		);

		// get API login logs table
		$logTable = JTableVRE::getInstance('apilog', 'VRETable');
		// save log
		$logTable->save($data);

		return (bool) $logTable->id;
	}

	/**
	 * Update the user manifest after a successful authentication.
	 * @usedby 	APIs::connect()
	 *
	 * @return 	boolean  True on success, otherwise false.
	 *
	 * @uses 	APIs::getUser() Access the user object.
	 */
	protected function updateUserManifest()
	{
		if ($this->getUser() === null)
		{
			return false;
		}

		// prepare login data
		$data = array(
			'id'         => $this->getUser()->id(),
			'last_login' => VikRestaurants::now(),
		);

		// get API user login table
		$userTable = JTableVRE::getInstance('apiuser', 'VRETable');
		// save manifest
		return $userTable->save($data);
	}

	/**
	 * Check if the provided user has been banned.
	 * This action is executed only before the authentication.
	 * The ban is evaluated on the IP origin.
	 *
	 * A user is considered banned when its failures are equals or higher
	 * than the maximum number of failure attempts allowed.
	 *
	 * The failure attempts are always increased by the ban() function.
	 *
	 * @usedby 	APIs::connect()
	 *
	 * @param 	UserAPIs 	$user 	The object of the user.
	 *
	 * @return 	boolean 	True is the user is banned, otherwise false.
	 *
	 * @uses 	APIs::get() 	Get the maximum number of failure attempts from config.
	 *
	 * @see 	FrameworkAPIs::ban() to ban a user.
	 */
	protected function isBanned(UserAPIs $user)
	{
		// get the number of failures associated to the IP address of the user
		
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('fail_count'))
			->from($dbo->qn('#__vikrestaurants_api_ban'))
			->where($dbo->qn('ip') . ' = ' . $dbo->q($user->getSourceIp()));

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		// if the failures count is equals or higher than the maximum allowed, it means the user is banned

		if ($dbo->getNumRows())
		{
			return (int) $dbo->loadResult() >= $this->get('max_failure_attempts', 10);
		}

		return false;
	}

	/**
	 * Considering this function is called after every failure, a ban is always needed.
	 * Every time this function is executed, the system will call the ban() function to apply the ban.
	 * @usedby 	APIs::connect()
	 *
	 * @param 	UserAPIs 	$user 	The object of the user.
	 *
	 * @return 	boolean 	Return true.
	 *
	 * @see 	FrameworkAPIs::ban() to ban a user.
	 */
	protected function needBan(UserAPIs $user)
	{
		// all failures need to be banned
		// ban() function is used to increase the number of failures
		return true;
	}

	/**
	 * Increase the failure attempts of the provided user.
	 * Once this function is terminated, the user is not effectively banned, unless its 
	 * total failures are equals or higher than the maximum number allowed.
	 * @usedby 	APIs::connect()
	 *
	 * @param 	UserAPIs  $user  The object of the user.
	 *
	 * @see 	FrameworkAPIs::isBanned()  Check if the user is banned.
	 */
	protected function ban(UserAPIs $user)
	{
		$dbo = JFactory::getDbo();

		// get the ID of the user to ban

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn(array('id', 'fail_count')))
			->from($dbo->qn('#__vikrestaurants_api_ban'))
			->where($dbo->qn('ip') . ' = ' . $dbo->q($user->getSourceIp()));

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$data = $dbo->loadAssoc();
		}
		else
		{
			// create new ban
			$data = array(
				'id'         => 0,
				'fail_count' => 0,
			);
		}

		// increase failure count
		$data['fail_count']++;

		// get API ban table
		$banTable = JTableVRE::getInstance('apiban', 'VRETable');
		// save ban
		$banTable->save($data);
	}

	/**
	 * Reset the count of failure attempts for the provided user.
	 * @usedby 	APIs::connect()
	 *
	 * @param 	UserAPIs 	$user 	The object of the user.
	 *
	 * @return 	boolean 	True if the user is correctly logged, otherwise false.
	 */
	protected function resetBan(UserAPIs $user)
	{
		if (!$user->id())
		{
			return false;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_api_ban'))
			->where($dbo->qn('ip') . ' = ' . $dbo->q($user->getSourceIp()));

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		
		if ($dbo->getNumRows())
		{
			$data = array(
				'id'         => $dbo->loadResult(),
				'fail_count' => 0,
			);

			// get API ban table
			$banTable = JTableVRE::getInstance('apiban', 'VRETable');
			// reset ban
			$banTable->save($data);
		}

		return true;
	}

	/**
	 * Prepares the document to output the given data.
	 *
	 * @param 	mixed  $data  The data to output.
	 * @param 	mixed  $type  The content type.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.4
	 */
	public function output($data, $type = 'application/json')
	{
		if (!is_null($data))
		{
			$app = JFactory::getApplication();

			// check whether the output requires a specific content type
			// and make sure the headers haven't been already sent
			if ($type && $this->sendHeaders)
			{
				// set content type and send the headers
				$app->setHeader('Content-Type', $type);
				$app->sendHeaders();

				// lock headers sending
				$this->sendHeaders = false;
			}
		
			// try to stringify an object in case of JSON content type
			if (!is_string($data) && preg_match("/json/i", $type))
			{
				$data = json_encode($data);
			}

			echo $data;
		}
	}

	/**
	 * Loads the configuration for the specified event and user.
	 * @userby  APIs::trigger()
	 *
	 * @param 	string    $eventName  The name of the event.
	 * @param 	UserAPIs  $user       The object of the user.
	 *
	 * @return 	mixed     Either an array or an object.
	 *
	 * @since 	1.8.4
	 */
	protected function loadEventConfig($eventName, UserAPIs $user = null)
	{
		$options = array();

		if (!$user)
		{
			// make sure we have a logged-in user
			if (!$this->isConnected())
			{
				// nope, return an empty array...
				return $options;
			}

			// use currently connected user
			$user = $this->getUser();
		}

		// get helper table
		$table = JTableVRE::getInstance('apiuseroptions', 'VRETable');

		// load options related to the specified ID and event
		$data = $table->getOptions($user->id(), $eventName);

		if ($data)
		{
			// existing record, use the stored configuration
			$options = $data->options;
		}

		return $options;
	}

	/**
	 * Saves the configuration for the specified event and user.
	 * @userby  APIs::trigger()
	 *
	 * @param 	EventAPIs  $event  The event requested.
	 * @param 	UserAPIs   $user   The object of the user.
	 *
	 * @return 	boolean   True on success, false otherwise.
	 *
	 * @since 	1.8.4
	 */
	protected function saveEventConfig(EventAPIs $event, UserAPIs $user = null)
	{
		$options = $event->getOptions();

		if (!$options)
		{
			// empty configuration, do not need to go ahead
			return true;
		}

		if (!$user)
		{
			// make sure we have a logged-in user
			if (!$this->isConnected())
			{
				// nope, saving failed
				return false;
			}

			// use currently connected user
			$user = $this->getUser();
		}

		// get helper table
		$table = JTableVRE::getInstance('apiuseroptions', 'VRETable');

		// set up data to bind
		$data = array();
		$data['id_login'] = $user->id();
		$data['id_event'] = $event->getName();
		$data['options']  = $event->getOptions();

		// store options
		return $table->save($data);
	}
}
