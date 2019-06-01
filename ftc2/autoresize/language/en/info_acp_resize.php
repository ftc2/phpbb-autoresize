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
	'ACP_AUTORESIZE_TITLE'			=> 'Auto-Resize Images Server-side',
	'ACP_AUTORESIZE_SETTINGS'		=> 'Settings',
));
