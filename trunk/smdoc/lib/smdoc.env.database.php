<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 *
 * $Id$
 */

/** 
 * Modified Database implementation that uses PEAR DB.
 * 
 * $Id$
 * 
 * @package smdoc
 * @subpackage db
 */

/** Include PEAR DB library */
require_once('DB.php');

/** Include object cache */
include_once(SM_DIR.'smdoc.env.cache.php');

/**
 * SquirrelMail modification to foowd db.
 * Uses PEAR to manage backends, provides some customized function
 * 
 * @package smdoc
 * @subpackage db
 */
class smdoc_db  
{
  /**
   * The database connect resource.
   *
   * @var resource
   */
  var $conn;

  /**
   * The date/time format used by this storage medium. This string should be
   * a PHP date function compatible date/time formatting string.
   *
   * @var string
   */
  var $dateTimeFormat = 'Y-m-d H:i:s';

  /**
   * An array of references to all objects loaded from this database.
   *
   * @var array
   */
  var $objects;

  /**
   * The default table to use.
   * 
   * @var string
   */
  var $table;

  /**
   * Reference to the Foowd object.
   *
   * @var object
   */
  var $foowd;

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
   * @param smdoc  $foowd Reference to the foowd environment object.
   * @param string $type The type of database object to load.
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
   * @param smdoc $foowd Reference to the foowd environment object.
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
      $this->foowd->track('msg', 'Error creating connection');
      $this->foowd->track();
      trigger_error('Could not create DB connection: '
                      . $dsn . "<br />\n"
                      . htmlspecialchars($this->conn->getMessage()), 
                    E_USER_ERROR);
    } 
    
    // Make it so that fetch gets back associative arrays
    $this->conn->setFetchMode(DB_FETCHMODE_ASSOC);
 
    $this->foowd = &$foowd;
    $this->objects =& new smdoc_object_cache($foowd, $this);
    $this->table = $db['db_table'];

    if ( isset($foowd->config_settings['archive']) )
    {
      $archive = $foowd->config_settings['archive'];
      if ( isset($archive['tidy_delay']) )  
        $this->tidy_delay = $archive['tidy_delay'];
    }

    $this->foowd->track();
  }

  /**
   * Destructs the storage object.
   */
  function __destruct() 
  {
    $this->foowd->track('smdoc_db->destructor');

    // clean up object cache
    $this->objects->__destruct();   
 
    // close connection
    $this->conn->disconnect();

    $this->foowd->track(); 
  }

  /**
   * Execute query
   *
   * @abstract
   * @access protected
   * @param string $query The query to execute
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
   * Execute query
   *
   * @abstract
   * @access protected
   * @param string $query The query to execute
   * @return array The array of all resulting records
   */
  function &queryAll($query)
  {
    $this->foowd->debug('sql', $query);
    $records =& $this->conn->getAll($query);
    if (DB::isError($records)) 
    {
      $this->foowd->debug('msg', $records->getMessage());
      $records = FALSE;
    }
    return $records;
  }

  /**
   * Escape a string for use in SQL string
   *
   * @param string $str String to escape
   * @return string The escaped string
   */
  function escape($str) 
  {
    return $this->conn->quote($str);
  }

  /**
   * Return an array of results given a query resource
   *
   * @param resource $result Result set to get results from
   * @return array The results as an associative array
   */
  function fetch($result) 
  {
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
   * Return result of count (*)
   * 
   * @param  mixed $in_source Source to get object from
   * @param  array $where Array of values to find object by
   */
  function count($in_source, $where = '')
  {
    $this->getSource($in_source, $source, $makeTable);

    if ( $where )
      $where = ' WHERE ' . $this->buildWhere($where);

    $select = 'SELECT count(*) FROM '.$source.$where;
    $query = $this->query($select);
    if ( $query === FALSE )
    {
      // Query failed, check for correct fields (possibly create Table)
      $fields = $this->getFields($in_source);
      if ( $fields === FALSE )
        return 0;

      $query = $this->query($select);
      if ( $query === FALSE )
        return 0;
    }
    
    $row = $this->fetch($query);
    return intval($row['count(*)']);
  }

  /**
   * Return the number of rows in a result set
   *
   * @param resource $result Result set to get results from
   * @return int The number of rows in the result set
   */
  function num_rows($result) 
  {
    if ( $result )
      return $result->numRows();
    return 0;
  }

  /**
   * See if a query was successful
   *
   * @param resource $result The query result to check
   * @return bool If the query affected any rows
   */
  function query_success($result) 
  {
    return $this->conn->affectedRows() > 0;
  }

  /**
   * Add an object reference to the loaded objects array.
   *
   * @access protected
   * @param array $indexes Array of indexes and values to find object by
   * @param object $object Reference of the object to add
   */
  function addToLoadedReference(&$object) 
  {
    $this->objects->addToLoadedReference($object);
  }

  /**
   * Check if an object is referenced in the object reference array.
   *
   * @access protected
   * @param array $indexes Array of indexes and values to find object by
   * @param string $source The source to fetch the object from
   */
  function &checkLoadedReference($indexes, $source) 
  {
    return $this->objects->checkLoadedReference($indexes, $source);
  }

  /**
   * Verify that title is unique. 
   * If it is, and the uniqueObjectid parameter is TRUE, 
   * a unique objectid is generated and assigned to the objectid parameter.
   *
   * @param string $title The proposed title
   * @param int $workspaceid The workspace to search in, FALSE to leave workspaceid out
   * @param int $objectid The object id generated from the title
   * @param mixed $in_source Source to get object from
   * @param bool $uniqueObjectid Generate unique object id for unique title
   * @return TRUE if Title is Unique.
   */
  function isTitleUnique($title, $workspace, &$objectid, 
                         $in_source = NULL, $uniqueObjectid = TRUE)
  {
    $this->foowd->track('smdoc_db->isTitleUnique', $title);
    $this->getSource($in_source,$source,$makeTable);

    $indexes['title'] = $title;
    if ( $workspace !== FALSE )
      $indexes['workspaceid'] = $workspace;

    $where = ' WHERE'.$this->buildWhere($indexes);
    $select = 'SELECT title FROM '.$source.$where;

    // Make the query to find all items in the same workspace
    // that have the same title
    $query = $this->query($select);
    if ( $query === FALSE )
    {
      // Query failed, check for correct fields (possibly create Table)
      $fields = $this->getFields($in_source);
      if ( $fields === FALSE )
        trigger_error('Unable to create table using specified source: '.$in_source['table']);

      $query = $this->query($select);
      if ( $query === FALSE )
        trigger_error('Unable to find title in specified source: '.$in_source['table']);
    }
    
    // If ANY rows were returned with the query, 
    // the title is NOT unique (result = FALSE).
    $result = $this->num_rows($query) > 0 ? FALSE : TRUE;

    // Regardless of the uniqueness of the title,
    // do we want a unique object id to be assigned?
    // This query is only against objectids, and is
    // independent of workspace.
    if ( $uniqueObjectid )
    {
      $objectid = crc32(strtolower($title));
      $select = 'SELECT objectid FROM '.$source.' WHERE objectid = ';
      $query = $this->query($select.$objectid);
      while ( $this->num_rows($query) > 0 )
      {
        $objectid++;
        $query = $this->query($select.$objectid);
      }
    }

    $this->foowd->track();
    return $result;
  }

  /**
   * Build where clause from indexes array.
   *
   * @access protected
   * @param array $indexes Array of indexes and values to find object by
   * @param string $conjuction Operand to use to join the elements of the clause
   * @return string The generated where clause.
   */
  function buildWhere($indexes, $conjunction = 'AND')  
  {
    // array('raw_where' => 'WHERE classid <> ERROR_CLASS_ID');
    if ( isset($index['raw_where']) )
        return $index['raw_where'];

    $where = '';

    if ( $conjunction != 'AND' )
      $conjunction = ' OR';

    foreach ($indexes as $key => $index) 
    {
      if ( !isset($index) )
        continue;

      $where .= $conjunction;

      // array('classid' => ERROR_CLASS_ID, 'objectid' => 83921);
      if (!is_array($index) ) 
      {
        $where .= ' '.$key.' = ';
        $where .= is_numeric($index) ? $index : $this->escape($index);
        $where .= ' ';
      } 
      else 
      {
        // dealing with an array:
        // Array
        // (
        //    [OR] => Array
        //        (
        //            [0] => Array
        //                (
        //                    [index] => classid
        //                    [op] => =
        //                    [value] => -679419151
        //                )
        //            [1] => Array
        //                (
        //                    [index] => classid
        //                    [op] => =
        //                    [value] => -17221723
        //                )
        //        )
        // )
        // SO, the key is a conjunction, 
        // and $index is an array containing clauses that should 
        // be grouped together with that conjunction

        if ( !isset($index['index']) ) 
          $where .= $this->buildWhere($index, $key);
        else 
        {
          // otherwise, we have an index/op/value array
          if ( !isset($index['op']) )
            $index['op'] = '=';
          elseif ( $index['op'] == '!=' )
            $index['op'] = '<>';
          $where .= ' '.$index['index'].' '.$index['op'].' ';

          if ( isset($index['field']) )
          {
            $field = $index['field'];
            $where .= $field;
          }
          else
          {
            if ( isset($index['value']) )
              $value = $index['value'];
            else
            {
              trigger_error('No value given for index "'.$index['index'].'".');
              $value = '';
            }
            $where .= is_numeric($value) ? $value : $this->escape($value);
          }

          $where .= ' ';
        }
      }
    }
    // Believe it or not, spacing here is important.
    // as part of building string, you'll get some garbage up front..
    // make sure there are 2 spaces before ( and one after )
    return '  ('.substr($where, 3).') ';
  }

  /**
   * Add workspaceid index to index array if one does not exist at the top level.
   *
   * @access protected
   * @param array $indexes Array of indexes and values to find object by
   */
  function setWorkspace(&$indexes)
  {
    if (is_array($indexes)) 
    {
      foreach($indexes as $key => $index) 
      {
        if ( $key == 'workspaceid' ||
             ( is_array($index) && 
               isset($index['index']) && 
               $index['index'] == 'workspaceid') )
          return; // Found workspace id in indexes, return early 
      }
    }

    if ( isset($this->foowd->user->workspaceid) &&
         $this->foowd->user->workspaceid != 0 )
    { 
      $workspaceid['OR'][] = array('index' => 'workspaceid', 
                                   'op' => '=', 
                                   'value' => $this->foowd->user->workspaceid);
      $workspaceid['OR'][] = array('index' => 'workspaceid', 
                                   'op' => '=', 
                                   'value' => 0 );
    }
    else
    {
      $workspaceid['index'] = 'workspaceid';
      $workspaceid['op'] = '=';
      $workspaceid['value'] = 0;
    }

    $indexes[] = $workspaceid;
  }

  /**
   * Get lastest version of an object.
   *
   * @param  array $where Array of values to find object by
   * @param  mixed $in_source Source to get object from
   * @param  array $indexes Array of indexes to fetch
   * @param  bool  $setWorkspace get specific workspace id (or any workspace ok)
   * @param  bool  $useVersion If TRUE, get most recent version (ORDER BY and LIMIT).
   * @return mixed The retrieved object or an array containing the retrieved object and the joined objects.
   */
  function &getObj($where = NULL, $in_source = NULL, $setWorkspace = TRUE, $useVersion = TRUE)
  {
    if ($object = &$this->checkLoadedReference($where, $in_source)) 
      return $object;

    $this->foowd->track('smdoc_db->getObj');

    $this->getSource($in_source,$source,$makeTable);
    if ( $setWorkspace )
      $this->setWorkspace($where);

    $version = $useVersion ? ' ORDER BY version DESC LIMIT 1'     : '';
    $where   = $where      ? ' WHERE' . $this->buildWhere($where) : '';
   
    $select = 'SELECT '.$source.'.object AS object FROM '.$source.$where.$version;
    $object = FALSE;

    $query = $this->query($select);
    if ( $query === FALSE )
    {
      // Query failed, check for correct fields (possibly create Table)
      $fields = $this->getFields($in_source);
      if ( $fields !== FALSE )
        $query = $this->query($select);
    }

    if ( $query )
    {
      $result = $this->fetch($query);
      if ( $result && isset($result['object']) )
      {
        $object = unserialize($result['object']);
        $object->foowd = &$this->foowd; // create Foowd reference
        $object->foowd_source = $in_source; // set source for object
        $this->addToLoadedReference($object);
      }
    }

    $this->foowd->track();
    return $object;
  }


  /**
   * Get all versions of an object.
   *
   * @param array $indexes Array of indexes and values to find object by
   * @param string $source The source to fetch the object from
   * @return array An array of the retrieved object versions indexed by version number.
   */
  function &getObjHistory($indexes, $in_source = NULL)
  {
    $this->foowd->track('foowd_db->getObjHistory');

    $this->getSource($in_source,$source,$makeTable);

    $this->setWorkspace($indexes);

    $where = ' WHERE'.$this->buildWhere($indexes);

    $select = 'SELECT object, classid, version FROM '.$source
              .$where.' ORDER BY version DESC';

    $records =& $this->queryAll($select);
    if ( $records === FALSE ) 
    {
      $this->foowd->track(); 
      return FALSE;
    }

    $return = array();
    $latest = 0;
    foreach ($records as $record) 
    {
      $return[$record['version']] = unserialize($record['object']);
      $return[$record['version']]->foowd = &$this->foowd; // create Foowd reference
      $return[$record['version']]->foowd_source = $in_source;
      $this->addToLoadedReference($return[$record['version']]);
      if ($record['version'] > $latest) {
        $latest = $record['version'];
      }
    }
    $return[0] = &$return[$latest]; // set reference on index zero to latest version
    $this->foowd->track();
    return $return;
  }


  /**
   * Get a list of objects.
   *
   * @param array $indexes Array of indexes to be returned
   * @param string $source The source to fetch the object from
   * @param array $where Array of indexes and values to find object by
   * @param mixed $order The index to sort the list on, or array of indices
   * @param mixed $limit The length of the list to return, or a LIMIT string
   * @param bool $returnObjects Return the actual objects not just the object meta data
   * @param bool $setWorkspace get specific workspace id (or any workspace ok)
   * @return array An array of object meta data or of objects.
   */   
  function &getObjList($indexes = NULL, $in_source = NULL, 
                       $where = NULL, $order = NULL, $limit = NULL,
                       $returnObjects = FALSE, $setWorkspace = TRUE) 
  {
    $this->foowd->track('smdoc_db->getObjList');

    $this->getSource($in_source, $source, $makeTable);

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

    $select = '';
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
      foreach ( $indexes as $index ) 
      {
        if ( $select != '')
          $select .= ', ';

        $select .= $index;
      }
    }
    $select = 'SELECT '.$select.' FROM '.$source.$where.$order.$limit;

    $records =& $this->queryAll($select);
    if ( $records === FALSE ) 
    {
      $this->foowd->track(); 
      return FALSE;
    }

    $return = array();
    foreach ($records as $record) 
    {
      $id = $record['objectid'];
      if ( isset($record['version']) ) 
        $id .= '.' . $record['version'];
      if ( isset($record['title']) )
        $id .= '.' . $record['title'];
      // This seems backwards, but if the workspace was explicitly set 
      // (!$setWorkspace) then we need to use it as a discriminator
      if ( isset($record['workspaceid']) && !$setWorkspace )
        $id .= '.' . $record['workspaceid'];

      if ( !isset($return[$id]) ) 
      {
        if ($returnObjects && isset($record['object']) ) 
        {
          $return[$id] = unserialize($record['object']);
          $return[$id]->foowd = &$this->foowd; // create Foowd reference
          $return[$id]->source = $in_source;
          $this->addToLoadedReference($return[$id]);
        } 
        else 
          $return[$id] = $record;
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
   * @param array $record1 Retrieved associative array created from query row.
   * @param array $record2 Retrieved associative array created from query row.
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
   * Our own internal method for getting the source that
   * should be used.
   * @access private
   * @param mixed $in_source Source specified by the caller
   * @param string $source Name of table to query
   * @param string $makeTable name of function for table creation
   */
  function getSource($in_source, &$source, &$makeTable)
  {
    if (is_array($in_source)) 
    {
      $source = $in_source['table'];
      $makeTable = $in_source['table_create'];
      return;
    }
    elseif ( isset($in_source) )
      $source = $in_source;
    else
      $source = $this->table;
    $makeTable = FALSE;
  }


  /**
   * Save an object.
   *
   * @param object $object The object to save
   * @return bool Success or failure.
   */
  function save(&$object) 
  {
    $this->foowd->track('smdoc_db->save');

    if (!isset($object->foowd_update) || $object->foowd_update) 
      $object->update(); // update object meta data

    $serializedObj = serialize($object);

    $this->getSource($object->foowd_source, $source, $makeTable);

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

    $where = ' WHERE ' . $this->buildWhere($where);
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
    $update .= $where;

    $saveResult = 0;

    // try to update existing record
    if ( $this->query_success($this->query($update)) ) 
      $saveResult = 1;
    // if fail, write new record
    elseif ( $this->query_success($this->query($insert)) )
      $saveResult = 2;
    // if fail, modify table to include indexes from class definition
    elseif ( $this->alterTable($object->foowd_source, $fieldArray, $object->foowd_indexes) )
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

  /**
   * Delete an object (and all archive versions).
   *
   * @param object $object The object to delete
   * @return bool Success or failure.
   */
  function delete(&$object) 
  {
    $this->foowd->track('foowd_db->delete');

    $this->getSource($object->foowd_source, $source, $makeTable);

    // buildWhere
    foreach( $object->foowd_primary_key as $index )
    {
      if ( $index != 'version' )
        $where[$index] = $object->foowd_original_access_vars[$index];
    }

    $delete = 'DELETE FROM '.$source.' WHERE ' . $this->buildWhere($where);

    $result =& $this->query($delete);
    $this->foowd->track();
    return $this->query_success($result);
  }

  /**
   * Tidy an objects archived versions.
   *
   * @param object $object The object to delete
   * @return bool Success or failure.
   */
  function tidy(&$object) 
  {
    $this->foowd->track('foowd_db->tidy');

    if ( !in_array('version',$object->foowd_primary_key) ||
         !in_array('updated',$object->foowd_primary_key) )
      return FALSE;

    $this->getSource($object->foowd_source, $source, $makeTable);

    $delete = 'DELETE FROM '.$source.' WHERE ';
    $first = 1;
    foreach ($object->foowd_primary_key as $key)
    {
      if ( !isset($object->foowd_original_access_vars[$key]) )
        continue;

      if ( $first ) 
        $first = 0; 
      else 
        $delete .= ' AND ';

      if ( $key == 'version' )
        $delete .= 'version < '.($object->foowd_original_access_vars['version'] - $this->foowd->minimum_number_of_archived_versions);
      elseif ( $key == 'updated' )
        $delete .= 'updated < \''.date($this->foowd->database->dateTimeFormat, strtotime($this->foowd->destroy_older_than)).'\'';
      else
        $delete .= $key.' = '.$object->foowd_original_access_vars[$key];
    }

    $result = $this->query($delete);

    $this->foowd->track();
    return ( $result ) ? TRUE : FALSE;    
  }

  /**
   * Get the fields for this table. If it fails, this method presumes that that
   * is because the table does not exist, so tries to create it.
   *
   * @access protected
   * @param mixed $in_source Source specified by the caller
   * @return array Array of field names
   */
  function getFields($in_source)
  {
    $this->getSource($in_source, $table, $makeTable);
    $select = 'DESCRIBE '.$table;
    $records =& $this->queryAll($select);

    if ( $records !== FALSE )
    {
      $return = array();
      foreach ($records as $field_data)
        $return[] = $field_data['Field'];

      return $return;
    }
    else
    {
      // If couldn't query table fields, table might not exist.
      // try creating it - if it succeeds, retry request,
      // if not, return FALSE
      if ( $makeTable )
        $result = call_user_func($makeTable, $this->foowd);
      else
        $result = $this->query('CREATE TABLE `'.$table.'` (
	`objectid`    int(11) NOT NULL default \'0\',
	`version`     int(10) unsigned NOT NULL default \'1\',
	`classid`     int(11) NOT NULL default \'0\',
	`workspaceid` int(11) NOT NULL default \'0\',
	`title`       varchar(32) NOT NULL default \'\',
	`updated`     datetime NOT NULL default \'0000-00-00 00:00:00\',
	`permissions` varchar(128) default \'\',
	`object`      longblob,
	PRIMARY KEY (`objectid`,`version`,`classid`,`workspaceid`),
	KEY `idxtblObjectTitle`       (`title`),
	KEY `idxtblObjectupdated`     (`updated`),
	KEY `idxtblObjectObjectid`    (`objectid`),
	KEY `idxtblObjectClassid`     (`classid`),
	KEY `idxtblObjectVersion`     (`version`),
	KEY `idxtblObjectWorkspaceid` (`workspaceid`)
	);');
      
      // create table worked, try getFields again.
      if ( $result !== FALSE )
        return $this->getFields($in_source);
    }
    return FALSE;
  } 

  /**
   * Do a SQL ALTER TABLE statement.
   *
   * @access protected
   * @param mixed $in_source Source specified by the caller
   * @param array $fieldArray An array of column clause elements.
   * @param mixed $indices Array of object indices
   * @return bool TRUE on success.
   */
  function alterTable($in_source, $fieldArray, $indices) 
  {
    $fields = $this->getFields($in_source);
    if ( $fields === FALSE )
      return FALSE;

    $missingFields = array();
    foreach ($fieldArray as $field => $value)
    {
      if ( !in_array($field, $fields) && $field != 'object')
        $missingFields[] = $indices[$field];
    }

    // Return TRUE - nothing missing.
    // This could happen if table was created by getFields..
    if ( count($missingFields) <= 0 )
      return TRUE;

    $this->getSource($in_source, $source, $makeTable);
    $SQLString = 'ALTER TABLE '.$source.' ADD COLUMN (';
    $indexes = array();
    $PrimaryKeyString = '';

    foreach ( $missingFields as $column )
    {
      if ( $column['name'] == '' || $column['type'] == '' )
        continue;

      $SQLString .= $column['name'].' '.$column['type'];

      if ( isset($column['length']) && is_numeric($column['length']) )
        $SQLString .= ' ('.$column['length'].')';

      if ( (isset($column['primary']) && $column['primary']) ||
           (isset($column['notnull']) && $column['notnull']) )
        $SQLString .= ' NOT NULL';

      if ( isset($column['default']) && is_numeric($column['default']) ) 
        $SQLString .= ' '.$this->keywords['default'].' '.$column['default'];
      elseif ( isset($column['default']) )
        $SQLString .= ' '.$this->keywords['default'].' "'.$column['default'].'"';

      if ( isset($column['identity']) && $column['identity'] ) 
        $SQLString .= ' '.$this->keywords['identity'];

      if ( isset($column['primary']) && $column['primary'] ) 
        $PrimaryKeyString .= $column['name'].', ';
      
      if (isset($column['index']))
        $indexes[] = $column['index'];

      $SQLString .= ', ';
    }
    $SQLString = substr($SQLString, 0, -2);

    if ($PrimaryKeyString != '') 
      $SQLString .= 'PRIMARY KEY ('.substr($PrimaryKeyString, 0, -2).')';

    $SQLString .= ')';

    $result = $this->query($SQLString);
    if ( $result === FALSE )
      return FALSE;

    if ( count($indexes) > 0 )
    {
      foreach ( $indexes as $column )
        $this->createIndex($in_source, $column);
    }

    return TRUE;
  }

  /**
   * Do a SQL CREATE INDEX statement.
   *
   * @access protected
   * @param mixed $in_source Source specified by the caller
   * @param string $column The column to add the index on.
   * @return bool TRUE on success.
   */
  function createIndex($table, $column) 
  {
    $this->getSource($in_source, $table, $makeTable);

    if ($this->query('CREATE INDEX idx'.$table.$column.' ON '.$table.' ('.$column.')'))
      return TRUE;

    return FALSE;
  }
  
}
