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

/**
 * Base implementation of workspaces.
 * Workspaces allow groups of pages to overlay the default set.
 * 
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package Foowd
 */


/** Method permissions */
setPermission('foowd_workspace', 'class', 'create', 'Gods');
setPermission('foowd_workspace', 'object', 'edit', 'Gods');
setPermission('foowd_workspace', 'object', 'enter', 'Gods');
setPermission('foowd_workspace', 'object', 'fill', 'Gods');
setPermission('foowd_workspace', 'object', 'empty', 'Gods');
setPermission('foowd_workspace', 'object', 'export', 'Gods');
setPermission('foowd_workspace', 'object', 'import', 'Gods');

/** Class descriptor */
setClassMeta('foowd_workspace', 'Workspace');
setConst('WORKSPACE_CLASS_ID', META_FOOWD_WORKSPACE_CLASS_ID);

/** Workspace settings */
setConst('WORKSPACE_EXPORT_COMPRESS', FALSE);

/**
 * The Foowd workspace class.
 *
 * Class for holding information about a workspace and providing methods for
 * placing objects in and removing them from the workspace, and exporting
 * and importing objects from XML files.
 *
 * @author Paul James
 * @package Foowd
 */
class foowd_workspace extends foowd_object 
{
  /**
   * A text description of the workspace.
   * 
   * @var string
   */
  var $description;
  
  var $level, $varName, $object; // temp import vars used by foowd_workspace::import
  
  /**
   * Constructs a new workspace.
   *
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param string $title The name of the workspace.
   * @param string $description A text description of the workspace.
   * @param string $viewGroup The user group for viewing the workspace.
   * @param string $adminGroup The user group for administrating the workspace.
   * @param string $deleteGroup The user group for deleting the workspace.
   * @param string $enterGroup The user group for entering the workspace.
   * @param string $fillGroup The user group for filling the workspace.
   * @param string $emptyGroup The user group for emptying the workspace.
   * @param string $exportGroup The user group for exporting the workspace.
   * @param string $importGroup The user group for importing the workspace.
   */
  function foowd_workspace( &$foowd,
                            $title = NULL,
                            $description = NULL,
                            $viewGroup = NULL,
                            $adminGroup = NULL,
                            $deleteGroup = NULL,
                            $enterGroup = NULL,
                            $fillGroup = NULL,
                            $emptyGroup = NULL,
                            $exportGroup = NULL,
                            $importGroup = NULL  ) 
  {
    $foowd->track('foowd_workspace->constructor');

    // base object constructor
    parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

    /* set object vars */
    $this->description = $description;

    /* set method permissions */
    if ($enterGroup != NULL) $this->permissions['enter'] = $enterGroup;
    if ($fillGroup != NULL) $this->permissions['fill'] = $fillGroup;
    if ($emptyGroup != NULL) $this->permissions['empty'] = $emptyGroup;
    if ($exportGroup != NULL) $this->permissions['export'] = $exportGroup;
    if ($importGroup != NULL) $this->permissions['import'] = $importGroup;

    $foowd->track();
  }

  /**
   * Serliaisation sleep method.
   *
   * Do not include Foowd meta arrays or temp import variables when serialising the object..
   *
   * @return array Array of the names of the member variables to keep when serialising.
   */
  function __sleep() 
  {
    $returnArray = get_object_vars($this);
    unset($returnArray['foowd_vars_meta']);
    unset($returnArray['foowd_indexes']);
    unset($returnArray['foowd_original_access_vars']);
    unset($returnArray['level']);
    unset($returnArray['varName']);
    unset($returnArray['object']);
    unset($returnArray['foowd']);
    return array_keys($returnArray);
  }

  /**
   * Serliaisation wakeup method.
   *
   * Re-create Foowd meta arrays not stored when object was serialized.
   */
  function __wakeup() 
  {
    parent::__wakeup();
    $this->foowd_vars_meta['description'] = '/^.{1,1024}$/';
  }
  
  /**
   * Move the current user into or out of the workspace.
   *
   * @return bool Returns TRUE on success.
   */
  function enterWorkspace() 
  {
    // Leave workspace if we're already in it.
    if ($this->foowd->user->workspaceid == $this->objectid) 
    { 
      if ($this->foowd->user->set('workspaceid', 0))
        return TRUE;
    } 
    else // Otherwise, enter a new workspace 
    { 
      if ($this->foowd->user->set('workspaceid', $this->objectid))
        return TRUE;
    }

    return FALSE;
  }

