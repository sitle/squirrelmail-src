<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Implementation of Site Translations based on Workspaces
 *
 * $Id$
 * @see foowd_workspace
 * @package smdoc
 */

/** METHOD PERMISSIONS **/
setPermission('smdoc_translation', 'class', 'create', 'Translator');
setPermission('smdoc_translation', 'class', 'enter', 'Everybody');
setPermission('smdoc_translation', 'object', 'fill', 'Translator');
setPermission('smdoc_translation', 'object', 'empty', 'Translator');
setPermission('smdoc_translation', 'object', 'export', 'Gods');
setPermission('smdoc_translation', 'object', 'import', 'Gods');

/** CLASS DESCRIPTOR **/
setClassMeta('smdoc_translation', 'Site Translation');

setConst('TRANSLATION_CLASS_ID', META_SMDOC_TRANSLATION_CLASS_ID);
setConst('TRANSLATION_DEFAULT_LANGUAGE', 'en_US');

/** Base workspace implementation */
require_once(SM_DIR . 'class.workspace.php');

/**
 * Extension of workspaces to allow all-in-one-place
 * management of site and translations.
 *
 * @package smdoc
 * @subpackage translation
 */
class smdoc_translation extends foowd_workspace 
{
  /**
   * filename of applicable language flag.. 
   * @var string
   */
  var $language_icon;

  /**
   * Constructor
   * @param smdoc $foowd Reference to the foowd environment object.
   */
  function smdoc_translation( &$foowd,
                              $title = NULL,
                              $description = NULL,
                              $icon = NULL )
  {
    $foowd->track('smdoc_translation->constructor');

    parent::foowd_workspace($foowd, $title, $description);

    /* set object vars */
    $this->language_icon = $icon;
    smdoc_translation::initialize($foowd);

    $foowd->track();
  }

  /**
   * Initializes array containing objectid, icon, and language code
   * for each defined translation
   * @static
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param bool $forceRefresh Force refresh of session cache.
   */
  function initialize($foowd, $forceRefresh = FALSE)
  {
    $foowd->track('smdoc_translation::initialize', $forceRefresh);

    $session_links = new input_session('lang_links', NULL);
    $session_langs = new input_session('languages', NULL);

    if ( isset($session_links->value) && 
         isset($session_langs->value) && !$forceRefresh )
    {
      $foowd->track();
      return;
    }

    $default_title = getConstOrDefault('TRANSLATION_DEFAULT_LANGUAGE', 'en_US');
    $default_icon = getConstOrDefault('TRANSLATION_DEFAULT_LANGUAGE_ICON', '');

    $links = array();
    $languages = array();

    $url_arr['class'] = 'smdoc_translation';
    $url_arr['method'] = 'enter';
    $url_arr['langid'] = '';
    $url = getURI($url_arr);

    // Add elements for the default translation
    $the_url = '<a href="' . $url . '0">';
    if ( !empty($default_icon) )
    {
      $the_url .= '<img src="' . $default_icon . '" ';
      $the_url .= 'alt="' . $default_title . '" ';
      $the_url .= 'title="' . $default_title . '" ';
      $the_rul .= ' />';
    }
    else
      $the_url .= $default_title;

    $the_url .=  '</a>';

    $links[0] = $the_url;
    $languages[0] = $default_title;


    // Fetch available translations 
    // no limit, retrieve objects, and don't bother with workspaces.
    $index[] = 'object';
    $where['classid'] = TRANSLATION_CLASS_ID;
    $order = 'title';
    $t_objects =& $foowd->getObjList($index, NULL, $where,
                                     $order, NULL, TRUE, FALSE);

    // Add each translation to the list
    foreach ( $t_objects as $trans_obj )
    {
      $the_url = '<a href="' .$url. $trans_obj->objectid. '">';

      if ( isset($trans_obj->language_icon) )
      {
        $the_url .= '<img src="' . $trans_obj->language_icon . '" ';
        $the_url .= 'title="' . $trans_obj->title . '" ';
        $the_url .= 'alt="' . $trans_obj->title . '" />';
      }
      else
        $the_url .= $trans_obj->title;

      $the_url .=  '</a>';

      $links[$trans_obj->objectid] = $the_url;
      $languages[$trans_obj->objectid] = $trans_obj->title;
      unset($trans_obj);
    }

    $session_links->set($links);
    $session_langs->set($languages);

    $foowd->track();
  }

/*** static methods **/

