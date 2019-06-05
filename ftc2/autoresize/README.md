# Auto-Resize Images Server-side

## Installation

Requires [ImageMagick](https://imagemagick.org/script/download.php) and the [PHP exec() function](https://www.php.net/manual/en/function.exec.php) to call it.

Copy the extension to `phpBB3/ext/ftc2/autoresize`

Go to "ACP" > "Customise" > "Extensions" and enable the "Auto-Resize Images Server-side" extension.

You may need to tweak the following settings to allow uploading larger files or enable `exec()`:

"ACP" > "General" > "Board configuration" > "Attachment settings":
 - set `Maximum file size` to a large value (or 0)
 - set `Maximum image dimensions` to 0

`php.ini` for the PHP installation used by your web server (look at your `phpinfo()` page or go to "ACP" > "System" > "General Tasks" > "PHP information"):
 - increase `post_max_size`
 - increase `upload_max_filesize`
 - look at `disable_functions` and remove `exec` if it's there

If you're getting an `HTTP error` error popup when uploading large files, look in your browser's console.
If you see plupload triggering `413 (Request Entity Too Large)`, then [try configuring your web server to allow a larger client request body](https://craftcms.stackexchange.com/a/2330).

## Description

Automatically resize (shrink) image attachments server-side upon upload.

![screenshot](https://i.imgur.com/YG0pcqL.png)

Hooks to the `core.modify_uploaded_file` PHP event.

Note that phpBB supports [client-side image resizing via plupload](http://www.plupload.com/docs/v2/Image-Resizing-on-Client-Side), a feature that should be better advertised...
You can use this feature by specifying `Maximum image dimensions` in "ACP" > "General" > "Board configuration" > "Attachment settings".

However, the [output quality is not that good](https://github.com/moxiecode/plupload/issues/707), especially over large dimensional changes.

Resizing server-side with ImageMagick produces significantly better results (and smaller filesize in my testing) due to superior resize algorithm at the expense of server load.

If image quality is important to you and your server can handle it, or if you don't have people constantly uploading files, then this might be an attractive solution.

Example:

phpBB built-in resizing (plupload):
![plupload_ex_](https://i.imgur.com/uQW8SfP.jpg)

ImageMagick resizing with this extension with default `-resize` parameters:
![imagick_ex](https://i.imgur.com/Os8njgm.jpg)

The second image has a slightly smaller filesize.

## Settings

From the ACP, you can trigger resizing based on uploads exceeding max filesize, max dimensions (width,height), or either one.

Regardless of how resizing is triggered, the image will be shrunk to not exceed specified max dimensions. Shrunk image is not *guaranteed* to be smaller than the max filesize.

You may specify ImageMagick parameters if you wish as well.

You **must** specify the path to ImageMagick. The `mogrify` binary must be available there.

This extension also includes a feature to log debug messages to a text file.

## Changelog

### v1.0.5 (06-05-2019)
 - fixed deprecated license string

### v1.0.4 (05-31-2019)
 - brings support for [phpBB v3.2.4 and above](https://www.phpbb.com/community/viewtopic.php?f=14&t=2492206). Tested with phpBB v3.2.7.
 - adds `-auto-orient` to the default ImageMagick parameters to fix rotation issues on some uploaded images

## License

[GPLv2](license.txt)
