<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 *
 * $Id$
 */

require_once 'DB.php';
include_once(SM_DIR.'env.database.php');
include_once(SM_DIR.'smdoc.env.cache.php');

/**
 * SquirrelMail modification to foowd db.
 * Uses PEAR to manage backends,
 * provides some customized function
 */
class smdoc_db extends foowd_db 
{
  /** 
   * Time in seconds since last update of object before checking for old
   * archived versions to delete. Default 1 day (86400 seconds).
   * @access public
   */
   var $tidy_delay = 86400;

  /**
   * Database factory method.
   *
   * Used for creating a database object of the correct sub-class. Given the
   * name of the DB layer to use, the method will load the corrisponding DB
   * layer class if it has not already been loaded.
   *
   * @static
   * @param object foowd The foowd environment object.
   * @param str type The type of database object to load.
   * @return mixed The new database object or FALSE on failure.
   */
  function factory(&$foowd)
  {
    $foowd->track('smdoc_db->factory');

    $dbClass = isset($db['db_class']) ? $db['db_class'] : 'smdoc_db';
    $dbFile  = isset($db['db_path'])  ? $db['db_path']  : SM_DIR . 'smdoc.env.database.php';

    if ( file_exists($dbFile) )
      require_once($dbFile);
    else
      trigger_error('Could not find database file: ' . $dbFile, E_USER_ERROR); 

    if (!class_exists($dbClass)) 
      trigger_error('Could not find database class: ' . $dbClass, E_USER_ERROR); 

    $foowd->track();
    return new $dbClass($foowd);
  }

  /**
   * Constructs a new database object.
   *
   * @param object foowd The foowd environment object.
   */
  function smdoc_db(&$foowd) 
  {
    $foowd->track('smdoc_db->constructor');
    
    $db = $foowd->config_settings['database'];

    // Ensure required values exist
    if ( !isset($db['db_persistent']) ) $db['db_persistent'] = TRUE;
    if ( !isset($db['db_host']) )       $db['db_host'] = 'localhost'; 
    if ( !isset($db['db_user']) )       $db['db_user'] = 'smdoc';
    if ( !isset($db['db_password']) )   $db['db_password'] = 'smdoc'; 
    if ( !isset($db['db_database']) )   $db['db_database'] = 'smdocs';
    if ( !isset($db['db_table']) )      $db['db_table'] = 'tblObject';
    if ( !isset($db['db_type']) )       $db['db_type'] = 'mysql';
 
    // create PEAR DB DSN: phptype(syntax)://user:pass@protocol(proto_opts)/database
    $dsn = $db['db_type'] . '://'
                . $db['db_user'] . ':' . $db['db_password']
                . '@' . $db['db_host']
                . '/' . $db['db_database'];

    // connect to DB
    $this->conn = DB::connect($dsn, $db['db_persistent']);

    // With DB::isError you can differentiate between an error or a valid connection.
    if ( DB::isError($this->conn) )
    {
      trigger_error('Could not create DB connection: '
                      . $dsn . "<br />\n"
                      . htmlspecialchars($this->conn->getMessage()), 
                    E_USER_ERROR);
    } 
    
    // Make it so that fetch gets back associative arrays
    $this->conn->setFetchMode(DB_FETCHMODE_ASSOC);
 
    $this->foowd = &$foowd;
    $this->objects = new smdoc_obj_cache();
    $this->default_source = $db['db_table'];

    if ( isset($foowd->config_settings['archive']) )
    {
      $archive = $foowd->config_settings['archive'];
      if ( isset($archive['tidy_delay']) )  
        $this->tidy_delay = $archive['tidy_delay'];
    }
  }

  /**
   * Destructs the storage object.
   */
  function destroy() 
  {
    $this->foowd->track('smdoc_db->destructor');

    // clean up object cache
    $this->objects->destroy();   
 
    // close connection
    $this->conn->disconnect();

    $this->foowd->track(); 
  }

  /**
   * Execute query
   *
   * @abstract
   * @access protected
   * @param str query The query to execute
   * @return resource The resulting query resource
   */
  function &query($query) 
  {
    $this->foowd->debug('sql', $query);

    $result = $this->conn->query($query);

    // Always check that $result is not an error
    if (DB::isError($result)) 
    {
      $this->foowd->debug('msg', $result->getMessage());
      return FALSE;
    }
    return $result;
  }

