<?php
/*
 * Modified page index for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * $Id$
 */


/** CLASS DESCRIPTOR **/
define('SQMINDEX_ID',-2548195);

$EXTERNAL_RESOURCES['sqmindex'] = SQMINDEX_ID;
$EXTERNAL_RESOURCES[SQMINDEX_ID]['func'] = 'sqmindex';
$EXTERNAL_RESOURCES[SQMINDEX_ID]['title'] = 'Site Index';

/**
 * Site Index.
 * Selective index of site content, alphabetized by title, 
 * then class, then version.
 *
 * Set array selected object elements into 'objectList' element of template.
 * Also set specify template name in 'body_template' element of template.
 * Sample contents of $t['changelist']:
 * <pre>
 * array (
 *   0 => array ( 
 *          'url' => 'index.php?objectid=8493242&classid=48943242&version=1'
 *          'objectid' => 438904324
 *          'title' => 'A Page'
 *          'classid' => 894302432
 *          'lang_id' => '<a href.... /a>'
 *          'updated' => '2003/11/09 11:48am'
 *          'workspaceid' => 9
 *          'ver' => 12
 *          'desc' => 'HTML Object'
 *        )
 * )
 * </pre>
 *
 * @param object foowd The foowd environment object.
 */
function sqmindex(&$foowd) 
{
  $foowd->track('sqmindex');

  /*
   * Print site content, leave out groups, workspaces, name_lookup
   */
  $where['notshort'] = array('index' => 'classid', 'op' => '!=', 'value' => META_SMDOC_NAME_LOOKUP_CLASS_ID);
  $orderby = array('title', 'classid', 'version');
  $indices = array('DISTINCT objectid','classid','title','workspaceid','updated');
 
  /*
   * standard doc information: additional indices, no special source table
   * where and orderby clauses from above, no limit (all), want only array, not 
   * actual objects, and yes, set the workspaceid appropriately.
   */
  $objects =& $foowd->getObjList($indices, NULL, $where,
                                 $orderby, NULL, 
                                 FALSE, TRUE);
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
        $list_objects[$i]['langid'] = foowd_translation::getLink($foowd, $object['workspaceid']);
      else 
        $list_objects[$i]['langid'] = '&nbsp;';

      $list_objects[$i]['updated'] = date(DATETIME_FORMAT, strtotime($object['updated']));

      $list_objects[$i]['desc'] = getClassDescription($object['classid']);
      $i++;
    }
  }

  $foowd->template->assign_by_ref('objectList', $list_objects);
  $foowd->template->assign('body_template', 'smdoc_external.siteindex.tpl');
  $foowd->track();
}
