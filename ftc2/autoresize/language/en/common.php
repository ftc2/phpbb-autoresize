<?php
/**
 *
 * Auto-Resize Images Server-side. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, ftc2
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_AUTORESIZE_TRIGGER'			=> 'Shrink uploaded images if they exceed',
	'ACP_AUTORESIZE_TRIGGER_DESC'		=> "Regardless of how resizing is triggered, the image will be shrunk to not exceed specified max dimensions. Shrunk image is not <i>guaranteed</i> to be smaller than the max filesize.",
	'ACP_AUTORESIZE_FILESIZE'			=> 'Maximum filesize',
	'ACP_AUTORESIZE_DIMENSIONS'			=> 'Maximum image dimensions',
	'ACP_AUTORESIZE_EITHER'				=> 'Either',
	'ACP_AUTORESIZE_IMPARAMS'			=> 'ImageMagick parameters',
	'ACP_AUTORESIZE_IMPARAMS_DESC'		=> 'Command preview',
	'ACP_AUTORESIZE_IMPATH'			=> 'ImageMagick path',
	'ACP_AUTORESIZE_IMPATH_DESC'			=> 'Path to ImageMagick installation. The `mogrify` binary must be available there.',

	'ACP_AUTORESIZE_DEBUG_TITLE'		=> 'Debugging',
	'ACP_AUTORESIZE_DEBUG'				=> 'Enable?',
	'ACP_AUTORESIZE_DEBUG_DESC'			=> 'Messages will be written to "phpBB3/ftc2_resize_log.txt"',

	'ACP_AUTORESIZE_SETTING_SAVED'		=> 'Settings have been saved successfully.',
));