  /** 
   * Retrieve url for given object id.
   * if objectid is NULL, return array containing all URLs
   *
   * @static
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param int $objectid Specific translation to retrieve.
   * @return URL for specified translation, or array of all translations.
   */
  function getLink(&$foowd, $objectid = FALSE)
  {
    $session_links = new input_session('lang_links', NULL);
    $workspaces = $foowd->getWorkspaceList();
    if ( !isset($session_links->value) ) 
    {
      smdoc_translation::initialize($foowd);
      $session_links->refresh();
    }

    if ($objectid === FALSE)
      return $session_links->value;

    if ( isset($session_links->value[$objectid]) )
      return $session_links->value[$objectid];
    elseif ( $workspaces != NULL && isset($workspaces[$objectid]) )
    {
      $url_arr['class']  = 'foowd_workspace';
      $url_arr['method'] = 'enter';
      $url_arr['langid'] = $objectid;
      $the_url = '<a href="' .getURI($url_arr). '">';
      $the_url .= htmlentities($workspaces[$objectid]) . '</a>';
      return $the_url;
    }
    else
      return NULL;
  }

  /** 
   * Retrieve given language.
   * if objectid is NULL, return array containing all languages
   *
   * @static 
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param int $objectid Specific translation to retrieve.
   * @return specified language string, or array of all languages.
   */
  function getLanguage(&$foowd, $objectid = FALSE)
  {
    $session_langs = new input_session('languages', NULL);
    if ( !isset($session_langs->value) ) 
    {
      smdoc_translation::initialize($foowd);
      $session_langs->refresh();
    }

    if ($objectid === FALSE)
      return $session_langs->value;

    if ( isset($session_langs->value[$objectid]) )
      return $session_langs->value[$objectid];
    else
      return NULL;
  }

/*** CLASS METHODS ***/

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
    $createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Object Title');
    $createDescription = new input_textbox('createDescription', '/^.{1,1024}$/', NULL, 'Description', FALSE);
    $createIcon = new input_textbox('createIcon');

    if ($createForm->submitted() && 
        $createTitle->wasSet && $createTitle->wasValid && $createTitle->value != '') 
    {
      // Ensure unique title
      $oid = NULL;
      if ( !$foowd->database->isTitleUnique($createTitle->value, $foowd->user->workspaceid, $oid, NULL, FALSE) )
        $result = 1;
      else
      {
        $object = &new $className($foowd, 
                                  $createTitle->value,
                                  $createDescription->value,
                                  $createIcon->value);

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


  /**
   * enter - class method
   * change to selected translation
   * @static
   * @param smdoc $foowd Reference to the foowd environment object. 
   */
  function class_enter(&$foowd) 
  {
    $translation_id = new input_querystring('langid');

    $foowd->track('foowd_workspace->class_enter', $translation_id->value);

    $uri_arr = array();
    if ( $translation_id->wasSet &&
         $translation_id->wasValid &&
         smdoc_translation::enterWorkspace($foowd, $translation_id->value) )
    { 
      if ( $translation_id->value == 0 )
        $_SESSION['ok'] = USER_DEFAULT_TRANSLATION;
      else
        $_SESSION['ok'] = USER_NEW_TRANSLATION;

      $uri_arr['objectid'] = $translation_id->value;
      $uri_arr['classid'] = TRANSLATION_CLASS_ID;
    }
    else
      $_SESSION['error'] = WORKSPACE_CHANGE_FAILED;

    $foowd->track();
//    $foowd->loc_forward( getURI($uri_arr, FALSE) );
//    exit;
  }

}
