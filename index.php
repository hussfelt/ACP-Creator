<?php
/**
* @version		1.0 hussfelt
* @package		ACPCreator
* @copyright	Copyright (C) Assign Me AB. All rights reserved.
* @license		GNU/GPL V 1.0
* This is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
*
*	WHAT?
* 	This file is used to generate ACP zip files for Alfresco. It is intended
*	to help users who need to export large quantaties of documents with metadata
*	from another system into Alfresco. It comes with a MySQL bridge, but can be
*	extended to connect to any type of datasource.
*
* 	HOW?
* 	Simply put this file in any given webfolder, configure your parameters
* 	and access the page from the web.
* 
* 	WHY?
* 	Why in PHP? Because it's simple. And roses are red. Black or White.
*
* 	RESULT:
* 	A zip-file will be presented to you for download, or written to your base-folder
* 	depending on configuration.
* 
*	CHANGE BRIDGE:
*	Just create a new class with the same methods as Bridge.php (MySQL), replace this file
*	and you should be set!
*
* 	FUTURE!
* 	We may add:
* 		* Add "Clean My Appartment" feature
* 		* Add comments for PHPDoc on all functions
*
*	CHANGELOG:
*/
// Includes
require_once 'class/ACPCreator.php';

/**
 * Settings
 */
// Create config settings
$SETTINGS['create_zip'] = true;
$SETTINGS['zip_download'] = false;

// Database settings
$SETTINGS['bridge']['host'] = "localhost";
$SETTINGS['bridge']['user'] = "root";
$SETTINGS['bridge']['password'] = "123123";
$SETTINGS['bridge']['db'] = "syvab";
// This is the field to use as relation and bind the database record to the fieldname 
$SETTINGS['bridge']['database_field'] = "digitaltritningsarkiv:dwgfil";

// File-connection
$SETTINGS['files']['start_path'] = "/users/henrik/Documents/Assign Me AB/Customers/SYVAB/ACPCreator/pdf/";

// File-locale to set in Alfresco
$SETTINGS['languages']['locale'] = 'sv_SE_'; 

// Return title and description with these language settings
$SETTINGS['languages']['locales'] = array(
	'sv_SE',
	'en_GB'
);

// Map fields from datasoruce to fields for alfresco metadata
$SETTINGS['map'] = array();

// Define the main XML scope
$SETTINGS['map']['main'] = array();

// Define what parameters to include in metadata
$SETTINGS['map']['main']['metadata'] = array();
$SETTINGS['map']['main']['metadata'][] = array('view:exportBy' => 'admin');
$SETTINGS['map']['main']['metadata'][] = array('view:exportDate' => '2010-11-25T10:20:48.666+01:00');
$SETTINGS['map']['main']['metadata'][] = array('view:exporterVersion' => '3.4.0 (b r23197)');
$SETTINGS['map']['main']['metadata'][] = array('view:exportOf' => 'app:company_home/cm:Data Dictionary');

// Define what kind of document type this is
$SETTINGS['map']['main']['doc']['type'] = 'syv:ritning';

// Define what metadata should be in the type scope of the XML
$SETTINGS['map']['main']['doc']['metadata'] = array();
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns' => '');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:nt' => 'http://www.jcp.org/jcr/nt/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:rn' => 'http://www.alfresco.org/model/rendition/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:sys' => 'http://www.alfresco.org/model/system/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:lnk' => 'http://www.alfresco.org/model/linksmodel/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:gd' => 'http://www.alfresco.org/model/googledocs/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:ver' => 'http://www.alfresco.org/model/versionstore/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:cmiscustom' => 'http://www.alfresco.org/model/cmis/custom');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:jcr' => 'http://www.jcp.org/jcr/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:emailserver' => 'http://www.alfresco.org/model/emailserver/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:fm' => 'http://www.alfresco.org/model/forum/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:ia' => 'http://www.alfresco.org/model/calendar');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:rule' => 'http://www.alfresco.org/model/rule/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:wcm' => 'http://www.alfresco.org/model/wcmmodel/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:sv' => 'http://www.jcp.org/jcr/sv/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:dl' => 'http://www.alfresco.org/model/datalist/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:syv' => 'http://www.syvab.se/model/content/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:st' => 'http://www.alfresco.org/model/site/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:usr' => 'http://www.alfresco.org/model/user/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:exif' => 'http://www.alfresco.org/model/exif/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:syvmadl' => 'http://www.syvab.se/model/maintenanceDataListModel/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:app' => 'http://www.alfresco.org/model/application/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:module' => 'http://www.alfresco.org/system/modules/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:d' => 'http://www.alfresco.org/model/dictionary/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:blg' => 'http://www.alfresco.org/model/blogintegration/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:alf' => 'http://www.alfresco.org');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:cmis' => 'http://www.alfresco.org/model/cmis/1.0/cs01');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:mix' => 'http://www.jcp.org/jcr/mix/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:wca' => 'http://www.alfresco.org/model/wcmappmodel/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:bpm' => 'http://www.alfresco.org/model/bpm/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:inwf' => 'http://www.alfresco.org/model/workflow/invite/nominated/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:imap' => 'http://www.alfresco.org/model/imap/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:cm' => 'http://www.alfresco.org/model/content/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:reg' => 'http://www.alfresco.org/system/registry/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:ver2' => 'http://www.alfresco.org/model/versionstore/2.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:stcp' => 'http://www.alfresco.org/model/sitecustomproperty/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:wcmwf' => 'http://www.alfresco.org/model/wcmworkflow/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:view' => 'http://www.alfresco.org/view/repository/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:imwf' => 'http://www.alfresco.org/model/workflow/invite/moderated/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:act' => 'http://www.alfresco.org/model/action/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:wf' => 'http://www.alfresco.org/model/workflow/1.0');
$SETTINGS['map']['main']['doc']['metadata'][] = array('xmlns:trx' => 'http://www.alfresco.org/model/transfer/1.0');
 

