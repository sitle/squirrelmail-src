<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Implementation of cache for objects retrieved from DB.
 * 
 * $Id$
 * 
 * @package smdoc
 * @subpackage db
 */

/**
 * SquirrelMail modification to caching of objects by
 * database class (deferred save, cached load, etc).
 * 
 * This cache is for objects fetched during page load..
 * it is not persisted throughout the user's session
 * at this time (concurrency/edit issues.. ).
 * 
 * @package smdoc
 * @subpackage db
 */
class smdoc_object_cache 
{
  /**
   * Array of loaded objects, grouped by source table
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
   * Reference to database object
   *
   * @var smdoc_db
   */
  var $db;

  /**
   * Constructs new cache object
   */
  function smdoc_object_cache(&$foowd, &$db)
  {
    $objects = array();
    $this->foowd =& $foowd;
    $this->db =& $db;
  }

  /**
   * Clean up object cache
   * 
   * This includes saving all modified objects,
   * and performing any deferred deletes.
   */
  function __destruct()
  {
    $this->foowd->track('foowd_db->destructor');

    if ( !empty($this->objects) )
    {
      foreach ($this->objects as $source)
      {
        if ( empty($source) )
          continue;

        foreach ( $source as $object )
        {
          if ( $object->objectid == 0 )
            continue;

          if ( isset($object->foowd_changed) && 
               $object->foowd_changed !== FALSE )
          {
            $this->foowd->debug('msg', 'Saving object '.$object->objectid);
            $result = $object->save();
            if ( $result === FALSE )
              $this->foowd->debug('msg','Could not save object '.$object->objectid);
          }
          else
            $this->foowd->debug('msg', 'Object '.$object->title.'['.$object->objectid.'] not changed');
        }    
      }
    }

    $this->foowd->track();
  }

  /**
   * Add an object reference to the loaded objects array.
   *
   * @access protected
   * 
   * @param object object Reference of the object to add
   */
  function addToLoadedReference(&$object) 
  {
    $this->db->getSource($object->foowd_source, $source, $tmp);

    $hash = '';
    asort($object->foowd_primary_key);
    foreach ( $object->foowd_primary_key as $key )
    {
      if ( $hash != '' )
        $hash .= '_';
      $hash .= $object->foowd_original_access_vars[$key];
    }
    $this->foowd->debug('msg', 'ADD Hash: ' . $hash);
    $this->objects[$source][$hash] =& $object;
  }

  /**
   * Check if an object is referenced in the object reference array.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   * @param string source The source to fetch the object from
   */
  function &checkLoadedReference($indexes, $source) 
  {
    $this->db->getSource($source, $source, $tmp);
    $hash = '';
    ksort($indexes);    
    foreach ( $indexes as $key => $value )
    {
      if ( $hash != '' )
        $hash .= '_';
      $hash .= $value;
    }

    $this->foowd->debug('msg', 'CHECK Hash: ' . $hash);

    if ( isset($this->objects[$source][$hash]) )
    {
      $this->foowd->debug('msg', 'CHECK Using exising loaded reference');
      return $this->objects[$source][$hash];
    }
    
    return FALSE;
  }

}
