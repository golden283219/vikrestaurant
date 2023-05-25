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

$app      = JFactory::getApplication();
$document = JFactory::getDocument();

$body  = isset($displayData['body'])   ? $displayData['body']  : '';
$title = !empty($displayData['title']) ? $displayData['title'] : $app->get('sitename');

if (!headers_sent())
{
	// declare headers with content type to avoid encoding errors
	header('Content-Type: text/html; charset=' . $document->getCharset());
}

?>

<!DOCTYPE html>
<html lang="<?php echo $document->getLanguage(); ?>" dir="<?php echo $document->getDirection(); ?>">
	<head>
		<meta charset="<?php echo $document->getCharset(); ?>" />
		<meta http-equiv="content-type" content="text/html; charset=<?php echo $document->getCharset(); ?>" />
		<meta name="robots" content="nofollow" />
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="HandheldFriendly" content="true" />
		<title><?php echo $title; ?></title>
	</head>
	<body>
		<?php echo $body; ?>
	</body>
</html>
