<?php
/**
* @version		$Id: ACPCreator.php 123 2010-11-25 13:35:53Z hussfelt $
* @package		ACPCreator
* @copyright	Copyright (C) 2010-2011 AssignMe AB. All rights reserved.
* @license		GNU/GPL V 1.0
* This is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* */
require_once 'Bridge.php';
require_once 'FormatHelper.php';
require_once 'FileHelper.php';

class ACPCreator {

	private $_settings = array();
	private $_xml = array();
	private $_xml_transfer_to_properties = array();
	private $_bridge = null;
	private $_files = array();
	private $_no_move_files = array();
	private $_map = array();
	private $_folder_name = "files";
	private $_folder_name_moved_files = "moved_files";
	private $_zip_folder = "zip";

	public function __construct($settings) {
		// Set settings
		$this->_settings = $settings;

		// Initiate bridge
		$this->_bridge = new Bridge($settings['bridge']);

		// Read the configured directory, populate files array
		$this->_populateFilesVariable();
	}

	/**
	 * getSetting
	 * @description Get a setting from the settings parameter
	 * @author Henrik Hussfelt
	 * @since 1.0
	 */
	public function getSetting($key, $subkey = null) {
		// if setting is not set, die
		// check if subkey set
		if ($subkey) {
			if(!isset($this->_settings[$key][$subkey])) {
				die("Settings not set correctly. [".$key.":".$subkey."]");
			}
			return $this->_settings[$key][$subkey];			
		} else {
			if(!isset($this->_settings[$key])) {
				die("Settings not set correctly. [".$key."]");
			}
			return $this->_settings[$key];
		}
	}

	/**
	 * executeHeadGeneration
	 * @description Will generate headers for the Alfresco Content Package XML file
	 * @author Henrik Hussfelt
	 * @since 1.0 
	 */
	public function executeHeadGeneration() {
		// Get the main settings for the document
		$mainData = $this->getSetting('map', 'main');

		// Add XML header
		$this->_addXML('<?xml version="1.0" encoding="UTF-8"?>', 0);

		// Add Alfresco main XML header
		$this->_addXML('<view:view xmlns:view="http://www.alfresco.org/view/repository/1.0">', 0);

		// Add metadata header
		$this->_addXML('<view:metadata>', 1);

		// Loop through metadata nodes and add
		foreach ($mainData['metadata'] as $metadata) {
			foreach ($metadata as $key => $val) {
				$this->_addXML('<' . $key . '>' . $val . '</' . $key . '>', 2);
			}
		}
		// Add metadata footer
		$this->_addXML('</view:metadata>', 1); 
	}

	/**
	 * executeFileGeneration
	 * @description Will fetch all files from the MySQL table, and generate XML for each one of them
	 * @author Henrik Hussfelt
	 * @since 1.0 
	 */
	public function executeFileRowsGeneration() {
		// Get the main settings for the document
		$mainData = $this->getSetting('map', 'main');
		// Get the file settings for the document
		$fileData = $this->getSetting('map', 'file');

		// Loop through all files in files array
		foreach ($this->_files as $file) {
			// Check that file exists in db relation, else add it to no-move array
			$datasource = $this->getSetting('bridge', 'db') . ":" . $this->getSetting('bridge', 'database_field');
			$checkFile = $this->_bridge->fetchRow($datasource, $file['name']);
			if ($checkFile) {
				// Build head metadata for the filetype
				$headXml = '<' . $mainData['doc']['type'];
				// Loop through the metadata to add to the main file-tag
				foreach ($mainData['doc']['metadata'] as $md) {
					foreach ($md as $key => $val) {
						// Format: keyname="value"
						$headXml .= ' ' . $key . '="' . $val . '"';
					}
				}
	
				// Add the filename to the end of the node
				$headXml .= ' view:childName="' . $file['name'] . '">';
				$this->_addXML($headXml, 1);
	
				// Add the Aspect nodes, if any, to the document node
				$this->_generateAspectNodeObject($fileData['aspects'], $file['name']);
	
				// Add the Acl nodes, if any, to the document node
				$this->_generateAclNodeObject($fileData['acl'], $file['name']);
	
				// Add the Properties nodes, to the document node
				$this->_generatePropertiesNodeObject($fileData['properties'], $file);
				
				// Add end of this node
				$this->_addXML('</' . $mainData['doc']['type'] . '>', 1);

				// Clear the transfer to the properties node
				$this->_clearXMLTransferToProperties();
			} else {
				// Add to no-move array
				$this->_no_move_files[] = $file['name'];
			}
		}
	}

