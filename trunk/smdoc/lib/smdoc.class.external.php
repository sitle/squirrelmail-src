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
if ( !isset($EXTERNAL_RESOURCES) ) 
  $EXTERNAL_RESOURCES = array();
if ( !isset($EXTERNAL_LOOKUP) ) 
  $EXTERNAL_LOOKUP = array();

if ( @file_exists(PAGE_MAP) )
  include_once(PAGE_MAP);
else
  $INTERNAL_LOOKUP = array();

/** METHOD PERMISSIONS */
setPermission('smdoc_external', 'class',  'create', 'Nobody');
setPermission('smdoc_external', 'object', 'admin',  'Nobody');
setPermission('smdoc_external', 'object', 'revert', 'Nobody');
setPermission('smdoc_external', 'object', 'delete', 'Nobody');
setPermission('smdoc_external', 'object', 'clone',  'Nobody');
setPermission('smdoc_external', 'object', 'permissions', 'Nobody');
setPermission('smdoc_external', 'object', 'history','Nobody');
setPermission('smdoc_external', 'object', 'diff',   'Nobody');

/** CLASS DESCRIPTOR **/
setClassMeta('smdoc_external', 'Externally Defined Objects');
setConst('EXTERNAL_CLASS_ID', META_SMDOC_EXTERNAL_CLASS_ID);
setConst('REGEX_SHORTNAME', '/^[a-z_-]{0,11}$/');

/**
 * Class allowing external tools to be rendered within the
 * SM_doc/FOOWD framework.
 * 
 * Also provides a basic mapping for well-known simple names
 * (faq, privacy..) to objectid/className pairs.
 */
class smdoc_external extends foowd_object 
{
  /**
   * Url constructed from classid and objectid
   * For forwarding.
   */
  var $url;

  /**
   * Constructor
   * Initialize new instance of smdoc_external. 
   */
  function smdoc_external(&$foowd, $objectid = NULL) 
  {
    global $EXTERNAL_RESOURCES;

    $foowd->track('smdoc_external->smdoc_external');  

    $this->foowd =& $foowd;
    $this->objectid = $objectid;
    $this->classid = EXTERNAL_CLASS_ID;
    $this->workspaceid = 0;
    $this->url = getURI(array('classid' => EXTERNAL_CLASS_ID,
                              'objectid' => $objectid), FALSE);
    
    $this->title = htmlspecialchars($EXTERNAL_RESOURCES[$objectid]['title']);
    $this->version = 0;

    $last_modified = time();
    $this->creatorid = 0;
    $this->creatorName = 'System';
    $this->created = $last_modified;
    $this->updatorid = 0;
    $this->updatorName = 'System';
    $this->updated = $last_modified;

    // method permissions
    if ( isset($EXTERNAL_RESOURCES[$objectid]['group']) ) 
      $this->permissions['view'] = $EXTERNAL_RESOURCES[$objectid]['group'];

    $foowd->track();
  }

  /**
   * Map given objectName to objectid/classid pair.
   * 
   * @param string objectName Name of object to locate.
   * @param int objectid ID of located object
   * @param int classid  ID of class for located object
   * @return TRUE if found, false if not.
   */
  function lookupObjectName(&$foowd, $objectName, &$objectid, &$classid)
  {
    $foowd->track('smdoc_external::lookupObjectName', $objectName);

    global $EXTERNAL_RESOURCES;
    global $INTERNAL_LOOKUP;

    $result = FALSE;
    if ( isset($EXTERNAL_RESOURCES[$objectName]) )
    {
      $objectid = $EXTERNAL_RESOURCES[$objectName];
      $classid = EXTERNAL_CLASS_ID;
      $result = TRUE;
    }
    elseif ( isset($INTERNAL_LOOKUP[$objectName]) )
    {
      $objectid = $INTERNAL_LOOKUP[$objectName]['objectid'];
      $classid  = $INTERNAL_LOOKUP[$objectName]['classid'];
      $result = TRUE;
    } 

    $foowd->track();
    return $result;
  }
 
  /**
   * Factory method
   *
   * see if object id is present in list of external resources.
   * If so, create and return new smdoc_external object.
   *
   * @param smdoc Foowd environment object
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
    if ( isset($EXTERNAL_RESOURCES[intval($objectid)]) ) 
      return TRUE;
    return FALSE;
  }

  /**
   * Clean up short name when object is deleted.
   * 
   * @param smdoc      foowd    Foowd environment object
   * @param object     obj      Object to make shortname for
   */
  function deleteShortName(&$foowd, &$obj)
  {
    global $INTERNAL_LOOKUP;
    $oid = intval($obj->objectid);

    if ( isset($INTERNAL_LOOKUP[$oid]) )
    {
      $name = $INTERNAL_LOOKUP[$oid];
      unset($INTERNAL_LOOKUP[$oid]);

      if ( isset($INTERNAL_LOOKUP[$name]) )
      {
        unset($INTERNAL_LOOKUP[$name]);
        unset($INTERNAL_LOOKUP[$oid]);
      }

      smdoc_external::savePageMap();
    }
  }

