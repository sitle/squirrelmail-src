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
 * ARRAY filled with external resources
 * Each static resource should supply the following:
 *   func - name of function to call to retrieve content
 *   title - title of external resource
 *   group - Group permitted to view external resource (Everyone by default)
 */
if ( !isset($EXTERNAL_RESOURCES) ) $EXTERNAL_RESOURCES = array();

/** METHOD PERMISSIONS **/
setPermission('smdoc_external', 'class',  'create', 'Nobody');
setPermission('smdoc_external', 'object', 'admin', 'Nobody');
setPermission('smdoc_external', 'object', 'revert', 'Nobody');
setPermission('smdoc_external', 'object', 'delete', 'Nobody');
setPermission('smdoc_external', 'object', 'clone', 'Nobody');
setPermission('smdoc_external', 'object', 'permissions', 'Nobody');
setPermission('smdoc_external', 'object', 'history', 'Nobody');
setPermission('smdoc_external', 'object', 'diff', 'Nobody');

/** CLASS DESCRIPTOR **/
setClassMeta('smdoc_external', 'Externally Defined Objects');

setConst('EXTERNAL_CLASS_ID', META_SMDOC_EXTERNAL_CLASS_ID);

class smdoc_external extends foowd_object {

    var $url;
/*** Constructor ***/

	function smdoc_external(
		&$foowd,
		$objectid = NULL
	) {
		global $EXTERNAL_RESOURCES;
        $foowd->track('smdoc_external->smdoc_external');
		
        $this->__wakeup(); // init meta arrays

        $this->foowd =& $foowd;
		$this->objectid = $objectid;
		$this->classid = EXTERNAL_CLASS_ID;
        $this->workspaceid = 0;
        $this->creatorid = 0;
        $this->creatorName = 'System';
        $this->updatorid = 0;
        $this->updatorName = 'System';
        $this->url = getURI(array('classid' => EXTERNAL_CLASS_ID,
                                  'objectid' => $objectid), FALSE);
		
		$this->title = htmlspecialchars($EXTERNAL_RESOURCES[$objectid]['title']);

        $this->version = 0;

        $last_modified = time();
        $this->created = $last_modified;
        $this->updated = $last_modified;

        // method permissions
        $view_group = NULL;
        if ( isset($EXTERNAL_RESOURCES[$objectid]['group']) ) 
            $view_group = htmlspecialchars($EXTERNAL_RESOURCES[$objectid]['group']);

		if ($view_group != NULL) $this->permissions['view'] = $view_group;

        $foowd->track();
    }


    /**
     * Factory method
     * see if object id is present in list of external resources.
     * If so, create and return new smdoc_external object.
     *
     * @param int objectid Id of object to search for
     * @return new External object or NULL.
     */
    function &factory(&$foowd, $objectid)
    {
      global $EXTERNAL_RESOURCES;
      $ext_obj = NULL;
      if ( smdoc_external::objectExists($objectid) ) 
        $ext_obj = new smdoc_external($foowd, intval($objectid));

      return $ext_obj;
    }

    /**
     * See if object id is present in list of external resources.
     *
     * @param int objectid Id of object to search for
     * @return TRUE if specified id registered as external object,
     *         FALSE otherwise.
     */
     function objectExists($objectid)
    {
      global $EXTERNAL_RESOURCES;
      if ( isset($EXTERNAL_RESOURCES) &&
           is_array($EXTERNAL_RESOURCES) &&
           array_key_exists(intval($objectid), $EXTERNAL_RESOURCES) ) 
        return TRUE;
      return FALSE;
    }

	/**
	 * Set a member variable.
	 *
	 * Checks the new value against the regular expression stored in
	 * foowd_vars_meta to make sure the new value is valid.
	 *
	 * @class foowd_object
	 * @method public set
	 * @param str member The name of the member variable to set.
	 * @param optional mixed value The value to set the member variable to.
	 * @return mixed always returns false
	 */
    function set($member, $value = NULL) {
        return FALSE;
    }

	/**
	 * Set a member variable to a complex array value.
	 *
	 * @class foowd_object
	 * @method protected setArray
	 * @param array array The array value to set the member variable to.
	 * @param str regex The regular expression the values of the array must match for the assignment to be valid.
	 * @return mixed Always returns false.
	 */
	function setArray($array, $regex) {
        return FALSE;
    }

	/**
	 * Save the object.
	 *
	 * @class foowd_object
	 * @method public save
	 * @param optional bool incrementVersion Increment the object version.
	 * @param optional bool doUpdate Update the objects details.
	 * @return mixed Always returns false.
	 */
    function save() {
        return FALSE;
    }

	/**
	 * Delete the object.
	 *
	 * @class foowd_object
	 * @method protected delete
	 * @return bool Always returns false.
	 */
    function delete() {
        return FALSE;
    }

  
	/**
	 * Output the object.
	 *
	 * @class foowd_object
	 * @method private method_view
	 * @param object foowd The foowd environment object.
	 */
    function method_view() {     
        global $EXTERNAL_RESOURCES;
        $this->foowd->track('smdoc_external->method_view');

        $methodName = $EXTERNAL_RESOURCES[$this->objectid]['func'];
        
        $result['title'] = $this->title;
        
        if (function_exists($methodName)) {
            $methodName(&$this->foowd, &$result);
        } else {
            triggerError('Request for unknown method, '. $methodName 
                         . ', on external resource, ' . $this->title
                         . ' (object id = ' . $this->objectid . ')' );
        }
        $this->foowd->track();
        return $result;
    }

    function method_history(&$foowd) {
      header('Location: '. $this->url);
    }

    function method_admin(&$foowd) {
      header('Location: '. $this->url);
    }
    
    function method_revert(&$foowd) {
      header('Location: '. $this->url);
    }

    function method_delete(&$foowd) {
      header('Location: '. $this->url);
    }
    
    function method_clone(&$foowd) {
      header('Location: '. $this->url);
    }

} // end static class
?>
