<?php
/*
 * Modified page index for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * $Id$
 */

/** CLASS DESCRIPTOR **/
define('SQMCHANGES_CLASS_ID',-275885230);

$EXTERNAL_RESOURCES[SQMCHANGES_CLASS_ID]['func'] = 'sqmchanges';
$EXTERNAL_RESOURCES[SQMCHANGES_CLASS_ID]['title'] = 'Recent Changes';

/**
 * This prints the more generally used site index, which limits what is 
 * shown based on the group, and includes categorized links for document
 * creation closer to other documents of the same type.
 */
function sqmchanges(&$foowd, &$result) {
    $foowd->track('sqmchanges');

    $objects = $foowd->getObjects(NULL,
                                  NULL,
                                  array('updated DESC'),
                                  20);
    $list_objects = array();

    $i = 0;
    foreach ($objects as $object) 
    {
      if ( isset($object->permissions['view']) &&
           !$foowd->user->inGroup($object->permissions['view'], $object->creatorid) )
         continue;
    
      $list_objects[$i]['url'] = getURI(array('objectid' => $object->objectid,
                                              'classid' => $object->classid,
                                              'version' => $object->version));
      $list_objects[$i]['title']  = $object->title;
      
      if ( $object->workspaceid != 0 )
        $list_objects[$i]['langid'] = foowd_translation::getLink($foowd, $object->workspaceid);
      else 
        $list_objects[$i]['langid'] = '&nbsp;';

      if (isset($object->updatorName)) {
          $list_objects[$i]['updated'] = date(DATETIME_FORMAT, $object->updated);
          $list_objects[$i]['updated_by'] = $object->updatorName;
      } else {
          $list_objects[$i]['updated'] = date(DATETIME_FORMAT, $object->created);
          $list_objects[$i]['updated_by'] = $object->creatorName;
      }
      $list_objects[$i]['ver'] = $object->version;
      $list_objects[$i]['desc'] = getClassDescription($object->classid);
    }

    $result['changeList'] =& $list_objects;
    $result['body_template'] = 'smdoc_external.changes.php';
    $foowd->track();
}
?>
