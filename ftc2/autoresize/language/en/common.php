<?php
/**
 *
 * Auto-Resize Images & Avatars Server-side. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, ftc2
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
	'ACP_AUTORESIZE_I_TITLE'			=> 'Image Attachment Settings',
	'ACP_AUTORESIZE_A_TITLE'			=> 'Avatar Settings',
	'ACP_AUTORESIZE_ENABLE'				=> 'Enable?',

	'ACP_AUTORESIZE_TRIGGER'			=> 'Shrink uploaded images if they exceed',
	'ACP_AUTORESIZE_TRIGGER_DESC'		=> "Regardless of how resizing is triggered, the image will be shrunk to not exceed specified max dimensions. Shrunk image is not <i>guaranteed</i> to be smaller than the max filesize.",
	'ACP_AUTORESIZE_FILESIZE'			=> 'Maximum filesize',
	'ACP_AUTORESIZE_DIMENSIONS'			=> 'Maximum image dimensions',
	'ACP_AUTORESIZE_EITHER'				=> 'Either',
	'ACP_AUTORESIZE_IMPARAMS'			=> 'ImageMagick parameters',
	'ACP_AUTORESIZE_IMPARAMS_DESC'		=> 'Command preview',

	'ACP_AUTORESIZE_DEBUG_TITLE'		=> 'Debugging',
	'ACP_AUTORESIZE_DEBUG_DESC'			=> 'Messages will be written to "phpBB3/ftc2_resize_log.txt"',
	
	'ACP_AUTORESIZE_ERR_NO_IM'			=> 'ERROR: ImageMagick not installed. Install it (e.g. "apt-get install imagemagick") and make sure phpBB is configured with its correct path (ACP > General > Attachment settings > ImageMagick path).',
	'ACP_AUTORESIZE_ERR_NO_EXEC'		=> 'ERROR: PHP exec() function not found. This is required to run ImageMagick commands. Contact your sysadmin.',

	'ACP_AUTORESIZE_WRN_TRIGGER'		=> "WARNING: phpBB max_filesize <= ftc2_autoresize_filesize, so a resize will never be triggered. consider setting max_filesize to 0 in phpBB's attachment settings if phpBB isn't letting you upload large files.",

	'ACP_AUTORESIZE_SETTING_SAVED'		=> 'Settings have been saved successfully.',
));
