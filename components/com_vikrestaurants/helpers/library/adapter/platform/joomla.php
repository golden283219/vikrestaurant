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

// this should be already loaded from autoload.php
VRELoader::import('library.adapter.version.listener');

/**
 * Helper class used to adapt the application to the requirements
 * of the installed Joomla! version.
 *
 * @see 	VersionListener 	Used to evaluate the current Joomla! version.
 *
 * @since  	1.8
 */
class VREApplicationJoomla extends VREApplication
{
	/**
	 * Backward compatibility for Joomla admin list <table> class.
	 *
	 * @return 	string 	The class selector to use.
	 */
	public function getAdminTableClass()
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return 'adminlist';
		}
		else if (VersionListener::isJoomla3x())
		{
			// 3.x
			return 'table table-striped';
		}
		else
		{
			// 4.x
			return 'table';
		}
	}
	
	/**
	 * Backward compatibility for Joomla admin list <table> head opening.
	 *
	 * @return 	string 	The <thead> tag to use.
	 */
	public function openTableHead()
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '';
		}
		else
		{
			// 3.x
			return '<thead>';
		}
	}
	
	/**
	 * Backward compatibility for Joomla admin list <table> head closing.
	 *
	 * @return 	string 	The </thead> tag to use.
	 */
	public function closeTableHead()
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '';
		}
		else
		{
			// 3.x
			return '</thead>';
		}
	}
	
	/**
	 * Backward compatibility for Joomla admin list <th> class.
	 *
	 * @param 	string 	$align 	The additional class to use for horizontal alignment.
	 *							Accepted rules should be: left, center or right.
	 *
	 * @return 	string 	The class selector to use.
	 */
	public function getAdminThClass($align = 'center')
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return 'title';
		}
		else
		{
			// 3.x
			return 'title ' . $align;
		}
	}
	
	/**
	 * Backward compatibility for Joomla admin list checkAll JS event.
	 *
	 * @param 	integer  The total count of rows in the table.	
	 *
	 * @return 	string 	 The check all checkbox input to use.
	 */
	public function getAdminToggle($count)
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '<input type="checkbox" name="toggle" value="" onclick="checkAll(' . $count . ');" />';
		}
		else
		{
			// 3.x
			return '<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle" />';
		}
	}
	
	/**
	 * Backward compatibility for Joomla admin list isChecked JS event.
	 *
	 * @return 	string 	The JS function to use.
	 */
	public function checkboxOnClick()
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return 'isChecked(this.checked);';
		}
		else
		{
			// 3.x
			return 'Joomla.isChecked(this.checked);';
		}
	}

	/**
	 * Helper method to send e-mails.
	 *
	 * @param 	string 	 $from_address	 The e-mail address of the sender.
	 * @param 	string 	 $from_name 	 The name of the sender.
	 * @param 	string 	 $to 			 The e-mail address of the receiver.
	 * @param 	string 	 $reply_address  The reply to e-mail address.
	 * @param 	string 	 $subject 		 The subject of the e-mail.
	 * @param 	string 	 $hmess 		 The body of the e-mail (HTML is supported).
	 * @param 	array 	 $attachments 	 The list of the attachments to include.
	 * @param 	boolean  $is_html 		 True to support HTML body, otherwise false for plain text.
	 * @param 	string 	 $encoding 		 The encoding to use.
	 *
	 * @return 	boolean  True if the e-mail was sent successfully, otherwise false.
	 */
	public function sendMail($from_address, $from_name, $to, $reply_address, $subject, $hmess, $attachments = null, $is_html = true, $encoding = 'base64')
	{
		$mailer = JFactory::getMailer();
		
		// encode subject only on Joomla 3.x
		if (VersionListener::isJoomla3x())
		{
			$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		}
		
		if ($is_html)
		{
			$hmess = "<html>\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head>\n<body>$hmess</body>\n</html>";
		}

		$sender = array($from_address, $from_name);
		$mailer->setSender($sender);
		$mailer->addRecipient($to);
		$mailer->addReplyTo($reply_address);
		$mailer->setSubject($subject);
		$mailer->setBody($hmess);
		$mailer->isHTML($is_html);

		$mailer->Encoding = $encoding;

		if ($attachments !== null && is_array($attachments))
		{
			foreach ($attachments as $attach)
			{
				if (!empty($attach) && file_exists($attach))
				{
					$mailer->addAttachment($attach);
				}
			}
		}

		return $mailer->Send();
	}

	/**
	 * Backward compatibility for Joomla add stylesheet.
	 *
	 * @param   string  $url      URL to the linked style sheet.
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9').
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'scriptid', 'async' => 'async', 'data-test' => 1).
	 *
	 * @return 	void
	 */
	public function addStyleSheet($url = '', $options = array(), $attribs = array())
	{
		if (empty($url))
		{
			return;
		}

		/**
		 * Add versioning to options array in order
		 * to reset the cache every time a new update is released.
		 * Versioning is used only in case it wasn't specified.
		 *
		 * @since 1.7.4
		 */
		if (!isset($options['version']))
		{
			// make sure the constant is defined
			if (defined('VIKRESTAURANTS_SOFTWARE_VERSION'))
			{
				$options['version'] = VIKRESTAURANTS_SOFTWARE_VERSION;
			}
		}
		else if (empty($options['version']) || $options['version'] == 'auto')
		{
			// unset versioning
			unset($options['version']);
		}
		
		if (VersionListener::isJoomla25())
		{
			// 2.5
			$doc = JFactory::getDocument();
			$doc->addStyleSheet($url, $options, $attribs);
		}
		else
		{
			// 3.x
			JHtml::_('stylesheet', $url, $options, $attribs);
		}
	}
	
	/**
	 * Backward compatibility for Joomla add script.
	 *
	 * @param   string  $file     Path to file.
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9').
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'scriptid', 'async' => 'async', 'data-test' => 1).
	 *
	 * @return 	void
	 */
	public function addScript($file = '', $options = array(), $attribs = array())
	{
		if (empty($file))
		{
			return;
		}

		/**
		 * Add versioning to options array in order
		 * to reset the cache every time a new update is released.
		 * Versioning is used only in case it wasn't specified.
		 *
		 * @since 1.7.4
		 */
		if (!isset($options['version']))
		{
			// make sure the constant is defined
			if (defined('VIKRESTAURANTS_SOFTWARE_VERSION'))
			{
				$options['version'] = VIKRESTAURANTS_SOFTWARE_VERSION;
			}
		}
		else if (empty($options['version']) || $options['version'] == 'auto')
		{
			// unset versioning
			unset($options['version']);
		}
		
		if (VersionListener::isJoomla25())
		{
			// 2.5
			$doc = JFactory::getDocument();
			$doc->addScript($file, $options, $attribs);
		}
		else
		{
			// 3.x
			JHtml::_('script', $file, $options, $attribs);
		}
	}
	
	/**
	 * Backward compatibility for Joomla framework loading.
	 *
	 * @param 	string 	$fw 	The framework to load. 
	 */
	public function loadFramework($fw = '')
	{
		if (empty($fw))
		{
			return;
		}
		
		if (VersionListener::isJoomla25())
		{
			/**
			 * Backward compatibility for Joomla 2.5
			 *
			 * @since 1.8
			 */
			$this->addScript(VREASSETS_URI . 'js/jquery.min.js');
		}
		else
		{
			// 3.x
			JHtml::_($fw, true);
		}
	}
	
	/**
	 * Backward compatibility for punycode conversion.
	 *
	 * @param 	string 	$mail 	The e-mail to convert in punycode.
	 *
	 * @return 	string 	The punycode conversion of the e-mail.
	 *
	 * @since 	1.4
	 */
	public function emailToPunycode($email = '')
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return $email;
		}
		else
		{
			// 3.x
			return JStringPunycode::emailToPunycode($email);
		}
	}

	/**
	 * Backward compatibility for card/row opening.
	 *
	 * @param 	string 	$class 	 The class attribute for the fieldset.
	 * @param 	string 	$id 	 The ID attribute for the fieldset.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.8.5
	 */
	public function openCard($class = '', $id = '')
	{
		if (VersionListener::isJoomla3x())
		{
			// 3.x
			return '<div class="row-fluid' . ($class ? ' ' . $class : '') . '"' . ($id ? ' id="' . $id . '' : '') . '>';
		}
		else
		{
			// 4.x
			return '<div class="card' . ($class ? ' ' . $class : '') . '"' . ($id ? ' id="' . $id . '' : '') . '>'
				. '<div class="card-body">'
				. '<div class="row-fluid">';
		}
	}

	/**
	 * Backward compatibility for card/row closing.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.8.5
	 */
	public function closeCard()
	{
		if (VersionListener::isJoomla3x())
		{
			// 3.x
			return '</div>';
		}
		else
		{
			// 4.x
			return '</div></div></div>';
		}
	}

	/**
	 * Backward compatibility for Joomla fieldset opening.
	 *
	 * @param 	string 	$legend  The title of the fieldset.
	 * @param 	string 	$class 	 The class attribute for the fieldset.
	 * @param 	string 	$id 	 The ID attribute for the fieldset.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function openFieldset($legend, $class = '', $id = '')
	{
		$id = $id ? ' id="' . $id . '"' : '';

		if (VersionListener::isJoomla25())
		{
			// 2.5
			return (!empty($legend) ? '<h2>' . $legend . '</h2>' : '') . '<table class="adminform ' . $class . '"' . $id . '>';
		}
		else
		{
			// 3.x, 4.x
			$class = $class ? preg_split("/\s+/", $class) : array();
			
			if (VersionListener::isJoomla3x())
			{
				// 3.x
				$class[] = 'form-horizontal';
			}
			else
			{
				// 4.x
				$class[] = 'options-form';
			}

			return '<fieldset class="' . implode(' ', array_unique($class)) . '"' . $id . '>'
				. ($legend ? '<legend>' . $legend . '</legend>' : '');
		}
	}
	
	/**
	 * Backward compatibility for Joomla fieldset closing.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function closeFieldset()
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '</table>';
		}
		else
		{
			// 3.x
			return '</fieldset>';
		}
	}

	/**
	 * Backward compatibility for Joomla empty fieldset opening.
	 *
	 * @param 	string 	$class 	An additional class to use for the fieldset.
	 * @param 	string 	$id 	The ID attribute for the fieldset.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function openEmptyFieldset($class = '', $id = '')
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return $this->openFieldset('');
		}
		else
		{
			// 3.x
			return $this->openFieldset('', $class, $id);
		}
	}
	
	/**
	 * Backward compatibility for Joomla empty fieldset opening.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function closeEmptyFieldset()
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return $this->closeFieldset();
		}
		else
		{
			// 3.x
			return $this->closeFieldset();
		}
	}
	
	/**
	 * Backward compatibility for Joomla control opening.
	 *
	 * @param 	string 	$label 	The label of the control field.
	 * @param 	string 	$class 	The class of the control field.
	 * @param 	mixed 	$attr 	The additional attributes to add (string or array).
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function openControl($label, $class = '', $attr = '')
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '<tr class="' . $class . '" ' . $attr . '>
				<td width="200"><b>' . $label . '</b></td>
				<td>';
		}
		else
		{
			$class = 'control-group' . ($class ? ' ' . $class : '');

			/**
			 * Added support for attributes array.
			 *
			 * @since 1.8
			 */
			if (is_array($attr))
			{
				$tmp = '';

				if (isset($attr['id']))
				{
					// unset field ID attribute
					unset($attr['id']);
				}

				foreach ($attr as $k => $v)
				{
					// use ID attribute in case of "idparent"
					$k = $k == 'idparent' ? 'id' : $k;

					$tmp .= ' ' . $k . '="' . htmlspecialchars($v) . '"';
				}

				$attr = $tmp;
			}
			
			// 3.x
			return '<div class="' . $class . '" ' . trim($attr) . '>
				<div class="control-label">
					<b>' . $label . '</b>
				</div>
				<div class="controls">';
		}
	}
	
	/**
	 * Backward compatibility for Joomla control closing.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function closeControl()
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '</td></tr>';
		}
		else
		{
			// 3.x
			return '</div></div>';
		}
	}

	/**
	 * Returns the specified editor.
	 *
	 * @param 	mixed	 $editor  The editor to load.
	 * 							  The default one if not specified.
	 *
	 * @return 	JEditor  The editor instance.
	 *
	 * @since 	1.8.3
	 */
	public function getEditor($editor = null)
	{
		if (VersionListener::isJoomla4x())
		{
			// load default one if not specified
			$editor = $editor ? $editor : JFactory::getApplication()->get('editor');

			// return editor instance
			return JEditor::getInstance($editor);
		}
		else
		{
			// fallback to default JFactory method
			return JFactory::getEditor($editor);
		}
	}

	/**
	 * Returns the codemirror editor in Joomla 3.x, otherwise a simple textarea.
	 *
	 * @param 	string 	$name 	The name of the textarea.
	 * @param 	string 	$value 	The value of the textarea.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function getCodeMirror($name, $value)
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '<textarea name="' . $name . '" style="width: 100%;height: 520px;">' . $value . '</textarea>';
		}
		else
		{
			// 3.x
			return JEditor::getInstance('codemirror')
				->display($name, $value, '600', '600', 30, 30, false);
		}
	}

	/**
	 * Backward compatibility for Joomla Bootstrap tabset opening.
	 *
	 * @param 	string 	$group 	The group of the tabset.
	 * @param 	string 	$attr 	The attributes to use.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function bootStartTabSet($group, $attr = array())
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '';
		}
		else
		{
			/**
			 * In case the cookie attribute was included within the,
			 * array, we can register the script that will be used to 
			 * handle the tab changes. The last selected tab will
			 * be stored in a cookie in order to be pre-selected
			 * when refreshing the page.
			 *
			 * @since 1.8
			 */
			if (isset($attr['cookie']))
			{
				$this->bootstrapTabSetCookie = '<script>' . JHtml::_('vrehtml.scripts.tabhandler', $group, $attr['cookie']) . '</script>';
			}
			else
			{
				$this->bootstrapTabSetCookie = '';
			}

			if (VersionListener::isJoomla3x())
			{
				// 3.x
				return JHtml::_('bootstrap.startTabSet', $group, $attr);
			}
			else
			{
				// 4.x
				return JHtml::_('uitab.startTabSet', $group, $attr);
			}
		}
	}
	
	/**
	 * Backward compatibility for Joomla Bootstrap tabset closing.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function bootEndTabSet()
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '';
		}
		else
		{
			if (VersionListener::isJoomla3x())
			{
				// 3.x
				$html = JHtml::_('bootstrap.endTabSet');
			}
			else
			{
				// 4.x
				$html = JHtml::_('uitab.endTabSet');
			}

			/**
			 * Append the 'cookie' script after creating the tabset.
			 *
			 * @since 1.8
			 */
			return $html . $this->bootstrapTabSetCookie;
		}
	}
	
	/**
	 * Backward compatibility for Joomla Bootstrap add tab.
	 *
	 * @param 	string 	$group 	  The tabset parent group.
	 * @param 	string 	$id 	  The id of the tab.
	 * @param 	string 	$label 	  The title of the tab.
	 * @param 	array 	$options  A list of options.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function bootAddTab($group, $id, $label, array $options = array())
	{
		/**
		 * In case the badge option is specified, append a badge
		 * to the tab label, displaying the count of records.
		 *
		 * @since 1.8
		 */
		if (isset($options['badge']))
		{
			$badge_class = isset($options['badge']['class']) ? $options['badge']['class'] : 'badge-info';
			$badge_id    = isset($options['badge']['id'])    ? $options['badge']['id']    : $id . '_tab_badge';
			$badge_count = isset($options['badge']['count']) ? $options['badge']['count'] : (int) $options['badge'];

			$label .= "<span class=\"badge {$badge_class} tab-badge-count\" id=\"{$badge_id}\" data-count=\"{$badge_count}\"> </span>";
		}

		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '<h3>' . $label . '</h3>';
		}
		else if (VersionListener::isJoomla3x())
		{
			// 3.x
			return JHtml::_('bootstrap.addTab', $group, $id, $label);
		}
		else
		{
			// 4.x
			return JHtml::_('uitab.addTab', $group, $id, $label);
		}
	}
	
	/**
	 * Backward compatibility for Joomla Bootstrap end tab.
	 *
	 * @return 	string 	The html to display.
	 *
	 * @since 	1.6
	 */
	public function bootEndTab()
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '';
		}
		else if (VersionListener::isJoomla3x())
		{
			// 3.x
			return JHtml::_('bootstrap.endTab');
		}	
		else
		{
			// 4.x
			return JHtml::_('uitab.endTab');
		}
	}

	/**
	 * Backward compatibility for Joomla Bootstrap open modal JS event.
	 *
	 * @param 	string 	$onclose 	The javascript function to call on close event.
	 *
	 * @return 	string 	The javascript function.
	 *
	 * @since 	1.6
	 */
	public function bootOpenModalJS($onclose = '')
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			return "jQuery('#jmodal-' + id).css('marginLeft', '0px');
				jQuery('.modal-header .close').hide();
				jQuery('#jmodal-' + id).dialog({
					resizable: true,
					height: 600,
					width: 750,
					" . (!empty($close) ? "close:$onclose," : "") . "
					modal: true
				});
				jQuery('#jmodal-' + id).trigger('show');
				return false;";
		}
		else
		{
			// 3.x
			return "var modal = jQuery('#jmodal-' + id).modal('show');
				if(url) {
					var iframe = modal.find('iframe');

					if (iframe.length == 0) {
						modal.find('.modal-body').prepend('<iframe src=\"' + url + '\"></iframe>');
					} else {
						iframe.attr('src', url);
					}
				}
				" . (!empty($onclose) ? "modal.off('hidden').on('hidden', " . $onclose . ");" : "") . "
				return false;";
		}
	}
	
	/**
	 * Backward compatibility for Joomla Bootstrap dismiss modal JS event.
	 *
	 * @param 	string 	$selector 	The selector to identify the modal box.
	 *
	 * @return 	string 	The javascript function.
	 *
	 * @since 	1.6
	 */
	public function bootDismissModalJS($selector = null)
	{
		if (!is_null($selector))
		{
			/**
			 * The static selector is no more supported due
			 * to the possibility of conflicts between 2 or more
			 * modals loaded within the same page.
			 *
			 * @deprecated 1.9
			 */
			JFactory::getDocument()->addScriptDeclaration('console.warn("The selector is deprecated in ' . __METHOD__ . '()");');
		}

		if (VersionListener::isJoomla25())
		{
			// 2.5
			return "jQuery('#jmodal-' + id).dialog('close');";
		}
		else
		{
			// 3.x
			return "jQuery('#jmodal-' + id).modal('toggle');";
		}
	}

	/**
	 * Backward compatibility to fit the layout of the left main menu.
	 *
	 * @param 	JDocument 	$document 	The base Joomla document.
	 *
	 * @since 	1.7
	 */
	public function fixContentPadding($document = null)
	{
		if (is_null($document))
		{
			$document = JFactory::getDocument();
		}

		if (VersionListener::isJoomla25())
		{
			// 2.5
			$document->addStyleDeclaration(
<<<CSS
/* main menu adapter */
.vre-leftboard-menu .title a {color: #fff !important;}
.vre-leftboard-menu .custom a {color: #fff !important;}
CSS
			);
		}
		else
		{
			// 3.x

			/**
			 * Do not stick menu in case of devices with smaller screens.
			 *
			 * @since 1.8
			 */
			$document->addStyleDeclaration(
<<<CSS
/* main menu adapter */
@media screen and (min-width: 780px) {
	.subhead-collapse{margin-bottom: 0 !important;}
	.container-fluid.container-main{margin: 0 !important;padding: 0 !important;}
	#system-message-container{padding: 0px 5px 0 5px;}
	#system-message-container .alert{margin-top: 10px;}
}
CSS
			);
		}
	}

	/**
	 * Add javascript support for Bootstrap popovers.
	 *
	 * @param 	string 	$selector   Selector for the popover.
	 * @param 	array 	$options     An array of options for the popover.
	 * 					Options for the popover can be:
	 * 						animation  boolean          apply a css fade transition to the popover
	 *                      html       boolean          Insert HTML into the popover. If false, jQuery's text method will be used to insert
	 *                                                  content into the dom.
	 *                      placement  string|function  how to position the popover - top | bottom | left | right
	 *                      selector   string           If a selector is provided, popover objects will be delegated to the specified targets.
	 *                      trigger    string           how popover is triggered - hover | focus | manual
	 *                      title      string|function  default title value if `title` tag isn't present
	 *                      content    string|function  default content value if `data-content` attribute isn't present
	 *                      delay      number|object    delay showing and hiding the popover (ms) - does not apply to manual trigger type
	 *                                                  If a number is supplied, delay is applied to both hide/show
	 *                                                  Object structure is: delay: { show: 500, hide: 100 }
	 *                      container  string|boolean   Appends the popover to a specific element: { container: 'body' }
	 *
	 * @since 	1.7
	 */
	public function attachPopover($selector = '.vrPopover', array $options = array())
	{
		if (VersionListener::isJoomla25())
		{
			// 2.5
			JFactory::getDocument()->addStyleDeclaration("jQuery(document).ready(function(){jQuery('{$selector}').tooltip();}");
		}
		else if (VersionListener::isJoomla3x())
		{
			// 3.x
			JHtml::_('bootstrap.popover', $selector, $options);
		}
		else
		{
			// 4.x
			static $pool = [];

			if (!$pool)
			{
				// Autoload popover only once.
				// Use a selector that does not exist.
				JHtml::_('bootstrap.popover', '.tag_placeholder_for_j4', $options);
			}

			if (!isset($pool[$selector]))
			{
				// init popover for the specified selector only once
				$pool[$selector] = 1;

				JFactory::getDocument()->addScriptDeclaration(
<<<JS
(function($) {
	'use strict';

	$(function() {
		// init popover via JS after the page loading
		__vikrestaurants_j40_popover_init('{$selector}');
	});
})(jQuery);
JS
				);
			}
		}
	}

	/**
	 * Create a standard tag and attach a popover event.
	 * NOTE. FontAwesome framework MUST be loaded in order to work.
	 *
	 * @param 	string 	$selector   Selector for the popover.
	 * @param 	array 	$options     An array of options for the popover.
	 *
	 * @see 	VREApplication::attachPopover() for further details about options keys.
	 *
	 * @since 	1.7
	 */
	public function createPopover(array $options = array())
	{
		$options['content']   = isset($options['content'])   ? $options['content']   : '';
		$options['placement'] = isset($options['placement']) ? $options['placement'] : 'right';
		$options['trigger']   = isset($options['trigger'])   ? $options['trigger']   : 'hover focus';

		/**
		 * Added support for custom popover classes.
		 *
		 * @since 1.8
		 */
		$class = 'vr-quest-popover';

		if (isset($options['class']))
		{
			$class = htmlspecialchars($options['class'], ENT_QUOTES);
			unset($options['class']);
		}

		$icon = 'question-circle';

		if (isset($options['icon']))
		{
			$icon = $options['icon'];
			unset($options['icon']);
		}

		// init data
		$data = array();

		foreach ($options as $k => $v)
		{
			// copy all except for title and content
			if ($k !== 'title' && $k !== 'content')
			{
				$data[$k] = $v;
			}
		}

		/**
		 * Initialize using provided data.
		 * Title and content will be filled from tag attributes.
		 *
		 * @since 1.8
		 */
		$this->attachPopover('.' . $class, $data);

		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '<i class="fas fa-' . $icon . ' ' . $class . '" title="' . $options['content'] . '"></i>';
		}
		else
		{
			// 3.x or 4.x

			// check if we are using J4
			$j4 = VersionListener::isJoomla4x();

			if ($j4)
			{
				// move content into the property used by Bootstrap 5 (data-bs-content)
				$options['bs-content'] = $options['content'];
			}

			$attr = '';

			foreach ($options as $k => $v)
			{
				// In Joomla 4 (Bootstrap 5) the title is fetched from the native
				// "title" attribute, for this reason we should not use anymore
				// the previous "data-title" attribute in the latest versions of 
				// Joomla.
				if (!$j4 || $k !== 'title')
				{
					// prepend "data-"
					$k = 'data-' . $k;
				}

				$attr .= $k . '="' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '" ';
			}

			return '<i class="fas fa-' . $icon . ' ' . $class . '" ' . $attr . '></i>';
		}
	}

	/**
	 * Create a text span and attach a popover event.
	 *
	 * @param 	array 	$options    An array of options for the popover.
	 *
	 * @see 	VREApplication::attachPopover() for further details about options keys.
	 *
	 * @since 	1.8
	 */
	public function textPopover(array $options = array())
	{
		$options['title'] 	= isset($options['title']) ? $options['title'] : '';
		$options['content'] = isset($options['content']) ? $options['content'] : '';
		$options['trigger'] = isset($options['trigger']) ? $options['trigger'] : 'hover focus';

		$class = 'vre-text-popover';

		if (isset($options['class']))
		{
			$class = $options['class'];
			unset($options['class']);
		}

		// attach an empty array option so that the data will be recovered 
		// directly from the tag during the runtime
		$this->attachPopover('.' . $class, array());

		if (VersionListener::isJoomla25())
		{
			// 2.5
			return '<span class="' . $class . '" title="' . $options['content'] . '">' . $options['title'] . '</span>';
		}
		else
		{
			// 3.x
			$attr = '';
			foreach ($options as $k => $v)
			{
				$attr .= 'data-' . $k . '="' . str_replace('"', '&quot;', $v) . '" ';
			}

			return '<span class="' . $class . '" ' . $attr . '>' . $options['title'] . '</span>';
		}
	}

	/**
	 * Return the date format specs.
	 *
	 * @param 	string 	$format 	  The format to use.
	 * @param 	array 	&$attributes  Some attributes to use.
	 *
	 * @return 	string 	The adapted date format.
	 *
	 * @since 	1.7.1
	 */
	public function jdateFormat($format = null, array &$attributes = array())
	{
		if ($format === null)
		{
			$format = VREFactory::getConfig()->getString('dateformat');

			if (!empty($attributes['showTime']))
			{
				// concat the time format (24 hours format only)
				$format .= ' %H:%M';
			}
		}

		$format = str_replace('Y', '%Y', $format);
		$format = str_replace('m', '%m', $format);
		$format = str_replace('d', '%d', $format);

		return $format;
	}

	/**
	 * Provides support to handle the Joomla calendar across different frameworks.
	 *
	 * @param 	mixed 	$value 		 The date or the timestamp to fill.
	 * @param 	string 	$name 		 The input name.
	 * @param 	string 	$id 		 The input id attribute.
	 * @param 	string 	$format 	 The date format.
	 * @param 	array 	$attributes  Some attributes to use.
	 * 
	 * @return 	string 	The calendar field.
	 *
	 * @since 	1.7.1
	 */
	public function calendar($value, $name, $id = null, $format = null, array $attributes = array())
	{
		$html = '';

		// check if we have a timestamp to handle
		if (preg_match("/^\d+$/", $value))
		{
			$config = VREFactory::getConfig();
			// get date format
			$conv_format = $config->get('dateformat');
			// use time format too in case we need to show also the time
			if (!empty($attributes['showTime']))
			{
				// use 24 hours format only
				$conv_format .=  ' H:i';
			}
			// convert the timestamp in a string date
			$value = date($conv_format, $value);
		}

		if ($id === null)
		{
			$id = $name;
		}

		if ($format === null)
		{
			$format = $this->jdateFormat(null, $attributes);
		}

		if (VersionListener::isJoomla37() || VersionListener::isHigherThan(VersionListener::J37))
		{
			// make sure to display the clear | today | close buttons
			$attributes['todayBtn'] = isset($attributes['todayBtn']) ? $attributes['todayBtn'] : 'true';

			// never fill the value within the calendar creation method to 
			// avoid Joomla parsing a wrong date format
			$html = JHtml::_('calendar', '', $name, $id, $format, $attributes);

			// if the value if set, make sure it has been filled in
			if ($value)
			{
				// Considering that the Joomla validation may not recognize the 
				// specified format, we need to fill manually the value via Javascript 
				// if the datepicker field is empty.
				JFactory::getDocument()->addScriptDeclaration("jQuery(document).ready(function(){
					if (jQuery('#$id').val().length == 0) {
						jQuery('#$id').val('$value').attr('data-alt-value', '$value');
					}
				});");
			}
		}
		else
		{
			$html = JHtml::_('calendar', '', $name, $id, $format, $attributes);

			if (isset($attributes['onChange']))
			{
				JFactory::getDocument()->addScriptDeclaration("jQuery('#{$id}_img').on('change', function(){
					jQuery('.day').on('change', function(){
						{$attributes['onChange']}
					});
				});");

				// remove to avoid duplicated events
				unset($attributes['onChange']);
			}

			if (!empty($value))
			{
				JFactory::getDocument()->addScriptDeclaration("jQuery(document).ready(function(){
					jQuery('#{$id}').val('$value');
				});");
			}

		}

		return $html;
	}

	/**
	 * Method used to obtain a Joomla media form field.
	 *
	 * @return 	string 	The media in HTML.
	 *
	 * @since 	1.8.5
	 */
	public function getMediaField($name, $value = null, array $data = array())
	{
		// init media field
		$field = new JFormFieldMedia(null, $value);
		// setup an empty form as placeholder
		$field->setForm(new JForm('managepayment.media'));

		// force field attributes
		$data['name']  = $name;
		$data['value'] = $value;

		if (empty($data['previewWidth']))
		{
			// there is no preview width, set a defualt value
			// to make the image visible within the popover
			$data['previewWidth'] = 480;	
		}

		/**
		 * J4 requires us to use the magic method to set the id and types properties
		 * or the media manager won't work in case of multiple instances on the same page
		 * nor would it display any image unless the proper types is passed.
		 */
		if (VersionListener::isJoomla4x())
		{
			static $index = 1;
			// use an incremental value for the id
			$field->id = preg_replace("/[^A-Za-z0-9\-_]+/", '', $name) . $index++;
			// accept all images
			$field->types = 'images';
		}

		// render the field	
		return $field->render('joomla.form.field.media', $data);
	}

	/**
	 * Method used to handle the reCAPTCHA events.
	 *
	 * @param 	string 	$event 		The reCAPTCHA event to trigger.
	 * 								Here's the list of the accepted events:
	 * 								- display 	Returns the HTML used to 
	 *											display the reCAPTCHA input.
	 *								- check 	Validates the POST data to make sure
	 * 											the reCAPTCHA input was checked.
	 * @param 	array  	$options 	A configuration array.
	 *
	 * @return 	mixed 	The event response.
	 *
	 * @since 	1.7.4
	 */
	public function reCaptcha($event = 'display', array $options = array())
	{
		// obtain global dispatcher and load captcha plugins
		$dispatcher = VREFactory::getEventDispatcher();
		$dispatcher->import('captcha');

		/**
		 * Use dynamic ID to support multiple ReCaptcha within the same pages.
		 *
		 * @since 1.8.2
		 */
		static $id = 1;
		
		if ($event == 'check')
		{
			try
			{
				/**
				 * Let the response code is always retrieved by the
				 * ReCaptcha plugin.
				 *
				 * Since Joomla 3.9.15 an error occurred because
				 * the dispatcher always passed the default caller
				 * options array to the plugin, which was considering
				 * it as the code to use for the validation.
				 *
				 * @since 1.8
				 */
				$code = null;

				// check the reCAPTCHA answer
				$res = $dispatcher->is('onCheckAnswer', array($code));
			}
			catch (Exception $err)
			{
				// possible SPAM, avoid breaking the flow
				// and return a failed response
				$res = false;
			}

			// Filter the responses returned by the plugins.
			// Return true if there is still a successful element within the list.
			return $res;
		}
		else if ($event == 'display')
		{
			// show reCAPTCHA input
			$dispatcher->trigger('onInit', array('vre_dynamic_recaptcha_' . $id));
			$res = $dispatcher->triggerOnce('onDisplay', array(null, 'vre_dynamic_recaptcha_' . $id, 'required'));

			// increase ID by one
			$id++;

			// return the first succesful result
			return (string) $res;
		}
	}

	/**
	 * Checks if the com_user captcha is configured.
	 * In case the parameter is set to global, the default one
	 * will be retrieved.
	 * 
	 * @param 	string 	 $plugin  The plugin name to check. Leave empty
	 * 							  to use any type of captcha.
	 *
	 * @return 	boolean  True if configured, otherwise false.
	 *
	 * @since 	1.7.4
	 */
	public function isCaptcha($plugin = null)
	{
		// get global captcha
		$defCaptcha = JFactory::getApplication()->get('captcha', null);
		// in case the user config is set to "use global", the default one will be used
		$captcha 	= JComponentHelper::getParams('com_users')->get('captcha', $defCaptcha);

		if ($captcha)
		{
			// check whether we should look for a specific captcha
			if (empty($plugin) || !strcasecmp($captcha, $plugin))
			{
				// no specified captcha or matching type
				return true;
			}
		}

		// missing captcha configuration
		return false;
	}

	/**
	 * Checks if the global captcha is configured.
	 * 
	 * @param 	string 	 $plugin  The plugin name to check. Leave empty
	 * 							  to use any type of captcha.
	 *
	 * @return 	boolean  True if configured, otherwise false.
	 *
	 * @since 	1.7.4
	 */
	public function isGlobalCaptcha($plugin = null)
	{
		// get global captcha
		$captcha = JFactory::getApplication()->get('captcha', null);

		if ($captcha)
		{
			// check whether we should look for a specific captcha
			if (empty($plugin) || !strcasecmp($captcha, $plugin))
			{
				// no specified captcha or matching type
				return true;
			}
		}

		// missing captcha configuration
		return false;
	}

	/**
	 * Rewrites an internal URI that needs to be used outside of the website.
	 * This means that the routed URI MUST start with the base path of the site.
	 *
	 * @param 	mixed 	 $query 	The query string or an associative array of data.
	 * @param 	boolean  $xhtml  	Replace & by &amp; for XML compliance.
	 * @param 	mixed 	 $itemid 	The itemid to use. If null, the current one will be used.
	 *
	 * @return 	string 	The complete routed URI.
	 *
	 * @since 	1.7.4
	 */
	public function routeForExternalUse($query = '', $xhtml = true, $itemid = null)
	{
		$app = JFactory::getApplication();

		if (is_array($query))
		{
			// make sure the array is not empty
			if ($query)
			{
				$query = '?' . http_build_query($query);
			}
			else
			{
				$query = '';
			}

			// the query is an array, build the query string
			$query = 'index.php' . $query;
		}

		if (is_null($itemid) && $app->isClient('site'))
		{
			// no item id, get it from the request
			$itemid = $app->input->getInt('Itemid', 0);
		}

		if ($itemid)
		{
			if ($query)
			{
				// check if the query string contains a '?'
				if (strpos($query, '?') !== false)
				{
					// the query already starts with 'index.php?' or '?'
					$query .= '&';
				}
				else
				{
					// the query string is probably equals to 'index.php'
					$query .= '?';
				}
			}
			else
			{
				// empty query, create the default string
				$query = 'index.php?';
			}

			// the item id is set, append it at the end of the query string
			$query .= 'Itemid=' . $itemid;
		}

		// get base path
		$uri  = JUri::getInstance();
		$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		// route the query string and append it to the base path to create the final URI
		$uri = $base . JRoute::_($query, $xhtml);

		// remove administrator/ from URL in case this method is called from admin
		if ($app->isClient('administrator'))
		{
			$adminPos 	= strrpos($uri, 'administrator/');
			$uri 		= substr_replace($uri, '', $adminPos, 14);
		}

		return $uri;
	}

	/**
	 * Routes an admin URL for being used outside from the website (complete URI).
	 *
	 * @param 	mixed 	 $query 	The query string or an associative array of data.
	 * @param 	boolean  $xhtml  	Replace & by &amp; for XML compliance.
	 *
	 * @return 	string 	The complete routed URI. 
	 *
	 * @since 	1.8
	 */
	public function adminUrl($query = '', $xhtml = true)
	{
		$app = JFactory::getApplication();

		if (is_array($query))
		{
			// make sure the array is not empty
			if ($query)
			{
				$query = '?' . http_build_query($query);
			}
			else
			{
				$query = '';
			}

			// the query is an array, build the query string
			$query = 'index.php' . $query;
		}

		// finalise admin URI
		$uri = JUri::root() . 'administrator/' . $query;

		if ($xhtml)
		{
			$uri = str_replace('&', '&amp;', $uri);
		}

		return $uri;
	}

	/**
	 * Prepares a plain/routed URL to be used for an AJAX request.
	 *
	 * @param 	mixed 	 $query 	The query string or a routed URL.
	 * @param 	boolean  $xhtml  	Replace & by &amp; for XML compliance.
	 *
	 * @return 	string 	The AJAX end-point URI.
	 *
	 * @since 	1.8.3
	 */
	public function ajaxUrl($query = '', $xhtml = false)
	{
		if (JFactory::getApplication()->isClient('site') && preg_match("/^index\.php/", $query))
		{
			// rewrite plain URL
			$uri = JRoute::_($query, $xhtml);
		}
		else
		{
			// routed URL given (or admin location), use it directly
			$uri = $query;

			if ($xhtml)
			{
				// try to make "&" XML safe
				$uri = preg_replace("/&(?!amp;)/", '&amp;', $uri);
			}
		}

		return $uri;
	}
	
	/**
	 * Prepares the specified content before being displayed.
	 *
	 * @param 	mixed  &$content  The table content instance or a string to fetch.
	 * @param 	mixed  $params 	  True to apply the full description, false to apply 
	 * 							  the short description, if any. Any other non scalar 
	 * 							  value to pass a configuration for plugins.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public function onContentPrepare(&$content, $params = array())
	{
		$pattern = "/<hr\s+id=(\"|')system-readmore(\"|')\s*\/*>/i";

		// create Content table in case a string was passed
		if (is_string($content))
		{
			$text = $content;

			$content = JTable::getInstance('content');
			$content->text = $text;
		}

		if (is_bool($params))
		{
			// BC: take the specified type of text (full or short)
			$full = $params;

			// use an empty array
			$params = array();
		}
		else
		{
			/**
			 * Extract type of text from parameters.
			 * If not specified, the full text will be used.
			 *
			 * @since 1.8.3
			 */
			$full = isset($params['fulltext']) ? (bool) $params['fulltext'] : true;
		}

		// import content plugins
		$dispatcher = VREFactory::getEventDispatcher();
		$dispatcher->import('content');

		/**
		 * This is the first stage in preparing content for output and is the
		 * most common point for content orientated plugins to do their work.
		 * Since the article and related parameters are passed by reference,
		 * event handlers can modify them prior to display.
		 *
		 * @param 	string   $context   The context of the content being
		 * 								passed to the plugin.
		 * @param 	JTable   &$article  A reference to the article that is
		 * 								being rendered by the view.
		 * @param 	mixed    &$params   A reference to an associative array 
		 * 								of relevant parameters.
		 * @param 	integer  $page      An integer that determines the "page"
		 * 								of the content that is to be generated.
		 *
		 * @return 	void
		 *
		 * @since 	1.8.3
		 */
		$dispatcher->trigger('onContentPrepare', array('com_vikrestaurants', &$content, &$params, 0));

		// check if the description owns a readmore separator
		if (preg_match($pattern, $content->text))
		{
			// split the description in 2 chunks
			$chunks = preg_split($pattern, $content->text, 2);

			// overwrite text with short (0) or full (1) description
			$content->text = $chunks[$full ? 1 : 0];

			/**
			 * Register intro and full texts too.
			 *
			 * @since 1.8.3
			 */
			$content->introtext = $chunks[0];
			$content->fulltext  = $chunks[1];
		}
	}

	/**
	 * Returns a list of supported payment gateways.
	 *
	 * @return 	array 	A list of paths.
	 *
	 * @since 	1.8
	 */
	public function getPaymentDrivers()
	{
		VRELoader::import('library.payment.factory');

		/**
		 * Load the available payment drivers through the new framework.
		 *
		 * @since 1.8.5
		 */
		return VREPaymentFactory::getSupportedDrivers();
	}

	/**
	 * Returns the configuration form of a payment.
	 *
	 * @param 	string 	$payment  The name of the payment.
	 *
	 * @return 	mixed 	The configuration array/object.
	 *
	 * @since 	1.8
	 */
	public function getPaymentConfig($payment)
	{
		VRELoader::import('library.payment.factory');

		/**
		 * Load the configuration form of the requested driver through
		 * the new framework.
		 *
		 * @since 1.8.5
		 */
		return VREPaymentFactory::getPaymentConfig($payment);
	}

	/**
	 * Provides a new payment instance for the specified arguments.
	 *
	 * @param 	string 	  $payment 	The name of the payment that should be instantiated.
	 * @param 	mixed 	  $order 	The details of the order that has to be paid.
	 * @param 	mixed 	  $config 	The payment configuration array or a JSON string.
	 *
	 * @return 	mixed 	  The payment instance.
	 *
	 * @throws 	RuntimeException
	 *
	 * @since 	1.8
	 */
	public function getPaymentInstance($payment, $order = array(), $config = array())
	{
		VRELoader::import('library.payment.factory');
		
		/**
		 * Creates a paymentinstance for the requested driver through
		 * the new framework.
		 *
		 * @since 1.8.5
		 */
		return VREPaymentFactory::getPaymentInstance($payment, $order, $config);
	}

	/**
	 * Returns a list of supported SMS providers.
	 *
	 * @return 	array 	A list of paths.
	 *
	 * @since 	1.8
	 */
	public function getSmsDrivers()
	{
		// return a list of PHP files contained within the admin/smsapi folder
		return glob(VREADMIN . DIRECTORY_SEPARATOR . 'smsapi' . DIRECTORY_SEPARATOR . '*.php');
	}

	/**
	 * Returns the configuration form of a SMS provider.
	 *
	 * @param 	string 	$driver  The name of the driver.
	 *
	 * @return 	mixed 	The configuration array/object.
	 *
	 * @since 	1.8
	 */
	public function getSmsConfig($driver)
	{
		// strip file extension, if specified
		$driver = preg_replace("/\.php$/i", '', $driver);

		// build driver path
		$path = VREADMIN . DIRECTORY_SEPARATOR . 'smsapi' . DIRECTORY_SEPARATOR . $driver . '.php';
		
		if (!file_exists($path))
		{
			throw new RuntimeException(sprintf("SMS provider [%s] not found", $driver), 404);
		}
			
		// load SMS driver
		require_once $path;

		// make sure we have a valid instance
		if (method_exists('VikSmsApi', 'getAdminParameters'))
		{
			// return configuration array
			return VikSmsApi::getAdminParameters();
		}

		// fallback to an empty array
		return array();
	}

	/**
	 * Provides a new SMS driver instance for the specified arguments.
	 *
	 * @param 	string 	  $driver 	The name of the provider that should be instantiated.
	 * 								If not specified, the default one will be used.
	 * @param 	mixed 	  $config 	The SMS configuration array or a JSON string.
	 * @param 	mixed 	  $order 	The details of the order that has to be notified.
	 *
	 * @return 	mixed 	  The driver instance.
	 *
	 * @throws 	RuntimeException
	 *
	 * @since 	1.8
	 */
	public function getSmsInstance($driver = null, $config = null, $order = array())
	{
		if (is_null($driver))
		{
			// get default driver if not specified
			$driver = VREFactory::getConfig()->get('smsapi');
		}

		if (empty($driver))
		{
			// SMS API not configured
			throw new RuntimeException('SMS API framework not configured', 500);
		}

		// strip file extension, if specified
		$driver = preg_replace("/\.php$/i", '', $driver);

		// build driver path
		$path = VREADMIN . DIRECTORY_SEPARATOR . 'smsapi' . DIRECTORY_SEPARATOR . $driver . '.php';
		
		if (!file_exists($path))
		{
			throw new RuntimeException(sprintf('SMS provider [%s] not found', $driver), 404);
		}
			
		// load SMS driver
		require_once $path;

		if (is_null($config))
		{
			// get default configuration if not specified
			$config = VREFactory::getConfig()->get('smsapifields');
		}

		if (is_string($config))
		{
			// decode config from JSON
			$config = (array) json_decode($config, true);
		}
		else
		{
			// always cast to array
			$config = (array) $config;
		}
		
		// init SMS driver and return it
		return new VikSmsApi($order, $config);
	}

	/**
	 * Returns the component manufacturer name or link.
	 *
	 * @param 	array   $options  An array of options:
	 * 							  - link (boolean) True to return a link, false to return the
	 * 								name only (false by default);
	 *							  - short (boolean) True to display the short manufacturer name,
	 * 								false otherwise (false by default);
	 * 							  - long (boolean) True to display the long manufacturer name,
	 * 								false otherwise (true by default);
	 * 							  - separator (string) A separator string to insert between the
	 * 								names fetched ('-' by default).
	 *
	 * @return  string  The manufacturer name or link.
	 *
	 * @since   1.8
	 */
	public function getManufacturer(array $options = array())
	{
		// add support for manufacturer default options
		$options['manufacturer'] = array(
			// specify a default URI
			'link'  => 'https://extensionsforjoomla.com',
			// specify the manufacturer short name
			'short' => 'e4j',
			// specify the manufacturer long name
			'long'  => 'Extensionsforjoomla.com',
		);

		if (empty($options['short']))
		{
			// if the short name should not be displayed, use a lower-case version of the long name
			$options['manufacturer']['long'] = strtolower($options['manufacturer']['long']);
		}

		// invoke parent to complete name building
		return parent::getManufacturer($options);
	}

	/**
	 * Displays the platform alert.
	 *
	 * @param 	array 	$options  The alert display data.
	 *
	 * @return 	string  The HTML of the alert.
	 *
	 * @see 	alert()
	 *
	 * @since 	1.8
	 */
	protected function displayAlert(array $data)
	{
		$app = JFactory::getApplication();

		// search in site folder if we are in the back-end
		if ($app->isClient('administrator'))
		{
			$base = VREBASE . DIRECTORY_SEPARATOR . 'layouts';
		}
		else
		{
			$base = null;
		}

		// register script to handle cookie alert
		JHtml::_('vrehtml.scripts.cookiealert');

		// instantiate layout file
		$layout = new JLayoutFile('blocks.alert', $base);

		// display layout
		return $layout->render($data);
	}

	/**
	 * Returns a list of users that can be assigned to an operator.
	 * Excludes all the users that belong to the following groups:
	 * - Public
	 * - Registered
	 * - Guest
	 *
	 * @param 	integer  $id  The selected user ID.
	 *
	 * @return 	array
	 *
	 * @since 	1.8.2
	 */
	public function getOperatorUsers($id = 0)
	{
		$dbo = JFactory::getDbo();

		$excluded = $dbo->getQuery(true)
			->select(1)
			->from($dbo->qn('#__vikrestaurants_operator', 'o'))
			->where($dbo->qn('o.jid') . ' = ' . $dbo->qn('u.id'));

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array(
				'u.id',
				'u.name',
				'a.group_id',
				'g.title',
			)))
			->from($dbo->qn('#__users', 'u'))
			->leftjoin($dbo->qn('#__user_usergroup_map', 'a') . ' ON ' . $dbo->qn('u.id') . ' = ' . $dbo->qn('a.user_id'))
			->leftjoin($dbo->qn('#__usergroups', 'g') . ' ON ' . $dbo->qn('g.id') . ' = ' . $dbo->qn('a.group_id'))
			->where(1)
			->andWhere(array(
				$dbo->qn('u.id') . ' = ' . (int) $id,
				// exclude public, registered, guest user groups
				$dbo->qn('a.group_id') . ' NOT IN (1, 2, 9)',
			))
			->andWhere(array(
				$dbo->qn('u.id') . ' = ' . (int) $id,
				sprintf('NOT EXISTS(%s)', $excluded),
			))
			->group($dbo->qn('u.id'))
			->order(array(
				$dbo->qn('a.group_id') . ' ASC',
				$dbo->qn('u.name') . ' ASC',
			));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$users = $dbo->loadObjectList();
		}
		else
		{
			$users = array();
		}

		return $users;
	}

	/**
	 * Returns a list of site installed languages.
	 *
	 * @return  array  key/value pair with the language file and real name.
	 *
	 * @since   1.8.2
	 */
	public function getKnownLanguages()
	{
		if (VersionListener::isJoomla4x())
		{
			// use language helper on Joomla 4.0
			return JLanguageHelper::getKnownLanguages();
		}
		
		// fallback to default method
		return parent::getKnownLanguages();
	}

	/**
	 * Helper method used to set up the application according
	 * to the current platform requirements.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.3
	 */
	public function setup()
	{
		// begin Joomla 4.0 setup
		if (VersionListener::isJoomla4x())
		{
			VRELoader::import('library.adapter.bc.j40');

			// instantiate helper
			$helper = new VREJoomla40SetupHelper();

			// turn off database strict mode
			$helper->disableStrictMode();

			// scripts will be always loaded on footer
			$helper->addScriptsFooter();

			// adjust the classes used for the badges
			// $helper->replaceBadgeClass();

			// use the horizontal version of the VikRestaurants menu
			$helper->useHorizontalMenu();

			// add support again for behavior.modal
			$helper->registerModal();

			// adjusts the Joomla toolbar
			$helper->prepareToolbar();
		}
	}
}