	/**
	 * executeFootGeneration
	 * @description Will generate footers for the Alfresco Content Package XML file
	 * @author Henrik Hussfelt
	 * @since 1.0 
	 */
	public function executeFootGeneration() {
		// Add view footer
		$this->_addXML('</view:view>', 0); 
	}

	/**
	 * printXML
	 * Print the XML to the browser
	 */
	public function printXML() {
		foreach ($this->_xml as $row) {
			echo $row;
		}
	}

	/**
	 * prepareFolderStructure
	 * Prepares the folder structure for the zip-file and moves all found files to this folder
	 */
	public function prepareFolderStructure() {
		// Get config
		$filesConfig = $this->getSetting('files');

		// First, check if _files variable equals _no_move_files, in that case we should do nothing more
		if (count($this->_files) == count($this->_no_move_files)) {
			die('No files with relation left in folder: ' . $filesConfig['start_path'] . ' Nothing to do. Goodbye!');
		}

		// Create folder to store files in.
		$folder_name = $this->_zip_folder . "/" . $this->_folder_name;
		if (!file_exists($folder_name)) {
			mkdir($folder_name, 0777, true) or die('Could not create folder: ' . $folder_name);
		}
		// Create folder to store migrated files in.
		if (!file_exists($this->_folder_name_moved_files)) {
			mkdir($this->_folder_name_moved_files, 0777, true) or die('Could not create folder: ' . $this->_folder_name_moved_files);
		}

		// Move files that we found earlier to this new folder
		foreach ($this->_files as $file) {
            // If the files is not in the "Not move files" variable, move it
            if (!in_array($file['name'], $this->_no_move_files)) {
            	// Make a copy of the file, we do not want to move as this might complicate things if something goes wrong
            	copy($filesConfig['start_path'] . "/" . $file['name'], $folder_name . "/" . $file['content_name']);
            	// Move the file to a subfolder, telling us that it was moved.
            	rename($filesConfig['start_path'] . "/" . $file['name'], $this->_folder_name_moved_files . "/" . $file['name']);
            }
        }
	}

	/**
	 * prepareAddXMLFile
	 * Will add the XML file to the folder structure.
	 */
	public function prepareAddXMLFile() {
	    // Okay, files are moved, now write the XML string to the file.
		if (file_exists($this->_zip_folder)) {
		    // Set filename same as folder_name name with .xml extension as this is the way Alfresco want's it.
			$xml_filename = $this->_folder_name . '.xml';
			foreach ($this->_xml as $row) {
				file_put_contents($this->_zip_folder . "/" . $xml_filename, $row, FILE_APPEND | LOCK_EX);
			}
		} else {
			die("Something must have gone wrong, I cant find the folder called '" . $this->_zip_folder . "' that was supposed to be created earlier.");
		}
	}

