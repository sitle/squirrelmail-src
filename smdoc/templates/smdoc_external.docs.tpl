<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for viewing generated documentation for smdoc Framework
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */
?>

<script language="JavaScript" type="text/javascript" src="templates/iframe.js"></script>
<table class="smdoc_table" style="width: 100%; margin-bottom:0px; padding-bottom: 0px;">
  <tr>
    <td class="col_left" style="width: 300px">
      <iframe src="<?php echo $t['doc_elements']; ?>" class="doc_elements" 
              name="left_bottom" id="left_bottom" onLoad="resizeMe(this)">
        <a href="<?php echo $t['doc_index']; ?>">View using regular frames</a>
      </iframe>
    </td>
    <td class="col_right">
      <iframe src="<?php echo $t['doc_content']; ?>" class="doc_content"
              name="right" id="right" onLoad="resizeMe(this)">
        <a href="<?php echo $t['doc_index']; ?>">View using regular frames</a>
      </iframe>
    </td>
  </tr>
  <tr>
    <td class="subtext_center"><?php echo $t['method']; ?></td>
    <td class="subtext_center">
      <?php printf(_("If this looks like garbage, or you prefer an undecorated frameset, try <a href=\"%s\">here</a>."), 
                   $t['doc_index']); ?>
    </td>
  </tr>
</table>

<?php
// vim: syntax=php
