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
 * Template file used to display the step bar with the
 * progress made during the booking process.
 *
 * @since 1.8
 */

// take only the needed arguments
$args = array();

foreach ($this->args as $k => $v)
{
	if (in_array($k, array('date', 'hourmin', 'people')))
	{
		$args[$k] = $v;
	}
}

if (JFactory::getApplication()->getUserState('vre.search.family'))
{
	// include family flag in query string
	$args['family'] = 1;
}

$data = array(
	/**
	 * Flag used to check whether the step bar should be
	 * displayed or not. If not specified, the step bar
	 * will be visible within the document.
	 *
	 * @var boolean	 
	 */
	'display' => true,

	/**
	 * The step to display as active [1-3].
	 * If not specified, the first one will be used.
	 *
	 * @var integer
	 */
	'active' => 3,

	/**
	 * An associative array containing the search arguments,
	 * such as date, hourmin and people. These arguments will
	 * be appended within the step URL to avoid losing them.
	 *
	 * @var array
	 */
	'args' => $args,

	/**
	 * An optional Itemid to be used when routing a URL.
	 * For the moment, pass null to take the current one.
	 *
	 * @var integer|null
	 */
	'Itemid' => null,
);

/**
 * The step bar is displayed from the layout below:
 * /components/com_vikrestaurants/layouts/blocks/stepbar.php
 *
 * @since 1.8
 */
echo JLayoutHelper::render('blocks.stepbar', $data);