  /**
   * Get objects to fill workspace with.
   *
   * @param array classTypes Array of class types of objects to select.
   * @param int d1 Day of selection start.
   * @param int m1 Month of selection start.
   * @param int y1 Year of selection start.
   * @param int d2 Day of selection end.
   * @param int m2 Month of selection end.
   * @param int y2 Year of selection end.
   * @return array An array of objects.
   */
  function &getFillObjects($classTypes, $d1, $m1, $y1, $d2, $m2, $y2) 
  {
    // Set up where clause based on parameters
    $whereClause = array();

    // Start with classTypes
    if (is_array($classTypes)) 
    {
      $classArray = array('OR');
      foreach ($classTypes as $classid) 
      {
        if ( $classid != META_SMDOC_NAME_LOOKUP_CLASS_ID &&
             $classid != META_SMDOC_GROUP_APPEXT_CLASS_ID )
          $classArray[] = array('index' => 'classid', 'op' => '=', 'value' => $classid);
      }
      $whereClause[] = $classArray;
    }

    $whereClause['notshort'] = array('index' => 'classid', 'op' => '!=', 'value' => META_SMDOC_NAME_LOOKUP_CLASS_ID);
    $whereClause['notgroup'] = array('index' => 'classid', 'op' => '!=', 'value' => META_SMDOC_GROUP_APPEXT_CLASS_ID);

    // Then go for dates - after m/d/y
    if (checkdate($m1, $d1, $y1)) 
      $whereClause[] = array('index' => 'updated', 'op' => '>', 'value' => date($this->foowd->database->dateTimeFormat, mktime(0, 0, 0, $m1, $d1, $y1)));

    // dates - before m/d/y
    if (checkdate($m2, $d2, $y2))
      $whereClause[] = array('index' => 'updated', 'op' => '<', 'value' => date($this->foowd->database->dateTimeFormat, mktime(0, 0, 0, $m2, $d2, $y2)));

    // If we have conditions specified, also specify AND 
    if (count($whereClause) > 0)
      array_unshift($whereClause, 'AND');

    // Get object list - no order, no limit,
    // get actual objects, and set to user's current workspace.
    $objects =& $this->foowd->getObjList( 
                        array('title', 'classid'),    // indexes to return
                        NULL,                         // source
                        $whereClause,                 // conditions
                        NULL,                         // order
                        NULL,                         // limit
                        TRUE,                         // return actual object
                        TRUE );                       // set to user's current workspace

    // If no objects returned, return null
    if ( empty($objects) )
      return NULL;

    // For objects returned from query, 
    // only give back those we're allowed to clone
    foreach ($objects as $key => $object) 
    {
      // Don't clone ourselves
      if ( $object->classid == $this->classid && $object->objectid == $this->objectid )
        unset($objects[$key]);
      elseif ( !$this->foowd->hasPermission(get_class($object), 'clone', 'object', $object) )
        unset($objects[$key]); 
    }

    return $objects;
  }
  
  /**
   * Get selection items for fill selection box.
   *
   * @param array objects Array of objects returned from {@link foowd_workspace::getFillObjects}.
   * @return array An array of items for a selection box.
   */
  function getFillSelectionItems(&$objects) 
  {
    $items = array();
    if (is_array($objects)) 
    {
      foreach ($objects as $object)
      {
        $key = $object->objectid.'_'.$object->classid;
        if ( isset($object->version) )
          $key .= '_'.$object->version;

        $value = $object->getTitle().' ('.getClassName($object->classid);
        if ( isset($object->version) )
          $value .= ' v'.$object->version;
        $value .= ')';

        $items[$key] = $value;
      }
    }
    return $items;
  }
  