  /** 
   * Add textbox to admin form to allow creation of shortname
   * for objects
   * @param smdoc      foowd    Foowd environment object
   * @param object     obj      Object to make shortname for
   * @param input_form form     Form to add element to
   */
  function addShortName(&$foowd, &$obj, &$form)
  {
    global $INTERNAL_LOOKUP;
    global $EXTERNAL_RESOURCES;

    include_once(INPUT_DIR.'input.textbox.php');
    $oid = intval($obj->objectid);

    if ( isset($INTERNAL_LOOKUP[$oid]) )
      $name = $INTERNAL_LOOKUP[$oid];
    else 
      $name = '';

    $shortBox = new input_textbox('shortname', REGEX_SHORTNAME, $name, 'Short Name', FALSE);
    if ( $form->submitted() && $shortBox->wasValid && $shortBox->value != $name )
    {
      if ( isset($EXTERNAL_RESOURCES[$shortBox->value]) ||
           isset($INTERNAL_LOOKUP[$shortBox->value]) )
      {
        $this->foowd->template->assign('failure', OBJECT_UPDATE_FAILED);
        $shortBox->wasValid = FALSE;
      }
      else 
      {
        if ( isset($INTERNAL_LOOKUP[$name]) )
        {
          unset($INTERNAL_LOOKUP[$name]);
          unset($INTERNAL_LOOKUP[$oid]);
        }

        if ( !empty($shortBox->value) )
        {
          $INTERNAL_LOOKUP[$shortBox->value]['objectid'] = $oid;
          $INTERNAL_LOOKUP[$shortBox->value]['classid'] = intval($obj->classid);
          $INTERNAL_LOOKUP[$oid] = $shortBox->value;
        }

        if ( smdoc_external::savePageMap() )
          $this->foowd->template->assign('success', OBJECT_UPDATE_OK);
        else
          $this->foowd->template->assign('failure', OBJECT_UPDATE_FAILED);
      }
    }
    
    $form->addObject($shortBox);
  }
  
  /** 
   * Save INTERNAL_LOOKUP array to PAGE_MAP file.
   */
  function savePageMap()
  {
    global $INTERNAL_LOOKUP;
    $str = '$INTERNAL_LOOKUP';

    $file = @fopen(PAGE_MAP.'.tmp', 'w');
    if( $file && @fwrite($file, '<?php '."\n") )
    {
      if ( count($INTERNAL_LOOKUP) <= 0 )
        @fwrite($file, $str . ' = array();' . "\n");
      else
      {
        foreach ( $INTERNAL_LOOKUP as $key => $val )
        {
          if ( !is_numeric($key) )
            $key = '\''.$key.'\'';

          if ( is_array($val) )
          {
            foreach ( $val as $key2 => $val2 )
            {
              if ( !is_numeric($key2) )
                $key2 = '\''.$key2.'\'';
              if ( !is_numeric($val2) )
                $val2 = '\''.$val2.'\'';

              if ( !@fwrite($file, $str 
                     . '['.$key.']['.$key2.'] = '. $val2 . ";\n") )
                return FALSE;
            }
          }
          else
          {
            if ( !is_numeric($val) )
              $val = '\''.$val.'\'';            

            if ( !@fwrite($file, $str
                     . '['.$key.'] = '. $val . ";\n") )
              return FALSE;
          }
        }
      }

      @fwrite($file, '?>'."\n");
      fclose($file);
      if ( @copy(PAGE_MAP.'.tmp', PAGE_MAP) )
        return TRUE;
    }

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
  function set($member, $value = NULL) 
  {
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
  function setArray($array, $regex) 
  {
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
  function save() 
  {
    return FALSE;
  }

  /**
   * Delete the object.
   *
   * @class foowd_object
   * @method protected delete
   * @return bool Always returns false.
   */
  function delete() 
  {
    return FALSE;
  }

  /**
   * Output the object.
   *
   * @class foowd_object
   * @method private method_view
   * @param object foowd The foowd environment object.
   */
  function method_view() 
  {     
    global $EXTERNAL_RESOURCES;
    $this->foowd->track('smdoc_external->method_view');

    $methodName = $EXTERNAL_RESOURCES[$this->objectid]['func'];        
    $result['title'] = $this->title;
        
    if (function_exists($methodName)) 
      $methodName(&$this->foowd, &$result);
    else
      triggerError('Request for unknown method, '. $methodName 
                   . ', on external resource, ' . $this->title
                   . ' (object id = ' . $this->objectid . ')' );
        
    $this->foowd->track();
    return $result;
  }

  function method_history() 
  {
    $_SESSION['error'] = INVALID_METHOD;
    $this->foowd->loc_forward( $this->url );
  }

  function method_admin() 
  {
    $_SESSION['error'] = INVALID_METHOD;
    $this->foowd->loc_forward( $this->url );
  }
    
  function method_revert()
  {
    $_SESSION['error'] = INVALID_METHOD;
    $this->foowd->loc_forward( $this->url );
  }

  function method_delete()
  {
    $_SESSION['error'] = INVALID_METHOD;
    $this->foowd->loc_forward( $this->url );
  }
    
  function method_clone()
  {
    $_SESSION['error'] = INVALID_METHOD;
    $this->foowd->loc_forward( $this->url );
  }

} // end static class
?>
