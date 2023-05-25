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

JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.fancybox');

if ($this->params['googleapikey'])
{
	// load Google Maps only in case the API Key was specified
	JHtml::_('vrehtml.assets.googlemaps', $this->params['googleapikey'], 'places');
}

$params = $this->params;

$vik = VREApplication::getInstance();

/**
 * Prepares CodeMirror editor scripts for being used
 * via Javascript/AJAX.
 *
 * @wponly
 */
$vik->prepareEditor('codemirror');

/**
 * Recover selected tab from the browser cookie.
 *
 * @since 1.8
 */
$_selected_tab_view = JFactory::getApplication()->input->cookie->get('vikrestaurants_config_tab', 1, 'uint');

if (($_selected_tab_view == 2 && !$params['enablerestaurant']) || ($_selected_tab_view == 3 && !$params['enabletakeaway']))
{
	// use default "Global" tab in case the last selected tab was a section that it is no more accessible
	$_selected_tab_view = 1;
}

/**
 * Trigger event to display custom HTML.
 * In case it is needed to include any additional fields,
 * it is possible to create a plugin and attach it to an event
 * called "onDisplayViewConfig". The event method receives the
 * view instance as argument.
 *
 * @since 1.8.3
 */
$forms = $this->onDisplayView();

$tabs = $custTabs = array();

// build default tabs: global, restaurant, take-away, sms, application
$tabs[] = JText::_('VRECONFIGTABNAME1');
$tabs[] = JText::_('VRECONFIGTABNAME2');
$tabs[] = JText::_('VRECONFIGTABNAME3');
$tabs[] = JText::_('VRECONFIGTABNAME4');
$tabs[] = JText::_('VRECONFIGTABNAME5');

/**
 * Iterate all form items to be displayed within as
 * custom tabs within the nav bar.
 *
 * @since 1.8.3
 */
foreach ($forms as $tabName => $tabForms)
{
	// include tab
	$custTabs[] = JText::_($tabName);
}

// make sure the selected tab is still available
if ($_selected_tab_view > count($tabs) + count($custTabs))
{
	// reset to first tab
	$_selected_tab_view = 1;
}

$icons_lookup = array(
	1 => 'cogs',
	2 => 'utensils',
	3 => 'shopping-basket',
	4 => 'comment-dots',
	5 => 'plug',
);

?>

<div class="vr-config-head-wrapper">

	<!-- NAVIGATION -->

	<div id="navigation">
		<ul>
			<?php
			foreach (array_merge($tabs, $custTabs) as $i => $tab)
			{
				$key = $i + 1;
				?>
				<li id="vretabli<?php echo $key; ?>" class="vretabli<?php echo ($_selected_tab_view == $key ? ' vreconfigtabactive' : ''); ?>">
					<a href="javascript: void(0);" onclick="changeTabView('<?php echo $key; ?>');">
						<?php
						if (isset($icons_lookup[$key]))
						{
							// display related icon on small devices
							?><i class="fas fa-<?php echo $icons_lookup[$key]; ?> mobile-only"></i><?php
						}
						else
						{
							// display the first 3 characters on small devices
							?>
							<span class="mobile-only">
								<?php echo strtoupper(mb_substr($tab, 0, 3, 'UTF-8')); ?>
							</span>
							<?php
						}
						?>
						<span class="hidden-phone"><?php echo $tab; ?></span>
					</a>
				</li>
				<?php
			}
			?>
		</ul>
	</div>

	<!-- SEARCH TOOLBAR -->

	<div class="vre-config-toolbar hidden-phone">

		<div class="btn-group pull-right input-append" style="margin-left: 5px;">
			<input type="text" id="vre-search-param" value="" placeholder="Settings Research" size="24" />

			<button type="button" class="btn" onClick="hideSearchBar();">
				<i class="icon-remove"></i>
			</button>
		</div>

	</div>

</div>

<!-- SEARCH FLOATING BAR -->

<div class="vre-config-searchbar" style="display: none">
	<div class="vre-config-searchbar-results">
		<span class="vre-config-searchbar-stat badge"></span>
		<span class="vre-config-searchbar-control">
			<a href="javascript: void(0);" class="" onClick="goToPrevMatch();">
				<i class="fas fa-chevron-left big"></i>
			</a>
		</span>
		<span class="vre-config-searchbar-control">
			<a href="javascript: void(0);" class="" onClick="goToNextMatch();">
				<i class="fas fa-chevron-right big"></i>
			</a>
		</span>
	</div>
	<div class="vre-config-searchbar-gotop">
		<a href="javascript: void(0);" onClick="animateToPageTop();"><?php echo JText::_('VRGOTOP'); ?></a>
	</div>
