<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
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
      <IFRAME src="docs/li_smdoc.html" class="doc_elements" 
              name="left_bottom" id="left_bottom" onLoad="resizeMe(this)">
        <a href="/docs/index.html">View using regular frames</a>
      </IFRAME>
    </td>
    <td class="col_right">
      <IFRAME src="docs/blank.html" class="doc_content"
              name="right" id="right" onLoad="resizeMe(this)">
        <a href="/docs/index.html">View using regular frames</a>
      </IFRAME>
    </td>
  </tr>
  <tr>
    <td class="subtext_center"><?php echo $t['method']; ?></td>
    <td class="subtext_center">
      <?php printf(_("If this looks like garbage, or you prefer an undecorated frameset, try <a href=\"%s\">here</a>."), 
                   "docs/index.html"); ?>
    </td>
  </tr>
</table>
