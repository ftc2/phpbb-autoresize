<?php
/**
 *
 * Auto-Resize Images Server-side. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, ftc2
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ftc2\autoresize\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['acme_demo_goodbye']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('ftc2_autoresize_trigger', 'filesize')),
			array('config.add', array('ftc2_autoresize_filesize', 262144)),
			array('config.add', array('ftc2_autoresize_width', 1000)),
			array('config.add', array('ftc2_autoresize_height', 1000)),
			array('config.add', array('ftc2_autoresize_imparams', '-auto-orient -resize')),
			array('config.add', array('ftc2_autoresize_impath', '/usr/bin')),
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
