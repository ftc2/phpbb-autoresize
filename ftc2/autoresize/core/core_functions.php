<?php
/**
 *
 * Auto-Resize Images & Avatars Server-side. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, ftc2
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ftc2\autoresize\core\core_functions;


/**
 * Auto-Resize Images & Avatars Server-side Core functions
 */
class core_functions
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config		$config		Config object
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\user $user
	)
	{
		$this->config = $config;
		$this->user = $user;
	}

	/**
	 * Append trailing slash to path if necessary
	 *
	 * @param $path	Path
	 */
	public function add_slash($path)
	{
		if (substr($path, -1) !== '/')
		{
			$path .= '/';
		}
		return $path;
	}

	/**
	 * Check if ImageMagick is installed
	 */
	public function im_installed()
	{
		$imagick_path = $this->add_slash($this->config['img_imagick']);
		if ($config['img_imagick'] && file_exists($imagick_path . 'mogrify'))
		{
			return true;
		}
		return false;
	}

	/**
	 * Check if PHP exec() is enabled
	 */
	public function exec_enabled()
	{
		return function_exists('exec');
	}

	/**
	 * Check if resizing can be triggered based on settings
	 *
	 * @param $trigger	Trigger type
	 * @param $max_filesize	Trigger type
	 */
	public function can_trigger($trigger, $max_filesize)
	{
		if ($trigger == 'filesize' && $this->config['max_filesize'] != 0 && $this->config['max_filesize'] <= $max_filesize)
		{
			$this->dbg_log("WARNING: phpBB max_filesize <= ftc2_autoresize_filesize, so a resize will never be triggered. consider setting max_filesize to 0 in phpBB's attachment settings if phpBB isn't letting you upload large files.");
			return false;
		}
		return true;
	}

	/**
	 * Logger for debugging
	 *
	 * @param $err	Message to log
	 * @param $verbosity	0: log as-is, 1: use print_r(), 2: use var_dump()
	 */
	public function dbg_log($err, $verbosity = 0)
	{
		if ($this->config['ftc2_autoresize_debug'])
		{
			$log_file = 'ftc2_resize_log.txt';
			if ($verbosity == 1)
			{
				$err = print_r($err, true);
			}
			elseif ($verbosity == 2)
			{
				ob_start();
				var_dump($err);
				$err = ob_get_clean();
			}
			error_log ($err . "\r\n", 3, $log_file);
		}
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

		$this->resize($event, $trigger, $max_filesize, $max_width, $max_height, $imparams);
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

// 		$this->resize($trigger, $max_filesize, $max_width, $max_height, $imparams);
		return true;
	}

	/**
	 * Resize images if they're too big
	 *
	 * @param \phpbb\event\data $event	Event object [filedata, is_image]
	 */
	public function resize($event, $trigger, $max_filesize, $max_width, $max_height, $imparams)
	{
		$time1 = microtime(true);
		$this->dbg_log('INFO: [' . date('Y-m-d h:i:sa') . '] ' . $this->user->data['username'] . ': ' . $event['filedata']['real_filename']);

		/**
		 * pre-checks
		 */
		if (!$this->im_installed())
		{
			$this->dbg_log('ERROR: ImageMagick not installed. install it and make sure phpBB is configured with its correct path.');
			return false;
		}

		if (!$this->exec_enabled()))
		{
			$this->dbg_log('ERROR: PHP exec() function not found.');
			return false;
		}

		if (!$this->can_trigger($trigger, $max_filesize))
		{
			$this->dbg_log("WARNING: phpBB max_filesize <= ftc2_autoresize_filesize, so a resize will never be triggered. consider setting max_filesize to 0 in phpBB's attachment settings if phpBB isn't letting you upload large files.");
		}

		/**
		 * get image info
		 */
		if (!$event['is_image'])
		{
			$this->dbg_log("ERROR: {$event['filedata']['real_filename']} is not an image.");
			return false;
		}

		$file_path = join('/', array(trim($this->config['upload_path'], '/'), trim($event['filedata']['physical_filename'], '/')));
		$dimensions = @getimagesize($file_path);

		if ($dimensions === false)
		{
			$this->dbg_log("ERROR: {$event['filedata']['real_filename']} has invalid dimensions.");
			return false;
		}

		list($width, $height, ) = $dimensions;

		if (empty($width) || empty($height))
		{
			$this->dbg_log("ERROR: {$event['filedata']['real_filename']} has invalid dimensions.");
			return false;
		}

		/**
		 * resize?
		 */
		if ($trigger == 'filesize' && $event['filedata']['filesize'] > $max_filesize)
		{
			$this->dbg_log('INFO: image filesize too big; resizing.');
		}
		elseif ($trigger == 'dimensions' && ($width > $max_width || $height > $max_height))
		{
			$this->dbg_log('INFO: image resolution too big; resizing.');
		}
		elseif ($trigger == 'either' && ($event['filedata']['filesize'] > $max_filesize || ($width > $max_width || $height > $max_height)))
		{
			$this->dbg_log('INFO: image filesize and/or resolution too big; resizing.');
		}
		else
		{
			$this->dbg_log('INFO: resize not triggered.');
			return false;
		}

		/**
		 * resize!
		 */
		$imagick_path = $this->add_slash($this->config['img_imagick']);

		// mogrify $imparams 600x950> img_path
		$imagick_cmd = escapeshellcmd($imagick_path . 'mogrify' . ((defined('PHP_OS') && preg_match('#^win#i', PHP_OS)) ? '.exe' : '') . ' ' . $imparams . ' ' . $max_width . 'x' . $max_height . '> "' . str_replace('\\', '/', $file_path) . '"');
		$this->dbg_log("INFO: $imagick_cmd");
		@exec($imagick_cmd);

		$this->dbg_log('INFO: resized from ' . $event['filedata']['filesize'] . ' B to ' . @filesize($file_path) . ' B');
		$this->dbg_log('INFO: resize execution time: ' . (microtime(true) - $time1) . 's');

		return true;
	}

	/**
	 * Resize uploaded images if they're too big
	 *
	 * @param \phpbb\event\data $event	Event object [filedata, is_image]
	 */
	public function resize_image_attachment($event)
	{
		$time1 = microtime(true);
		$this->dbg_log('INFO: [' . date('Y-m-d h:i:sa') . '] ' . $this->user->data['username'] . ': ' . $event['filedata']['real_filename']);

		/**
		 * pre-checks
		 */
		if (!$this->config['img_imagick'])
		{
			$this->dbg_log('ERROR: ImageMagick not installed. install it and make sure phpBB is configured with its correct path.');
			return false;
		}

		if (!function_exists('exec'))
		{
			$this->dbg_log('ERROR: PHP exec() function not found.');
			return false;
		}

		if ($this->config['ftc2_autoresize_trigger'] == 'filesize' && $this->config['max_filesize'] != 0 && $this->config['max_filesize'] <= $this->config['ftc2_autoresize_filesize'])
		{
			$this->dbg_log("WARNING: phpBB max_filesize <= ftc2_autoresize_filesize, so a resize will never be triggered. consider setting max_filesize to 0 in phpBB's attachment settings if phpBB isn't letting you upload large files.");
		}


		/**
		 * get image info
		 */
		if (!$event['is_image'])
		{
			$this->dbg_log("ERROR: {$event['filedata']['real_filename']} is not an image.");
			return false;
		}

		$file_path = join('/', array(trim($this->config['upload_path'], '/'), trim($event['filedata']['physical_filename'], '/')));
		$dimensions = @getimagesize($file_path);

		if ($dimensions === false)
		{
			$this->dbg_log("ERROR: {$event['filedata']['real_filename']} has invalid dimensions.");
			return false;
		}

		list($width, $height, ) = $dimensions;

		if (empty($width) || empty($height))
		{
			$this->dbg_log("ERROR: {$event['filedata']['real_filename']} has invalid dimensions.");
			return false;
		}


		/**
		 * resize?
		 */
		if ($this->config['ftc2_autoresize_trigger'] == 'filesize' && $event['filedata']['filesize'] > $this->config['ftc2_autoresize_filesize'])
		{
			$this->dbg_log('INFO: image filesize too big; resizing.');
		}
		elseif ($this->config['ftc2_autoresize_trigger'] == 'dimensions' && ($width > $this->config['ftc2_autoresize_width'] || $height > $this->config['ftc2_autoresize_height']))
		{
			$this->dbg_log('INFO: image resolution too big; resizing.');
		}
		elseif ($this->config['ftc2_autoresize_trigger'] == 'either' && ($event['filedata']['filesize'] > $this->config['ftc2_autoresize_filesize'] || ($width > $this->config['ftc2_autoresize_width'] || $height > $this->config['ftc2_autoresize_height'])))
		{
			$this->dbg_log('INFO: image filesize and/or resolution too big; resizing.');
		}
		else
		{
			$this->dbg_log('INFO: resize not triggered.');
			return false;
		}


		/**
		 * resize!
		 */
		$imagick_path = $this->config['img_imagick'];

		if (substr($imagick_path, -1) !== '/')
		{
			$imagick_path .= '/';
		}

		// mogrify $ftc2_autoresize_imparams 600x950> img_path
		$imagick_cmd = escapeshellcmd($imagick_path . 'mogrify' . ((defined('PHP_OS') && preg_match('#^win#i', PHP_OS)) ? '.exe' : '') . ' ' . $this->config['ftc2_autoresize_imparams'] . ' ' . $this->config['ftc2_autoresize_width'] . 'x' . $this->config['ftc2_autoresize_height'] . '> "' . str_replace('\\', '/', $file_path) . '"');
		$this->dbg_log("INFO: $imagick_cmd");
		@exec($imagick_cmd);

		$this->dbg_log('INFO: resized from ' . $event['filedata']['filesize'] . ' B to ' . @filesize($file_path) . ' B');
		$this->dbg_log('INFO: resize execution time: ' . (microtime(true) - $time1) . 's');

		return true;
	}
}
