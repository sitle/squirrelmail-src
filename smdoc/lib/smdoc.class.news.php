<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Implementation of simple class used to store 
 * data in the DB in the correct format.
 * Permissions to common methods are denied (Nobody).
 *
 * $Id$
 * @package smdoc
 */

/** METHOD PERMISSIONS */
setPermission('smdoc_news', 'class',  'create', 'News');
setPermission('smdoc_news', 'class',  'list',   'Everybody');

setPermission('smdoc_news', 'object', 'history','Nobody');
setPermission('smdoc_news', 'object', 'diff',   'Nobody');
setPermission('smdoc_news', 'object', 'revert', 'Nobody');

setPermission('smdoc_news', 'object', 'delete', 'News');
setPermission('smdoc_news', 'object', 'edit',   'News');

/** Class Descriptor/Meta information */
setClassMeta('smdoc_news', 'News');
setConst('NEWS_CLASS_ID', META_SMDOC_NEWS_CLASS_ID);
setConst('NEWS_CLASS_NAME', 'smdoc_news');


/**
 * Array identifying news source
 * @global array $NEWS_SOURCE
 */
global $NEWS_SOURCE;
$NEWS_SOURCE = array('table' => 'smdoc_news',
                     'table_create' => array(getClassname(NEWS_CLASS_ID),'makeTable'));


/** 
 * Global containing default news categories 
 * @global array $NEWS_CATEGORIES
 */
global $NEWS_CATEGORIES;
$NEWS_CATEGORIES = array('None','Development','Stable','Plugins');

/**
 * News items/blurbs
 *
 * This class defines a HTML/Textile text area and 
 * methods to view and edit that area.
 *
 * @package smdoc
 * @subpackage text
 * @author Erin Schnabel
 */