	/**
	 * createZipFile
	 * Will create a zip-file of all files found and put in the same folder as the script
	 * @author David Walsh, http://davidwalsh.name/
	 * @modifier Henrik Hussfelt
	 * @param $overwrite
	 */
	public function createZipFile($overwrite = false) {

		// Variables
		$valid_files = array();
		$path = $this->_zip_folder . "/" . $this->_folder_name;
		$zip_name = $this->_zip_folder . "/" . $this->_folder_name . ".zip";

		// If the zip file already exists and overwrite is false, return false
		if (file_exists($zip_name) && !$overwrite) {
			die("Sorry, configured not to overwrite the zipfile '" . $zip_name . "', make backup of it first.");
		}

		// If files were passed in...
		if (is_array($this->_files)) {
			// Cycle through each file
			foreach($this->_files as $file) {
	            // If the files is not in the "Not move files" variable, add it
            	if (!in_array($file['name'], $this->_no_move_files)) {
					// Make sure the file exists
					if (file_exists($path . "/" . $file['content_name'])) {
						$valid_files[] = $file['content_name'];
					} else {
						die('Something went wrong, cant find the file that I should have moved, path: ' . $path . "/" . $file['content_name']);
					}
            	}
			}
		}

		// If we have good files...
		if(count($valid_files)) {
			// Create the archive
			$zip = new ZipArchive();

			if ($zip->open($zip_name, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
				die("Killing myself, can't create the zip-archive, 'ZipArchive' not working correctly?");
			}

			// Add the files
			foreach($valid_files as $file) {
				$zip->addFile( $path . "/" . $file,  $this->_folder_name . "/" . $file);
			}

			// Also add the XML file
			$zip->addFile($this->_zip_folder . "/" . $this->_folder_name . ".xml", $this->_folder_name . ".xml");

			// Close the zip -- done!
			$zip->close();

			// Check to make sure the file exists
			if (!file_exists($zip_name)) {
				die("Killing myself, the zip-archive was not created(!?), 'ZipArchive' not working correctly?");
			}
		}
	}

	/**
	 * forceZipDownload
	 */
	public function forceZipDownload() {
		$zip_name = $this->_zip_folder . "/" . $this->_folder_name . ".zip"; 
		if(!file_exists($zip_name)) {
			// File doesn't exist, output error
			die("Killing myself, the zip-archive was not created(!?), 'ZipArchive' not working correctly?");
		} else {
			// Set headers
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=".$this->_folder_name . ".zip");
			header("Content-Type: application/zip");
			header("Content-Transfer-Encoding: binary");
			
			// Read the file from disk
			readfile($zip_name);
		}
	}

	/**
	 * _addXML
	 * @description Add a line to the XML variable, with tabs
	 * @author Henrik Hussfelt
	 * @since 1.0
	 * @params $line The line to input, $tabs With this tabspace
	 */
	private function _addXML($line, $tabs = 0) {
		$this->_xml[] = str_repeat("\t", $tabs) . $line . "\n";
	}

	/**
	 * _createXML
	 * @description Create an XML line, with tabs
	 * @author Henrik Hussfelt
	 * @since 1.0
	 * @params $line The line to input, $tabs With this tabspace
	 */
	private function _createXML($line, $tabs = 0) {
		return str_repeat("\t", $tabs) . $line . "\n";
	}

	/**
	 * _addXMLTransferToProperties
	 * @description Create an XML line, with tabs
	 * @author Henrik Hussfelt
	 * @since 1.0
	 * @params $line The line to input, $tabs With this tabspace
	 */
	private function _addXMLTransferToProperties($line, $tabs = 0) {
		$this->_xml_transfer_to_properties[] = str_repeat("\t", $tabs) . $line . "\n";
	}

	/**
	 * _clearXMLTransferToProperties
	 * @description Clear the transfer scope
	 * @author Henrik Hussfelt
	 * @since 1.0
	 */
	private function _clearXMLTransferToProperties() {
		$this->_xml_transfer_to_properties = array();
	}

	/**
	 * _formatData
	 * @description Will format the data input as Alfresco XML want's it
	 * @author Henrik Hussfelt
	 * @params $data - Data to format, $type - The type to format the data as, array $file - Filename if applicable and content name if applicable
	 * @return $data - Formatted data
	 * @since 1.0 
	 */
	private function _formatData($data, $type, $file = null) {
		// Set up return parameter
		$return = false;
		switch ($type) {
			case 'date':
				$return = FormatHelper::getAtomDateTime($data);
				break;
			case 'filename':
				$return = FormatHelper::filename($data, $file['name']);
				break;
			case 'string':
				$return = (string) $data;
				break;
			case 'int':
				$return = (int) intval($data);
				break;
			case 'content-string':
				// Replace content strings $1-5 with proper values
				// contentUrl=$1|mimetype=$2|size=$3|encoding=$4|locale=$5

				// Set the full path
				$full_path = $this->_settings['files']['start_path'] . "/" .$file['name'];
				// Get mimetype with helper class
				$mimetype = FileHelper::findMimetype($full_path);
				// Get filesize
				$size = filesize($full_path);
				// Get encoding of file with helper class
				$encoding = FileHelper::findEncoding($full_path);
				// Set return string
				$return = str_replace(array('$1','$2','$3','$4','$5'), array($this->_folder_name . "/" . $file['content_name'], $mimetype, $size, $encoding, $this->getSetting('languages', 'locale')), $data);
				break;
			default:
				// Default action, return everything
				$return = $data;
				break;
		}
		// Return data or false
		return $return;
	}

	/**
	 * _populateFilesVariable
	 * @description Reads the configured folder and populates the array with filenames.
	 * @author Henrik Hussfelt
	 * @since 1.0 
	 */
	private function _populateFilesVariable() {
		// Get config
		$filesConfig = $this->getSetting('files');

		// Create files array
		$files = array();

		// Check if folder is found and set to handle
		if ($handle = opendir($filesConfig['start_path'])) {

			// Start file-counter, so that we have a path to move to and use in XML file
			$x=0;
			// Loop through all files
		    while (false !== ($file = readdir($handle))) {
		    	// But skip any files that begin with .
		    	if (substr($file, 0, 1) != ".") {
		    		// Prepare content name for Alfresco XML file
		    		$content_name = "content" . $x . "." . FileHelper::getExtension($file);
		    		// Create array
		    		$fileArr = array(
		    			'name' => $file,
		    			'content_name' => $content_name
		    		);
		    		// Add to array
		            array_push($files, $fileArr);
		            // Add one to counter
		            $x+=1;
		        }
		    }

		    // Set the main varable
		    $this->_files = $files;

		    // Close the directory
		    closedir($handle);
		}

		// If there where no files, kill app
		if (count($this->_files) == 0) {
			die('No files in folder: ' . $filesConfig['start_path'] . ' Nothing to do. Goodbye!');
		}
	}

	/**
	 * _generateAspectNodeObject
	 * @description Will generate the Aspect node object for a file
	 * @author Henrik Hussfelt
	 * @since 1.0 
	 */
	private function _generateAspectNodeObject($aspects, $file) {
		// Start the aspect node
		$this->_addXML('<view:aspects>', 2);

		// Check the aspects variable for dynamic entries, add them if there are any
		if (count($aspects['dynamic']) > 0) {
			// Loop through aspects and add them one by one
			foreach ($aspects['dynamic'] as $dynamic) {
				// Get data from bridge
				$data = $this->_bridge->fetchRow($dynamic['datasource'], $file);
				// Format the data as defined
				$data = $this->_formatData($data, $dynamic['type'], $file);
				// Create XML line
				$xml = '<' . $dynamic['alfresco'] . '></' . $dynamic['alfresco'] . '>';
				// Insert to XML array
				$this->_addXML($xml, 3);

				// This code will explode and loop through data from datasource and prepare to add to the content properties object
				// Prepare a variable to store in
				$xml_to_add_to_properties = '';
				// Create and add start XML line
				$this->_addXMLTransferToProperties('<' . $dynamic['settings']['group_as'] . '>', 3);
				// Explode the data as defined in split_datasource_on
				$dataArr = explode($dynamic['settings']['split_datasource_on'], $data);
				// Loop through the $data results and add them one by one for properties later.
				foreach ($dataArr as $res) {
					// Format the data as defined
					$res = $this->_formatData(trim($res), $dynamic['type'], $file);
					// Add the XML
					$this->_addXMLTransferToProperties('<' . $dynamic['settings']['value_name'] . '>' . $res . '</' . $dynamic['settings']['value_name'] . '>', 4);
				}
				// Create and add end XML line
				$this->_addXMLTransferToProperties('</' . $dynamic['settings']['group_as'] . '>', 3);
			}
		}
		
		// Check the aspects variable for static entries, add them if there are any
		if (count($aspects['static']) > 0) {
			// Loop through aspects and add them one by one
			foreach ($aspects['static'] as $static) {
				// Format the data as defined
				$data = $this->_formatData($static['value'], $static['type'], $file);
				// Create XML line
				$xml = '<' . $static['alfresco'] . '>' . $data . '</' . $static['alfresco'] . '>';
				// Insert to XML array
				$this->_addXML($xml, 3);
			}
		}

		// End the aspect node
		$this->_addXML('</view:aspects>', 2);
	}

	/**
	 * _generateAclNodeObject
	 * @description Will generate the Acl node object for a file
	 * @author Henrik Hussfelt
	 * @since 1.0 
	 */
	private function _generateAclNodeObject($acl, $file) {
		// Start the acl node
		$this->_addXML('<view:acl>', 2);

		// Check the acl variable for dynamic entries, add them if there are any
		if (count($acl['dynamic']) > 0) {
			// Loop through acl and add them one by one
			foreach ($acl['dynamic'] as $dynamic) {
				// Get data from bridge
				$data = $this->_bridge->fetchRow($dynamic['datasource'], $file);
				// Format the data as defined
				$data = $this->_formatData($data, $dynamic['type'], $file);
				// Create XML line
				$xml = '<' . $dynamic['alfresco'] . '>' . $data . '</' . $dynamic['alfresco'] . '>';
				// Insert to XML array
				$this->_addXML($xml, 3);
			}
		}

		// Check the acl variable for static entries, add them if there are any
		if (count($acl['static']) > 0) {
			// Loop through acl and add them one by one
			foreach ($acl['static'] as $static) {
				// Format the data as defined
				$data = $this->_formatData($static['value'], $static['type'], $file);
				// Create XML line
				$xml = '<' . $static['alfresco'] . '>' . $data . '</' . $static['alfresco'] . '>';
				// Insert to XML array
				$this->_addXML($xml, 3);
			}
		}

		// End the acl node
		$this->_addXML('</view:acl>', 2);
	}

	/**
	 * _generatePropertiesNodeObject
	 * @description Will generate the Aspect node object for a file
	 * @author Henrik Hussfelt
	 * @since 1.0 
	 */
	private function _generatePropertiesNodeObject($properties, $file) {
		// Start the properties node
		$this->_addXML('<view:properties>', 2);

		// Check the properties variable for dynamic entries, add them if there are any
		if (count($properties['dynamic']) > 0) {
			// Loop through properties and add them one by one
			foreach ($properties['dynamic'] as $dynamic) {
				// Get data from bridge
				$data = $this->_bridge->fetchRow($dynamic['datasource'], $file['name']);
				// Format the data as defined
				$data = $this->_formatData($data, $dynamic['type'], $file);
				// Format data depending on locale
				$this->_formatAndAddUseLocale($dynamic, $data);
			}
		}

		// Check the properties variable for static entries, add them if there are any
		if (count($properties['static']) > 0) {
			// Loop through properties and add them one by one
			foreach ($properties['static'] as $static) {
				// Format the data as defined
				$data = $this->_formatData($static['value'], $static['type'], $file);
				// Format data depending on locale
				$this->_formatAndAddUseLocale($static, $data);
			}
		}

		// Check the properties variable for arrays entries, add them if there are any
		if (count($properties['arrays']) > 0) {

			// Check the properties variable for arrays entries in dynamic array, add them if there are any
			if (count($properties['arrays']['dynamic']) > 0) {
				// Loop through arrays-dynamic-properties and add them one by one
				foreach ($properties['arrays']['dynamic'] as $dynamic) {
					// Create and add start XML line
					$this->_addXML('<' . $dynamic['group_as'] . '>', 3);
					// Get values from bridge
					$values = $this->_bridge->fetchRows($dynamic['datasource'], $file['name'], $dynamic['where_field']);
					// Loop through values and add them
					foreach ($values as $value) {
						// Format the data as defined
						$data = $this->_formatData($value, $dynamic['type'], $file);
						// Add the XML
						$this->_addXML('<' . $dynamic['value_name'] . '>' . $data . '</' . $dynamic['value_name'] . '>', 4);
					}
					// Create and add end XML line
					$this->_addXML('</' . $dynamic['group_as'] . '>', 3);
				}
			}

			// Check the properties variable for arrays entries in static array, add them if there are any
			if (count($properties['arrays']['static']) > 0) {
				// Loop through arrays-static-properties and add them one by one
				foreach ($properties['arrays']['static'] as $static) {
					// Create and add start XML line
					$this->_addXML('<' . $static['group_as'] . '>', 3);
					// Loop through values and add them
					foreach ($static['values'] as $value) {
						// Format the data as defined
						$data = $this->_formatData($value['value'], $value['type'], $file);
						// Add the XML
						$this->_addXML('<' . $static['value_name'] . '>' . $data . '</' . $static['value_name'] . '>', 4);
					}
					// Create and add end XML line
					$this->_addXML('</' . $static['group_as'] . '>', 3);
				}
			}
		}

		// Also, if there are anything filled in the $_xml_transfer_to_properties which contains arrays of values
		// add this to the XML structure
		if (count($this->_xml_transfer_to_properties) > 0) {
			// Loop through the transfer
			foreach ($this->_xml_transfer_to_properties as $xml_row) {
				// Add with tab 0 as this is defined before.
				$this->_addXML($xml_row, 0);
			}
		}

		// End the aspect node
		$this->_addXML('</view:properties>', 2);
	}

	/**
	 * _formatAndAddUseLocale
	 * Format string depending on use of locales, and add it to the XML scope
	 * @param string $elementArray
	 * @param string $value
	 */
	private function _formatAndAddUseLocale($elementArray, $value) {
		// Check if use_locales, then generate array
		if (isset($elementArray['use_locales']) && $elementArray['use_locales'] == 1) {
			// Create and insert start of XML line
			$this->_addXML('<' . $elementArray['alfresco'] . '>', 3);
			// Get languages to set
			$languages = $this->getSetting('languages', 'locales');
			foreach ($languages as $lang) {
				// Add language line
				$this->_addXML('<view:mlvalue view:locale="' . $lang . '">' . $value . '</view:mlvalue>', 4);
			}
			// Create and insert end of XML line
			$this->_addXML('</' . $elementArray['alfresco'] . '>', 3);
		} else {
			// Create and insert XML line
			$this->_addXML('<' . $elementArray['alfresco'] . '>' . $value . '</' . $elementArray['alfresco'] . '>', 3);
		}
	}
}
?>