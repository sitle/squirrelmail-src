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
    <td rowspan="14" width="10"><img src="empty.png" border="0" alt="" /></td>
    <td></td>
</tr>
<tr>
  <th align="left"><?php echo _("Username"); ?>:</th>
  <td><?php echo $t['username']; ?></td>
</tr>
<tr>
  <th align="left"><?php echo _("Created"); ?>:</th>
  <td class="smalldate"><?php echo $t['created']; ?></td>
</tr>
<tr>
  <th align="left" valign="top"><?php echo _("Last Visit"); ?>:</th>
  <td class="smalldate"><?php echo $t['lastvisit']; ?></td>
</tr>
<tr>
  <th align="left" valign="top"><?php echo _("IRC nickname"); ?>:</th>
  <td><?php echo ($t['irc_nick']) ? $t['irc_nick'] : $none; ?><br />
      <span class="subtext">IRC handle(s) used in #squirrelmail on irc.freenode.net</span></td>
</tr>
<tr>
  <th align="left"><?php echo _("Other IMs"); ?>:</th>
  <td><?php if ( is_array($t['IM_nicks']) && !empty($t['IM_nicks']) )
            { 
              echo '<table border="0" cellspacing="0" cellpadding="0">';
              foreach( $t['IM_nicks'] as $prot => $id )
              {
                echo '<tr><td>', htmlspecialchars($prot), '&nbsp;</td><td>',
                     htmlspecialchars($id), '</td></tr>';
              }
              echo '</table>';
            }
            else
              echo $none;
       ?></td>
</tr>


<?php if ( $t['update'] ) 
      { ?>
<tr>
    <td colspan="3" align="center" class="separator"><br /><? echo _("Private Attributes"); ?>:</td>
</tr>
<tr>
    <td colspan="3" align="center" class="subtext">
    <?php 
        $string = _("<a href=\"%s\">Private attributes</a> are not shared with third parties.");
        printf($string, getURI(array('object' => 'privacy')));
    ?></td>
</tr>
<tr>
  <th align="left"><?php echo _("Email"); ?>:</th>
  <td><?php echo ($t['email']) ? htmlspecialchars($t['email']) : $none; ?></td>
</tr>
<tr>
  <th align="left"><?php echo _("Preferred SMTP Server"); ?>:</th>
  <td><?php echo ($t['SMTP_server'] == 'Unknown') ? $none : $t['SMTP_server']; ?></td>
</tr>
<tr>
  <th align="left"><?php echo _("Preferred IMAP Server"); ?>:</th>
  <td><?php echo ($t['IMAP_server'] == 'Unknown') ? $none : $t['IMAP_server']; ?></td>
</tr>
<tr>
  <th align="left"><?php echo _("SquirrelMail Version"); ?>:</th>
  <td><?php echo ($t['SM_version'] == 'Unknown') ? $none : $t['SM_version']; ?><br />
      <span class="subtext">Version of SquirrelMail you most frequently use/maintain</span></td>
</tr>
<tr>
    <td colspan="3" align="center"><br /><a href="<?php echo $t['update']; ?>">Update your profile</a>.</td>
</tr>

<?php } ?>
</table>
<?php
show($t);
}
