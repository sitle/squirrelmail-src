<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for editing text-based pages
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */

$t['body_function'] = 'text_plain_edit_body';

/** Include base template */
include(TEMPLATE_PATH.'index.tpl');

/**
 * Base template will call back to this function
 *
 * @param smdoc $foowd Reference to the foowd environment object.
 * @param string $className String containing invoked className.
 * @param string $method String containing called method name.
 * @param smdoc_user $user Reference to active user.
 * @param object $object Reference to object being invoked.
 * @param mixed $t Reference to array filled with template parameters.
 * @see index.tpl
 */
function text_plain_edit_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $objects =& $t['form']->objects;
  $t['form']->display_start('smdoc_form');
  $objects['editCollision']->display();

  if ( isset($t['preview']) )
  { ?>
<div class="preview"><?php echo $t['preview']; ?></div>
<div class="form_submit"><?php $t['form']->display_buttons(); ?></div>
<?php 
  }
?>

<p align="center"><?php $objects['editArea']->display('smdoc_textarea'); ?></p>  
<p align="center">
<?php 
  if ( isset($objects['noNewVersion']) )
  {
    $objects['noNewVersion']->display();
    echo _("Minor Update (Do not create new version)");
  }
?>
</p>
<div class="form_submit"><?php $t['form']->display_buttons(); ?></div>

<?php
  $t['form']->display_end();
}
