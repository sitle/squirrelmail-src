<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for user list.
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */

$t['title'] = _("Site News");
$t['body_function'] = 'show_news_body';

/** Include base template */
include_once(TEMPLATE_PATH.'index.tpl');

/**
 * Base template will call back to this function
 *
 * @param smdoc $foowd Reference to the foowd environment object.
 * @param string $className String containing invoked className.
 * @param string $method String containing called method name.
 * @param smdoc_user $user Reference to active user.
 * @param object $object Reference to object being invoked.
 * @param mixed $t Reference to array filled with template parameters.
 */
function show_news_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  if ( empty($t['newslist']) )
    echo '<h3>'._("No News Items Found").'</h3>';

  if ( $foowd->hasPermission('smdoc_news', 'create', 'CLASS') ) 
  {
    $uri_arr['class']='smdoc_news';
    $url = getURI($uri_arr);
    echo '<p>'._("News Editor").': <a href="'.$url.'">'._("Add News Item").'</a></p>';
  }

  // If there are no news items, return without displaying the table, etc.
  if ( empty($t['newslist']) )
    return;

?>
<table class="smdoc_table" width="80%">
<?php 
      $row = 0;
      foreach ( $t['newslist'] as $news )
      {
        $uri_arr['objectid'] = $news->objectid;
        $uri_arr['classid']  = NEWS_CLASS_ID;
        $url = getURI($uri_arr);
?>
      <tr class="row_odd">
        <td class="heading"><a href="<?php echo $url; ?>"><?php echo $news['title']; ?></a></td>
        <td class="smalldate newsdate"><?php echo date('Y/m/d H:i T',strtotime($news['updated'])); ?></td>
      </tr>
      <tr class="row_even">
        <td class="newssummary" colspan="2"><?php echo $news['summary']; ?></td>
      </tr>
<?php    
        $row = !$row;
       } // end foreach user in list
?>
</table>
<?php
} // end user_list_body



