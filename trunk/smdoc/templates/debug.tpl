<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
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

/**
 * Template invoked by debug object display function.
 *
 * @param int $accessNumber Number of DB accesses.
 * @param int $execTime Execution time for the page.
 * @param string $trackString String containing accumulated debug information.
 * @param bool $debugVar TRUE if additional var information be displayed (_SESSION, _GET, etc.).
 */
function debug_display($accessNumber, $execTime, &$trackString, $debugVar, &$templateVar)
{
?>
<!-- begin debug -->
<div id="debug_output">

<h1>Debug Information</h1>
<pre>Total DB Executions: <?php echo $accessNumber; ?>&nbsp;
Total Execution Time: <?php echo $execTime; ?>  seconds
</pre>

<h2>Execution History</h2>
<pre><?php echo $trackString; ?></pre>

<?php if ( $debugVar ) 
      { ?>
<h2>Request</h2>
<?php   show($_REQUEST); ?>
<h2>Session</h2>
<?php   show($_SESSION); ?>
<h2>Cookie</h2>
<?php   show($_COOKIE);
      } ?>
</div>
<!-- end debug -->

</body>
</html>
<?php
}
