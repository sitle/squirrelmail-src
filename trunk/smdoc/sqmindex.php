<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * This file is an addition to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Alternate entry point providing a site index.
 *
 * $Id$
 *
 * Selective index of site content, alphabetized by title, 
 * then class, then version.
 *
 * Values set in template:
 *  + objectlist    - below
 *  + body_template - specific filename (will be relative to TEMPLATE PATH)
 *  + method        - empty string
 *  + title         - 'Site Index'
 *
 * Sample contents of $t['changelist']:
 * <pre>
 * array (
 *   0 => array ( 
 *     'url' => 'index.php?objectid=8493242&classid=48943242&version=122'
 *     'objectid' => 438904324
 *     'title' => 'A Page'
 *     'classid' => 894302432
 *     'lang_id' => '<a href.... /a>'
 *     'updated' => '2003/11/09 11:48am'
 *     'workspaceid' => 9
 *     'ver' => 12
 *     'desc' => 'HTML Object'
 *        )
 * )
 * </pre>
 * 
 * @package smdoc
 * @subpackage extern
 */

/** 
 * Initial configuration, start session
 * @see config.default.php
 */
require('config.php');

/** Class to verify $_GET/querystring parameter data */
require_once(INPUT_DIR . 'input.querystring.php');

/* 
 * Initialize smdoc/FOOWD environment
 */
$foowd = new smdoc($foowd_parameters);

/*
 * Check for shorthand objectid using well-known object name:
 * e.g. object=sqmindex, object=privacy, etc.
 */
$fullIndex_q = new input_querystring('p','/^[01]*$/');
if ( $fullIndex_q->wasSet && $fullIndex_q->wasValid && $foowd->user->inGroup('Gods') )
  $fullIndex = $fullIndex_q->value ? TRUE : FALSE;
else
  $fullIndex = FALSE;

/*
 * Print site content, leave out groups, workspaces, name_lookup
 */
if ( $fullIndex )
{
  $where = array();
  $currentWorkspace = FALSE;
  $objid='objectid';
  $orderby = array('title', 'classid', 'version');
}
else
{
  $where['notshort'] = array('index' => 'classid', 'op' => '!=', 'value' => META_SMDOC_NAME_LOOKUP_CLASS_ID);
  $where['notgroup'] = array('index' => 'classid', 'op' => '!=', 'value' => META_SMDOC_GROUP_APPEXT_CLASS_ID);
  $where['notwkspce'] = array('index' => 'classid', 'op' => '!=', 'value' => WORKSPACE_CLASS_ID);
  $where['notlang'] = array('index' => 'classid', 'op' => '!=', 'value' => TRANSLATION_CLASS_ID);
  $currentWorkspace = TRUE;
  $objid = 'DISTINCT objectid';
  $orderby = array('title', 'classid', 'workspaceid DESC', 'version');
}
$indices = array( $objid,'classid','title','workspaceid','updated');
 
/*
 * standard doc information: additional indices, no special source table
 * where and orderby clauses from above, no limit (all), want only array, not 
 * actual objects, and set the workspaceid based on full index or not (above).
 */
$objects =& $foowd->getObjList($indices, NULL, $where,
                               $orderby, NULL, 
                               FALSE, $currentWorkspace);
$list_objects = array();

$i = 0;
if ( count($objects) > 0 )
{
  foreach ($objects as $object) 
  {
    if ( !$foowd->hasPermission(getClassName($object['classid']), 'view', 'object', $object) )
      continue;

    $list_objects[$i] = $object;

    $uri_arr['objectid'] = $object['objectid'];
    $uri_arr['classid']  = $object['classid'];
    $list_objects[$i]['url'] = getURI($uri_arr);

    if ( $object['workspaceid'] != 0 )
    {
      $list_objects[$i]['langid'] = 
          smdoc_translation::getLink($foowd, $object['workspaceid']);
    }
    else 
      $list_objects[$i]['langid'] = '&nbsp;';

    $list_objects[$i]['updated'] = date(DATETIME_FORMAT, strtotime($object['updated']));

    $list_objects[$i]['desc'] = getClassDescription($object['classid']);
    $i++;
  }
}

$foowd->template->assign('title', _("Site Index"));
$foowd->template->assign('method', '');
$foowd->template->assign_by_ref('objectList', $list_objects);
$foowd->template->assign('body_template', 'smdoc_external.siteindex.tpl');

$foowd->template->display();

/*
 * destroy Foowd - triggers cleanup of database object and 
 * display of debug information.
 */
$foowd->__destruct();

