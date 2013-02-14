<?php
/**
* @version		$Id: FormatHelper.php 123 2010-11-25 13:35:53Z hussfelt $
* @package		ACPCreator
* @copyright	Copyright (C) 2010-2011 AssignMe AB. All rights reserved.
* @license		GNU/GPL V 1.0
* This is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* */

class FormatHelper {

	/**
	 * Empty constructor
	 */
	public function __construct() {
	}

	/**
	 * Returns date and time formatted as Y-m-d\TH:i:sP
	 * 
	 * Example: 2000-07-01T00:00:00+00:00
	 * @return string date
	 *  
	 */
	public static function getAtomDateTime($date) {
		$time = strtotime($date);
		return date(DATE_ATOM, $time);
	}

	/**
	 * Returns a valid filename, and if no-existant value,
	 * creates one from the $filename var
	 * 
	 * @return string filename
	 */
	public static function filename($data, $filename) {
		// Check if data is null/strlen=0, then use filename
		if ($data == null || strlen($data) == 0) {
			return preg_replace('/[^a-zA-Z0-9-.]/', ' ', $filename);
		} else {
			return preg_replace('/[^a-zA-Z0-9-.]/', ' ', $data);
		}
	}
}
?>