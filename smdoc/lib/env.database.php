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
 * marked by *SQM
 * $Id$
 */

/*
env.database.php
Foowd database base object
*/

/**
 * The Foowd abstract database class.
 *
 * Abstract class for storage abstraction layer, handles storage connection
 * and querying in a non-implementation specific way.
 *
 * @author Paul James
 * @abstract
 * @package Foowd/DB
 */
class foowd_db {

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
   * @var str
   */
  var $dateTimeFormat = 'Y-m-d H:i:s';

  /**
   * An array of references to all objects loaded from this database.
   *
   * @var array
   */
  var $objects; // *SQM

  /**
   * Default table 
   * 
   * @var str
   */
  var $default_source;  // *SQM

  /**
   * Reference to the Foowd object.
   *
   * @var object
   */
  var $foowd;

  /**
   * Constructs a new storage object.
   *
   * @abstract
   * @param object foowd The foowd environment object.
   */
  function foowd_db(&$foowd) {
    trigger_error('This is an abstract class and can not be instanciated.', E_USER_ERROR);
  }

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
  function factory(&$foowd, $type) {
    trigger_error('Function provided by smdoc_db: foowd_db->factory', E_USER_ERROR);
  }

  /**
   * Destructs the storage object.
   */
  function destroy() {
    trigger_error('Function provided by smdoc_db: foowd_db->destroy', E_USER_ERROR);
  }

  /**
   * Execute query
   *
   * @abstract
   * @access protected
   * @param str query The query to execute
   * @return resource The resulting query resource
   */
  function query($query) {
    return FALSE;
  }

  /**
   * Escape a string for use in SQL string
   *
   * @abstract
   * @access protected
   * @param str str String to escape
   * @return str The escaped string
   */
  function escape($str) {
    return FALSE;
  }

  /**
   * Add an object reference to the loaded objects array.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   * @param object object Reference of the object to add
   */
  function addToLoadedReference(&$object) {
    trigger_error('Function provided by smdoc_db: foowd_db->addToLoadedReference', E_USER_ERROR);
  }

  /**
   * Check if an object is referenced in the object reference array.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   */
  function &checkLoadedReference($indexes) {
    trigger_error('Function provided by smdoc_db: foowd_db->checkLoadedReference', E_USER_ERROR);
  }

  /**
   * Create a unique hash to store the object reference under.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   */
  function createHashFromIndexes($indexes) {
    trigger_error('Function removed by smdoc: foowd_db->createHashFromIndexes', E_USER_ERROR);
  }

