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

/**
 * SquirrelMail modification to caching of objects by
 * database class (deferred save, cached load, etc).
 * 
 * This cache is for objects fetched during page load..
 * it is not persisted throughout the user's session
 * at this time (concurrency/edit issues.. ).
 */
class smdoc_obj_cache 
{
  /**
   * Array of loaded objects, grouped by source table
   *
   * @var array
   */
  var $objects;

  /**
   * Constructs new cache object
   */
  function smdoc_object_cache()
  {
  }

  /**
   * Clean up object cache
   * 
   * This includes saving all modified objects,
   * and performing any deferred deletes.
   */
  function destroy()
  {
  }

  /**
   * Add an object reference to the loaded objects array.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   * @param object object Reference of the object to add
   */
  function addToLoadedReference(&$object) 
  {
  }


  /**
   * Check if an object is referenced in the object reference array.
   *
   * @access protected
   * @param array indexes Array of indexes and values to find object by
   * @param str source The source to fetch the object from
   */
  function &checkLoadedReference($indexes, $source) 
  {
    return FALSE;
  }

}
