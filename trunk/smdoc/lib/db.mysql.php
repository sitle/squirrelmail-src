<?php
/*
Copyright 2003, Paul James

This file is part of the Framework for Object Orientated Web Development (Foowd).

Foowd is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foowd is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foowd; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/* 
 * Modified by SquirrelMail Development
 * $Id$
 */

/*
db.mysql.php
MySQL Database Connectivity
*/

/**
 * The Foowd MySQL database class.
 *
 * Database class for connecting to MySQL databases.
 *
 * @author Paul James
 * @package Foowd/DB
 */
class foowd_db_mysql extends foowd_db {

  /**
   * Constructs a new storage object.
   *
   * @param object foowd The foowd environment object.
   */
  function foowd_db_mysql(&$foowd) {
    $foowd->track('foowd_db_mysql->constructor');
    
    $this->foowd = &$foowd;

    $db = $foowd->config_settings['database'];

    /*
     * Ensure required values exist
     */
    if ( !isset($db['db_persistent']) ) $db['db_persistent'] = TRUE;
    if ( !isset($db['db_host']) )       $db['db_host'] = 'localhost'; 
    if ( !isset($db['db_user']) )       $db['db_user'] = 'smdoc';
    if ( !isset($db['db_password']) )   $db['db_password'] = 'smdoc'; 
    if ( !isset($db['db_name']) )       $db['db_name'] = 'sm_docs';
    if ( !isset($db['db_table']) )      $db['db_table'] = 'tblObject';
    
    // set connection type
    $connect = $db['db_persistent'] ? 'mysql_pconnect' : 'mysql_connect';
    
    // connect to DB
    $this->conn = $connect($db['db_host'], $db['db_user'], $db['db_password']);

    if ($this->conn) {
      if ( !mysql_select_db($db['db_database'], $this->conn) ) {
        trigger_error('Could not open database: '.htmlspecialchars($db['db_database']), E_USER_ERROR);
      }
    } else {
      trigger_error('Could not connect to host: '.htmlspecialchars($db['db_host']), E_USER_ERROR);
    }
    
    $foowd->track();
  }
  
  /**
   * Destructs the storage object.
   */
  function destroy() {
    $this->foowd->track('foowd_db_mysql->destructor');
    parent::destroy();
    $this->foowd->track(); 
    return mysql_close($this->conn);
  }
  
  /**
   * Execute query
   */
  function query($query) {
    $this->foowd->debug('sql', $query);
    return mysql_query($query, $this->conn);
  }
  
  /**
   * Escape a string for use in SQL string
   *
   * @param str str String to escape
   * @return str The escaped string
   */
  function escape($str) {
    return mysql_escape_string($str);
  }
  
  /**
   * Return an array of results given a query resource
   *
   * @param resource result Result set to get results from
   * @return array The results as an associative array
   */
  function fetch($result) {
    return mysql_fetch_array($result);
  }

  /**
   * Return the number of rows in a result set
   *
   * @param resource result Result set to get results from
   * @return int The number of rows in the result set
   */
  function num_rows($result) {
    return mysql_num_rows($result);
  }
  
  /**
   * See if a query was successful
   *
   * @param resource result The query result to check
   * @return bool If the query affected any rows
   */
  function query_success($result){
    return mysql_affected_rows($this->conn) > 0;
  }

}

?>