</div>

<!-- SETTINGS FORM -->

<form action="index.php" method="post" name="adminForm" id="adminForm">
	
	<!-- GLOBAL -->

	<div id="vretabview1" class="vretabview" style="<?php echo ($_selected_tab_view != 1 ? 'display: none;' : ''); ?>">
		
		<?php echo $this->loadTemplate('global'); ?>
		
	</div>

	<!-- RESTAURANT -->
	
	<div id="vretabview2" class="vretabview" style="<?php echo ($_selected_tab_view != 2 ? 'display: none;' : ''); ?>">

		<?php echo $this->loadTemplate('restaurant'); ?>
		
	</div>

	<!-- TAKEAWAY -->
	
	<div id="vretabview3" class="vretabview" style="<?php echo ($_selected_tab_view != 3 ? 'display: none;' : ''); ?>">
		
		<?php echo $this->loadTemplate('takeaway'); ?>

	</div>

	<!-- SMS APIS -->
	
	<div id="vretabview4" class="vretabview" style="<?php echo ($_selected_tab_view != 4 ? 'display: none;' : ''); ?>">
		
		<?php echo $this->loadTemplate('sms'); ?>
		
	</div>

	<!-- FRAMEWORK APIS -->
	
	<div id="vretabview5" class="vretabview" style="<?php echo ($_selected_tab_view != 5 ? 'display: none;' : ''); ?>">
		
		<?php echo $this->loadTemplate('apps'); ?>
		
	</div>

	<?php
	$i = 0;

	/**
	 * Iterate all form items to be displayed as
	 * custom tabs within the nav bar.
	 *
	 * @since 1.8.3
	 */
	foreach ($forms as $formName => $formHtml)
	{
		// sanitize form name
		$key = count($tabs) + (++$i);

		?>
		<div id="vretabview<?php echo $key; ?>" class="vretabview" style="<?php echo ($_selected_tab_view != $key ? 'display: none;' : ''); ?>">
			<?php echo $formHtml; ?>
		</div>
		<?php
	}
	?>
	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
// manage file modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-managetmpl',
	array(
		'title'       => JText::_('VRJMODALEMAILTMPL'),
		'closeButton' => true,
		'keyboard'    => false, 
		'bodyHeight'  => 80,
		'url'		  => '', // it will be filled dinamically
		'footer'      => '<button type="button" class="btn" data-role="file.savecopy">' . JText::_('VRSAVEASCOPY') . '</button>'
					   . '<button type="button" class="btn btn-success" data-role="file.save">' . JText::_('JAPPLY') . '</button>',
	)
);
?>

