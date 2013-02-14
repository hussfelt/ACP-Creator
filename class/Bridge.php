<?php
/**
* @version		1.0 hussfelt
* @description	MySQL Bridge for the ACPCreator
* @package		ACPCreator
* @copyright	Copyright (C) 2010-2011 AssignMe AB. All rights reserved.
* @license		GNU/GPL V 1.0
* This is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

class Bridge
{
    private $_params = array(
        'server'   => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => '',
    	'database_field' => ''
    );

    private $_connection;
    private $_query;

    public function  __construct($settings) {
    	$this->_params['server'] = $settings['host'];
    	$this->_params['username'] = $settings['user'];
    	$this->_params['password'] = $settings['password'];
    	$this->_params['database'] = $settings['db'];
    	$this->_params['database_field'] = $settings['database_field'];
        $this->setConnection();
    }

    public function __destruct() {
        $this->closeConnection();
    }

    private function _getParam($aKey) {
        return array_key_exists($aKey, $this->_params) ? $this->_params[$aKey] : null;
    }

    public function getConnection() {
        return $this->_connection;
    }

    protected function setConnection() {
        $this->_connection = mysql_connect(
            $this->_getParam('server'),
            $this->_getParam('username'),
            $this->_getParam('password')
        );

        mysql_select_db($this->_getParam('database'));

        $result = mysql_query("set names 'utf8'");
    }

    protected function closeConnection() {
        return mysql_close($this->getConnection());
    }

    public function getQuery() {
    	return $this->_query;
    }

	public function setQuery($query) {
		$this->_query = $query;
	}

    private function _formatDataSource($datasource){
    	// Format coming in should be database:table:field
    	$data = explode(":", $datasource);
    	// Group data into array
    	$return = array();
    	// Set database
    	$return['database'] = $data[0];
    	// Set table
    	$return['table'] = $data[1];
    	// Set field
    	$return['field'] = $data[2];
    	// Return the data
    	return $return;
    	
    }

    /**
     * fetchRow
     * Fetch one row from datasoruce
     * @param string $datasource
     * @param string $file
     * @return string $result
     */
    public function fetchRow($datasource, $file) {
		// Parse datasource
    	$data = $this->_formatDataSource($datasource);

        // If we are trying to use another database, change
        if ($this->_getParam('database') != $data['database']) {
        	// Change db
        	mysql_select_db($data['database']);
        	// Change configured db
        	$this->_params['database'] = $data['database'];
        }

        // Check that we are looking in the same table as database_field has defined
        // Else exit with error that we are using a function that does not support this.
        $dbfield = explode(":", $this->_getParam('database_field'));
        if ($data['table'] != $dbfield[0]) {
        	// Kill the application with errormessage!
        	die("Database table: '".$data['table']."' does not match config database_field: '".$this->_getParam('database_field')."', relation can not be made"); 
        }

        // Set the query
        $this->setQuery("SELECT ".$data['field']." FROM `".$data['table']."` WHERE `".$dbfield[1]."`='$file'");

        // Run the query
        $query = mysql_query($this->getQuery());

        // Return result or false
        if($query) {
        	// Fetch data
        	$result = mysql_fetch_assoc($query);
        	// Return data
        	return $result[$data['field']];
        } else {
            return false;
        }
    }

    /**
     * fetchRows
     * Fetch many rows with condition
     * @param string $datasource
     * @param string $file
     * @param string $where_field
     * @return array
     */
    public function fetchRows($datasource, $file, $where_field) {
		// Parse datasource
    	$data = $this->_formatDataSource($datasource);

        // If we are trying to use another database, change
        if ($this->_getParam('database') != $data['database']) {
        	// Change db
        	mysql_select_db($data['database']);
        	// Change configured db
        	$this->_params['database'] = $data['database'];
        }

        // Set the query
        $this->setQuery("SELECT ".$data['field']." FROM `".$data['table']."` WHERE `".mysql_real_escape_string($where_field)."`='".mysql_real_escape_string($file)."'");

        // Run the query
        $query = mysql_query($this->getQuery());

        // Return result or false
        if($query) {
        	// Setup return array
        	$return = array();
        	// Loop through results
        	while ($result = mysql_fetch_array($query)) {
        		// Push data to array
        		$return[] = $result[$data['field']];
        	}
        	// Return data array
        	return $return;
        } else {
            return false;
        }
    }
}
?>