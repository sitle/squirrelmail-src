<?php

/**
 * message_display.tpl
 *
 * Copyright (c) 1999-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Template for viewing a message
 *
 * @version $Id$
 * @package squirrelmail
 */



function message_display($aTemplateVars) {
    extract($aTemplateVars);
?>

<table width="100%" cellpadding="0" cellspacing="0" align="center" border="0">
  <tr><td>
    <table width="100%" cellpadding="1" cellspacing="0" align="center" border="0" bgcolor="<?php echo $color[9];?>">
      <tr><td>
        <table width="100%" cellpadding="3" cellspacing="0" align="center" border="0">
          <tr bgcolor="<?php echo $color[4];?>"><td>
            <table cellpadding="1" cellspacing="5" align="<?php echo $align['left'];?>" border="0">
              <tr><td align="<?php echo $align['left'];?>"><br />

<!-- start message body -->

<?php echo $messagebody;?>

<!-- end message body -->

              </td></tr>
            </table>
          </td></tr>
        </table>
      </td></tr>
    </table>
  </td></tr>
  <tr><td height="5" colspan="2" bgcolor="<?php echo $color[4];?>"></td></tr>
<?php
    if (count($aAttachments)) {
?>
<!-- start attachment area -->
</table>
<table width="100%" cellpadding="1" cellspacing="0" align="center" border="0" bgcolor="<?php echo $color[9];?>">
  <tr><td>
    <table width="100%" cellpadding="0" cellspacing="0" align="center" border="0" bgcolor="<?php echo $color[4];?>">
      <tr><td align="<?php echo $align['left'];?>" color="<?php echo $color[9];?>">
        <b><?php echo _("Attachments");?>:</b>
      </td></tr>
      <tr><td>
        <table width="100%" cellpadding="2" cellspacing="2" align="center"'.' border="0" bgcolor="<?php echo $color[0];?>">
          <tr><td>
<?php
        foreach($aAttachments as $aAttachment) {
?>
            <tr>
              <td><a href="<?php echo $aAttachment['defaultlink'];?>"><?php echo $aAttachment['name'];?></a>&nbsp;</td>
              <td><small><b><?php echo $aAttachment['size'];?></b>&nbsp;&nbsp;</small></td>
              <td><small>[ <?php echo $aAttachment['type'];?>/<?php echo $aAttachment['subtype'];?> ]&nbsp;</small></td>
              <td><small><b><?php echo $aAttachment['description'];?></b></small></td>
              <td><small>&nbsp;
<?php
            $skipspaces = 1;
            foreach ($aAttachment['links'] as $val) {
                if ($skipspaces) {
                    $skipspaces = 0;
                } else {
                    ?>&nbsp;&nbsp;|&nbsp;&nbsp;<?php
                }?>
                   <a href="<?php echo $val['href'];?>"><?php echo $val['text'];?></a>
<?php
            } // foreach ($aAttachment['links'] as $val) ?>
              </small></td>
            </tr>
<?php
        } // foreach($aAttachments as $aAttachment) ?>
          </td></tr>
        </table>
      </td></tr>
    </table>
  </td></tr>
  <tr><td height="5" colspan="2" bgcolor="<?php echo $color[4];?>"></td></tr>
<!-- end attachment area -->
<?php } // if (count($aAttachments))?>
</table>

<?php if ($inline_images && $images_list) {?>
<table align="center" cellspacing="0" border="0" cellpadding="2">
<?php      foreach($images_list as $imgurl) {?>
  <tr>
    <td>
      <img src="<?php echo $imgurl;?>" align="<?php $align['left'];?>">
    </td>
  </tr>
<?php      } // foreach($images_list as $imgurl)
       } // if ($inline_images && $images_list) ?>
</table>

<?php
    message_navigation($aTemplateVars);
}

