<?php
/*
 * Modified page index for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * $Id$
 */


/** CLASS DESCRIPTOR **/
define('SQMINDEX_CLASS_ID',-2548195);

$EXTERNAL_RESOURCES[SQMINDEX_CLASS_ID]['func'] = 'sqmindex';
$EXTERNAL_RESOURCES[SQMINDEX_CLASS_ID]['title'] = 'Site Index';

/**
 * This prints the more generally used site index, which limits what is 
 * shown based on the group, and includes categorized links for document
 * creation closer to other documents of the same type.
 */
function sqmindex(&$foowd, &$result) {
    $foowd->track('sqmindex');

    /**
     * Print site content, leave out users, groups, workspaces
     */
    $where[] = 'AND';
    $where[] = 'classid != '.WORKSPACE_CLASS_ID;
    $where[] = 'classid != '.USER_CLASS_ID;
    $where[] = 'classid != '.TRANSLATION_CLASS_ID;

    $orderby = array('title', 'classid');
    $objects = $foowd->getObjects($where, NULL, $orderby);
    $list_objects = array();

    $i = 0;
    if ( count($objects) > 0 )
    {
      foreach ($objects as $object) 
      {
        if (is_array($object->permissions) && isset($object->permissions['view'])) {
          $methodPermission = $object->permissions['view'];
        } else {
          $methodPermission = getPermission(get_class($object), 'view', 'object');
        }

        if ( !$foowd->user->inGroup($methodPermission, $object->creatorid) )
          continue;
        $list_objects[$i]['url'] = getURI(array('objectid' => $object->objectid,
                                              'classid' => $object->classid));
        $list_objects[$i]['title']  = $object->title;

        if ( $object->workspaceid != 0 )
          $list_objects[$i]['langid'] = foowd_translation::getLink($foowd, $object->workspaceid);
        else 
          $list_objects[$i]['langid'] = '&nbsp;';

        if ( $methodPermission != 'Everyone' )
          $list_objects[$i]['permission'] = $foowd->groups->getDisplayName($foowd, $methodPermission);
        else 
          $list_objects[$i]['permission'] = '&nbsp;';

        if (isset($object->updated)) {
          $list_objects[$i]['updated'] = date(DATETIME_FORMAT, $object->updated);
        } else {
          $list_objects[$i]['updated'] = date(DATETIME_FORMAT, $object->created);
        }
        $list_objects[$i]['desc'] = getClassDescription($object->classid);
        $i++;
      }
    }
    $result['objectList'] =& $list_objects;
    $result['body_template'] = 'smdoc_external.siteindex.php';
    $foowd->track();
}
