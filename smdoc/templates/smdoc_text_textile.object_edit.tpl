<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for editing Textile document
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */
$t['body_function'] = 'text_textile_edit_body';

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
function text_textile_edit_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $obj = $t['form']->objects;

      if ( isset($t['preview']) ) { ?>
<div class="preview"><?php echo $t['preview']; ?></div>
<?php }

      include_once(TEMPLATE_PATH.'smdoc_text_textile.howto.tpl');
      $t['form']->display_start('smdoc_form'); 
      $obj['editCollision']->display();

      if ( isset($t['preview']) ) { ?>
    <div class="form_submit"><?php $t['form']->display_buttons(); ?></div>
<?php } ?>

<!-- begin create body -->
<div id="textileform">

<?php echo $obj['editArea']->display('smdoc_textarea'); 
      if ( isset($obj['noNewVersion']) ) {
        ?><p><?php
        echo $obj['noNewVersion']->display() . 
             _("Minor Update (Do not create new version)");
        ?></p><?php
      } ?>

<div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div>

<?php $t['form']->display_end(); ?>
</div>
<!-- end create body -->
<div class="float-clear">&nbsp;</div>
<?php
}


