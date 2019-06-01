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
 * Auto-Resize Images Server-side ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang_ext('ftc2/autoresize', 'common');
		$this->tpl_name = 'acp_autoresize_body';
		$this->page_title = $user->lang('ACP_AUTORESIZE_TITLE');
		add_form_key('ftc2/autoresize');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('ftc2/autoresize'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$config->set('ftc2_autoresize_trigger', $request->variable('ftc2_autoresize_trigger', 'filesize'));
			$config->set('ftc2_autoresize_filesize', $request->variable('ftc2_autoresize_filesize', 262144));
			$config->set('ftc2_autoresize_width', $request->variable('ftc2_autoresize_width', 1000));
			$config->set('ftc2_autoresize_height', $request->variable('ftc2_autoresize_height', 1000));
			$config->set('ftc2_autoresize_imparams', $request->variable('ftc2_autoresize_imparams', '-auto-orient -resize'));
			$config->set('ftc2_autoresize_impath', $request->variable('ftc2_autoresize_impath', '/usr/bin'));
			$config->set('ftc2_autoresize_debug', $request->variable('ftc2_autoresize_debug', 0));

			trigger_error($user->lang('ACP_AUTORESIZE_SETTING_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'						=> $this->u_action,
			'FTC2_AUTORESIZE_FILESIZE'		=> $config['ftc2_autoresize_filesize'],
			'FTC2_AUTORESIZE_WIDTH'			=> $config['ftc2_autoresize_width'],
			'FTC2_AUTORESIZE_HEIGHT'		=> $config['ftc2_autoresize_height'],
			'FTC2_AUTORESIZE_TRIGGER'		=> $config['ftc2_autoresize_trigger'],
			'FTC2_AUTORESIZE_IMPARAMS'		=> $config['ftc2_autoresize_imparams'],
			'FTC2_AUTORESIZE_IMPATH'		=> $config['ftc2_autoresize_impath'],
			'FTC2_AUTORESIZE_DEBUG'			=> $config['ftc2_autoresize_debug'],
		));
	}
}
