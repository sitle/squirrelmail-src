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
  var $objects;

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
    if ($object = &$this->checkLoadedReference($indexes, $source)) {
      return $object;
    }

// set source
    if (!isset($source)) {
      $source = $this->foowd->config_settings['database']['db_table'];
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
      $source = $this->foowd->config_settings['database']['db_table'];
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
    $this->foowd->track('foowd_db->getObjList');

// set source
    if (!isset($source)) {
      $source = $this->foowd->config_settings['database']['db_table'];
    }

// set workspace
    $this->setWorkspace($indexes);

// build where
    $where = ' WHERE'.$this->buildWhere($indexes);

// build order
    if (isset($order)) {
      if (is_array($order)) {
        $order = ' ORDER BY '.join(', ', $order);
      } else {
        $order = ' ORDER BY '.$order;
      }
      if (isset($reverse) && !$reverse) {
        $order .= ' DESC';
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

    $select = 'SELECT '.$source.'.objectid AS objectid, '.$source.'.classid AS classid, '.$source.'.version AS version, '.$source.'.workspaceid AS workspaceid, '.$source.'.title AS title, '.$source.'.object AS object FROM '.$source.$where.$order.$limit;

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
        $this->foowd->track(); return $return;
      } else {
        $this->foowd->track(); return FALSE;
      }
    } else {
      $this->foowd->track(); return FALSE;
    }

  }

  /**
   * Save an object.
   *
   * @param object object The object to save
   * @return bool Success or failure.
   */
  function save(&$object) {
    $this->foowd->track('foowd_db->save');

    if (!isset($object->foowd_update) || $object->foowd_update) {
      $object->update(); // update object meta data
    }

    $serializedObj = serialize($object);

    if (isset($object->foowd_source)) {
      $source = $object->foowd_source;
    } else {
      $source = $this->foowd->config_settings['database']['db_table'];
    }

// build field array from object indexes
    $fieldArray['object'] = $serializedObj;
    foreach ($object->foowd_indexes as $index => $definition) {
      if (isset($object->$index)) {
        if ($object->$index == FALSE) {
          $fieldArray[$index] = 0;
        } else {
          $fieldArray[$index] = $object->$index;
        }
      }
    }

// build where
    $where = ' WHERE objectid = '.$object->foowd_original_access_vars['objectid']
      .' AND version = '.$object->foowd_original_access_vars['version']
      .' AND classid = '.$object->foowd_original_access_vars['classid']
      .' AND workspaceid = '.$object->foowd_original_access_vars['workspaceid'];

// build update query
    $update = 'UPDATE '.$source.' SET ';
    $foo = FALSE;
    foreach($fieldArray as $field => $value) {
      if ($foo) {
        $update .= ', ';
      }
      if (isset($object->foowd_indexes[$field]['type']) && $object->foowd_indexes[$field]['type'] == 'INT') {
        $update .= $field.' = '.$value;
      } elseif (isset($object->foowd_indexes[$field]['type']) && $object->foowd_indexes[$field]['type'] == 'DATETIME') {
        $update .= $field.' = \''.date($this->dateTimeFormat, $value).'\'';
      } else {
        $update .= $field.' = \''.$this->escape($value).'\'';
      }
      $foo = TRUE;
    }
    $update .= $where;

// build insert query
    $insert = 'INSERT INTO '.$source.' (';
    $values = '';
    $foo = FALSE;
    foreach($fieldArray as $field => $value) {
      if ($foo) {
        $insert .= ', ';
        $values .= ', ';
      }
      $insert .= $field;
      if (isset($object->foowd_indexes[$field]['type']) && $object->foowd_indexes[$field]['type'] == 'INT') {
        $values .= $value;
      } elseif (isset($object->foowd_indexes[$field]['type']) && $object->foowd_indexes[$field]['type'] == 'DATETIME') {
        $values .= '\''.date($this->dateTimeFormat, $value).'\'';
      } else {
        $values .= '\''.$this->escape($value).'\'';
      }
      $foo = TRUE;
    }
    $insert .= ') VALUES ('.$values.')';

    $saveResult = 0;
// try to update existing record
    $result = $this->query($update);
    if ($this->query_success($result)) {
      $saveResult = 1;
    } else {
// if fail, write new record
      $result = $this->query($insert);
      if ($this->query_success($result)) {
        $saveResult = 2;
      } else {
// if fail, modify table to include indexes from class definition
        if ($this->alterTable($source, $fieldArray)) {
          $result = $this->query($update);
          if ($this->query_success($result)) {
            $saveResult = 3;
          } else {
            $result = $this->query($insert);
            if ($this->query_success($result)) {
              $saveResult = 4;
            }
          }
        }
      }
    }

// tidy old archived versions
    if ($saveResult && $object->updated < time() - $this->foowd->tidy_delay) {
      $this->tidy($object);
    }

    $this->foowd->track(); return $saveResult;
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
      $source = $object->foowd_source;
    } else {
      $source = $this->foowd->config_settings['database']['db_table'];
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
      $source = $this->foowd->config_settings['database']['db_table'];
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