// FILE DEFINITION START
// This is where to define what information each filenode will have in the XML file generated.
// Each file file can have both dynamic and static references, on several head node tags.
// Per default there are normal properties, aspects and ACL available to define.

// Define Aspects attached to the document, both dynamic and static
$SETTINGS['map']['file']['aspects']['dynamic'] = array();
$SETTINGS['map']['file']['aspects']['static'] = array();

// Set dynamic aspects mapping
//$SETTINGS['map']['file']['aspects']['dynamic'][] = array('alfresco' => 'foo:AspectName', 'datasource' => 'databasename.tablename.fieldname');
$SETTINGS['map']['file']['aspects']['dynamic'][] = array(
																'alfresco' => 'syv:anlaggningskodAspect',
																'datasource' =>  'd7329:digitaltritningsarkiv:anlaggningskod',
																'type' => 'string',
																'settings' => array(
																	'group_as' => 'syv:anlaggningskod',
																	'group_as_meta' => '',
																	'value_name' => 'view:value',
																	'split_datasource_on' => ','
																)
													);
													
// NOTE: 
// If you are adding STATIC aspects, and you want to fill them with properties, you should fill:
// $SETTINGS['map']['file']['properties']['arrays']['static'][]
// defined further down in this document.
$SETTINGS['map']['file']['aspects']['dynamic'][] = array(
																'alfresco' => 'syv:anlaggningsdelAspect',
																'datasource' =>  'd7329:digitaltritningsarkiv:anlaggningsdel',
																'type' => 'string',
																'settings' => array(
																	'group_as' => 'syv:anlaggningsdel',
																	'group_as_meta' => '',
																	'value_name' => 'view:value',
																	'split_datasource_on' => ','
																)
													);

// Set static aspects mapping
$SETTINGS['map']['file']['aspects']['static'][] = array('alfresco' => 'cm:auditable',					'value' => '', 'type' => 'string');
$SETTINGS['map']['file']['aspects']['static'][] = array('alfresco' => 'sys:referenceable',				'value' => '', 'type' => 'string');
$SETTINGS['map']['file']['aspects']['static'][] = array('alfresco' => 'cm:titled',						'value' => '', 'type' => 'string');
$SETTINGS['map']['file']['aspects']['static'][] = array('alfresco' => 'cm:author',						'value' => '', 'type' => 'string');
$SETTINGS['map']['file']['aspects']['static'][] = array('alfresco' => 'cm:taggable',					'value' => '', 'type' => 'string');
$SETTINGS['map']['file']['aspects']['static'][] = array('alfresco' => 'cm:generalclassifiable',			'value' => '', 'type' => 'string');

// Define Properties for the document, both dynamic and static
// Set dynamic properties mapping
$SETTINGS['map']['file']['properties'] = array();
$SETTINGS['map']['file']['properties']['dynamic'] = array();
//$SETTINGS['map']['file']['properties']['dynamic'][] = array('alfresco' => 'cm:name',			'datasource' =>  'd7329:digitaltritningsarkiv:ritningsnamn', 'type' => 'filename');
$SETTINGS['map']['file']['properties']['dynamic'][] = array('alfresco' => 'cm:created',			'datasource' =>  'd7329:digitaltritningsarkiv:ritningsdatum', 'type' => 'date');
$SETTINGS['map']['file']['properties']['dynamic'][] = array('alfresco' => 'syv:revitionsDatum',	'datasource' =>  'd7329:digitaltritningsarkiv:revdatum', 'type' => 'date');
$SETTINGS['map']['file']['properties']['dynamic'][] = array('alfresco' => 'syv:ritningstyp',	'datasource' =>  'd7329:digitaltritningsarkiv:ritningstyp', 'type' => 'string');
$SETTINGS['map']['file']['properties']['dynamic'][] = array('alfresco' => 'syv:skala',			'datasource' =>  'd7329:digitaltritningsarkiv:skala', 'type' => 'string');
$SETTINGS['map']['file']['properties']['dynamic'][] = array('alfresco' => 'syv:benamning',		'datasource' =>  'd7329:digitaltritningsarkiv:benamning', 'type' => 'string');
$SETTINGS['map']['file']['properties']['dynamic'][] = array('alfresco' => 'syv:ritningsnummer',	'datasource' =>  'd7329:digitaltritningsarkiv:ritningsnummer', 'type' => 'string');
$SETTINGS['map']['file']['properties']['dynamic'][] = array('alfresco' => 'syv:anmarkning',		'datasource' =>  'd7329:digitaltritningsarkiv:anmarkning', 'type' => 'string');