<script>
	
	jQuery(document).ready(function() {

		jQuery('select.short').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 100,
		});

		jQuery('select.small-medium').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 150,
		});

		jQuery('select.medium').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('select.medium-large').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 250,
		});

		jQuery('select.large').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 300,
		});

		// check if the URL requested a specific setting
		if (document.location.hash) {
			// get setting input (starts with the specified hash)
			var input = jQuery('*[name^="' + document.location.hash.replace(/^#/, '') + '"]');

			// find tab view to which the input belong
			var tabView = input.closest('.vretabview');

			// extract tabView index from ID
			var matches = tabView.attr('id').match(/^vretabview(\d+)$/);

			if (matches && matches.length) {
				// activate the tab view of the input
				changeTabView(matches[1]);
				// set the focus to the input
				jQuery(input).focus();
				// animate to the input position
				jQuery('html, body').animate({ scrollTop: input.offset().top - 200 });
			}
		}
		
	});

	// lock / unlock an input starting from the specified link

	function lockUnlockInput(link) {
		var input = jQuery(link).closest('td').find('input');

		if (input.prop('readonly')) {
			input.prop('readonly', false);

			jQuery(link).find('i').removeClass('fa-lock').addClass('fa-unlock-alt');
		} else {
			input.prop('readonly', true);

			jQuery(link).find('i').removeClass('fa-unlock-alt').addClass('fa-lock');
		}
	}
	
	// TAB LISTENERS
	
	var last_tab_view = <?php echo $_selected_tab_view; ?>;
	
	// switch configuration tab

	function changeTabView(tab_pressed) {
		if (tab_pressed != last_tab_view) {
			jQuery('.vretabli').removeClass('vreconfigtabactive');
			jQuery('#vretabli' + tab_pressed).addClass('vreconfigtabactive');
			
			jQuery('.vretabview').hide();
			jQuery('#vretabview' + tab_pressed).show();
			
			storeTabSelected(tab_pressed);
			
			last_tab_view = tab_pressed;
		}
	}

	function storeTabSelected(tab) {
		/**
		 * Store active tab in a cookie and keep it there until the session expires.
		 *
		 * @since 1.8
		 */
		document.cookie = 'vikrestaurants.config.tab=' + tab + '; path=/';
	}
	
	// open jmodal handler
	
	function vrOpenJModal(id, url, jqmodal) {
		<?php echo $vik->bootOpenModalJS(); ?>
	}

	/**********************
	 ***** SEARCH BAR *****
	 **********************/

	var searchBar = new SearchBar(false);

	// init search bar events

	jQuery(document).ready(function(){

		jQuery('#vre-search-param').on('keyup', function(event){
			
			var value = jQuery('#vre-search-param').val().toLowerCase();
			searchBar.setMatches(getParamsFromSearch(value));

			if (searchBar.isNull()) {
				hideSearchBar();
			} else {
				displaySearchBar();
				if (searchBar.size() > 0 && event.keyCode == 13) {
					goToCurrentMatch();
					jQuery('#vre-search-param').blur();
				}
			}
		});
		
		jQuery(window).on('scroll', function(){
			windowScrollControl(true);
		});
		
		jQuery(document).bind('keydown', function (event){
			if (searchBar.isNull() || jQuery('#vre-search-param').is(':focus')) {
				return;
			}
			
			switch (event.keyCode) {
				case 37: goToPrevMatch(); break; // left arrow > prev match
				case 39: goToNextMatch(); break; // right arrow > next match
				case 13: goToNextMatch(); break; // enter > next match
				case 27: hideSearchBar(); break; // esc > hide search bar
			}
		});

	});

	// get matches from value
	
	function getParamsFromSearch(value) {
		if (value.length == 0) {
			return false;
		}
		
		var matches = new Array();
		
		jQuery('.adminparamcol').each(function(){
			
			if(jQuery(this).text().toLowerCase().indexOf(value) != -1) {
				var style = jQuery(this).parent().attr('style');
				
				if (typeof style === 'undefined' || style.length === 0) {
					matches.push(jQuery(this));
				}
			}

		});
		
		return matches;
	}

	// display search bar on submit
	
	function displaySearchBar() {
		if (searchBar.size() == 0) {
			jQuery('.vre-config-searchbar-stat').text('<?php echo addslashes(JText::_('VRNOMATCHES')); ?>');
			jQuery('.vre-config-searchbar-control').hide();
		} else {
			jQuery('.vre-config-searchbar-stat').text('1/'+searchBar.size());
			jQuery('.vre-config-searchbar-control').show();
		}
		
		windowScrollControl(false);
		
		jQuery('.vre-config-searchbar').show();
	}

	// hide search bar
	
	function hideSearchBar() {
		jQuery('#vre-search-param').val('');
		jQuery('.vre-config-searchbar').hide();
		jQuery('.adminparamcol b').removeClass('badge vre-orange-badge');
		searchBar.clear();
	}

	// handle window scroll to display/hide GO TOP button
	
	function windowScrollControl(effect) {
		if (jQuery(window).scrollTop() <= 0) {
			
			if(effect) {
				jQuery('.vre-config-searchbar-gotop').fadeOut();
			} else {
				jQuery('.vre-config-searchbar-gotop').hide();
			}

		} else {

			if (effect) {
				jQuery('.vre-config-searchbar-gotop').fadeIn();
			} else {
				jQuery('.vre-config-searchbar-gotop').show();
			}

		}
	}

	// go to first match
	
	function goToCurrentMatch() {
		if (searchBar.size() == 0) {
			return;
		}
		
		var elem = searchBar.getElement();
		highlightMatch(elem);
		checkMatchParent(elem);
		animateToScrollTop( elem.offset().top-200 );
	}

	// go to previous match (to last match if cannot go back)
	
	function goToPrevMatch() {
		if (searchBar.size() == 0) {
			return;
		}
		
		var elem = searchBar.previous();
		highlightMatch(elem);
		checkMatchParent(elem);
		animateToScrollTop( elem.offset().top-200 );
		jQuery('.vre-config-searchbar-stat').text((searchBar.getCurrentIndex() + 1) + '/' + searchBar.size());
	}
	
	// go to next match (to first match if cannot go forward)

	function goToNextMatch() {
		if (searchBar.size() == 0) {
			return;
		}
		
		var elem = searchBar.next();
		highlightMatch(elem);
		checkMatchParent(elem);
		animateToScrollTop( elem.offset().top-200 );
		jQuery('.vre-config-searchbar-stat').text((searchBar.getCurrentIndex() + 1) + '/' + searchBar.size());
	}

	// animate scroll to find match
	
	function animateToScrollTop(px) {
		jQuery('html, body').stop(true, true).animate({ scrollTop: px });
	}

	// animate scroll to go back to the search bar
	
	function animateToPageTop() {
		jQuery('html, body').stop(true, true).animate({ scrollTop: 0 }).promise().done(function() {
			jQuery('#vre-search-param').focus();
		});
	}

	// highlight current match
	
	function highlightMatch(match) {
		jQuery('.adminparamcol b').removeClass('badge vre-orange-badge');
		match.children().first().addClass('badge vre-orange-badge');
	}

	// if the current match is not visible, find the section parent and show it
	
	function checkMatchParent(match) {
		var parent = match.parent();
		while (parent.length > 0 && !parent.hasClass('vretabview')) {
			parent = parent.parent();
		}
		
		if (parent.length > 0 && !parent.is(':visible')) {
			changeTabView(parent.attr('id').split('vretabview')[1]);
		}
	}

	var SELECTED_MAIL_TMPL_FIELD = null;
	var SELECTED_MAIL_TMPL_GROUP = null;

	/**
	 * Handle manage file events.
	 *
	 * @since 1.8
	 */
	jQuery(document).ready(function() {

		jQuery('button[data-role="file.save"]').on('click', function() {
			// trigger click of save button contained in managefile view
			window.modalFileSaveButton.click();
		});

		jQuery('button[data-role="file.savecopy"]').on('click', function() {
			// trigger click of savecopy button contained in managefile view
			window.modalFileSaveCopyButton.click();
		});

		jQuery('#jmodal-managetmpl').on('hidden', function() {
			// check if the file was saved
			if (window.modalSavedFile) {
				var selector, lookup;

				if (SELECTED_MAIL_TMPL_GROUP == 'restaurant') {
					selector = jQuery('select[name="mailtmpl"],select[name="adminmailtmpl"],select[name="cancmailtmpl"]');
					lookup   = MAIL_TMPL_LOOKUP;
				} else {
					selector = jQuery('select[name="tkmailtmpl"],select[name="tkadminmailtmpl"],select[name="tkcancmailtmpl"],select[name="tkstockmailtmpl"],select[name="tkreviewmailtmpl"]');
					lookup   = TKMAIL_TMPL_LOOKUP;
				}

				// insert file in all template dropdowns
				if (addTemplateFileIntoSelect(window.modalSavedFile, selector, lookup)) {
					// auto-select new option for the related select
					jQuery(SELECTED_MAIL_TMPL_FIELD).select2('val', window.modalSavedFile.name);
				}
			}
		});

		function addTemplateFileIntoSelect(file, selector, lookup) {
			if (lookup.hasOwnProperty(file.name)) {
				// file already in list
				return false;
			}

			// register file in lookup
			lookup[file.name] = file.base64;

			// insert new option within the select
			jQuery(selector).each(function() {
				jQuery(this).append('<option value="' + file.name + '">' + file.name + '</option>');
			});

			return true;
		}

	});

	/**
	 * Opens a new browser page to display
	 * a preview of the selected mail template.
	 *
	 * @param 	string 	group  The template group (restaurant or takeaway).
	 * @param 	string 	alias  The template alias (e.g. customer).
	 * @param 	string 	tmpl   The template file name.
	 *
	 * @since 	1.8
	 */
	function goToMailPreview(group, alias, tmpl) {
		// define base URL
		var url = 'index.php?option=com_vikrestaurants&task=configuration.mailpreview&tmpl=component';
		// append group
		url += '&group=' + group;
		// append template alias
		url += '&alias=' + alias;
		// extract mail template from select
		url += '&file=' + jQuery('select[name="' + tmpl + '"]').val();
		// always use current language
		url += '&langtag=<?php echo JFactory::getLanguage()->getTag(); ?>';

		// open URL in a blank tab of the browser
		window.open(url, '_blank');
	}
	
</script>
