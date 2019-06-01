<?php
/**
 *
 * Auto-Resize Images Server-side. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, ftc2
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ftc2\autoresize\acp;

/**
 * Auto-Resize Images Server-side ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\ftc2\autoresize\acp\main_module',
			'title'		=> 'ACP_AUTORESIZE_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_AUTORESIZE_SETTINGS',
					'auth'	=> 'ext_ftc2/autoresize && acl_a_board',
					'cat'	=> array('ACP_AUTORESIZE_TITLE')
				),
			),
		);
	}
}
