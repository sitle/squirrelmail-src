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
class foowd_translation extends foowd_workspace {

    var $language_icon;

    /**
     * Constructor
     */
    function foowd_translation( &$foowd,
                                $title = NULL,
                                $description = NULL,
                                $viewGroup = NULL,
                                $adminGroup = NULL,
                                $deleteGroup = NULL,
                                $enterGroup = NULL,
                                $icon = NULL )
    {
        $foowd->track('foowd_translation->constructor');

        /* set object vars */
        $this->language_icon = $icon;
        $this->initialize();

        $foowd->track();
    }

/*** STATIC METHODS ***/

    /**
     * STATIC
     * Initializes array containing objectid, icon, and language code
     * for each defined translation
     */
    function initialize(&$foowd, $forceRefresh = FALSE)
    {
      $default_title = getConstOrDefault('TRANSLATION_DEFAULT_LANGUAGE', 'en_US');
      $default_icon = getConstOrDefault('TRANSLATION_DEFAULT_LANGUAGE_ICON', '');

      $session_langs = new input_session('translations', NULL);
      if ( isset($session_langs->value) && !$forceRefresh )
        return;

      $translations = array();
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
      $translations[$trans_obj->objectid] = $the_url;

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

        $translations[$trans_obj->objectid] = $the_url;
        unset($trans_obj);
      }
      $session_langs->set($translations);
    }

    /** STATIC
     * Retrieve url for given object id.
     * if objectid is NULL, return array containing all URLs
     */
    function getLink(&$foowd, $objectid = NULL)
    {
      $session_langs = new input_session('translations', NULL);
      if ( !isset($session_langs->value) ) {
        foowd_translation::initialize($foowd);
        $session_langs->refresh();
      }

      if ($objectid == NULL)
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