  /**
   * Build where clause from indexes array.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   * @param str conjuction Operand to use to join the elements of the clause
   * @return str The generated where clause.
   */
  function buildWhere($indexes, $conjunction = 'AND')  // *SQM - much of method changed
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
      if (!is_array($index)) 
      {
        $where .= ' '.$key.' = ';
        $where .= is_numeric($index) ? $index : '\''.$this->escape($index).'\'';
        $where .= ' ';
      } 
      else 
      {
        // dealing with an array:
        // Array (
        //   [0] => OR
        //   [1] => Array (
        //            [index] => classid
        //            [op] => =
        //            [value] => 84324322
        //        )
        //   [2] => Array (
        //            [index] => classid
        //            [op] => =
        //            [value] => 4324324324
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
          if ( !isset($index['value']) ) 
          {
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

  /**
   * Add workspaceid index to index array if one does not exist at the top level.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   */
  function setWorkspace(&$indexes) {
    $found = FALSE;
    if (is_array($indexes)) {
      foreach($indexes as $key => $index) {
        if (is_array($index) && isset($index['index']) && $index['index'] == 'workspaceid') {
          $found = TRUE;
        } elseif ($key == 'workspaceid') {
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
   * Get lastest version of an object.
   *
   * @param array indexes Array of indexes and values to find object by
   * @param array joins Array of sources and indexes of objects to also fetch based upon indexes of first object
   * @param str source The source to fetch the object from
   * @return mixed The retrieved object or an array containing the retrieved object and the joined objects.
   */
  function &getObj($indexes, $joins = NULL, $source = NULL) {
    $this->foowd->track('foowd_db->getObj');

// set workspace
    $this->setWorkspace($indexes);

// check previously loaded object reference
    if ($object = &$this->checkLoadedReference($indexes, $source)) {    // *SQM
      return $object;
    }

// set source
    if (!isset($source)) {
      $source = $this->default_source;  // *SQM
    }

// build joins
    $join = '';
    $fields = '';
    $orderby = '';
    if (is_array($joins)) {
      $foo = 0;
      $table = array();
      foreach ($joins as $table => $index) {
        if (is_numeric($table)) {
          $table[$foo] = $source;
        }
        $join .= ' LEFT JOIN '.$table[$foo].' AS join'.$foo.' ON '.$source.'.'.$index[0].' = join'.$foo.'.'.$index[1];
        $fields .= ', join'.$foo.'.object AS object'.$foo.', join'.$foo.'.classid AS classid'.$foo;
        $orderby .= ', join'.$foo.'.version DESC';
        $foo++;
      }
    }

    if (isset($indexes['version']) && $indexes['version'] == 0) {
      unset($indexes['version']);
    }

// build where
    $where = ' WHERE'.$this->buildWhere($indexes);

// build query
    $select = 'SELECT '.$source.'.object AS object, '.$source.'.classid AS classid'.$fields.' FROM '.$source.$join.$where.' ORDER BY '.$source.'.version DESC'.$orderby.' LIMIT 1';

    if ($query = $this->query($select)) {
      if ($result = $this->fetch($query)) {
        if (is_array($joins)) {
          $return = array();
          $return['object'] = $this->foowd->unserialize($result['object'], $result['classid']);
          $max = count($joins);
          for($foo = 0; $foo < $max; $foo++) {
            if (isset($result['object'.$foo])) {
              $return['join'.$foo] = $this->foowd->unserialize($result['object'.$foo], $result['classid'.$foo]);
              $return['join'.$foo]->foowd_source = $table[$foo];
            } else {
              $return['join'.$foo] = FALSE;
            }
          }
          $this->foowd->track(); return $return;
        } else {
          $object = $this->foowd->unserialize($result['object'], $result['classid']);
          $object->foowd = &$this->foowd; // create Foowd reference
          $object->foowd_source = $source; // set source for object
          $this->addToLoadedReference($object);
          $this->foowd->track(); return $object;
        }
      } else {
        $this->foowd->track(); return FALSE;
      }
    } else {
      $this->foowd->track(); return FALSE;
    }

  }

  /**
   * Get an object.
   *
   * @param array indexes Array of indexes and values to find object by
   * @param str source The source to fetch the object from
   * @return array An array of the retrieved object versions indexed by version number.
   */
  function &getObjHistory($indexes, $source = NULL) {
    $this->foowd->track('foowd_db->getObjHistory');

// set source
    if (!isset($source)) {
      $source = $this->default_source;  // *SQM
    }

// set workspace
    $this->setWorkspace($indexes);

// build where
    $where = ' WHERE'.$this->buildWhere($indexes);

// build select
    $select = 'SELECT '.$source.'.object AS object, '.$source.'.classid AS classid, '.$source.'.version AS version FROM '.$source.$where.' ORDER BY '.$source.'.version';

    if ($query = $this->query($select)) {
      if ($this->num_rows($query) > 0) {
        $return = array();
  $latest = 0;
        while ($record = $this->fetch($query)) {
          $return[$record['version']] = $this->foowd->unserialize($record['object']. $record['classid']);
          $return[$record['version']]->foowd = &$this->foowd; // create Foowd reference
          $return[$record['version']]->foowd_source = $source;
          $this->addToLoadedReference($return[$record['version']]);
          if ($record['version'] > $latest) {
            $latest = $record['version'];
          }
        }
  $return[0] = &$return[$latest]; // set reference on index zero to latest version
        $this->foowd->track(); return $return;
      } else {
        $this->foowd->track(); return FALSE;
      }
    } else {
      $this->foowd->track(); return FALSE;
    }

  }

  /**
   * Get a list of objects.
   *
   * @param array indexes Array of indexes and values to find object by
   * @param str source The source to fetch the object from
   * @param array order The index to sort the list on
   * @param bool reverse Display the list in reverse order
   * @param int offset Offset the list by this many items
   * @param int number The length of the list to return
   * @param bool returnObjects Return the actual objects not just the object meta data
   * @return array An array of object meta data or of objects.
   */
  function &getObjList($indexes, $source = NULL, $order = NULL, $reverse = NULL, $offset = NULL, $number = NULL, $returnObjects = FALSE) {
    trigger_error('Function provided by smdoc: foowd_db->getObjList', E_USER_ERROR);
  }

  /**
   * Save an object.
   *
   * @param object object The object to save
   * @return bool Success or failure.
   */
  function save(&$object) {
    trigger_error('Function provided by smdoc: foowd_db->save', E_USER_ERROR);
  }

  /**
   * Delete an object (and all archive versions).
   *
   * @param object object The object to delete
   * @return bool Success or failure.
   */
  function delete(&$object) {
    $this->foowd->track('foowd_db->delete');

    if (isset($object->foowd_source)) {
      $source = $object->foowd_source['table'];           // *SQM
      $makeTable = $object->foowd_source['table_create']; // *SQM
    } else {
      $source = $this->default_source;  // *SQM
    }

// build delete
    $delete = 'DELETE FROM '.$source
      .' WHERE objectid = '.$object->foowd_original_access_vars['objectid']
      .' AND classid = '.$object->foowd_original_access_vars['classid']
      .' AND workspaceid = '.$object->foowd_original_access_vars['workspaceid'];

    if ($this->query($delete)) {
      $this->foowd->track();
      return TRUE;
    } else {
      $this->foowd->track();
      return FALSE;
    }
  }

  /**
   * Tidy an objects archived versions.
   *
   * @param object object The object to delete
   * @return bool Success or failure.
   */
  function tidy(&$object) {
    $this->foowd->track('foowd_db->tidy');

    if (isset($object->foowd_source)) {
      $source = $object->foowd_source;
    } else {
      $source = $this->default_source;  // *SQM
    }

// build delete
    $delete = 'DELETE FROM '.$source
      .' WHERE objectid = '.$object->foowd_original_access_vars['objectid']
      .' AND classid = '.$object->foowd_original_access_vars['classid']
      .' AND workspaceid = '.$object->foowd_original_access_vars['workspaceid']
      .' AND version < '.($object->foowd_original_access_vars['version'] - $this->foowd->minimum_number_of_archived_versions)
      .' AND updated < \''.date($this->foowd->database->dateTimeFormat, strtotime($this->foowd->destroy_older_than)).'\'';

    if ($this->query($delete)) {
      $this->foowd->track(); return TRUE;
    } else {
      $this->foowd->track(); return FALSE;
    }

  }

  /**
   * Get the fields for this table. If it fails, this method presumes that that
   * is because the table does not exist, so tries to create it.
   *
   * @access protected
   * @return array Array of field names
   */
  function getFields($table) {
    if ($query = $this->query('SELECT * FROM '.$table.' LIMIT 1')) {
      $return = array();
      if ($record = $this->fetch($query)) {
        foreach ($record as $field => $value) {
          if (!is_numeric($field)) {
            $return[] = $field;
          }
        }
        return $return;
      }
    } else { // failed to get current table structure, so maybe it doesn't exist so try and make it
      $this->query('CREATE TABLE '.$table.' (
        \'objectid\' int(11) NOT NULL default \'0\',
        \'version\' int(10) unsigned NOT NULL default \'1\',
        \'classid\' int(11) NOT NULL default \'0\',
        \'workspaceid\' int(11) NOT NULL default \'0\',
        \'object\' longblob,
        \'title\' varchar(255) NOT NULL default \'\',
        \'updated\' datetime NOT NULL default \'0000-00-00 00:00:00\',
        PRIMARY KEY (\'objectid\',\'version\',\'classid\',\'workspaceid\'),
        KEY \'idxtblObjectTitle\'(\'title\'),
        KEY \'idxtblObjectupdated\'(\'updated\'),
        KEY \'idxtblObjectObjectid\'(\'objectid\'),
        KEY \'idxtblObjectClassid\'(\'classid\'),
        KEY \'idxtblObjectVersion\'(\'version\'),
        KEY \'idxtblObjectWorkspaceid\'(\'workspaceid\')
      )');
    }
    return array(
      'objectid' => NULL,
      'version' => NULL,
      'classid' => NULL,
      'workspaceid' => NULL,
      'object' => NULL,
      'title' => NULL,
      'updated' => NULL
    );
  }

  /**
   * Do a SQL ALTER TABLE statement.
   *
   * @access protected
   * @param str table The source table to alter
   * @param array fieldArray An array of column clause elements.
   * @return bool TRUE on success.
   */
  function alterTable($table, $fieldArray) {

    if ($fields = $this->getFields($table)) {
      $missingFields = array();
      foreach ($fieldArray as $field => $value) {
        if (!in_array($field, $fields) && $field != 'object') {
          $missingFields[] = $object->foowd_indexes[$field];
        }
      }

      if (isset($missingFields) && is_array($missingFields)) {
        $SQLString = 'ALTER TABLE '.$table.' ADD COLUMN (';
        $PrimaryKeyString = '';
        $indexes = array();
        foreach($missingFields as $column) {
          if ($column['name'] != '' && $column['type'] != '') {
            $SQLString .= $column['name'].' '.$this->dataTypes[$column['type']];
            if (isset($column['length']) && is_numeric($column['length'])) $SQLString .= '('.$column['length'].')';
            if (isset($column['notnull']) && $column['notnull']) {
              $SQLString .= ' '.$this->keywords['notnull'];
            }
            if (isset($column['default']) && is_numeric($column['default'])) {
              $SQLString .= ' '.$this->keywords['default'].' '.$column['default'];
            } elseif (isset($column['default'])) {
              $SQLString .= ' '.$this->keywords['default'].' "'.$column['default'].'"';
            }
            if (isset($column['identity']) && $column['identity']) {
              $SQLString .= ' '.$this->keywords['identity'];
            }
            if (isset($column['primary']) && $column['primary']) {
              $PrimaryKeyString .= $column['name'].', ';
              if (!isset($column['notnull'])) {
                $SQLString .= ' '.$this->keywords['notnull'];
              }
            }
            if (isset($column['index'])) {
              $indexes[] = $column['index'];
            }
            $SQLString .= ', ';
          }
        }
        if ($PrimaryKeyString != '') {
          $PrimaryKeyString = 'PRIMARY KEY ('.substr($PrimaryKeyString, 0, -2).'), ';
          $SQLString .= $PrimaryKeyString;
        }
        $SQLString = substr($SQLString, 0, -2);
        $SQLString .= ')';
        if ($this->query($SQLString)) {
          if (count($indexes) > 0) { // there are indexes to create
            foreach ($indexes as $column) {
              $this->createIndex($table, $column);
            }
          }
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Do a SQL CREATE INDEX statement.
   *
   * @access protected
   * @param str table The source table to create
   * @param str column The column to add the index on.
   * @return bool TRUE on success.
   */
  function createIndex($table, $column) {
    if ($this->query('CREATE INDEX idx'.$table.$column.' ON '.$table.' ('.$column.')')) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

}

?>
