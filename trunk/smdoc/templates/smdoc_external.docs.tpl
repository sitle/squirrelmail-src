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
<table class="smdoc_table" style="width: 100%">
  <tr>
    <td class="col_left">
      <IFRAME src="docs/li_smdoc.html" name="left_bottom" class="doc_elements">
        <a href="/docs/index.html">View using regular frames</a>
      </IFRAME>
    </td>
    <td class="col_right">
      <IFRAME src="docs/blank.html" name="right" class="doc_content">
        <a href="/docs/index.html">View using regular frames</a>
      </IFRAME>
    </td>
  </tr>
</table>

<p class="subtext_center">
<?php printf(_("If this looks like garbage, try <a href=\"%s\">here</a>"), 
             "docs/index.html"); ?>
</p>
