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
class foowd_workspace extends foowd_object {

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
   * @param string title The name of the workspace.
   * @param string description A text description of the workspace.
   * @param string viewGroup The user group for viewing the workspace.
   * @param string adminGroup The user group for administrating the workspace.
   * @param string deleteGroup The user group for deleting the workspace.
   * @param string enterGroup The user group for entering the workspace.
   * @param string fillGroup The user group for filling the workspace.
   * @param string emptyGroup The user group for emptying the workspace.
   * @param string exportGroup The user group for exporting the workspace.
   * @param string importGroup The user group for importing the workspace.
   */
  function foowd_workspace(
    &$foowd,
    $title = NULL,
    $description = NULL,
    $viewGroup = NULL,
    $adminGroup = NULL,
    $deleteGroup = NULL,
    $enterGroup = NULL,
    $fillGroup = NULL,
    $emptyGroup = NULL,
    $exportGroup = NULL,
    $importGroup = NULL
  ) 
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
    if ($this->foowd->user->workspaceid == $this->objectid) { // leave workspace
      if ($this->foowd->user->set('workspaceid', 0)) {
        return TRUE;
      } else {
        trigger_error('Could not update user.');
      }
    } else { // enter workspace
      if ($this->foowd->user->set('workspaceid', $this->objectid)) {
        return TRUE;
      } else {
        trigger_error('Could not update user.');
      }
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
    $whereClause = array();
    if (is_array($classTypes)) {
      $classArray = array('OR');
      foreach ($classTypes as $classid) {
        $classArray[] = array('index' => 'classid', 'op' => '=', 'value' => $classid);
      }
      $whereClause[] = $classArray;
    }
    if (checkdate($m1, $d1, $y1)) {
      $whereClause[] = array('index' => 'updated', 'op' => '>', 'value' => date($this->foowd->database->dateTimeFormat, mktime(0, 0, 0, $m1, $d1, $y1)));
    }
    if (checkdate($m2, $d2, $y2)) {
      $whereClause[] = array('index' => 'updated', 'op' => '<', 'value' => date($this->foowd->database->dateTimeFormat, mktime(0, 0, 0, $m2, $d2, $y2)));
    }
    if (count($whereClause) > 0) {
      array_unshift($whereClause, 'AND');
    }

    $objects =& $this->foowd->getObjList(
      $whereClause,
      NULL,
      array('title', 'classid'),
      NULL,
      NULL,
      NULL,
      TRUE
    );

    if ($objects) {
      foreach ($objects as $key => $object) {
        if ((isset($object->permissions['clone']) && !$this->foowd->user->inGroup($object->permissions['clone'])) || ($object->classid == $this->classid && $object->objectid == $this->objectid)) {
          unset($objects[$key]); // drop the reference to objects which we don't have permission to clone
        }
      }
      return $objects;
    } else {
      return NULL;
    }
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
    if (is_array($objects)) {
      foreach ($objects as $object) {
        $items[$object->objectid.'_'.$object->classid.'_'.$object->version] = $object->getTitle().' ('.getClassName($object->classid).' v'.$object->version.')';
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
   * @return int 0 = cloned successfully<br />
   *             1 = moved successfully<br />
   *             2 = nothing selected<br />
   *             3 = display object list form<br />
   */
  function fillWorkspace(&$objects, $selection, $clone = FALSE, $move = FALSE) 
  {
    $error = FALSE;
    if ($clone || $move) {
      if (is_array($selection)) {
        foreach ($objects as $object) {
          if (in_array(intval($object->objectid).'_'.intval($object->classid).'_'.intval($object->version), $selection)) {
            if ($object->set('workspaceid', $this->objectid)) {
              if ($clone) { // adjust original workspace so as to create new object rather than overwrite old one.
                $object->foowd_original_access_vars['objectid'] = $object->objectid;
                $object->foowd_original_access_vars['workspaceid'] = $object->workspaceid;
              }
            } else {
              $error = TRUE;
              trigger_error('Could not clone/move object "', $object->getTitle(), '".');
            }
          }
        }
      } else {
        return 2; // nothing selected
      }
    }
    if ($clone) {
      if (!$error) {
        return 0; // cloned successfully
      } else {
        trigger_error('Not all the objects could be cloned correctly.');
      }
    } elseif ($move) {
      if (!$error) {
        return 1; // moved successfully
      } else {
        trigger_error('Not all the objects could be moved correctly.');
      }
    } else {
      return 3; // display object list form
    }
  }

  /**
   * Get objects that can be removed from the workspace.
   *
   * @return array An array of objects and a selection list.
   */
  function getEmptyObjects() 
  {
    $objects =& $this->foowd->getObjList(
      array(
        array('index' => 'workspaceid', 'op' => '=', 'value' => $this->objectid),
        array('index' => 'classid', 'op' => '!=', 'value' => crc32(strtolower($this->foowd->user_class)))
      ),
      NULL,
      array('title', 'classid'),
      NULL,
      NULL,
      NULL,
      TRUE
    );

    if ($objects) {
      $items = NULL;
      foreach ($objects as $object) {
        if ((isset($object->permissions['clone']) && !$this->foowd->user->inGroup($object->permissions['clone'])) || ($object->classid == $this->classid && $object->objectid == $this->objectid)) {
          unset($objects[$key]); // drop the reference to objects which we don't have permission to clone
        } else {
          $items[$object->objectid.'_'.$object->classid.'_'.$object->version] = $object->getTitle().' ('.getClassName($object->classid).' v'.$object->version.')';
        }
      }
      return array(
        'objects' => &$objects,
        'items' => $items
      );
    } else {
      return NULL;
    }
  }

  /**
   * Empty the workspace of the given objects.
   *
   * @param array objects Array of objects returned from {@link foowd_workspace::getEmptyObjects}.
   * @param array selection Array of selected objects to remove from the workspace.
   * @param bool move Move objects out of the workspace back into the base workspace.
   * @param bool delete Delete the objects.
   * @return int 0 = moved successfully<br />
   *             1 = deleted successfully<br />
   *             2 = nothing selected<br />
   *             3 = display object list form<br />
   */
  function emptyWorkspace(&$objects, $selection, $move = FALSE, $delete = FALSE) 
  {
    $error = FALSE;
    if ($move || $delete) {
      if (is_array($selection)) {
        foreach ($objects as $object) {
          if (in_array(intval($object->objectid).'_'.intval($object->classid).'_'.intval($object->version), $selection)) {
            if ($move) {
              if (!$object->set('workspaceid', 0)) {
                $error = TRUE;
                trigger_error('Could not move object "'.$object->getTitle().'".');
              }
            } elseif ($delete) {
              if (!$object->delete()) {
                $error = TRUE;
                trigger_error('Could not delete object "'.$object->getTitle().'".');
              }
            }
          }
        }
      } else {
        return 2; // nothing selected
      }
    }
    if ($move) {
      if (!$error) {
        return 0; // moved successfully
      } else {
        trigger_error('Not all the objects could be moved correctly.');
      }
    } elseif ($delete) {
      if (!$error) {
        return 1; // deleted successfully
      } else {
        trigger_error('Not all the objects could be deleted.');
      }
    } else {
      return 3; // display object list form
    }
  }

  /**
   * Export objects within workspace as XML.
   *
   * @return mixed The resulting XML string or FALSE if there are no objects in workspace.
   */
  function export() 
  {
    $objects =& $this->foowd->getObjList(array('workspaceid' => $this->objectid), NULL, NULL, NULL, NULL, NULL, TRUE);
    if ($objects) {
      ob_start();
      echo '<?xml version="1.0"?>', "\n";
      echo '<foowd version="', $this->foowd->version, '" generated="', time(), '">', "\n";
      foreach ($objects as $object) {
        echo "\t", '<', get_class($object), '>', "\n";
        $object->vars2XML(get_object_vars($object), $object->__sleep());
        echo "\t", '</', get_class($object), ">\n";
      }
      echo "</foowd>\n";
      $data = ob_get_contents();
      ob_end_clean();
      return $data;
    } else {
      return FALSE;
    }
  }

  /**
   * Import objects into workspace from an XML file.
   *
   * @param object importFile The file input object that uploaded the XML file.
   * @return mixed An error string or TRUE on success.
   */
  function import(&$importFile) 
  {
    if ($importFile->isUploaded()) {
      if ($importFile->file['type'] == 'text/xml' || $importFile->file['type'] == 'application/x-gzip-compressed') {
        if ($importFile->file['type'] == 'text/xml') {
          $filename = $importFile->file['tmp_name'];
        } else {
          $filename = 'compress.zlib://'.$importFile->file['tmp_name'];
        }
        $fp = fopen($filename, 'r');
        if ($fp) {

          //set_time_limit(0);

          $xml = fread($fp, filesize($importFile->file['tmp_name']));

          fclose($fp); // all read

          $this->object = array(); // our fetch array where we stick all our read in values

          $p = xml_parser_create();
          xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, FALSE); // no thanks
          xml_set_element_handler($p, array(&$this, 'importXMLStart'), array(&$this, 'importXMLEnd'));
          xml_set_character_data_handler ($p, array(&$this, 'importXMLChar'));
          if (!xml_parse($p, $xml)) { // do XML parse, if bad XML tell them so
            return xml_error_string(xml_get_error_code($p));
          } else {
            return TRUE;
          }
          xml_parser_free($p);
        } else {
          return 2;
        }
      } else {
        return 1;
      }
    } else {
      return $importFile->getError();
    }
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
    $this->level++; // increase the depth level
    if (substr($name, 0, 1) == ':') $name = substr($name, 1); // remove colon from front of numeric tags
    $this->varName[$this->level] = $name; // remember our tags name
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
    if ($this->level == 1) { // if we're back at level one, then we have an object, lets save it
      $class = $this->varName[$this->level + 1]; // get class name
      if (class_exists($class)) { // make sure class exists within the system
        $title = $this->object['title']; // hopefully we have a title element
        $obj = &new $class($this->foowd, $title); // create new object
        $objectVars = get_object_vars($obj); // get member vars the object has
        if (is_object($obj)) {
          foreach ($objectVars as $field => $default) { // set member vars
            if (isset($this->object[$field])) {
              if (is_array($this->object[$field])) {
                $obj->$field = $this->object[$field];
              } elseif (is_int($this->object[$field])) {
                $obj->$field = (int)$this->object[$field];
              } else {
                $obj->$field = $this->object[$field];
              }
            }
          }
          $obj->__wakeup(); // wakeup object (load object meta data)
          $obj->workspaceid = $this->objectid;
          $obj->foowd_original_access_vars['workspaceid'] = $this->objectid;
          $obj->foowd_changed = TRUE;
          printf(_("Object \"%s\" imported into workspace.<br />"), $obj->getTitle());
          flush();
        } else {
          trigger_error('Can not create object of class "'.$class.'".');
        }
      } else {
        trigger_error('Can not create object of unknown class "'.$class.'".');
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
    if ($this->level > 2) { // if we're in deep enough, we have some attributes to find
      for ($foo = $this->level; $foo > 3; $foo--) { // loop until our data is nested in arrays to it's correct depth
        if ((is_array($data) || trim($data) != '') && isset($this->varName[$foo])) { // make sure the data is ok to deal with
          $data = array($this->varName[$foo] => $data);
        }
      }
      if (isset($this->object[$this->varName[3]])) { // there's already data for this attribute so append
        if (is_array($this->object[$this->varName[3]]) || is_array($data)) {
          if (is_array($data)) {
            $this->object = array_merge_recursive($this->object, array($this->varName[3] => $data));
          }
        } else {
          $this->object[$this->varName[3]] = $this->object[$this->varName[3]].$data;
        }
      } else { // new attribute
        if (is_array($data) || trim($data) != '') { // don't save white space from between tags, it's pointless
          $this->object[$this->varName[3]] = $data;
        }
      }
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
    $createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, _("Title").':');
    $createDescription = new input_textbox('createDescription', '/^.{1,1024}$/', NULL, _("Description").':', NULL, NULL, NULL, FALSE);
    if (!$createForm->submitted() || $createTitle->value == '') {
      $createForm->addObject($createTitle);
      $createForm->addObject($createDescription);
      $foowd->template->assign_by_ref('form', $createForm);
    } else {
      $object = &new $className(
        $foowd,
        $createTitle->value,
        $createDescription->value
      );
      if ($object->objectid != 0) {
        $foowd->template->assign('success', TRUE);
        $foowd->template->assign('objectid', $object->objectid);
        $foowd->template->assign('classid', $object->classid);
      } else {
        $foowd->template->assign('success', FALSE);
      }
    }

    $foowd->track();
  }

/* Object methods */

  /**
   * Output the object.
   */
  function method_view() {
    $this->foowd->track('foowd_workspace->method_view');

    $this->foowd->template->assign('title', $this->getTitle());
    $this->foowd->template->assign('created', date($this->foowd->datetime_format, $this->created));
    $this->foowd->template->assign('author', $this->creatorName);
    $this->foowd->template->assign('access', getPermission(get_class($this), 'enter', 'object'));
    $this->foowd->template->assign('description', htmlspecialchars($this->description));

    if ($this->foowd->user->workspaceid == $this->objectid) {
      $this->foowd->template->assign('enter', FALSE);
    } else {
      $this->foowd->template->assign('enter', TRUE);
    }
    $this->foowd->template->assign('objectid', $this->objectid);
    $this->foowd->template->assign('classid', $this->classid);

    $objects = $this->foowd->getObjList(array('workspaceid' => $this->objectid), NULL, 'title', NULL, NULL, NULL, TRUE);

    if ($objects) {
      foreach ($objects as $object) {
        $this->foowd->template->append('objects', array(
          'title' => $object->getTitle(),
          'objectid' => $object->objectid,
          'classid' => $object->classid,
          'created' => date($this->foowd->datetime_format, $object->created),
          'author' => htmlspecialchars($object->creatorName),
          'class' => getClassDescription($object->classid)
        )); 
      }
    }
    
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
    
    $objects = $this->foowd->getObjList(array('workspaceid' => $this->objectid), NULL, 'title', NULL, NULL, NULL, TRUE);
    if (!$objects) {
      parent::method_delete();
    } else {
      $this->foowd->template->assign('success', FALSE);
    }
    
    $this->foowd->track(); return $return;
  }

  /**
   * Place the user in or take the user out of the workspace and redirect to
   * the view method.
   */
  function method_enter() 
  {
    $this->foowd->track('foowd_workspace->method_enter');
    if ($this->enterWorkspace($this->foowd)) {
      header('Location: ?objectid='.$this->objectid.'&classid='.$this->classid.'&version='.$this->version);
    }
    $this->foowd->track();
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
