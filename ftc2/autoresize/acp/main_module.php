<?php
/**
 *
 * Auto-Resize Images & Avatars Server-side. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, ftc2
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ftc2\autoresize\acp;

/**
 * Auto-Resize Images & Avatars Server-side ACP module
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user, $phpbb_container;

		$user->add_lang_ext('ftc2/autoresize', 'common');
		$this->tpl_name = 'acp_autoresize_body';
		$this->page_title = $user->lang('ACP_AUTORESIZE_TITLE');

		/** @var \ftc2\autoresize\core\core_functions $core_functions */
		$core_functions = $phpbb_container->get('ftc2.autoresize.core');

		add_form_key('ftc2/autoresize');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('ftc2/autoresize'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$config->set('ftc2_autoresize_i_enable', $request->variable('ftc2_autoresize_i_enable', true));
			$config->set('ftc2_autoresize_i_trigger', $request->variable('ftc2_autoresize_i_trigger', 'filesize'));
			$config->set('ftc2_autoresize_i_filesize', $request->variable('ftc2_autoresize_i_filesize', 262144));
			$config->set('ftc2_autoresize_i_width', $request->variable('ftc2_autoresize_i_width', 1000));
			$config->set('ftc2_autoresize_i_height', $request->variable('ftc2_autoresize_i_height', 1000));
			$config->set('ftc2_autoresize_i_imparams', $request->variable('ftc2_autoresize_i_imparams', '-auto-orient -resize'));

			$config->set('ftc2_autoresize_a_enable', $request->variable('ftc2_autoresize_a_enable', true));
			$config->set('ftc2_autoresize_a_trigger', $request->variable('ftc2_autoresize_a_trigger', 'either'));
			$config->set('ftc2_autoresize_a_filesize', $request->variable('ftc2_autoresize_a_filesize', 10240));
			$config->set('ftc2_autoresize_a_width', $request->variable('ftc2_autoresize_a_width', 100));
			$config->set('ftc2_autoresize_a_height', $request->variable('ftc2_autoresize_a_height', 100));
			$config->set('ftc2_autoresize_a_imparams', $request->variable('ftc2_autoresize_a_imparams', '-resize'));

			$config->set('ftc2_autoresize_debug', $request->variable('ftc2_autoresize_debug', 0));

			trigger_error($user->lang('ACP_AUTORESIZE_SETTING_SAVED') . adm_back_link($this->u_action));
		}

		$im_installed = $core_functions->im_installed();
		$exec_enabled = $core_functions->im_installed();
		$i_can_trigger = $core_functions->can_trigger($config['ftc2_autoresize_i_trigger'], $config['ftc2_autoresize_i_filesize']);

		$template->assign_vars(array(
			'U_ACTION'							=> $this->u_action,

// 			'FTC2_AUTORESIZE_I_ENABLE'			=> ($config['ftc2_autoresize_i_enable']) ? true : false,
			'FTC2_AUTORESIZE_I_ENABLE'			=> $config['ftc2_autoresize_i_enable'],
			'FTC2_AUTORESIZE_I_FILESIZE'		=> $config['ftc2_autoresize_i_filesize'],
			'FTC2_AUTORESIZE_I_WIDTH'			=> $config['ftc2_autoresize_i_width'],
			'FTC2_AUTORESIZE_I_HEIGHT'			=> $config['ftc2_autoresize_i_height'],
			'FTC2_AUTORESIZE_I_TRIGGER'			=> $config['ftc2_autoresize_i_trigger'],
			'FTC2_AUTORESIZE_I_IMPARAMS'		=> $config['ftc2_autoresize_i_imparams'],
			'FTC2_AUTORESIZE_I_CAN_TRIGGER'		=> $i_can_trigger,

			'FTC2_AUTORESIZE_A_ENABLE'			=> $config['ftc2_autoresize_a_enable'],
			'FTC2_AUTORESIZE_A_FILESIZE'		=> $config['ftc2_autoresize_a_filesize'],
			'FTC2_AUTORESIZE_A_WIDTH'			=> $config['ftc2_autoresize_a_width'],
			'FTC2_AUTORESIZE_A_HEIGHT'			=> $config['ftc2_autoresize_a_height'],
			'FTC2_AUTORESIZE_A_TRIGGER'			=> $config['ftc2_autoresize_a_trigger'],
			'FTC2_AUTORESIZE_A_IMPARAMS'		=> $config['ftc2_autoresize_a_imparams'],

			'FTC2_AUTORESIZE_IM_INSTALLED'		=> $im_installed,
			'FTC2_AUTORESIZE_EXEC_ENABLED'		=> $exec_enabled,

			'FTC2_AUTORESIZE_DEBUG'				=> ($config['ftc2_autoresize_debug']) ? true : false,
		));
	}
}
