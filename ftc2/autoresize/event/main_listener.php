<?php
/**
 *
 * Auto-Resize Images Server-side. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, ftc2
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ftc2\autoresize\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Auto-Resize Images Server-side Event listener for core.modify_uploaded_file
 */
class main_listener implements EventSubscriberInterface
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
    public function __construct(\phpbb\config\config $config, \phpbb\user $user)
    {
        $this->config = $config;
        $this->user = $user;
    }

    static public function getSubscribedEvents()
    {
        return array(
            'core.modify_uploaded_file' => 'resize_image_attachment',
        );
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
     * Resize uploaded images if they're too big
     *
     * @param \phpbb\event\data $event	Event object [filedata, is_image]
     */
    public function resize_image_attachment($event)
    {
        $time1 = microtime(true);
        $this->dbg_log('-------------');
        $this->dbg_log('INFO: [' . date('Y-m-d h:i:sa') . '] ' . $this->user->data['username'] . ': ' . $event['filedata']['real_filename']);

        $scale_method = $this->config['ftc2_scale_method_trigger'];
        $this->dbg_log("INFO: scale_method: $scale_method.");

        if ($scale_method === 'imageMagick') {
            $this->resizeUsingImageMagick($event);
        } else if ($scale_method === 'gdLibrary') {
            $this->resizeUsingGdLibrary($event);
        } else {
            $this->dbg_log('ERROR: scale_method `'.$scale_method.'` not supported.');
        }
    }

    private function resizeUsingImageMagick($event)
    {
        $imagick_path = $this->config['ftc2_autoresize_impath'];
        $imagick_path = str_replace('\\', '/', $imagick_path);
        if (substr($imagick_path, -1) !== '/')
        {
            $imagick_path .= '/';
        }
        $imagick_path .= 'mogrify' . ((defined('PHP_OS') && preg_match('#^win#i', PHP_OS)) ? '.exe' : '');

        /**
         * pre-checks
         */
        if (!file_exists($imagick_path))
        {
            $this->dbg_log('ERROR: `' . $imagick_path . '` not found. install ImageMagick and make sure the path is correct in this extension\'s settings.');
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
         *  start resize!
         */
        // mogrify $ftc2_autoresize_imparams 600x950> img_path
        $imagick_cmd = escapeshellcmd($imagick_path . ' ' . $this->config['ftc2_autoresize_imparams'] . ' ' . $this->config['ftc2_autoresize_width'] . 'x' . $this->config['ftc2_autoresize_height'] . '> "' . str_replace('\\', '/', $file_path) . '"');
        $this->dbg_log("INFO: $imagick_cmd");
        @exec($imagick_cmd);
        /***
         * end resize!
         */

        $this->dbg_log('INFO: resized from ' . $event['filedata']['filesize'] . ' B to ' . @filesize($file_path) . ' B');
        $this->dbg_log('INFO: resize execution time: ' . (microtime(true) - $time1) . 's');

        return true;
    }

    private function resizeUsingGdLibrary($event)
    {
        $this->dbg_log("INFO: Start resizeUsingGdLibrary");

        $file_path = join('/', array(trim($this->config['upload_path'], '/'), trim($event['filedata']['physical_filename'], '/')));
        $this->resizeToWidth($file_path, $event['filedata']['real_filename']);
    }

    private function resizeToWidth($source_url, $realFileName) {
        $source_url_parts = pathinfo($source_url);
        $filename = $source_url_parts['filename'];
        list($width, $height) = getimagesize($source_url);
        $width;
        $height;

        $after_width = $this->config['ftc2_autoresize_width'];
        $after_height = $this->config['ftc2_autoresize_height'];

        $extension = $this->fileExtension($realFileName);
        $img = $this->getImage($extension, $source_url);
        $imgResized = null;

        $resizeType = $this->config['ftc2_autoresize_trigger'];

        $this->dbg_log('INFO: ftc2_autoresize_trigger '.$resizeType);

        if ($resizeType === 'dimensions' || $resizeType === 'either') {
            if ($width > $after_width || $height > $after_height) {
                $this->dbg_log('INFO: Resize Dimensions required');

                $newDimensions = $this->getBestDimensionsByDimensionsResize($width, $height);

                $imgResized = imagescale($img, $newDimensions['width'], $newDimensions['height']);
                imagejpeg($imgResized, $source_url);

                $this->dbg_log('INFO: Image resized and saved '.$source_url);
            }
        }

        $img = $this->getImage($extension, $source_url);

        if ($resizeType === 'filesize' || $resizeType === 'either') {
            $imageSize = @filesize($source_url);

            if ($imageSize > $this->config['ftc2_autoresize_filesize']) {
                $this->dbg_log('INFO: Resize Filesize required');

                list($width, $height) = getimagesize($source_url);
                $newDimensions = $this->getBestDimensionsByFilesizeResize($imageSize, $width, $height);

                $imgResized = imagescale($img, $newDimensions['width'], $newDimensions['height']);
                imagejpeg($imgResized, $source_url);
            }
        }

        imagedestroy($img);
        imagedestroy($imgResized);
    }

    private function getImage($extension, $source_url) {
        if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'JPG' || $extension === 'JPEG') {
            return imagecreatefromjpeg($source_url);
        } elseif ($extension === 'png' || $extension === 'PNG') {
            return imagecreatefrompng($source_url);
        } else {
            $this->dbg_log('INFO: image extension is not supporting');
            return null;
        }
    }

    private function getBestDimensionsByFilesizeResize($imageSize, $imgWidth, $imgHeight) {
        $this->dbg_log('INFO: FilesizeResize imgWidth='.$imgWidth.'; imgHeight='.$imgHeight);

        $ratio = $this->config['ftc2_autoresize_filesize'] * 100 / $imageSize;

        $after_width = round($imgWidth * $ratio / 100, 2);
        $after_height = round($imgHeight * $ratio / 100, 2);

        $this->dbg_log('INFO: FilesizeResize Ratio='.$ratio.'; after_width='.$after_width.'; after_height='.$after_height);

        return [
            'width' => $after_width,
            'height' => $after_height
        ];
    }

    private function getBestDimensionsByDimensionsResize($imgWidth, $imgHeight) {
        $this->dbg_log('INFO: DimensionsResize imgWidth='.$imgWidth.'; imgHeight='.$imgHeight);

        $scaledWidth = $this->config['ftc2_autoresize_width'];
        $scaledHeigth = $this->config['ftc2_autoresize_height'];
        $ratioWidth = $this->getRatio($imgWidth, $scaledWidth);
        $ratioHeight = $this->getRatio($imgHeight, $scaledHeigth);

        $this->dbg_log('INFO: Ratio width='.$ratioWidth.'; height='.$ratioHeight);

        if ($ratioWidth > $ratioHeight) {
            $reduced_height = round(($imgHeight / 100) * $ratioWidth, 2);

            $after_width = $scaledWidth;
            $after_height = $imgHeight - $reduced_height;
        } else {
            $reduced_width = round(($imgWidth / 100) * $ratioHeight, 2);

            $after_width = $imgWidth - $reduced_width;
            $after_height = $scaledHeigth;
        }

        $this->dbg_log('INFO: DimensionsResize after_width='.$after_width.'; after_height='.$after_height);

        return [
            'width' => $after_width,
            'height' => $after_height
        ];
    }

    private function getRatio($orginal, $after) {
        $reduced = ($orginal - $after);
        return round(($reduced / $orginal) * 100, 2);
    }

    private function fileExtension($file_name) {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }
}
