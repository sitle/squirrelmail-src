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

/** CLASS DECLARATION **/
class database
{
  var $dbhost,
      $dbuser,
      $dbpass,
      $dbname,
      $dbtable;                   // database connection details

  var $debug;                     // debug object
  var $db_conn;                   // connection established with DB
  var $makeTblCallback;           // function used to make table

  /**
   * Constructor
   */
  function database(&$foowd, $database = NULL, $makeTableFunction = NULL)
  {
    /*
     * Database connection initialization
     */
    $this->dbhost  = getVarOrConst($database['host'], 'DB_HOST');
    $this->dbname  = getVarOrConst($database['name'], 'DB_NAME');
    $this->dbuser  = getVarOrConst($database['user'], 'DB_USER');
    $this->dbpass  = getVarOrConst($database['password'], 'DB_PASS');
    $this->dbtable = getVarOrConst($database['table'], 'DB_TABLE');
    $this->makeTblCallback = $makeTableFunction;

    if ( $foowd->debug )
      $this->debug =& $foowd->debug;
    else
      $this->debug = false;
  }

/*** MYSQL SPECIFIC FUNCTIONS - should be overridden by subclasses ***/
  /**
   * Open database connection
   */
  function open()
  {
    $this->db_conn = mysql_connect($this->dbhost,
                                   $this->dbuser,
                                   $this->dbpass);
    if ($this->db_conn)
    {
      if ( mysql_select_db($this->dbname, $this->db_conn) )
        return TRUE;
      else
        trigger_error('Could not select database "'.htmlspecialchars($this->dbname).'".', E_USER_ERROR);
    }
    else
      trigger_error('Could not connect to host "'.htmlspecialchars($this->dbhost).'".', E_USER_ERROR);
    return FALSE;
  }


  /**
   * Close database connection
   */
  function close() {
    return mysql_close($this->db_conn);
  }

  /**
   * number of rows in returned recordset
   */
  function returnedRows(&$query) {
    return mysql_num_rows($query);
  }

  /**
   * get the next record
   */
  function getRecord($query) {
      return mysql_fetch_assoc($query);
  }

  /**
   * get last identity value created
   */
  function getIdentity() {
      return mysql_insert_id($this->db_conn);
  }

  /**
   * get the field type of a field index
   */
  function getFieldType($query, $index) {
      return mysql_field_type($query, $index);
  }

  /**
   * get number of rows affected by last operation
   */
  function getAffectedRows() {
      return mysql_affected_rows();
  }

  /**
   * execute mysql query
   * Used internally by generic DB methods below..
   */
  function impl_execQuery($SQLString) {
    return mysql_query($SQLString, $this->db_conn);
  }

/*** GENERIC DB METHODS ***/

  /**
   * execute DB query,
   * called by DB functions
   *   $SQLString - string containing SQL string
   *   $table_info - array containing name of table, and callback
   *   $makeTable - false, or name of callback function to create table
   *  return $query results.
   */
  function execQuery($SQLString, $table_info = false, $makeTable = true)
  {
    $query = $this->impl_execQuery($SQLString);

    if ( $query )
      return $query;

    if ( $makeTable )
    {
      if ( $table_info )
      {
        $table = $table_info['name'];
        $callback = $table_info['callback'];
      }
      else
      {
        $table = $this->dbtable;
        $callback = $this->makeTblCallback;
      }

      if ( $table && $callback )
      {
        if ( function_exists($callback) ) 
        {
          $query = $callback($this, $table);
          if ( $query ) // table was created.. try original query again
          {
            $query = $this->impl_execQuery($SQLString);
            if ( $query )
              return $query;
          }
        }
      }
    }
    trigger_error('Database query failed', E_USER_ERROR);
  }


  /**
   * recursively expand array-based WHERE
   */
  function getWhere($conditions)
  {
    $SQL = '(';
    $conjunction = array_shift($conditions);

    if ($conjunction == 'or' || $conjunction == 'OR')
        $conjunction = 'OR';
    elseif ($conjunction == 'and' || $conjunction == 'AND')
      $conjunction = 'AND';
    elseif (count($conditions) == 0)
      return $conjunction;
    else
      $conjunction = 'AND';

    foreach ($conditions as $condition)
    {
        if (is_array($condition))
          $SQL .= $this->getWhere($condition).' '.$conjunction.' ';
        else
          $SQL .= $condition.' '.$conjunction.' ';
    }

    return substr($SQL, 0, -(strlen($conjunction) + 2)).')';
  }

