<?php
/*
 * Modified page index for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * $Id$
 */

define('SQMCHANGES_ID',-275885230);

$EXTERNAL_RESOURCES['sqmchanges'] = SQMCHANGES_ID;
$EXTERNAL_RESOURCES[SQMCHANGES_ID]['func'] = 'sqmchanges';
$EXTERNAL_RESOURCES[SQMCHANGES_ID]['title'] = 'Recent Changes';

/**
 * Lists 20 objects (not users) most recently edited.
 * Could be workspace, HTML, plain, whatever.
 *
 * Set array selected object elements into 'changelist' element of template.
 * Also set specify template name in 'body_template' element of template.
 * 
 * Sample contents of $t['changelist']:
 * <pre>
 * array (
 *   0 => array ( 
 *          'url' => 'index.php?objectid=8493242&classid=48943242&version=1'
 *          'title' => 'A Page'
 *          'lang_id' => '<a href.... /a>'
 *          'updated' => '2003/11/09 11:48am'
 *          'updated_by' => 'Joe Schmoe'
 *          'ver' => 12
 *          'desc' => 'HTML Object'
 *        )
 * )
 * </pre>
 * @param object foowd The foowd environment object.
 */
function sqmchanges(&$foowd) 
{
  $foowd->track('sqmchanges');

  /*
   * get 20 most recent changes
   * No special indices, use default source, no special where clause,
   * order by updated descending, limit to 20 rows,
   * return the full objects, and don't restrict to certain workspace
   */
  $where['notshort'] = array('index' => 'classid', 'op' => '!=', 'value' => META_SMDOC_NAME_LOOKUP_CLASS_ID);
  $where['notgroup'] = array('index' => 'classid', 'op' => '!=', 'value' => META_SMDOC_APP_GROUPS_CLASS_ID);
 
  $objects =& $foowd->getObjList(NULL, NULL, $where,
                                 array('updated DESC'), 20, 
                                 TRUE, FALSE );
  $list_objects = array();
  $i = 0;
  foreach ($objects as $object) 
  {
    if ( isset($object->permissions['view']) &&
         !$foowd->user->inGroup($object->permissions['view'], $object->creatorid) )
     continue;

    $uri_arr['objectid'] = $object->objectid;
    $uri_arr['classid']  = $object->classid;
    $uri_arr['version']  = $object->version;    
    $list_objects[$i]['url'] = getURI($uri_arr);
    $list_objects[$i]['title']  = $object->title;
      
    if ( $object->workspaceid != 0 )
      $list_objects[$i]['langid'] = foowd_translation::getLink($foowd, $object->workspaceid);
    else 
      $list_objects[$i]['langid'] = '&nbsp;';

    if (isset($object->updatorName)) 
    {
      $list_objects[$i]['updated'] = date(DATETIME_FORMAT, $object->updated);
      $list_objects[$i]['updated_by'] = $object->updatorName;
    } 
    else 
    {
      $list_objects[$i]['updated'] = date(DATETIME_FORMAT, $object->created);
      $list_objects[$i]['updated_by'] = $object->creatorName;
    }
    
    $list_objects[$i]['ver'] = $object->version;
    $list_objects[$i]['desc'] = getClassDescription($object->classid);
    $i++;
  }

  $foowd->template->assign_by_ref('changeList', $list_objects);
  $foowd->template->assign('body_template', 'smdoc_external.changes.tpl');
  $foowd->track();
}
?>
