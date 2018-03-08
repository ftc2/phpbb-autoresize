<?php
/**
 *
 * Auto-Resize Images & Avatars Server-side. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, ftc2
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ftc2\autoresize\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Auto-Resize Images & Avatars Server-side Event listener
 */
class main_listener implements EventSubscriberInterface
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\user */
	protected $user;

	/* @var \ftc2\autoresize\core\core_functions */
	protected $core_functions;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config		$config		Config object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \ftc2\autoresize\core\core_functions $core_functions)
	{
		$this->config = $config;
		$this->user = $user;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.modify_uploaded_file' => 'image_attachment_handler',
			'core.avatar_driver_upload_move_file_before' => 'avatar_handler',
		);
	}

	/**
	 * Image attachment handler
	 *
	 * @param \phpbb\event\data $event	Event object [filedata, is_image]
	 */
	public function image_attachment_handler($event)
	{
		$trigger		= $this->config['ftc2_autoresize_i_trigger'];
		$max_filesize	= $this->config['ftc2_autoresize_i_filesize'];
		$max_width		= $this->config['ftc2_autoresize_i_width'];
		$max_height		= $this->config['ftc2_autoresize_i_height'];
		$imparams		= $this->config['ftc2_autoresize_i_imparams'];

		$core_functions->resize($event, $trigger, $max_filesize, $max_width, $max_height, $imparams);
	}

	/**
	 * Avatar handler
	 *
	 * @param \phpbb\event\data $event	Event object [filedata, is_image]
	 */
	public function avatar_handler($event)
	{
		$trigger		= $this->config['ftc2_autoresize_a_trigger'];
		$max_filesize	= $this->config['ftc2_autoresize_a_filesize'];
		$max_width		= $this->config['ftc2_autoresize_a_width'];
		$max_height		= $this->config['ftc2_autoresize_a_height'];
		$imparams		= $this->config['ftc2_autoresize_a_imparams'];

// 		$core_functions->resize($trigger, $max_filesize, $max_width, $max_height, $imparams);
		return true;
	}
}
