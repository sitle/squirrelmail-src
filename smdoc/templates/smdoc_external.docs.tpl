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
<iframe width="250" src="<?php echo $t['doc_elements']; ?>" id="left" onLoad="resizeMe(this,'<?php echo $t['doc_index'];?>')">
  <a href="<?php echo $t['doc_index']; ?>">View using regular frames</a>
</iframe>
<iframe src="<?php echo $t['doc_content']; ?>" id="right" onLoad="resizeMe(this,'<?php echo $t['doc_index'];?>')">
  <a href="<?php echo $t['doc_index']; ?>">View using regular frames</a>
</iframe>

<div><?php printf(_("If this looks like garbage, or you prefer an undecorated frameset, try <a href=\"%s\">here</a>."), $t['doc_index']); ?></div>
<?php
