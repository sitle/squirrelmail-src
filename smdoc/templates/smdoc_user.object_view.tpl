<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for basic object view
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */
unset($t['version']);
$t['body_function'] = 'user_view_body';

/** Include base template */
include(TEMPLATE_PATH.'index.tpl');

/**
 * Base template will call back to this function
 *
 * @param smdoc foowd Reference to the foowd environment object.
 * @param string className String containing invoked className.
 * @param string method String containing called method name.
 * @param smdoc_user user Reference to active user.
 * @param object object Reference to object being invoked.
 * @param mixed t Reference to array filled with template parameters.
 */
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
  <td class="value"><?php echo $t['title']; ?>
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
      if ( isset($t['nicks']) && is_array($t['nicks']) || !empty($t['nicks']) )
      {
?>
<tr><td colspan="2"><div class="separator"><?php echo _("Public Contact Information"); ?></div></td></tr>
<?php   ksort($t['nicks']);
        foreach ( $t['nicks'] as $prot => $id )
        {
          switch ($prot)
          {
            case 'MSN':
            case 'Email':
              $id =  htmlspecialchars(mungEmail($id));
              break;
            case 'IRC':
              $id = htmlspecialchars($id);
              $id .= ' - <span class="subtext">#squirrelmail (<a href="http://freenode.net">irc.freenode.net</a>)</span>';
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
        } // END foreach
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
  <td class="heading"><?php echo _("Group Membership"); ?>:</td>
  <td class="value">Registered
<?php   if ( !empty($object->groups) )
          foreach($object->groups as $group)
            echo ', ' . smdoc_group::getDisplayName($group);
?>
  </td>
</tr>
<?php   if ( !$t['show_email'] )
        { ?>
<tr>
  <td class="heading"><?php echo _("Email"); ?>:</td>
  <td class="value"><?php echo isset($t['email']) ? htmlspecialchars($t['email']) : $none; ?></td>
</tr>
<?php   } ?>
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
