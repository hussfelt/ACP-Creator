<?php
/**
* @version		$Id: FileHelper.php 123 2010-11-25 13:35:53Z hussfelt $
* @package		ACPCreator
* @copyright	Copyright (C) 2010-2011 AssignMe AB. All rights reserved.
* @license		GNU/GPL V 1.0
* This is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* */

class FileHelper {

	static $_mediatypes = array (
			'epub' => 'application/epub+zip',
			'fb2'  => 'text/xml',
			'pdf'  => 'application/pdf',
			'rtf'  => 'application/rtf',
			'txt'  => 'text/plain');

	/**
	 * Empty constructor
	 */
	public function __construct() {
	}

	/**
	 * Get the file encoding
	 *
	 * @return String with file encoding
	 * @author Henrik Hussfelt
	 * @since	1.0
	 */
	public static function findEncoding($filepath) {
		if ( class_exists('finfo', false) ) { // PHP >= 5.3.0 or PECL fileinfo >= 0.1.0
			$finfo = finfo_open(FILEINFO_MIME_ENCODING); // return mime type ala mimetype extension
			$encoding = finfo_file($finfo, $filepath);
			finfo_close($finfo);
		} else {
			// For now, kill application
			die('You need to compile php with finfo: http://php.net/manual/en/book.fileinfo.php');
		}
		return $encoding;
	}

	/**
	 * Get the mime type
	 *
	 * @return String with mime type
	 * @author Alejandro Moreno Calvo <almorca@almorca.es>
	 * @modified Henrik Hussfelt
	 * @exception FileNotFoundException if the file does not exist.
	 * @exception Exception if the file name starts with '.'
	 * @since	1.0
	 */
	public static function findMimetype($filepath) {
		$pathInfo = pathinfo($filepath);
		$extension = $pathInfo['extension'];
		$mediatype;
		if ( array_key_exists($extension, self::$_mediatypes) ) {
			/* NOTE: This method have a security risk because the file may have been misslabled intentionally.
			 * E.g. .exe rename to .jpg
			 */
			$mediatype = self::$_mediatypes[$extension];
		} else { // mime type is not set, get from server settings
			$mediatype = '';

			if ( class_exists('finfo', false) ) { // PHP >= 5.3.0 or PECL fileinfo >= 0.1.0
				$constant = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
				$fileInfo = finfo_open($constant);
				$mediatype = finfo_file($fileInfo, $filepath);
				finfo_close($fileInfo);
			} else if ( function_exists("mime_content_type") ) {
				/* NOTE: this function is available since PHP 4.3.0, but only if
				 * PHP was compiled with --with-mime-magic or, before 4.3.2, with --enable-mime-magic.
				 *
				 * On Windows, you must set mime_magic.magicfile in php.ini to point to the mime.magic
				 * file bundeled with PHP; sometimes, this may even be needed under linux/unix.
				 *
				 * Also note that this has been DEPRECATED in favor of the fileinfo extension
				 */
				$tempMime = mime_content_type($filepath);
				list($mediatype, $charset) = explode('; ', $tempMime); // get the mime type and delete the charset
			} else if ( strstr($_SERVER[HTTP_USER_AGENT], "Macintosh") ) { // correct output on macs
				$mediatype = trim(exec('file -b --mime ' . escapeshellarg($this->_filepath)));
			} else { // regular unix systems
				$mediatype = trim(exec('file -bi '. escapeshellarg($this->_filepath)));
			}

			if ($mediatype == '') { // mediatype is unknow
				$mediatype = "application/unknown";
			}
		}
		return $mediatype;
	}

	/**
	 * getExtension
	 * Gets a filenames extension and returns it, or empty string if no extension found
	 * @param string $filename
	 */
	static function getExtension($filename) {
		$pos = strrpos($filename, '.');
		if ($pos === false) { // dot is not found in the filename
			$extension = '';
		} else {
			$extension = substr($filename, $pos+1);
		} 
		return $extension;
	}
}