class smdoc_news extends smdoc_text_textile
{
  /**
   * Make a Foowd database table.
   *
   * When a database query fails due to a non-existant database table, this
   * method is envoked to create the missing table and execute the SQL
   * statement again.
   *
   * @global array Specifies table information for user persistance.
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param string SQLString The original SQL string that failed to execute due to missing database table.
   * @return mixed The resulting database query resource or FALSE on failure.
   */
  function makeTable(&$foowd)
  {
    global $NEWS_SOURCE;
    $foowd->track('smdoc_news->makeTable');
    $sql = 'CREATE TABLE `'.$NEWS_SOURCE['table'].'` (
              `objectid` int(11) NOT NULL default \'0\',
              `updated` datetime NOT NULL default \'0000-00-00 00:00:00\',
              `title` varchar(32) NOT NULL default \'\',
              `summary` varchar(255) NOT NULL default \'\',
              `creatorid` int(11) NOT NULL default \'0\',
              `creatorName` varchar(32) NOT NULL default \'\',
              `objectid` int(11) NOT NULL default \'0\',
              `object` longblob,
              PRIMARY KEY  (`objectid`),
              KEY `idxnews_updated` (`updated`)
            );';
    $result = $foowd->database->query($sql);
    $foowd->track();
    return $result;
  }

  /**
   * Fetch the News
   *
   * @global array Specifies table information for news persistance.
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param mixed newsArray Array containing news information (objectid).
   * @return retrieved news or FALSE on failure.
   */
  function &fetchNews(&$foowd, $newsArray = NULL)
  {
    global $NEWS_SOURCE;
    $foowd->track('smdoc_news::fetchNews', $newsArray);
    if ( isset($newsArray['objectid']) )
      $where['objectid'] = $newsArray['objectid'];
    else
      return FALSE;
    $news =& $foowd->getObj($where, $NEWS_SOURCE, NULL, FALSE, FALSE);

    $foowd->track();
    return $news;
  }

  /** 
   * Attribution of News Category (Dev/Stable/Plugins/etc.)
   * @var array
   */
  var $categories;

  /**
   * News summary - this shows up in the news index, 
   * with a link to the extended body, if present.
   * @var summary
   */
  var $summary;

  /**
   * Constructs a new plain text object.
   *
   * @global array Specifies table information for news persistance.
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param string title The objects title.
   * @param string body The text content body.
   * @param string viewGroup The user group for viewing the object.
   * @param string adminGroup The user group for administrating the object.
   * @param string deleteGroup The user group for deleting the object.
   * @param string editGroup The user group for editing the object.
   * @param array categories Array containing categories for news item.
   */
  function smdoc_news( &$foowd,
                       $title = NULL,
                       $summary = NULL,
                       $body = NULL,
                       $categories = NULL)
  {
    global $NEWS_SOURCE;
    $foowd->track('smdoc_news->constructor');
  
    $this->foowd =& $foowd;

    // Don't use workspace id when looking for getting objectid, 
    // in this case, we don't care about the title, but we do want a unique objectid.
    $this->isTitleUnique($title, FALSE, $objectid, $NEWS_SOURCE, TRUE);

    // init meta arrays
    $this->__wakeup();

    // Initialize variables
    $this->title = $title;
    $this->objectid = $objectid;
    $this->workspaceid = 0;
    $this->classid = NEWS_CLASS_ID;

    $this->creatorid   = $foowd->user->objectid;
    $this->creatorName = $foowd->user->title;
    $this->created     = time();
    $this->updatorid   = $this->creatorid;
    $this->updatorName = $this->creatorName;
    $this->updated     = $this->created;
 
    // set object vars
    $this->body = $body;
    $this->summary = $summary;
    $this->categories = $categories;

    // set original access vars
    $this->foowd_original_access_vars['title'] = $this->title;
    $this->foowd_original_access_vars['objectid'] = $this->objectid;
    $this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;

    // add to loaded object reference list
    $foowd->database->addToLoadedReference($this, $NEWS_SOURCE);

    // object created successfuly, queue for saving
    $this->foowd_changed = TRUE;

    $foowd->track();
  }

  /**
   * Serialisation wakeup method.
   * @global array Specifies table information for user persistance.
   */
  function __wakeup() 
  {
    parent::__wakeup();

    global $NEWS_SOURCE;
    $this->foowd_source = $NEWS_SOURCE;

    // add some regex verification
    unset($this->foowd_vars_meta['version']);

    // re-arrange our indices
    unset($this->foowd_indexes['version']);
    unset($this->foowd_indexes['classid']);
    unset($this->foowd_indexes['workspaceid']);
    $this->foowd_indexes['summary'] = array('name' => 'summary', 'type' => 'VARCHAR', 'length' => 255, 'notnull' => TRUE, 'default' => '');
    $this->foowd_indexes['creatorid'] = array('name' => 'creatorid', 'type' => 'INT', 'notnull' => TRUE, 'default' => '');
    $this->foowd_indexes['creatorName'] = array('name' => 'creatorName', 'type' => 'VARCHAR', 'length' => 32, 'notnull' => TRUE, 'default' => ''); 

    // Original access vars
    unset($this->foowd_original_access_vars['version']);
    $this->foowd_original_access_vars['classid'] = NEWS_CLASS_ID;
    $this->foowd_original_access_vars['title'] = $this->title;

    // Default primary key
    $this->foowd_primary_key = array('objectid');
  }


  /** 
   * Create dropdown for News Category
   * Check result, and update news item.
   * 
   * @global array Specifies default list of categories
   * @param object form Form to add category dropdown box to.
   */
  function addCategories(&$form)
  {
    global $NEWS_CATEGORIES;
    include_once(INPUT_DIR.'input.dropdown.php');

    $cats = empty($this->categories) ? 'None' : $this->categories;
    $editCategory = new input_dropdown('editCategory', $cats, $NEWS_CATEGORIES, 'Category', TRUE);
    $form->addObject($editCategory);

    if ($form->submitted()) 
    {
      $sel_cats = $editCategory->value;
      $new_cats = array();

      // As long as None was not selected, peruse array
      if ( !in_array('None', $sel_cats) )
      {
        foreach ( $NEWS_CATEGORIES as $news )
        {
          if ( in_array($news, $sel_cats) )
            $new_cats = $news;
        }
      }
      $this->set('categories', $new_cats);
    }
  }

