<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for debug display
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */
function debug_display($accessNumber, $execTime, &$trackString, $debugVar)
{
?>
<!-- begin debug -->
<div class="debug_output">
<a name="debug"><img src="templates/images/empty.gif" alt="------------- debug ------------------------------------------" /></a>

<div class="debug_output_heading">Debug Information</div>
<pre>Total DB Executions: <?php echo $accessNumber; ?>&nbsp;
Total Execution Time: <?php echo $execTime; ?>  seconds
</pre>

<div class="debug_output_heading">Execution History</div>
<pre><?php echo $trackString; ?></pre>

<?php if ( $debugVar ) 
      { ?>
<div class="debug_output_heading">Request</div>
<?php   show($_REQUEST); ?>

<div class="debug_output_heading">Session</div>
<?php   show($_SESSION); ?>

<div class="debug_output_heading">Cookie</div>
<?php   show($_COOKIE);
      } ?>
</div>
<!-- end debug -->

</body>
</html>
<?php
}
?>