  /**
   * Fill the workspace with the given objects.
   *
   * @param array objects Array of objects returned from {@link foowd_workspace::getFillObjects}.
   * @param array selection Array of selected objects to place in workspace.
   * @param bool clone Clone objects into workspace.
   * @param bool move Move objects into workspace.
   * @return int 0 = successful<br />
   *             1 = could not save changes<br />
   *             2 = nothing selected<br />
   */
  function fillWorkspace(&$objects, $selection, $clone = FALSE, $move = FALSE ) 
  {
    $error = FALSE;
    if ( !is_array($selection) ) 
      return 2; // nothing selected.
 
    if ($clone || $move) 
    {
      foreach ($objects as $object) 
      {
        $key = intval($object->objectid).'_'.intval($object->classid);
        if ( isset($object->version) )
          $key .= '_'.intval($object->version);

        // If object is in selection
        if ( in_array($key, $selection) ) 
        {
          // set new workspace into object
          if ( $object->set('workspaceid', $this->objectid) ) 
          {
            // original_access_vars monitor for changes.
            // If we're cloning, adjust original workspace so
            // we create a new object when we save, instead of just overwriting
            // the original (i.e. move it).
            if ($clone) 
            { 
              $object->foowd_original_access_vars['objectid'] = $object->objectid;
              $object->foowd_original_access_vars['workspaceid'] = $object->workspaceid;
            }          
          }
          else
            $error = TRUE; // error saving changes
        }
      }
    }

    if ( $error )
      return 1; // error saving changes
    
    return 0; // successful
  }

  /**
   * Get objects that can be removed from the workspace.
   *
   * @return array An array of objects and a selection list.
   */
  function getEmptyObjects() 
  {
    // Get objects in this workspace that are NOT system elements, or... 
    $whereClause['workspace'] = array('index' => 'workspaceid', 'op' => '=', 'value' => $this->objectid);
    $whereClause['notshort'] = array('index' => 'classid', 'op' => '!=', 'value' => META_SMDOC_NAME_LOOKUP_CLASS_ID);
    $whereClause['notgroup'] = array('index' => 'classid', 'op' => '!=', 'value' => META_SMDOC_GROUP_APPEXT_CLASS_ID);

    // Get object list - no order, no limit, get actual objects
    $objects =& $this->foowd->getObjList( 
                        array('title', 'classid'),    // indexes to return
                        NULL,                         // source
                        $whereClause,                 // conditions
                        NULL,                         // order
                        NULL,                         // limit
                        TRUE,                         // return actual object
                        FALSE );                      // workspace in where clause

    if ( empty($objects) )
      return NULL;

    // For objects returned from query, 
    // only give back those we're allowed to clean
    foreach ($objects as $key => $object) 
    {
      // Don't empty ourselves
      if ( $object->classid == $this->classid && $object->objectid == $this->objectid )
        unset($objects[$key]);
      elseif ( !$this->foowd->hasPermission(get_class($object), 'clone', 'object', $object) )
        unset($objects[$key]);
    }

    $items = $this->getFillSelectionItems($objects);    

    return array(
      'objects' => &$objects,
      'items' => $items
    );
  }

  /**
   * Empty the workspace of the given objects.
   *
   * @param array objects Array of objects returned from {@link foowd_workspace::getEmptyObjects}.
   * @param array selection Array of selected objects to remove from the workspace.
   * @param bool move Move objects out of the workspace back into the base workspace.
   * @param bool delete Delete the objects.
   * @return int 0 = successful<br />
   *             1 = unsuccessful<br />
   *             2 = nothing selected<br />
   */
  function emptyWorkspace(&$objects, $selection, $move = FALSE, $delete = FALSE) 
  {
    $error = FALSE;
    if ( !is_array($selection) ) 
      return 2; // nothing selected.
 
    if ($move || $delete) 
    {
      foreach ($objects as $object) 
      {
        $key = intval($object->objectid).'_'.intval($object->classid);
        if ( isset($object->version) )
          $key .= '_'.intval($object->version);

        if (in_array($key, $selection)) 
        {
          if ($move) 
          {
            if (!$object->set('workspaceid', 0)) 
              $error = TRUE;
          } 
          elseif ($delete) 
          {
            if (!$object->delete())
              $error = TRUE;
          }
        }
      }
    }

    if ( $error )
      return 1; // unsuccessful

    return 0; //successful
  }

  /**
   * Export objects within workspace as XML.
   *
   * @return mixed The resulting XML string or FALSE if there are no objects in workspace.
   */
  function export() 
  {
    // Get object list - no order, no limit, get actual objects
    $objects =& $this->foowd->getObjList( 
                        array('title', 'classid'),               // indexes to return
                        NULL,                                    // source
                        array('workspaceid' => $this->objectid), // conditions
                        NULL,                                    // order
                        NULL,                                    // limit
                        TRUE,                                    // return actual object
                        FALSE );                                 // workspace in where clause

    if ( empty($objects) )
      return FALSE;

    ob_start();
    
    echo '<?xml version="1.0"?>', "\n";
    echo '<foowd version="', $this->foowd->version, '" generated="', time(), '">', "\n";
    foreach ($objects as $object) 
    {
      echo "\t", '<', get_class($object), '>', "\n";
      $object->vars2XML(get_object_vars($object), $object->__sleep());
      echo "\t", '</', get_class($object), ">\n";
    }
    echo "</foowd>\n";
    
    $data = ob_get_contents();
    ob_end_clean();

    return $data;
  }

