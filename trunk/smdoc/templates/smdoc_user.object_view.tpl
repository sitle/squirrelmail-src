<?php
unset($t['version']);
$t['body_function'] = 'user_view_body';
include(TEMPLATE_PATH.'index.tpl');

function user_view_body(&$foowd, $className, $method, $user, $object, &$t)
{
  $none = '<span class="subtext"><em>&lt;none specified&gt;</em></span>';
  if ( !isset($t['update']) )
    $t['update'] = FALSE;
?>

<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td colspan="2"><div class="separator"><?php echo _("Public Profile Attributes"); ?></div></td></tr>
<tr>
  <td class="heading"><?php echo _("Username"); ?>:</td>
  <td class="value"><?php echo $t['username']; ?>
     <span class="subtext">&nbsp;[<?php echo $t['objectid'] ?>]</span>
  </td>
</tr>
<tr>
  <td class="heading"><?php echo _("Created"); ?>:</td>
  <td class="value"><span class="smalldate"><?php echo $t['created']; ?></span></td>
</tr>
<tr>
  <td class="heading"><?php echo _("Last Visit"); ?>:</td>
  <td class="value"><span class="smalldate"><?php echo $t['lastvisit']; ?></span></td>
</tr>
<?php // DISPLAY IM ID's IF PRESENT
      if ( isset($t['IM_nicks']) || isset($t['IRC_nick']) )
      {
?>
<tr><td colspan="2"><div class="separator"><?php echo _("Public Contact Information"); ?></div></td></tr>
<?php   if ( isset($t['IRC_nick']) )
        { ?>
<tr>
  <td class="heading"><?php echo _("IRC nickname"); ?>:</td>
  <td class="value"><?php echo $t['IRC_nick']; ?><br />
      <span class="subtext">#squirrelmail (<a href="http://freenode.net">irc.freenode.net</a>)</span></td>
</tr>
<?php   }

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
              case 'ICQ':
                $id = '<a href="http://wwp.icq.com/'.$id.'">'.$id.'</a>';
                break;
              case 'WWW':    
                $id = htmlspecialchars($id);
                $id = '<a href="'.$id.'">'.$id.'</a>'; 
                break;
              default:
                $id = htmlspecialchars($id);
                break;
            }
?>
<tr>
  <td class="heading"><?php echo htmlspecialchars($prot); ?>:</td>
  <td class="value"><?php echo $id; ?></td>
</tr>
<?php
          }
        }
      } // END DISPLAY IM IDs

      // begin AUTHOR only elements
      if ( $t['update'] ) 
      { 
?>
<tr><td colspan="2"><div class="separator">
    <?php echo _("Private Attributes"); ?>
    <span class="subtext">(<a href="#email">privacy</a>)</span>
    </div></td>
</tr>
<tr>
  <td class="heading"><?php echo _("Email"); ?>:</td>
  <td class="value"><?php echo isset($t['email']) ? htmlspecialchars($t['email']) : $none; ?></td>
</tr>
<tr>
  <td class="heading" colspan="2"><?php echo _("Preferred Applications"); ?>:</td>
</tr>
<tr>
  <td class="heading">&nbsp;&nbsp;<?php echo _("SMTP Server"); ?>:</th>
  <td class="value"><?php echo ($t['SMTP_server'] == 'Unknown') ? $none : $t['SMTP_server']; ?></td>
</tr>
<tr>
  <td class="heading">&nbsp;&nbsp;<?php echo _("IMAP Server"); ?>:</th>
  <td class="value"><?php echo ($t['IMAP_server'] == 'Unknown') ? $none : $t['IMAP_server']; ?></td>
</tr>
<tr>
  <td class="heading">&nbsp;&nbsp;<?php echo _("SquirrelMail Version"); ?>:</th>
  <td class="value"><?php echo ($t['SM_version'] == 'Unknown') ? $none : $t['SM_version']; ?></td>
</tr>
<?php } // END AUTHOR ONLY ELEMENTS  ?>
</table>

<?php if ( $t['update'] )
      {
        $uri_arr['objectid'] = $t['objectid'];
        $uri_arr['classid'] = $t['classid'];
        $uri_arr['method'] = 'update';
 ?>
<p class="subtext_center"><a href="<?php echo getURI($uri_arr); ?>"><?php echo _("Update your profile"); ?></a></p>

<div class="subtext_center"><a id="email" name="email"></a>
<?php 
        echo _("Your email and other contact information is only shared with your consent.")
            . '<br />'
            . sprintf(_("See our <a href=\"%s\">Privacy Policy</a>"),
                      getURI(array('object' => 'privacy')))
            . '</div>';
      }   
} // end display function
