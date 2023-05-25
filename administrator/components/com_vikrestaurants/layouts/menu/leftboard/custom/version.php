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

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   boolean  $newupdate   True if there is a new available update.
 * @var   boolean  $vikupdater  True if VikUpdater plugin is active.
 * @var   boolean  $connect     True to auto-search for new updates.
 * @var   string   $url         The fallback remote URL.
 * @var   string   $title       The item title.
 * @var   string   $label       The item label.
 */

JText::script('VRCHECKINGVERSION');
JText::script('VRERROR');

?>

<div class="version-box custom<?php echo $newupdate ? ' upd-avail' : ''; ?>">
	
	<?php
	if ($vikupdater)
	{
		// VikUpdater plugin is enabled

		$document = JFactory::getDocument();
		$document->addScriptDeclaration(
<<<JS
function callVersionChecker() {
	jQuery.noConflict();

	setVersionContent(Joomla.JText._('VRCHECKINGVERSION'));

	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php?option=com_vikrestaurants&task=updateprogram.checkversion&tmpl=component",
		data: {}
	}).done(function(resp) {
		var obj = jQuery.parseJSON(resp);

		console.log(obj);

		if (obj["status"] == 1) {

			if (obj.response.status == 1) {

				if (obj.response.compare == 1) {
					jQuery("#vr-versioncheck-link").attr("onclick", "");
					jQuery("#vr-versioncheck-link").attr("href", "index.php?option=com_vikrestaurants&view=updateprogram");

					obj.response.shortTitle += '<i class="upd-avail fas fa-exclamation-triangle"></i>';

					jQuery(".version-box.custom").addClass("upd-avail");
				}

				setVersionContent(obj.response.shortTitle, obj.response.title);

			} else {
				console.log(obj.response.error);
				setVersionContent(Joomla.JText._('VRERROR'));
			}

		} else {
			console.log("plugin disabled");
			setVersionContent(Joomla.JText._('VRERROR'));
		}

	}).fail(function(resp){
		console.log(resp);
		setVersionContent(Joomla.JText._('VRERROR'));
	});
}

function setVersionContent(cont, title) {
	jQuery("#vr-version-content").html(cont);

	if (title === undefined) {
		var title = "";
	}

	jQuery("#vr-version-content").attr("title", title);
}
JS
		);

		if ($connect)
		{
			$document->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	callVersionChecker();
});
JS
			);
		}
		?>
		<a
			href="<?php echo ($newupdate ? 'index.php?option=com_vikrestaurants&view=updateprogram' : 'javascript: void(0);'); ?>"
			onclick="<?php echo ($newupdate ? '' : 'callVersionChecker();'); ?>"
			id="vr-versioncheck-link"
		>
			<i class="fab fa-joomla"></i>
			<span id="vr-version-content" title="<?php echo $title; ?>">
				<?php 
				echo $label;

				if ($newupdate)
				{
					?><i class="upd-avail fas fa-exclamation-triangle"></i><?php
				}
				?>
			</span>
		</a>
		<?php
	}
	else
	{
		// VikUpdater plugin is disabled, fallback to remote url
		JHtml::_('behavior.modal');
		?>
		<a
			id="vcheck"
			href=""
			class="modal"
			rel="{handler: 'iframe'}"
			target="_blank"
			onclick="this.href='<?php echo $url; ?>';"
		>
			<i class="fab fa-joomla"></i>
			<span><?php echo $label; ?></span>
		</a>
		<?php
	}
	?>

</div>