// Set static properties mapping
$SETTINGS['map']['file']['properties']['static'] = array();
$SETTINGS['map']['file']['properties']['static'][] = array('alfresco' => 'sys:store-identifier',	'value' =>  'SpacesStore',			'type' => 'string');
$SETTINGS['map']['file']['properties']['static'][] = array('alfresco' => 'cm:creator',				'value' =>  'admin',				'type' => 'string');
$SETTINGS['map']['file']['properties']['static'][] = array('alfresco' => 'cm:modifier',				'value' =>  'admin',				'type' => 'string');
$SETTINGS['map']['file']['properties']['static'][] = array('alfresco' => 'cm:modified',				'value' =>  '2011-03-08T10:19:28.728+01:00', 'type' => 'date');
$SETTINGS['map']['file']['properties']['static'][] = array('alfresco' => 'cm:author',				'value' =>  'admin',				'type' => 'string');
$SETTINGS['map']['file']['properties']['static'][] = array('alfresco' => 'sys:store-protocol',		'value' =>  'workspace',			'type' => 'string');
$SETTINGS['map']['file']['properties']['static'][] = array('alfresco' => 'cm:content',				'value' =>  'contentUrl=$1|mimetype=$2|size=$3|encoding=$4|locale=$5', 'type' => 'content-string');
$SETTINGS['map']['file']['properties']['static'][] = array('alfresco' => 'cm:name',					'value' =>  '', 'type' => 'filename');
$SETTINGS['map']['file']['properties']['static'][] = array('alfresco' => 'cm:title',				'value' =>  '', 'type' => 'filename', 'use_locales' => 1);
$SETTINGS['map']['file']['properties']['static'][] = array('alfresco' => 'cm:description',			'value' =>  '', 'type' => 'string', 'use_locales' => 1);

// Define arrays in the properties node
$SETTINGS['map']['file']['properties']['arrays'] = array();
$SETTINGS['map']['file']['properties']['arrays']['static'] = array();
$SETTINGS['map']['file']['properties']['arrays']['dynamic'] = array();

/*$SETTINGS['map']['file']['properties']['arrays']['dynamic'][] = array(
													'datasource' =>  'd7329:digitaltritningsarkiv:ritningsnummer',
													'group_as' => 'cm:taggable',
													'group_as_meta' => '',
													'value_name' => 'view:value',
													'type' => 'string',
													'where_field' => 'pdffil' 
											);*/

$SETTINGS['map']['file']['properties']['arrays']['static'][] = array(
													'group_as' => 'cm:taggable',
													'group_as_meta' => '',
													'value_name' => 'view:value',
													'values' => array(
														array(
															'value' => '/cm:taggable/cm:ritningsdokument',
															'type' => 'string'
														)
													)
											);

// Define ACL for the document, both dynamic and static
// Define dynamic ACL mapping
$SETTINGS['map']['file']['acl']['dynamic'] = array();
// Define static ACL mapping
$SETTINGS['map']['file']['acl']['static'] = array();

// FILE DEFINITION END

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// THIS IS THE GENERATION EXECUTION!
// NO NEED TO EDIT ANYTHING BELOW THIS LINE
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

// Setup the ACP Creator service with our specified settings
$ACPCreator = new ACPCreator($SETTINGS);

// Get data from our services and generate XML code
$ACPCreator->executeHeadGeneration();
$ACPCreator->executeFileRowsGeneration();
$ACPCreator->executeFootGeneration();

// Create the FolderStructure and add XML file inside
$ACPCreator->prepareFolderStructure();
$ACPCreator->prepareAddXMLFile();

// Print XML to browser
//$ACPCreator->printXML();

// Echo out EOF so that we know the script run all the way through.
// But only if we are not sending a zip-file to the client
if(!$SETTINGS['zip_download']) {
	if($SETTINGS['create_zip']) {
		// Let's create the zipfile
		$ACPCreator->createZipFile();		
	}	
} else if($SETTINGS['create_zip']) {
	// Seems that we should create the zip-file and offer to the user as well, lets do it
	$ACPCreator->createZipFile();
	if ($SETTINGS['zip_download']) {
		$ACPCreator->forceZipDownload();
	}
}
echo "EOF";
?>