// ----------------------------- class methods --------------

  /**
   * Output an edit form and process its input
   * @global array Specifies default list of categories
   */
  function class_create(&$foowd, $className) 
  {
    global $NEWS_CATEGORIES;
    $foowd->track('smdoc_news->class_create');

    include_once(INPUT_DIR.'input.querystring.php');
    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.textbox.php');
    include_once(INPUT_DIR.'input.textarea.php');
    include_once(INPUT_DIR.'input.dropdown.php');

    $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
    $createForm = new input_form('createForm', NULL, SQ_POST, _("Create"), NULL);
    $createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Title', TRUE);
    $createBody = new input_textarea('createBody', NULL, '', 'Extended', 2048);
    $createSummary = new input_textarea('createSummary', NULL, '', 'Summary', 255);
    $category = new input_dropdown('createCategory', NULL, $NEWS_CATEGORIES, 'Category', TRUE);

    $result = 0;
    if ( $createForm->submitted() &&
         $createTitle->wasSet && $createTitle->wasValid && 
         $createSummary->wasSet && $createSummary->wasValid &&
         $createTitle->value != '' ) 
    {
      $object = &new $className($foowd, 
                                $createTitle->value,
                                $createSummary->value,
                                $createBody->value,
                                $category->value);

      if ( $object->objectid != 0 && $object->save($foowd) ) 
        $result = 1; // created ok
      else
        $result = 2; // error
    }

    switch ( $result )
    {
      case 1:
        $_SESSION['ok'] = OBJECT_CREATE_OK;
        $uri_arr['classid'] = $object->classid;
        $uri_arr['objectid'] = $object->objectid;
        $foowd->loc_forward(getURI($uri_arr, FALSE));
        exit;
      case 2:
        $foowd->template->assign('failure', OBJECT_CREATE_FAILED);
        break;
      default:
        $foowd->template->assign('failure', FORM_FILL_FIELDS);
    }

    $createForm->addObject($createTitle);
    $createForm->addObject($createSummary);
    $createForm->addObject($createBody);
    $createForm->addObject($category);
    $foowd->template->assign_by_ref('form', $createForm);
    $foowd->track();
  }

  /**
   * List of news items, showing date, title, and summary, with URL.
   *
   * Values set in template:
   *  + newslist      - array of news objects
   *  + body_template - specific filename (will be relative to TEMPLATE PATH)
   *  + method        - empty string
   *  + title         - 'Site News'
   * 
   * @static
   * @global array Specifies table information for user persistance.
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param string className The name of the class.
   */
  function class_list(&$foowd, $className)
  {    
    $foowd->track('smdoc_news->class_list');

    global $NEWS_SOURCE;

    /*
     * standard news information: specified indices, news source table,
     * specific orderby clause, however many, don't want objects, 
     * and ignore workspace.
     */
    $indices = array('objectid','title','summary','updated','creatorid','creatorName');
    $orderby = array('updated DESC');
    $objects =& $foowd->getObjList($indices, $NEWS_SOURCE, NULL,
                                   $orderby, NULL, 
                                   FALSE, FALSE);

    
    $foowd->template->assign('newslist', $objects);
    $foowd->track();
  }

// -----------------------------object methods --------------

  /**
   * Output an edit form and process its input
   */
  function method_edit() 
  {
    $this->foowd->track('smdoc_news->method_edit');

    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.textbox.php');
    include_once(INPUT_DIR.'input.textarea.php');

    $editForm = new input_form('editForm', NULL, 'POST', 
                               FORM_DEFAULT_SUBMIT, NULL, FORM_DEFAULT_PREVIEW);

    $editCollision = new input_hiddenbox('editCollision', REGEX_DATETIME, time());
    $editForm->addObject($editCollision);

    $editArea = new input_textarea('editArea', NULL, $this->body, NULL);
    $editForm->addObject($editArea);

    $this->addCategories($editForm);

    $this->foowd->template->assign_by_ref('form', $editForm);

    if ($editForm->submitted()) 
    {
      // No versioning for news items.
      $result = $this->edit($editArea->value, FALSE, $editCollision->value);

      switch ($result) 
      {
        case 1:
          $_SESSION['ok'] = OBJECT_UPDATE_OK;
          $url['classid'] = $this->classid;
          $url['objectid'] = $this->objectid;
          $this->save();
          $this->foowd->loc_forward(getURI($url));
          break;
        case 2:
          $this->foowd->template->assign('failure', OBJECT_UPDATE_COLLISION);
          break;
        default:
          $this->foowd->template->assign('failure', OBJECT_UPDATE_FAILED);
          break;
      }
    } 
    elseif ( $editForm->previewed() ) 
      $this->foowd->template->assign('preview', $this->processContent($editArea->value));

    $this->foowd->track();
  }

// ----------------------------- disabled methods --------------

  /**
   * Create a new version of this object. Set the objects version number to the
   * next available version number and queue the object for saving. This will
   * have the effect of creating a new object entry since the objects version
   * number has changed.
   */
  function newVersion() 
  {
    trigger_error('newVersion not supported for smdoc_news', E_USER_ERROR);    
  }

  /**
   * Clean up the archive versions of the object.
   *
   * @param smdoc $foowd Reference to the foowd environment object.
   * @return bool Returns TRUE on success.
   */
  function tidyArchive() 
  {
    trigger_error('tidyArchive does not apply to smdoc_news' , E_USER_ERROR);
  }

  /**
   * Clone the object.
   *
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param string title The title of the new object clone.
   * @param string workspace The workspace to place the object clone in.
   * @return bool Returns TRUE on success.
   */
  function clone($title, $workspace) 
  {
    trigger_error('Can not clone news items.' , E_USER_ERROR);
  }

  /**
   * Convert variable list to XML.
   *
   * @param array vars The variables to convert.
   * @param array goodVars List of variables to convert.
   */
  function vars2XML($vars, $goodVars) 
  {
    trigger_error('vars2XML does not apply to smdoc_news' , E_USER_ERROR);
  }
}

