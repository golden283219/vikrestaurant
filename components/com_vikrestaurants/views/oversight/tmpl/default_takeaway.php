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

if (!$this->ACCESS)
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

JHtml::_('behavior.keepalive');
JHtml::_('vrehtml.assets.fontawesome');

$operator = $this->user;

$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');

?>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=oversight&group=2' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="POST" name="optkform">

	<div class="vroversighthead">
		<h2><?php echo JText::sprintf('VRLOGINOPERATORHI', $operator->get('firstname')); ?></h2>

		<?php echo VikRestaurants::getToolbarLiveMap($operator); ?>
	</div>

	<?php
	// prepare widget layout data
	$data = array(
		'widget'   => 'orders',
		'group'    => 'takeaway',
		'config'   => array(
			'items'    => 50,
			'latest'   => 1,
			'incoming' => 1,
			'current'  => 1,
		),
		'timer'    => 60,
		'itemid'   => $itemid,
	);

	// display widget by using an apposite layout
	echo JLayoutHelper::render('oversight.widget', $data);
	?>

	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="view" value="oversight" />
	<input type="hidden" name="group" value="2" />

</form>
