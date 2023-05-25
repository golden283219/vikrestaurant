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

$rows = $this->payLog;

$vik = VREApplication::getInstance();

if (count($rows) == 0)
{
	?>
	<div style="margin:10px">
		<?php echo $vik->alert(JText::_('VRNOOPERATORLOG')); ?>
	</div>
	<?php
}
else
{
	// create log details layout
	$layout = new JLayoutFile('blocks.operatorlog');

	foreach ($rows as $row)
	{
		// set empty data attribute to avoid errors
		$row['content']        = '[]';
		$row['group']          = $this->filters['group'];
		$row['id_reservation'] = $this->filters['id'];
		
		// set current log for being used in sub-layout
		$data = array(
			'log'         => $row,
			'operator'    => false, // hide operator badge
			'reservation' => false, // hide reservation badge
		);

		echo $layout->render($data);
	}
}