  /**
   * Import objects into workspace from an XML file.
   *
   * @param object importFile The file input object that uploaded the XML file.
   * @return mixed An error string or TRUE on success.
   */
  function import(&$importFile) 
  {
    if ( !$importFile->isUploaded() )
      return $importFile->getError();

    if ( $importFile->file['type'] == 'text/xml' || 
         $importFile->file['type'] == 'application/x-gzip-compressed' ) 
    {
      if ($importFile->file['type'] == 'text/xml')
        $filename = $importFile->file['tmp_name'];
      else 
        $filename = 'compress.zlib://'.$importFile->file['tmp_name'];

      $fp = fopen($filename, 'r');
      if ($fp) 
      {
        //set_time_limit(0);
        $xml = fread($fp, filesize($importFile->file['tmp_name']));
        fclose($fp); // all read

        $this->object = array(); // our fetch array where we stick all our read in values

        $p = xml_parser_create();
        xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, FALSE); // no thanks
        xml_set_element_handler($p, array(&$this, 'importXMLStart'), array(&$this, 'importXMLEnd'));
        xml_set_character_data_handler ($p, array(&$this, 'importXMLChar'));

        // do XML parse; if bad XML, get error string.
        if ( xml_parse($p, $xml) )
          $result = TRUE;
        else
          $result = xml_error_string(xml_get_error_code($p));

        xml_parser_free($p);
        return $result;
      }
      return 2; // could not open file
    }
    return 1; // bad file type 
  }
  
  /**
   * Start tag processor for XML parser in {@link foowd_workspace::import}.
   *
   * @param object p The XML parser.
   * @param string name The name of the XML tag.
   * @param array attributes The attributes of the XML tag.
   */
  function importXMLStart($p, $name, $attributes) 
  {
    $this->level++;                         // increase the depth level

    if (substr($name, 0, 1) == ':') 
      $name = substr($name, 1);             // remove colon from front of numeric tags

    $this->varName[$this->level] = $name;   // remember our tags name
  }

  /**
   * End tag processor for XML parser in {@link foowd_workspace::import}.
   *
   * @param object p The XML parser.
   * @param string name The name of the XML tag.
   */
  function importXMLEnd($p, $name) 
  {
    $this->level--; // decrease our depth level
    if ($this->level == 1) 
    { // if we're back at level one, then we have an object, lets save it
      $class = $this->varName[$this->level + 1]; // get class name

      if ( !class_exists($class) )
        trigger_error('Can not create object of unknown class "'.$class.'".');
      else
      {
        $title = $this->object['title']; // hopefully we have a title element
        $obj = &new $class($this->foowd, $title); // create new object
        $objectVars = get_object_vars($obj); // get member vars the object has

        if ( !is_object($obj) )
          trigger_error('Can not create object of class "'.$class.'".');
        else 
        {
          foreach ($objectVars as $field => $default) 
          { // set member vars
            if (isset($this->object[$field])) 
            {
              if (is_array($this->object[$field])) 
                $obj->$field = $this->object[$field];
              elseif (is_int($this->object[$field]))
                $obj->$field = (int)$this->object[$field];
              else
                $obj->$field = $this->object[$field];
            }
          }

          $obj->__wakeup(); // wakeup object (load object meta data)
          $obj->workspaceid = $this->objectid;
          $obj->foowd_original_access_vars['workspaceid'] = $this->objectid;
          $obj->foowd_changed = TRUE;
          printf(_("Object \"%s\" imported into workspace.<br />"), $obj->getTitle());
          flush();
        } 
      } 

      $this->object = array(); // we'll be off for more, so make sure we have a fresh object array to start with
    }
  }

  /**
   * Character processor for XML parser in {@link foowd_workspace::import}.
   *
   * @param object p The XML parser.
   * @param string data The character data.
   */
  function importXMLChar($p, $data) 
  {
    // if we're in deep enough, we have some attributes to find
    if ($this->level > 2) 
    {
      // loop until our data is nested in arrays to it's correct depth
      for ($foo = $this->level; $foo > 3; $foo--) 
      {
        if ( (is_array($data) || trim($data) != '') && isset($this->varName[$foo]) ) 
          $data = array($this->varName[$foo] => $data);
      }

      // If there's already data for this attribute, append
      if (isset($this->object[$this->varName[3]])) 
      {
        if ( is_array($this->object[$this->varName[3]]) || is_array($data) ) 
          $this->object = array_merge_recursive($this->object, array($this->varName[3] => $data));
        else
          $this->object[$this->varName[3]] = $this->object[$this->varName[3]].$data;
      } 
      else if (is_array($data) || trim($data) != '')
        $this->object[$this->varName[3]] = $data;
    }
  }

