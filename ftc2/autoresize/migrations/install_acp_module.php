<?php
/**
 *
 * Auto-Resize Images & Avatars Server-side. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, ftc2
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ftc2\autoresize\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('ftc2_autoresize_i_enable', true)),
			array('config.add', array('ftc2_autoresize_i_trigger', 'filesize')),
			array('config.add', array('ftc2_autoresize_i_filesize', 262144)),
			array('config.add', array('ftc2_autoresize_i_width', 1000)),
			array('config.add', array('ftc2_autoresize_i_height', 1000)),
			array('config.add', array('ftc2_autoresize_i_imparams', '-auto-orient -resize')),

			array('config.add', array('ftc2_autoresize_a_enable', true)),
			array('config.add', array('ftc2_autoresize_a_trigger', 'either')),
			array('config.add', array('ftc2_autoresize_a_filesize', 10240)),
			array('config.add', array('ftc2_autoresize_a_width', 100)),
			array('config.add', array('ftc2_autoresize_a_height', 100)),
			array('config.add', array('ftc2_autoresize_a_imparams', '-resize')),

			array('config.add', array('ftc2_autoresize_debug', 0)),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_AUTORESIZE_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_AUTORESIZE_TITLE',
				array(
					'module_basename'	=> '\ftc2\autoresize\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
