<?php
$t['body_function'] = 'user_view_body';
include($foowd->template.'/index.php');

function user_view_body(&$foowd, $className, $method, $user, $object, &$t)
{
  $none = '<span class="subtext"><em>&lt;none specified&gt;</em></span>';
?>

<table border="0" align="center">
<tr>
    <td></td>
    <td rowspan="20" width="10"><img src="empty.png" border="0" alt="" /></td>
    <td></td>
</tr>
<tr>
  <td class="heading"><?php echo _("Username"); ?>:</td>
  <td><?php echo $t['username']; ?></td>
</tr>
<tr>
  <td class="heading"><?php echo _("Created"); ?>:</td>
  <td class="smalldate"><?php echo $t['created']; ?></td>
</tr>
<tr>
  <td class="heading"><?php echo _("Last Visit"); ?>:</td>
  <td class="smalldate"><?php echo $t['lastvisit']; ?></td>
</tr>
<?php // DISPLAY IM ID's IF PRESENT
  if ( isset($t['IM_nicks']) || isset($t['IRC_nick']) )
  {
?>
<tr>
    <td colspan="3" class="separator"><? echo _("Contact Information"); ?></td>
</tr>
<tr>
  <td class="heading"><?php echo _("IRC nickname"); ?>:</td>
  <td><?php echo isset($t['IRC_nick']) ? $t['IRC_nick'] : $none; ?><br />
      <span class="subtext">IRC handle(s) used in #squirrelmail on irc.freenode.net</span></td>
</tr>
<?php
    if ( isset($t['IM_nicks']) && is_array($t['IM_nicks']) && !empty($t['IM_nicks']) )
    { 
      ksort($t['IM_nicks']);
      while( list ($prot, $id) = each ($t['IM_nicks']) )
      { 
        switch ($prot)
        {
          case 'MSN':
          case 'Email':  
            $id =  htmlspecialchars(mungEmail($id));
            break;
          case 'WWW':    
            $id = htmlspecialchars($id);
            $id = '<a href="' . $id . '">' . $id . '</a>'; 
            break;
          default:
            $id = htmlspecialchars($id);
            break;
        }
?>
<tr>
  <td class="heading"><?php echo htmlspecialchars($prot); ?>:</td>
  <td><?php echo $id; ?></td>
</tr>
<?php
      }
    }
  } // END DISPLAY IM IDs

  // begin AUTHOR only elements
  if ( $t['update'] ) 
  { 
?>
<tr>
    <td colspan="3" class="separator"><? echo _("Private Attributes"); ?></td>
</tr>
<tr>
    <td colspan="3" class="subtext_center">
    <?php 
        $string = _("<a href=\"%s\">Private attributes</a> are not shared with third parties.");
        printf($string, getURI(array('object' => 'privacy')));
    ?>
    </td>
</tr>
<tr>
  <td class="heading"><?php echo _("Email"); ?>:</td>
  <td><?php echo ($t['email']) ? htmlspecialchars($t['email']) : $none; ?></td>
</tr>
<tr>
  <td class="heading"><?php echo _("Preferred Applications"); ?>:</td>
  <td class="heading">&nbsp;</td>
</tr>
<tr>
  <td class="heading">&nbsp;&nbsp;<?php echo _("SMTP Server"); ?>:</th>
  <td><?php echo ($t['SMTP_server'] == 'Unknown') ? $none : $t['SMTP_server']; ?></td>
</tr>
<tr>
  <td class="heading">&nbsp;&nbsp;<?php echo _("IMAP Server"); ?>:</th>
  <td><?php echo ($t['IMAP_server'] == 'Unknown') ? $none : $t['IMAP_server']; ?></td>
</tr>
<tr>
  <td class="heading">&nbsp;&nbsp;<?php echo _("SquirrelMail Version"); ?>:</th>
  <td><?php echo ($t['SM_version'] == 'Unknown') ? $none : $t['SM_version']; ?></td>
</tr>
<tr>
    <td colspan="3" class="subtext_center"><br /><a href="<?php echo $t['update']; ?>">Update your profile</a>.</td>
</tr>
<?php 
  } // END AUTHOR ONLY ELEMENTS
?>

</table>
<?php
} // end display function