  /**
   * select
   */
  function DBSelect($joins = false,  $fields = false,  $conditions = false,
                    $groups = false, $orders = false, $limit = false,
                    $table_info = false, $makeTable = true)
  {
    $SQLString = '';
    if ( $table_info )
      $table = $table_info['name'];
    else
      $table = $this->dbtable;

    if ($table && $fields)
    {
        $SQLString = 'SELECT';
        foreach ($fields as $field)
            $SQLString .= ' '.$field.',';

        $SQLString = substr($SQLString, 0, -1);
        $SQLString .= ' FROM '.$table;
        if ($joins)
        {
            foreach ($joins as $table => $condition)
            {
                if (is_numeric($table))
                    $SQLString .= ' '.$condition;
                else
                    $SQLString .= ' '.$table.' ON '.$condition;
            }
        }

        if ($conditions)
        {
            $SQLString .= ' WHERE ';
            $SQLString .= $this->getWhere($conditions);
        }

        if ($groups)
        {
            $SQLString .= ' GROUP BY';
            foreach ($groups as $group)
                $SQLString .= ' '.$group.',';
            $SQLString = substr($SQLString, 0, -1);
        }

        if ($orders)
        {
            $SQLString .= ' ORDER BY';
            foreach ($orders as $order)
                $SQLString .= ' '.$order.',';
            $SQLString = substr($SQLString, 0, -1);
        }
        if ($limit)
            $SQLString .= ' LIMIT '.$limit;

        if ($this->debug)
          $this->debug->DBTrack($SQLString);

        $query = $this->execQuery($SQLString, $table_info, $makeTable);
    }
    else
        $query = FALSE;

    if ($query && $this->getAffectedRows() > 0 )
        return $query;

    return FALSE;
  }

  /**
   * insert
   */
  function DBInsert($fields, $table_info = false, $makeTable = true)
  {
    $SQLString = '';
    if ( $table_info )
      $table = $table_info['name'];
    else
      $table = $this->dbtable;

    if ($table && $fields)
    {
      $SQLString = 'INSERT INTO '.$table.'(';

      foreach ($fields as $key => $field)
          $SQLString .= ' '.$key.',';

      $SQLString = substr($SQLString, 0, -1);
      $SQLString .= ') VALUES (';
      foreach ($fields as $field)
      {
          if (is_string($field))
            $SQLString .= ' "'.addslashes($field).'",';
          else
            $SQLString .= ' '.$field.',';

      }
      $SQLString = substr($SQLString, 0, -1);
      $SQLString .= ')';
      if ($this->debug)
          $this->debug->DBTrack($SQLString);

      $query = $this->execQuery($SQLString, $table_info, $makeTable);
      if ($query )
        return TRUE;
    }
    return FALSE;
  }

  /**
   * update
   */
  function DBUpdate($fields, $conditions,
                    $table_info = false, $makeTable = true)
  {
      $SQLString = '';
      if ( $table_info )
        $table = $table_info['name'];
      else
        $table = $this->dbtable;

      if ($table && $fields)
      {
        $SQLString = 'UPDATE '.$table.' SET ';
        foreach ($fields as $key => $field)
        {
            if (is_int($field))
                $SQLString .= ' '.$key.' = '.$field.',';
            elseif (is_string($field))
                $SQLString .= ' '.$key.' = "'.addslashes($field).'",';
        }
        $SQLString = substr($SQLString, 0, -1);
        if ($conditions)
        {
            $SQLString .= ' WHERE ';
            $SQLString .= $this->getWhere($conditions);
        }
        if ($this->debug)
            $this->debug->DBTrack($SQLString);

        $query = $this->execQuery($SQLString, $table_info, $makeTable);
        if ($query && $this->getAffectedRows() > 0)
            return TRUE;
      }
      return FALSE;
  }

