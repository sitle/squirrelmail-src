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

if ( $t['foowd']->hasPermission('smdoc_news', 'create', 'CLASS') ) 
{
    $t['edit_links']['news'] = array('name' => _("Create"),
                                     'uri'  => BASE_URL . '?class=smdoc_news');
}

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

  // If there are no news items, return without displaying the table, etc.
  if ( empty($t['newslist']) )
    return;
?>
<dl>
<?php foreach ( $t['newslist'] as $news )
      {
        $uri_arr['objectid'] = $news['objectid'];
        $uri_arr['classid']  = NEWS_CLASS_ID;
        $url = getURI($uri_arr);

        $update_arr['objectid'] = $news['creatorid'];
        $update_arr['classid'] = USER_CLASS_ID;
?>
    <div class="bottomline">
    <dt class="newsheadline">
        <div class="newsupdate">
            <?php echo date('d M Y',strtotime($news['updated'])) . _(" by "); ?> 
            <a href="<?php echo getURI($update_arr); ?>"><?php echo $news['creatorName']; ?></a>
        </div>
        <a href="<?php echo $url; ?>"><?php echo $news['title']; ?></a>
    </dt>   
    <dd>
        <?php echo $news['summary']; ?>
    </dd>
    </div>
<?php } // end foreach user in list  ?>
</dl>
<div class="float-clear">&nbsp;</div>
<?php
} // end user_list_body