  /**
   * Escape a string for use in SQL string
   *
   * @param str str String to escape
   * @return str The escaped string
   */
  function escape($str) {
    return $this->conn->quote($str);
  }

  /**
   * Return an array of results given a query resource
   *
   * @param resource result Result set to get results from
   * @return array The results as an associative array
   */
  function fetch($result) {
    // fetchRow() returns the row, NULL on no more data or a  
    // DB_Error, when an error occurs.
    $row = $result->fetchRow();

    // Always check that $result is not an error
    if (DB::isError($row)) 
    {
      $this->foowd->debug('msg', $row->getMessage());
      return FALSE;
    }
    
    return $row;
  }

  /**
   * Return the number of rows in a result set
   *
   * @param resource result Result set to get results from
   * @return int The number of rows in the result set
   */
  function num_rows($result) {
    return $result->numRows();
  }

  /**
   * See if a query was successful
   *
   * @param resource result The query result to check
   * @return bool If the query affected any rows
   */
  function query_success($result) {
    return $this->conn->affectedRows() > 0;
  }

  /**
   * release storage used by result set
   * @param resource result The result set to free
   * @return bool Returns TRUE on success, FALSE on failure.
   */
  function free_result($result) {
    return $result->free();
  }

  /**
   * Add an object reference to the loaded objects array.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   * @param object object Reference of the object to add
   */
  function addToLoadedReference(&$object) {
    $this->objects->addToLoadedReference($object);
  }

  /**
   * Check if an object is referenced in the object reference array.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   * @param str source The source to fetch the object from
   */
  function &checkLoadedReference($indexes, $source) {
    return $this->objects->checkLoadedReference($indexes, $source);
  }

  /**
   * Get a list of objects.
   *
   * @param array indexes Array of indexes to be returned
   * @param str source The source to fetch the object from
   * @param array where Array of indexes and values to find object by
   * @param mixed order The index to sort the list on, or array of indices
   * @param mixed limit The length of the list to return, or a LIMIT string
   * @param bool returnObjects Return the actual objects not just the object meta data
   * @param bool setWorkspace get specific workspace id (or any workspace ok)
   * @return array An array of object meta data or of objects.
   */   
  function &getObjList($indexes = NULL, $source = NULL, 
                       $where = NULL, $order = NULL, $limit = NULL, 
                       $returnObjects = FALSE, $setWorkspace = TRUE) 
  {
    $this->foowd->track('smdoc_db->getObjList');

    if ( $source == NULL )
      $source = $this->default_source;

    if ( $setWorkspace )
      $this->setWorkspace($where);

    if ( $where == NULL )
      $where = '';
    else
      $where = ' WHERE' . $this->buildWhere($where);
   
    if ( $order == NULL ) 
      $order = '';
    elseif ( is_array($order) ) 
      $order = ' ORDER BY '.join(', ', $order);
    else 
      $order = ' ORDER BY '.$order;

    // build limit, if a string, leave alone (properly formed LIMIT string)
    if ( $limit == NULL  ) 
      $limit = '';
    elseif ( !is_string($limit) ) 
      $limit = ' LIMIT ' . $limit;

    $select = 'SELECT ';
    if ( $indexes == NULL ) 
    {
      $select .= $source.'.objectid AS objectid, '
                .$source.'.classid AS classid, '
                .$source.'.version AS version, '
                .$source.'.workspaceid AS workspaceid, '
                .$source.'.title AS title, '
                .$source.'.object AS object';
    } 
    else 
    {
      $first = 1;
      foreach ( $indexes as $index ) 
      {
        if ( !$first )
          $select .= ', ';
        else
          $first = 0;

        $select .= $index;
      }
    }
    $select .= ' FROM '.$source.$where.$order.$limit;

    $this->foowd->debug('sql', $select);
    $records =& $this->conn->getAll($select);

    if (DB::isError($records)) 
    {
      $this->foowd->track(); 
      return FALSE;
    }

    $return = array();
        
    foreach ($records as $record) 
    {
      if ( !isset($return[$record['objectid']]) || 
           $this->checkRecordVersion($record['version'], $return[$record['objectid']]['version']) ) 
      {
        if ($returnObjects) 
        {
          $return[$record['objectid']] = $this->unserializeObject($source, $record) ;
          $return[$record['objectid']]->foowd = &$this->foowd; // create Foowd reference
          $return[$record['objectid']]->source = $source;
          $this->addToLoadedReference($return[$record['objectid']]);
        } 
        else 
          $return[$record['objectid']] = $record;
      }
    }
    
    $this->foowd->track(); 
    return $return;
  }

