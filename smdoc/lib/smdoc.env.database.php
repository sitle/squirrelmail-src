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

include_once(SM_PATH.'env.database.php');
include_once(SM_PATH.'db.mysql.php');

class smdoc_db_mysql extends foowd_db_mysql 
{
  /**
   * Name of function used to create table when 
   * table does not already exist.
   *
   * @var str
   */
  var $makeTableFunction;

  /**
   * Constructs a new database object.
   *
   * @param object foowd The foowd environment object.
   */
  function smdoc_db_mysql(&$foowd) 
  {
    parent::foowd_db_mysql($foowd);
    $this->makeTableFunction = NULL;
  }

  /**
   * Add workspaceid index to index array if one does not exist at the top level.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   */
  function setWorkspace(&$indexes) 
  {
//show($indexes);
    $found = FALSE;
    if (is_array($indexes)) 
    {
      foreach($indexes as $index) 
      {
        if (is_array($index) && isset($index['index']) && $index['index'] == 'workspaceid') 
        {
          $found = TRUE;
        }
      }
    }
    if (!$found) {
      $workspaceid['index'] = 'workspaceid';
      $workspaceid['op'] = '=';
      if (isset($this->foowd->user->workspaceid)) {
        $workspaceid['value'] = $this->foowd->user->workspaceid;
      } else {
        $workspaceid['value'] = 0;
      }
      $indexes[] = $workspaceid;
    }
  }

  /**
   * Set database table.
   *
   * Switch the table used for data storage and retrieval in this environment
   * object.
   *
   * @param mixed table Array containing old table name and create function.
   * @return mixed Array containing old table name and create function.
   */
  function setTable($table) {
        $oldTable['name'] = $this->table;
        $oldTable['function'] = $this->makeTableFunction;
        $this->table = $table['name'];
        $this->makeTableFunction = $table['function'];
    return $oldTable;
  }

  /**
   * Make a Foowd database table.
   *
   * When a database query fails due to a non-existant database table, this
   * method is envoked to create the missing table and execute the SQL
   * statement again.
   *
   * @param object foowd The foowd environment object.
   * @param str SQLString The original SQL string that failed to execute due to missing database table.
   * @return mixed The resulting database query resource or FALSE on failure.
   */
  function makeTable(&$foowd, $SQLString) {
        if ( isset($this->makeTableFunction) )
            return call_user_func($this->makeTableFunction, $foowd, $SQLString);
       
        return parent::makeTable($foowd, $SQLString);
    }

  /**
   * Get a list of objects.
   *
   * @param array indexes Array of indexes and values to find object by
   * @param str source The source to fetch the object from
   * @param array order The index to sort the list on
   * @param bool reverse Display the list in reverse order
   * @param int number The length of the list to return
   * @param bool returnObjects Return the actual objects not just the object meta data
   * @param bool setWorkspace get specific workspace id (or any workspace ok)
   * @return array An array of object meta data or of objects.
   */   
  function &getObjList($indexes = NULL, $source = NULL, 
                       $order = NULL, $number = NULL, 
                       $returnObjects = FALSE, $setWorkspace = TRUE) {
    $this->foowd->track('foowd_db->getObjList');

// set source
    if ( $source == NULL ) {
      $source = $this->foowd->config_settings['database']['db_table'];
    }

// set workspace
    if ( $setWorkspace )
      $this->setWorkspace($indexes);

// build where
    $where = '';
    if ( $indexes != NULL )
      $where = ' WHERE'.$this->buildWhere($indexes);
    
// build order
    if (isset($order)) {
      if (is_array($order)) {
        $order = ' ORDER BY '.join(', ', $order);
      } else {
        $order = ' ORDER BY '.$order;
      }
    } else {
      $order = '';
    }

// build limit
    if (isset($number)) {
      $limit = ' LIMIT ';
      if (isset($offset)) {
        $limit .= $offset.', ';
      }
      $limit .= $number;
    } else {
      $limit = '';
    }

    $select = 'SELECT '.$source.'.objectid AS objectid, '
                       .$source.'.classid AS classid, '
                       .$source.'.version AS version, '
                       .$source.'.workspaceid AS workspaceid, '
                       .$source.'.title AS title, '
                       .$source.'.object AS object FROM '.$source.$where.$order.$limit;

    if ($query = $this->query($select)) {
      if ($this->num_rows($query) > 0) {
        $return = array();
        while ($record = $this->fetch($query)) {
          if (!isset($return[$record['objectid']]) || $record['version'] > $return[$record['objectid']]['version']) {
            if ($returnObjects) {
              $return[$record['objectid']] = $this->foowd->unserialize($record['object']. $record['classid']);
              $return[$record['objectid']]->foowd = &$this->foowd; // create Foowd reference
              $return[$record['objectid']]->source = $source;
              $this->addToLoadedReference($return[$record['objectid']]);
            } else {
              $return[$record['objectid']] = array(
                'objectid' => $record['objectid'],
                'classid' => $record['classid'],
                'version' => $record['version'],
                'workspaceid' => $record['workspaceid'],
                'title' => $record['title']
              );
            }
          }
        }
        $this->foowd->track(); 
        return $return;
      } else {
        $this->foowd->track(); 
        return FALSE;
      }
    } else {
      $this->foowd->track(); 
      return FALSE;
    }

  }

  /**
   * Build where clause from indexes array.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   * @param str conjuction Operand to use to join the elements of the clause
   * @return str The generated where clause.
   */
  function buildWhere($indexes, $conjunction = 'AND') {
    if ( isset($index['raw_where']) )
        return $index['raw_where'];

    $where = '';
 
    if ( $conjunction != 'AND' )
      $conjunction = ' OR';

    foreach ($indexes as $key => $index) {
      if ( !isset($index) )  
        continue;

      $where .= $conjunction;
       
      // standard 'classid' => ERROR_CLASS_ID
      if (!is_array($index)) {
        $where .= ' '.$key.' = ';
        $where .= is_numeric($index) ? $index : '\''.$index.'\'';
        $where .= ' ';        
      } else {
        // dealing with an array as the $index
        if ( !isset($index['index']) ) {
          $where .= $this->buildWhere($index, $key);
        } else {
          if ( !isset($index['value']) ) {
            trigger_error('No value given for index "'.$index['index'].'".');
            $index['value'] = '';
          }

          if ( !isset($index['op']) )
            $index['op'] = '=';
          elseif ( $index['op'] == '!=' )
            $index['op'] = '<>';

          if ( !isset($index['value']) ) {
            trigger_error('No value given for index "'.$index['index'].'".');
            $value = '';
          } else {
            $value = $index['value'];
          }

          $where .= ' '.$index['index'].' '.$index['op'].' ';
          $where .= is_numeric($value) ? $value : '\''.$value.'\'';
          $where .= ' ';        
        }
      }
    }    

    return ' ('.substr($where, 3).') ';
  }

}