function message_display_js_head($aTemplateVars) {
    static $bInit;

    extract($aTemplateVars);

    if (!isset($bInit) && $javascript_on) {
        $binit = true;
    /**
     * Enter the JavaScript which need to be inserted in the HEAD section here
     * The script is inserted once, no matter how many times you display a single
     * template on one page.
     */
?>

<script language="JavaScript" type="text/javascript">
    <!--
    function openWindow(){
        window.open('about:blank', '<?php echo 'scrollbars=no,status=no,width=200,height=200,dependent=yes,directories=no,menubar=no,personalbar=no';?>);
        return true;
    }
    // -->
</script>

<?php
    }
}

function message_navigation($aTemplateVars) {
    // generate the navigationLinks
    $aLinks = generateNavigationLinks($aTemplateVars);
    extract ($aTemplateVars);
    extract ($aLinks);

    // BEGIN NAV ROW - PREV/NEXT, DEL PREV/NEXT, LINKS TO INDEX, etc.
?>

    <tr><td align="<?php echo $align['left'];?>" colspan="2" style="border: 1px solid '<?php echo $color[9];?>;">
      <small>
<?php if ($passed_ent_id) { // navigation inside multipart digest messages ?>

      [<?php echo $prev_ent_link;
          if ($up_ent_link) echo '&nbsp;|&nbsp;'.$up_ent_link;?>&nbsp;|&nbsp;<?php echo $next_ent_link;?>]&nbsp;&nbsp;[<?php echo $message_link;?>]
<?php } else { ?>
      [<?php echo $prev_link;?>&nbsp;|&nbsp;<?php echo $next_link;?>]
<?php     if (isset($del_prev_link) && isset($del_next_link)) {?>
      &nbsp;&nbsp;[<?php echo $del_prev_link;?>&nbsp;|&nbsp;<?php echo $del_next_link;?>]
<?php
           } // if (isset($del_prev_link) && isset($del_next_link))
       } //if ($passed_ent_id)
?>
      &nbsp;&nbsp;[<?php echo $message_list_link;?>]
      </small>
    </td></tr>
<?php
}



function message_controls($aTemplateVars) {
?>
<tr bgcolor="<?php echo $color[0];?>">
   <td><small>
<form name="composeForm" action=<?php echo createLinkString(createLinkArray('read_body','compose',array()));?> method="post" style="display: inline">
<input type="hidden" name="mailbox" value="<?php echo urlencode($mailbox);?>">
<input type="hidden" name="passed_id" value="<?php echo $passed_id;?>">
<input type="hidden" name="startMessage" value="<?php echo $startMessage;?>">
<?php if (isset($passed_ent_id)) { ?>
    <input type="hidden" name="passed_ent_id" value="<?php echo $passed_ent_id;?>">
<?php } ?>

<?php
/**

    $menu_row = '<tr bgcolor="'.$color[0].'"><td><small>';
    $comp_uri = $base_uri.'src/compose.php' .
                '?passed_id=' . $passed_id .
                '&amp;mailbox=' . $urlMailbox .
                '&amp;startMessage=' . $startMessage .
                 (isset($passed_ent_id) ? '&amp;passed_ent_id='.$passed_ent_id : '');
('.$comp_uri.'" '
              . $method.$target.$onsubmit.' style="display: inline">'."\n";

    // If Draft folder - create Resume link
    if (($mailbox == $draft_folder) && ($save_as_draft)) {
        $new_button = 'smaction_draft';
        $comp_alt_string = _("Resume Draft");
    } else if (handleAsSent($mailbox)) {
    // If in Sent folder, edit as new
        $new_button = 'smaction_edit_new';
        $comp_alt_string = _("Edit Message as New");
    }
    // Show Alt URI for Draft/Sent
    if (isset($comp_alt_string))
        $menu_row .= getButton('submit', $new_button, $comp_alt_string, $on_click) . "\n";

    $menu_row .= getButton('submit', 'smaction_reply', _("Reply"), $on_click) . "\n";
    $menu_row .= getButton('submit', 'smaction_reply_all', _("Reply All"), $on_click) ."\n";
    $menu_row .= getButton('submit', 'smaction_forward', _("Forward"), $on_click);
    if ($enable_forward_as_attachment)
        $menu_row .= '<input type="checkbox" name="smaction_attache" />' . _("As Attachment") .'&nbsp;&nbsp;'."\n";

    $menu_row .= '</form>&nbsp;';

    if ( in_array('\\deleted', $aMailbox['PERMANENTFLAGS'],true) ) {
    // Form for deletion. Form is handled by the originating display in $where. This is right_main.php or search.php
        $delete_url = $base_uri . "src/$where";
        $menu_row .= '<form name="deleteMessageForm" action="'.$delete_url.'" method="post" style="display: inline">';

        if (!(isset($passed_ent_id) && $passed_ent_id)) {
            $menu_row .= addHidden('mailbox', $aMailbox['NAME']);
            $menu_row .= addHidden('msg[0]', $passed_id);
            $menu_row .= addHidden('startMessage', $startMessage);
            $menu_row .= getButton('submit', 'delete', _("Delete"));
            $menu_row .= '<input type="checkbox" name="bypass_trash" />' . _("Bypass Trash");
        } else {
            $menu_row .= getButton('submit', 'delete', _("Delete"), '', FALSE) . "\n"; // delete button is disabled
        }

        $menu_row .= '</form>';
    }

    // Add top move link
    $menu_row .= '</small></td><td align="right">';
    if ( !(isset($passed_ent_id) && $passed_ent_id) &&
        in_array('\\deleted', $aMailbox['PERMANENTFLAGS'],true) ) {

        $menu_row .= '<form name="moveMessageForm" action="'.$base_uri.'src/'.$where.'?'.'" method="post" style="display: inline">'.
              '<small>'.

          addHidden('mailbox',$aMailbox['NAME']) .
          addHidden('msg[0]', $passed_id) . _("Move to:") .
              '<select name="targetMailbox" style="padding: 0px; margin: 0px">';

        if (isset($lastTargetMailbox) && !empty($lastTargetMailbox)) {
            $menu_row .= sqimap_mailbox_option_list($imapConnection, array(strtolower($lastTargetMailbox)));
        } else {
            $menu_row .= sqimap_mailbox_option_list($imapConnection);
        }
        $menu_row .= '</select> ';

        $menu_row .= getButton('submit', 'moveButton',_("Move")) . "\n" . '</form>';
    }
    $menu_row .= '</td></tr>';

    // echo rows, with hooks
    $ret = do_hook_function('read_body_menu_top', array($nav_row, $menu_row));
    if (is_array($ret)) {
        if (isset($ret[0]) && !empty($ret[0])) {
            $nav_row = $ret[0];
        }
        if (isset($ret[1]) && !empty($ret[1])) {
            $menu_row = $ret[1];
        }
    }
    echo '<table width="100%" cellpadding="3" cellspacing="0" align="center" border="0">';
    echo $nav_on_top ? $nav_row . $menu_row : $menu_row . $nav_row;
    echo '</table>'."\n";
    do_hook('read_body_menu_bottom');

*/
}


/**
 * Template logic
 *
 * The following functions are utility functions for this template. Do not
 * echo output in those functions. Output is generated above this comment block
 */

function generateNavigationLinks($aVars) {
    extract($aVars);

    $aResult = array();

    /**
     * prepare the links. I prefer to so this in the template so future
     * templates can move easier to buttons instead
     */
    $aUrlQuery = array(
                       'mailbox'      => $mailbox,       // current mailbox
                       'iAccount'     => $iAccount,      // current account
                       'startMessage' => $startMessage,  // offset in messages list
                       'where'        => $where,         // where from (search or messages list)
                       'what'         => $what);         // search query identifier

    if ($passed_ent_id) { // navigation inside multipart digest messages

        // keep track of current message
        $aUrlQuery['passed_id'] = $passed_id;

        // next. Use $next_ent_link in the template
        if ($next_ent) {
            $aUrlQuery['passed_ent_id'] = $next_ent;
            $aResult['next_ent_link'] = '<a href='.createLinkString(createLinkArray('read_body','read_body',$aUrlQuery)).'>'._("Next").'</a>';
        } else {
            $aResult['next_ent_link'] = _("Next");
        }

        // previous. Use $prev_ent_link in the template
        if ($prev_ent) {
            $aUrlQuery['passed_ent_id'] = $prev_ent;
            $aResult['prev_ent_link'] = '<a href='.createLinkString(createLinkArray('read_body','read_body',$aUrlQuery)).'>'._("Previous").'</a>';
        } else {
            $aResult['prev_ent_link'] = _("Previous");
        }

        // up. Use $up_ent_link in the template
        if ($up_ent) {
            $aUrlQuery['passed_ent_id'] = $up_ent;
            $aResult['up_ent_link'] = '<a href='.createLinkString(createLinkArray('read_body','read_body',$aUrlQuery)).'>'._("Up").'</a>';
        } else {
            $aResult['up_ent_link'] = '';
        }

        // remove passed_ent_id from url query.
        unset($aUrlQuery['passed_ent_id']);

        $aResult['message_link'] = '<a href='.createLinkString(createLinkArray('read_body','read_body',$aUrlQuery)).'>'._("View Message").'</a>';

    } else { // Normal navigation links

        // next. Use $next_link in the template
        if ($next != -1) {
            $aUrlQuery['passed_id'] = $next;
            $aResult['next_link'] = '<a href='.createLinkString(createLinkArray('read_body','read_body',$aUrlQuery)).'>'._("Next").'</a>';
        } else {
            $aResult['next_link'] = _("Next");
        }

        // previous. Use $prev_link in the template
        if ($prev != -1) {
            $aUrlQuery['passed_id'] = $prev;
            $aResult['prev_link'] = '<a href='.createLinkString(createLinkArray('read_body','read_body',$aUrlQuery)).'>'._("Previous").'</a>';
        } else {
            $aResult['prev_link'] = _("Previous");
        }

        // delete message and go to prev/next message navigation
        if ( $delete_prev_next_display ) {
            $aUrlQuery['delete_id'] = $passed_id;
            if ($prev != -1) {
                $aUrlQuery['passed_id'] = $prev;
                $aResult['del_prev_link'] = '<a href='.createLinkString(createLinkArray('read_body','read_body',$aUrlQuery)).'>'._("Delete & Prev").'</a>';
            } else {
                $aResult['del_prev_link'] = _("Delete & Prev");
            }

            if ($next != -1) {
                $aUrlQuery['passed_id'] = $next;
                $aResult['del_next_link'] = '<a href='.createLinkString(createLinkArray('read_body','read_body',$aUrlQuery)).'>'._("Delete & Next").'</a>';
            } else {
                $aResult['del_next_link'] = _("Delete & Next");
            }

            $aUrlQuery['passed_id'] = $passed_id;
            unset($aUrlQuery['delete_id']);
        }
    }
    // return to messages list link
    // $where contains the originating messages list, search results or messages list
    if ($where == 'search') {
        $msgs_str  = _("Search Results");
    } else {
        $msgs_str  = _("Message List");
    }
    $aResult['message_list_link'] = '<a href='.createLinkString(createLinkArray('read_body',$where,$aUrlQuery)).'>'.$msgs_str.'</a>';
    return $aResult;
}



?>