/* Class methods */

  /**
   * Output an object creation form and process its input.
   *
   * @static
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param string className The name of the class.
   */
  function class_create(&$foowd, $className) 
  {
    $foowd->track('foowd_workspace->class_create');
    
    include_once(INPUT_DIR.'input.querystring.php');
    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.textbox.php');
    
    $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
    $createForm = new input_form('createForm', NULL, SQ_POST, _("Create"), NULL);
    $createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Title');
    $createDescription = new input_textbox('createDescription', '/^.{1,1024}$/', NULL, 'Description', FALSE);

    if ($createForm->submitted() && 
        $createTitle->wasSet && $createTitle->wasValid && $createTitle->value != '') 
    {
     // Ensure unique title
      $oid = NULL;
      if ( !$foowd->database->isTitleUnique($createTitle->value, $foowd->user->workspaceid, $oid, NULL, FALSE) )
        $result = 1; // duplicate title;
      else
      {
        $object = &new $className($foowd, 
                                  $createTitle->value,
                                  $createDescription->value);
        if ( $object->objectid != 0 && $object->save($foowd) ) 
          $result = 0; // created ok
        else
          $result = 2; // error
      }
    } 
    else
      $result = -1;

    switch ( $result )
    {
      case 0:
        $_SESSION['ok'] = OBJECT_CREATE_OK;
        $uri_arr['classid'] = $object->classid;
        $uri_arr['objectid'] = $object->objectid;
        $foowd->loc_forward(getURI($uri_arr, FALSE));
        exit;
      case 1:
        $foowd->template->assign('failure', OBJECT_DUPLICATE_TITLE);
        $createTitle->wasValid = FALSE;
        break;
      case 2:
        $foowd->template->assign('failure', OBJECT_CREATE_FAILED);
        break;
      default:
        $foowd->template->assign('failure', FORM_FILL_FIELDS);
    }
      
    $createForm->addObject($createTitle);
    $createForm->addObject($createDescription);
    $foowd->template->assign_by_ref('form', $createForm);

    $foowd->track();
  }