  /* delete */
  function DBDelete(&$foowd, $conditions,
                    $table_info = false, $makeTable = true)
  {
    $SQLString = '';
    if ( $table_info )
      $table = $table_info['name'];
    else
      $table = $this->dbtable;

    if ($table)
    {
      $SQLString = 'DELETE FROM '.$table;
      if ($conditions)
      {
        $SQLString .= ' WHERE ';
        $SQLString .= $this->getWhere($conditions);
      }
      if ($this->debug)
        $this->debug->DBTrack($SQLString);

      if ( $this->execQuery($SQLString, false, false) )
        return TRUE;
    }
    return FALSE;
  }

  /**
   * create table
   */
  function DBCreateTable($name, $columns)
  {
    if ( isset($columns) && is_array($columns) )
    {
      $SQLString = 'CREATE TABLE '.$name.' (';
      $PrimaryKeyString = '';
      foreach($columns as $column)
      {
        if ($column['name'] != '' && $column['type'] != '')
        {
          $SQLString .= $column['name'].' '.$column['type'];
          if (isset($column['notnull']) && $column['notnull'] == 'true')
            $SQLString .= ' NOT NULL';

          if ( isset($column['default']) )
          {
            if ( is_numeric($column['default']))
              $SQLString .= ' DEFAULT '.$column['default'];
            elseif ($column['default'] != 'false' && $column['default'] != '')
              $SQLString .= ' DEFAULT "'.$column['default'].'"';
          }

          if (isset($column['identity']) && $column['identity'] == 'true')
              $SQLString .= ' AUTO_INCREMENT';

          if (isset($column['primary']) && $column['primary'])
          {
            $PrimaryKeyString .= $column['name'].', ';
            if (!isset($column['notnull']))
              $SQLString .= ' NOT NULL';
          }

          if (isset($column['index']) && $column['index'] != 'false' && $column['index'] != '')
            $SQLString .= ' INDEX '.$column['index'];

          $SQLString .= ', ';
        }
      }

      if ($PrimaryKeyString != '')
      {
        $PrimaryKeyString = 'PRIMARY KEY ('.substr($PrimaryKeyString, 0, -2).'), ';
        $SQLString .= $PrimaryKeyString;
      }

      $SQLString = substr($SQLString, 0, -2).')';
      if ($this->debug)
        $this->debug->DBTrack($SQLString);

      if ($this->execQuery($SQLString, false, false))
        return TRUE;
    }
    return FALSE;
  }


  /**
   * alter table
   */
  function DBAlterTable($columns, $table_info = false, $makeTable = true)
  {
    if ( $table_info )
      $name = $table_info['name'];
    else
      $name = $this->dbtable;

    if (isset($columns) && is_array($columns))
    {
      $SQLString = 'ALTER TABLE '.$name.' ADD COLUMN (';
      $PrimaryKeyString = '';
      $IndexString = '';
      foreach($columns as $column)
      {
        if ($column['name'] != '' && $column['type'] != '')
        {
          $SQLString .= $column['name'].' '.$column['type'];
          if (isset($column['notnull']) && $column['notnull'] == 'true')
            $SQLString .= ' NOT NULL';

          if ( isset($column['default']) )
          {
            if ( is_numeric($column['default']))
              $SQLString .= ' DEFAULT '.$column['default'];
            elseif ( $column['default'] != 'false' && $column['default'] != '')
              $SQLString .= ' DEFAULT "'.$column['default'].'"';
          }

          if (isset($column['identity']) && $column['identity'] == 'true')
            $SQLString .= ' AUTO_INCREMENT';

          if (isset($column['primary']) && $column['primary'])
          {
            $PrimaryKeyString .= $column['name'].', ';
            if (!isset($column['notnull']))
              $SQLString .= ' NOT NULL';
          }

          if (isset($column['index']) && $column['index'] != 'false' && $column['index'] != '')
            $IndexString .= ', ADD INDEX idx'.$name.$column['index'].' ('.$column['index'].')';

          $SQLString .= ', ';
        }
      }
      if ($PrimaryKeyString != '')
      {
          $PrimaryKeyString = 'PRIMARY KEY ('.substr($PrimaryKeyString, 0, -2).'), ';
          $SQLString .= $PrimaryKeyString;
      }

      $SQLString = substr($SQLString, 0, -2);
      $SQLString .= ')';

      if ($IndexString != '')
          $SQLString .= $IndexString;

      if ($this->debug)
          $this->debug->DBTrack($SQLString);

      if ( $this->execQuery($SQLString, $table_info, false) )
        return TRUE;
    }
    return FALSE;
  }
}
