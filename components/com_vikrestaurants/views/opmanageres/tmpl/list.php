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

JHtml::_('behavior.core');

$rows = $this->rows;

$input  = JFactory::getApplication()->input;
$from 	= $input->get('from', '', 'string');
$itemid = $input->get('Itemid', 0, 'uint');

$config = VREFactory::getConfig();

?>

<form name="manageresform" action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=opmanageres' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post" id="vrmanageform">

	<div class="vrfront-manage-headerdiv">
		<div class="vrfront-manage-titlediv">
			<h2><?php echo JText::sprintf('VREDITQUICKRESERVATIONSHARED', $rows[0]['tname']); ?></h2>
		</div>
		
		<div class="vrfront-manage-actionsdiv">
			<div class="vrfront-manage-btn">
				<button type="button" onClick="vrCloseQuickReservation();" id="vrfront-manage-btnclose" class="vrfront-manage-button"><?php echo JText::_('VRCLOSE'); ?></button>
			</div>
		</div>
	</div> 

	<div class="vreditres-allrows">
		
		<?php
		foreach ($rows as $r)
		{
			?>
			<div class="vreditres-row-block">
					
				<span class="vreditres-row-order" style="width: 25%; text-align: left;">
					<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=opreservation.edit&cid[]=' . $r['id'] . ($from ? '&from=' . $from : '') . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
						<?php echo $r['id'] . '-' . $r['sid']; ?>
					</a>
				</span>
				
				<span class="vreditres-row-name" style="width: 25%; text-align: left;">
					<?php echo $r['purchaser_nominative']; ?>
				</span>
				
				<span class="vreditres-row-time" style="width: 25%; text-align: left;">
					<?php echo JHtml::_('date', $r['checkin_ts'], JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat'), date_default_timezone_get()); ?>
				</span>
				
				<span class="vreditres-row-people" style="width: 15%; text-align: left;">
					<?php echo JText::plural('VRE_N_PEOPLE', $r['people']); ?>
				</span>
				
				<span class="vreditres-row-code" style="width: 10%; text-align: center;">
					<?php
					if (!empty($r['rescode']))
					{
						// get reservation code
						$code = JHtml::_('vikrestaurants.rescode', $r['rescode'], 1);

						if ($code)
						{
							if (empty($code->icon))
							{
								echo $code->code;
							}
							else
							{
								?>
								<img src="<?php echo VREMEDIA_SMALL_URI . $code->icon; ?>" title="<?php echo $this->escape($code->code); ?>"/>
								<?php
							}
						}
					}
					?>
				</span>

			</div>    
			<?php
		}
		?>
		
	</div>

	<input type="hidden" name="from" value="<?php echo $from; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="opmanageres" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="Itemid" value="<?php echo $itemid; ?>" />

</form>

<script>

	function vrCloseQuickReservation() {
		Joomla.submitform('opreservation.cancel', document.manageresform);	
	}

</script>