/* Object methods */

  /**
   * Output the object.
   */
  function method_view() 
  {
    $this->foowd->track('foowd_workspace->method_view');

    $this->foowd->template->assign('author', $this->creatorName);
    $this->foowd->template->assign('created', date(DATETIME_FORMAT, $this->created));
    $this->foowd->template->assign('access', getPermission(get_class($this), 'enter', 'object'));
    $this->foowd->template->assign('description', htmlspecialchars($this->description));

    if ($this->foowd->user->workspaceid == $this->objectid)
      $this->foowd->template->assign('enter', FALSE);
    else
      $this->foowd->template->assign('enter', TRUE);

    // Get objects within this workspace
    // Get object list - no order, no limit, get actual objects
    $whereClause['this'] = array('workspaceid' => $this->objectid);
    $indexes = array('DISTINCT objectid','title','classid','updated');
    $orderby = array('title', 'classid', 'version');
    $objects =& $this->foowd->getObjList( 
                        $indexes,        // indexes to return (default set)
                        NULL,            // source
                        $whereClause,    // conditions
                        $orderby,        // order
                        NULL,            // limit
                        FALSE,           // return actual object
                        FALSE );         // workspace in where clause
    $this->foowd->template->assign_by_ref('objectList', $objects);
    
    $this->foowd->track();
  }
  
  /**
   * Output the object deletion screen and handle its input.
   *
   * We override {@link foowd_object::method_delete} since the workspace must
   * be empty before we can delete it, so we check that here first.
   */
  function method_delete() 
  {
    $this->foowd->track('foowd_workspace->method_delete');

    include_once(INPUT_DIR.'input.form.php');
    $deleteForm = new input_form('deleteForm', NULL, SQ_POST, _("OK"), NULL);

    // If the form has been submitted, try to delete the workspace.
    if ($deleteForm->submitted() ) 
    {
      if ( $this->delete()) 
      {
        $_SESSION['ok'] = OBJECT_DELETE_OK;
        $uri = getURI(NULL, FALSE);
      }  
      else
      {
        $_SESSION['error'] = OBJECT_DELETE_FAILED;
        $uri_arr['objectid'] = $this->objectid;
        $uri_arr['classid'] = $this->classid;
        $uri = getURI($uri_arr, FALSE);
      }

      $this->foowd->loc_forward( $uri );
      exit;
    }
    else 
    {
      // Get objects within this workspace
      // Get object list - no order, no limit, get actual objects
      $whereClause['this'] = array('workspaceid' => $this->objectid);
      $objects =& $this->foowd->getObjList( 
                          NULL,            // indexes to return (default set)
                          NULL,            // source
                          $whereClause,    // conditions
                          array('title', 'classid', 'version'), // order
                          NULL,            // limit
                          FALSE,           // return actual object
                          FALSE );         // workspace in where clause

      if ( empty($objects) )
        $this->foowd->template->assign_by_ref('form', $deleteForm);
      else
        $foowd->template->assign_by_ref('objectList', $objects);
    }
   
    $this->foowd->track();
  }

  /**
   * Place the user in or take the user out of the workspace and redirect to
   * the view method.
   */
  function method_enter() 
  {
    $this->foowd->track('foowd_workspace->method_enter');

    if ($this->enterWorkspace($this->foowd)) 
      $_SESSION['ok'] = WORKSPACE_CHANGE_SUCCEEDED;
    else
      $_SESSION['error'] = WORKSPACE_CHANGE_FAILED;

    $uri_arr['objectid'] = $this->objectid;
    $uri_arr['classid'] = $this->classid;
 
    $this->foowd->track();
    $this->foowd->loc_forward( getURI($uri_arr, FALSE) );
    exit;
  }

  /**
   * Display the fill workspace form and process its input.
   */
  function method_fill() 
  {
    $this->foowd->track('foowd_workspace->method_fill');

    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.dropdown.php');
    include_once(INPUT_DIR.'input.textbox.php');

    $limitForm = new input_form('limitForm', NULL, SQ_POST, _("Limit Selection"), NULL);
    $limitSelect = new input_dropdown('limitSelect', NULL, getFoowdClassNames(), _("Class Types").':', 6, TRUE);
    $beforeDay = new input_textbox('beforeDay', '/^[1-3]?[0-9]$/', NULL, NULL, 2, 2, NULL, FALSE);
    $beforeMonth = new input_textbox('beforeMonth', '/^[0|1]?[0-9]$/', NULL, NULL, 2, 2, NULL, FALSE);
    $beforeYear = new input_textbox('beforeYear', '/^[1|2][0-9]{3}$/', NULL, NULL, 4, 4, NULL, FALSE);
    $afterDay = new input_textbox('afterDay', '/^[1-3]?[0-9]$/', NULL, NULL, 2, 2, NULL, FALSE);
    $afterMonth = new input_textbox('afterMonth', '/^[0|1]?[0-9]$/', NULL, NULL, 2, 2, NULL, FALSE);
    $afterYear = new input_textbox('afterYear', '/^[1|2][0-9]{3}$/', NULL, NULL, 4, 4, NULL, FALSE);

    $objects =& $this->getFillObjects(
      $limitSelect->value,
      $beforeDay->value,
      $beforeMonth->value,
      $beforeYear->value,
      $afterDay->value,
      $afterMonth->value,
      $afterYear->value
    );
    
    $items = $this->getFillSelectionItems($objects);

    $objectForm = new input_form('objectForm', NULL, SQ_POST, _("Clone Objects"), NULL, _("Move Objects"));
    $objectSelect = new input_dropdown('objectSelect', NULL, $items, _("Select Objects").':', 10, TRUE);

    $result = $this->fillWorkspace($objects, $objectSelect->value, $objectForm->submitted(), $objectForm->previewed());
    
    switch($result) {
    case 0: // cloned successfully
    case 1: // moved successfully
      $this->foowd->template->assign('success', TRUE);
      $this->foowd->template->assign('objectid', $this->objectid);
      break;
    case 2: // nothing selected to clone/move
      $this->foowd->template->assign('success', FALSE);
      $this->foowd->template->assign('error', 2);
      break;
    case 3: // display forms
      $this->foowd->template->assign_by_ref('limit_select', $limitSelect);
      $limitForm->addObject($beforeDay);
      $limitForm->addObject($beforeMonth);
      $limitForm->addObject($beforeYear);
      $limitForm->addObject($afterDay);
      $limitForm->addObject($afterMonth);
      $limitForm->addObject($afterYear);
      $this->foowd->template->assign_by_ref('limit_form', $limitForm);
      if ($items == NULL) {
        $this->foowd->template->assign('success', FALSE);
        $this->foowd->template->assign('error', 3);
      } else {
        $objectForm->addObject($objectSelect);
        $this->foowd->template->assign_by_ref('object_form', $objectForm);
      }
      break;
    }
    
    $this->foowd->track();
  }

  /**
   * Display the empty workspace form and process its input.
   */
  function method_empty() 
  {
    $this->foowd->track('foowd_workspace->method_empty');
    
    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.dropdown.php');
    
    $objectForm = new input_form('objectForm', NULL, SQ_POST, _("Move Objects"), NULL, _("Delete Objects"));

    $return =& $this->getEmptyObjects();
    $items =& $return['items'];
    $objects =& $return['objects'];

    $objectSelect = new input_dropdown('objectSelect', NULL, $items, _("Select Objects").':', 10, TRUE);
    
    $result = $this->emptyWorkspace($objects, $objectSelect->value, $objectForm->submitted(), $objectForm->previewed());
    
    switch ($result) {
    case 0: // moved successfully
      $this->foowd->template->assign('success', TRUE);
      $this->foowd->template->assign('code', 0);
      $this->foowd->template->assign('objectid', $this->objectid);
      break;
    case 1: // deleted successfully
      $this->foowd->template->assign('success', TRUE);
      $this->foowd->template->assign('code', 1);
      $this->foowd->template->assign('objectid', $this->objectid);
      break;
    case 2: // nothing selected
      $this->foowd->template->assign('success', FALSE);
      $this->foowd->template->assign('error', 2);
      break;
    case 3: // display object list form
      if ($items) {
        $objectForm->addObject($objectSelect);
        $this->foowd->template->assign_by_ref('form', $objectForm);
      } else {
        $this->foowd->template->assign('success', FALSE);
        $this->foowd->template->assign('error', 3);
      }
      break;
    }
        
    $this->foowd->track();
  }
  
  /**
   * Display the export workspace form and process its input.
   */
  function method_export() 
  {
    $this->foowd->track('foowd_workspace->method_export');
    $data = $this->export();
    if ($data) {
      $this->foowd->debug = FALSE;
      if (WORKSPACE_EXPORT_COMPRESS) {
        header("Content-type: application/x-gzip-compressed");
        header('Content-Disposition: attachment; filename='.$this->getTitle().'.xml.gz');
        echo gzencode($data, 9);
      } else {
        header("Content-type: text/xml");
        header('Content-Disposition: attachment; filename='.$this->getTitle().'.xml');
        echo $data;
      }
    } else {
      trigger_error('There is nothing to export from this workspace');
    }
    $this->foowd->track();
  }

  /**
   * Display the import workspace form and process its input.
   */
  function method_import() 
  {
    $this->foowd->track('foowd_workspace->method_import');

    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.file.php');

    $importForm = new input_form('importForm', NULL, SQ_POST, _("Import"), NULL);
    $importFile = new input_file('importFile', _("Import file").':', NULL, getConstOrDefault('INPUT_FILE_SIZE_MAX', 2097152));
    
    if ($importForm->submitted()) {
      $result = $this->import($importFile);
      if ($result == 0) {
        $this->foowd->template->assign('success', TRUE);
        $this->foowd->template->assign('objectid', $this->objectid);
        $this->foowd->template->assign('classid', $this->classid);
      } else {
        $this->foowd->template->assign('success', FALSE);
        $this->foowd->template->assign('error', $result);
      }
    } else {
      $importForm->addObject($importFile);
      $this->foowd->template->assign_by_ref('form', $importForm);
    }

    $this->foowd->track();
  }

}

?>
