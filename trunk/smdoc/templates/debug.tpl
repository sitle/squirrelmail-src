<div class="debug_output">

<div class="debug_output_heading">Debug Information</div>
<pre>
Total DB Executions: <?php echo $template['DB_ACCESS_COUNT']; ?> &nbsp;
Total Execution Time: <?php echo $template['EXECUTION_TIME']; ?> seconds
</pre>

<div class="debug_output_heading">Execution History</div>
<?php show($template['DEBUG_TRACK_STRING']); ?>

<?php 
  $show_var = getConstOrDefault('DEBUG_VAR', FALSE);
  if ( $show_var ) 
  {
?>
<div class="debug_output_heading">Request</div>
<?php show($_REQUEST); ?>

<div class="debug_output_heading">Session</div>
<?php show($_SESSION); ?>

<?php 
  }
?>
</div><br />
