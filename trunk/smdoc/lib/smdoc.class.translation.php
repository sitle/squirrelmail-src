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

/** METHOD PERMISSIONS **/
setPermission('foowd_translation', 'class', 'create', 'Translator');
setPermission('foowd_translation', 'object', 'enter', 'Everyone');
setPermission('foowd_translation', 'object', 'fill', 'Translator');
setPermission('foowd_translation', 'object', 'empty', 'Translator');
setPermission('foowd_translation', 'object', 'export', 'Gods');
setPermission('foowd_translation', 'object', 'import', 'Gods');

/** CLASS DESCRIPTOR **/
setClassMeta('foowd_translation', 'Site Translation');

setConst('WORKSPACE_CLASS_ID', META_FOOWD_TRANSLATION_CLASS_ID);
setConst('TRANSLATION_CLASS_ID', META_FOOWD_TRANSLATION_CLASS_ID);

setConst('TRANSLATION_DEFAULT_LANGUAGE', 'en_US');
//setConst('TRANSLATION_DEFAULT_LANGUAGE_ICON', 'en_US');

/** CLASS DECLARATION **/
class smdoc_translation extends foowd_workspace {

    var $language_icon;

  /**
   * Constructor
   */
  function smdoc_translation( &$foowd,
                              $title = NULL,
                              $description = NULL,
                              $viewGroup = NULL,
                              $adminGroup = NULL,
                              $deleteGroup = NULL,
                              $enterGroup = NULL,
                              $icon = NULL )
  {
    $foowd->track('smdoc_translation->constructor');

    /* set object vars */
    $this->language_icon = $icon;
    $this->initialize();

    $foowd->track();
  }

/*** STATIC METHODS ***/

  /**
   * Initializes array containing objectid, icon, and language code
   * for each defined translation
   *
   * @param object foowd The foowd environment object.
   * @param boolean forceRefresh Force refresh of session cache.
   */
  function initialize(&$foowd, $forceRefresh = FALSE)
  {
    $foowd->track('smdoc_translation::initialize', $forceRefresh);

    $default_title = getConstOrDefault('TRANSLATION_DEFAULT_LANGUAGE', 'en_US');
    $default_icon = getConstOrDefault('TRANSLATION_DEFAULT_LANGUAGE_ICON', '');

    $session_links = new input_session('lang_links', NULL);
    $session_langs = new input_session('languages', NULL);

    if ( isset($session_links->value) && 
         isset($session_langs->value) && !$forceRefresh )
    {
      $foowd->track();
      return;
    }

    $links = array();
    $languages = array();

    $url = getURI(array()). '?class=foowd_translation&method=enter&langid=';

    $the_url = '<a href="' . $url . '0">';
    if ( $default_icon != '' )
    {
      $the_url .= '<img src="' . $default_icon . '" ';
      $the_url .= 'alt="' . $default_title . '" border="0" />';
    }
    else
      $the_url .= $default_title;
    $the_url .=  '</a>';

    $links[0] = $the_url;
    $languages[0] = $default_title;

    $t_objects = $foowd->retrieveObjects(
                         array('classid = '.TRANSLATION_CLASS_ID),
                         NULL,
                         array('title'));

    while ($trans_obj = $foowd->retrieveObject($t_objects))
    {
      $the_url = '<a href="' .$url. $trans_obj->objectid. '">';
      if ( isset($trans_obj->language_icon) )
      {
        $the_url .= '<img src="' . $trans_obj->language_icon . '" ';
        $the_url .= 'alt="' . $trans_obj->title . '" border="0" />';
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

  /** 
   * Retrieve url for given object id.
   * if objectid is NULL, return array containing all URLs
   * 
   * @param object foowd The foowd environment object.
   * @param optional int objectid Specific translation to retrieve.
   * @return URL for specified translation, or array of all translations.
   */
  function getLink(&$foowd, $objectid = FALSE)
  {
    $session_links = new input_session('lang_links', NULL);
    if ( !isset($session_links->value) ) {
      foowd_translation::initialize($foowd);
      $session_links->refresh();
    }

    if ($objectid === FALSE)
      return $session_links->value;

    if ( isset($session_links->value[$objectid]) )
      return $session_links->value[$objectid];
    else
      return NULL;
  }

  /** 
   * Retrieve given language.
   * if objectid is NULL, return array containing all languages
   * 
   * @param object foowd The foowd environment object.
   * @param optional int objectid Specific translation to retrieve.
   * @return specified language string, or array of all languages.
   */
  function getLanguage(&$foowd, $objectid = FALSE)
  {
    $session_langs = new input_session('languages', NULL);
    if ( !isset($session_langs->value) ) 
    {
      foowd_translation::initialize($foowd);
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

    /**
     * enter - class method
     * change to selected translation
     */
    function class_enter(&$foowd) {
        $foowd->track('foowd_workspace->class_enter');
        $translation_id = new input_querystring('langid');

        $foowd->user->workspaceid = $translation_id;

        if ( $foowd->user->save($foowd, FALSE) )
        {
          header('Location: '.getURI(array('objectid' => $translation_id,
                                           'classid' => TRANSLATION_CLASS_ID)));
        } else {
          trigger_error('Could not update user with selected translation.');
        }

        $foowd->track();
    }

/*** METHODS ***/

}
