<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for performing object updates
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */
$t['body_function'] = 'user_update_body';

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
function user_update_body(&$foowd, $className, $method, $user, $object, &$t)
{
  $t['form']->display_start('smdoc_form');
  $obj =& $t['form']->objects;
?>
<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td colspan="2"><div class="separator">
        <?php echo _("Private Attributes"); ?>
        <span class="subtext">(<a href="#email">privacy</a>)</span>
        </div></td>
</tr>

<tr><td class="label"><?php echo _("User Name");?>:</td>
    <td class="value"><?php echo $obj['title']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td class="label"><?php echo _("Change Password");?>:</td>
    <td class="value"><?php echo $obj['password']->display(); ?></td></tr>
<tr><td class="label"><?php echo _("Verify New Password");?>:</td>
    <td class="value"><?php echo $obj['verify']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><td class="label"><?php echo _("Email");?>:</td>
    <td class="value"><?php echo $obj['email']->display(); ?><br />
    <span class="subtext">
       <?php echo $obj['show_email']->display()
                  . _("Share email with public contact information?"); ?>
       (<a href="#email">privacy</a>)</span>
    </td></tr>

<tr><td colspan="2"><div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div></td></tr>

<tr><td colspan="2"><div class="separator"><?php echo _("Public Contact Information"); ?></div></td></tr>

<?php ksort($obj['nick']);
      foreach ( $obj['nick'] as $box )
      { ?>
<tr><td class="label"><?php echo $box->caption; ?>:</td>
    <td class="value">
<?php   echo $box->display();
        if ( $box->name == 'IRC' )
        { ?>
         <span class="subtext"> - #squirrelmail (<a href="http://freenode.net">irc.freenode.net</a>)</span>
<?php   } ?>
    </td></tr>
<?php } ?>

<tr><td colspan="2"><div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div></td></tr>
<tr><td colspan="2"><div class="separator">
    <?php echo _("Server/Version Statistics"); ?>
    <span class="subtext">(<a href="#email">privacy</a>)</span>
    </div></td>
</tr>

<?php foreach ( $obj['stat'] as $box )
      { ?>
<tr><td class="label"><?php echo $box->caption; ?>:</td>
    <td class="value"><?php echo $box->display(); ?></td></tr>
<?php } ?>


<?php
/*
  if ( isset($t['form']) )
  {
    show($t['form']->objects);


    // add public header
    $table->insertObject(0, _("Public Contact Information"), array('class' => 'separator', 'onecell' => true));
    $table->insertSpace(1);
    $table->insertObject(3, _("Nick used on irc.freenode.net #squirrelmail channel"), array('value_class' => 'subtext'));
    $table->insertSpace(4);

    // add private header
    $table->insertObject(10, _("Private Attributes"), array('class' => 'separator', 'onecell' => true));
    $string = sprintf(_("<a href=\"%s\">Private attributes</a> are not shared with third parties."),
                      getURI(array('object' => 'privacy')));
    $table->insertObject(11, $string, array('class' => 'subtext_center', 'onecell' => true));
    $table->insertSpace(12);
    $table->insertSpace(15);
    $table->insertSpace(18);
    $table->addSpace();
*/
?>
</table>

<div class="form_submit">
<?php $t['form']->display_buttons(); ?>
</div>

<?php $t['form']->display_end(); ?>

<div class="subtext_center"><a id="email" name="email"></a>
<?php
  echo _("Your email and other contact information is only shared with your consent.") . '<br />'
       . _("Displayed emails are dynamically munged to discourage spam.") . '<br />'
       . _("Server and version statistics are only viewed collectively.") . '<br />'
       . sprintf(_("See our <a href=\"%s\">Privacy Policy</a>"),
                 getURI(array('object' => 'privacy')))
       . '</div>';

}
