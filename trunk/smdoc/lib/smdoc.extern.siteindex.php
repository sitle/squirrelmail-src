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

    /*
     * Print site content, leave out groups, workspaces
     */
    $orderby = array('title', 'classid', 'version');
    $indices = array('DISTINCT objectid','classid','version','title','workspaceid','updated');
 
    /*
     * standard doc information: additional indices, no special source table
     * where and orderby clauses from above, no limit (all), want only array, not 
     * actual objects, and yes, set the workspaceid appropriately.
     */
    $objects =& $foowd->getObjList($indices, NULL, NULL,
                                   $orderby, NULL, 
                                   FALSE, TRUE);
show($objects);

    $list_objects = array();

    $i = 0;
    if ( count($objects) > 0 )
    {
      foreach ($objects as $object) 
      {
        if ( !$foowd->hasPermission(getClassName($object['classid']), 'view', 'object', $object) )
          continue;

        $list_objects[$i] = $object;

        $list_objects[$i]['url'] = getURI(array('objectid' => $object['objectid'],
                                                'classid' => $object['classid']));

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