  /**
   * Highly specialized function to compare the versions of 
   * two records. If no version field is found, return TRUE for transparency.
   * Otherwise return true if the first record is greater than the second.
   * @access private
   * @param array record1 Retrieved associative array created from query row.
   * @param array record2 Retrieved associative array created from query row.
   * @return TRUE if version is defined in both records, and version1 > version2
   * @see #getObjList
   */
  function checkRecordVersion($record1, $record2) 
  {
    if ( !isset($record1['version']) || !isset($record2['version']) )
      return TRUE;
    
    return $record1['version'] > $record2['version'];
  }

  /**
   * Highly specialized function to lookup the classid of
   * an object to be deserialized based on either the 'classid' field,
   * or it's source. 
   * @access private
   * @param str source The source to fetch the object from
   * @param array record Retrieved associative array created from query row. 
   * @see #getObjList
   * @see #getObj
   */
  function unserializeObject($source, $record) 
  {
    if ( isset($record['object']) )
      return $this->foowd->unserialize($record['object']);
    
    return new foowd_object();
  }


  /**
   * Save an object.
   *
   * @param object object The object to save
   * @return bool Success or failure.
   */
  function save(&$object) 
  {
    $this->foowd->track('smdoc_db->save');

    if (!isset($object->foowd_update) || $object->foowd_update) 
      $object->update(); // update object meta data

    $serializedObj = serialize($object);

    if (is_array($object->foowd_source)) 
    {
      $source = $object->foowd_source['table'];
      $makeTable = $object->foowd_source['table_create'];
    }
    elseif ( isset($object->foowd_source) )
      $source = $object->foowd_source;
    else
      $source = $this->default_source;

    // Build array of DB indices from object meta data
    $fieldArray['object'] = $serializedObj;
    foreach ( $object->foowd_indexes as $index => $definition )
    {
      if ( isset($object->$index) )
      {
        if ( $object->$index == FALSE )
          $fieldArray[$index] = 0;
        else
          $fieldArray[$index] = $object->$index;
      }
    }

    // buildWhere
    foreach( $object->foowd_primary_key as $k => $index )
      $where[$index] = $object->foowd_original_access_vars[$index];
    $where = 'WHERE ' . $this->buildWhere($where);

    $update = 'UPDATE '.$source.' SET ';
    $insert = 'INSERT INTO '.$source.' (';
    $values = '';

    $foo = FALSE;
    foreach ( $fieldArray as $field => $value )
    {
      if ( $foo )
      {
        $update .= ', ';
        $insert .= ', ';
        $values .= ', ';
      }
      $foo = TRUE;
      $set = FALSE;

      $insert .= $field;
      if ( isset($object->foowd_indexes[$field]['type']) )
      {
        if ( $object->foowd_indexes[$field]['type'] == 'INT' )
        {
          $update .= $field.' = '.$value;
          $values .= $value;
          $set = TRUE;
        }
        elseif ( $object->foowd_indexes[$field]['type'] == 'DATETIME' )
        {
          $update .= $field.' = \''.date($this->dateTimeFormat, $value).'\'';
          $values .= '\''.date($this->dateTimeFormat, $value).'\'';
          $set = TRUE;
        }
      }

      if ( !$set )
      {
        $update .= $field.' = '.$this->escape($value);
        $values .= $this->escape($value);
      }
    }
    $insert .= ') VALUES ('.$values.')';

    $saveResult = 0;

    // try to update existing record
    if ( $this->query_success($this->query($update)) ) 
      $saveResult = 1;
    // if fail, write new record
    elseif ( $this->query_success($this->query($insert)) )
      $saveResult = 2;
    // if fail, modify table to include indexes from class definition
    elseif ( $this->alterTable($source, $fieldArray) )
    {
      // indexes were altered, retry update
      if ( $this->query_success($this->query($update)) ) 
        $saveResult = 3;
      // if that fails, retry insert
      elseif ( $this->query_success($this->query($insert)) )
        $saveResult = 4;
    }

    // tidy old archived versions
    $tidyDate = time() - $this->tidy_delay;
    if ( $saveResult && $object->updated < $tidyDate )
      $this->tidy($object);

    $this->foowd->track();
    return $saveResult;
  }

}
