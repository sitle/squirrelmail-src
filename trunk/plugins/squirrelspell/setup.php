<?php
/**
   SETUP.PHP
   ---------
   This is a standard SquirrelMail-1.0 API for plugins.
   								**/
								
function soupNazi(){
 // This function checks whether the user's USER_AGENT is known to
 // be broken. If so, returns true and the plugin is invisible to the
 // offending browser.
 global $HTTP_USER_AGENT;
 require ("../plugins/squirrelspell/sqspell_config.php");
 $soup_nazi=false;
 $soup_menu = explode(",", $SQSPELL_SOUP_NAZI);
 for ($i=0; $i<sizeof($soup_menu); $i++)
  if (stristr($HTTP_USER_AGENT, trim($soup_menu[$i]))) $soup_nazi=true;
 return $soup_nazi;
}

function squirrelmail_plugin_init_squirrelspell(){
 // Standard initialization API.
 global $squirrelmail_plugin_hooks;
 $squirrelmail_plugin_hooks["compose_button_row"]["squirrelspell"] = "squirrelspell_setup";
 $squirrelmail_plugin_hooks["options_link_and_description"]["squirrelspell"] = "squirrelspell_options";
 }

function squirrelspell_options(){
 // Gets added to the user's OPTIONS page.
 global $color;
 if (soupNazi()) return;
 ?>
 <script type="text/javascript">
 <!--
 // Using document.write to hide this functionality from people with
 // JavaScript turned off.
 document.write("<table width=\"50%\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\" align=\"center\">");
 document.write("<tr>");
 document.write("<td bgcolor=\"<?php echo $color[9] ?>\">");
 document.write("<a href=\"../plugins/squirrelspell/sqspell_options.php\">SpellChecker Options</a>"); 
 document.write("</td>");
 document.write("</tr>");
 document.write("<tr>");
 document.write("<td bgcolor=\"<?php echo $color[0] ?>\">");
 document.write("<p>Here you may set up how your personal dictionary");
 document.write("is stored, edit it, or choose which languages should be"); 
 document.write("available to you when spell-checking.</p>");
 document.write("</td>");
 document.write("</tr>");
 document.write("</table>");
 //-->
 </script>
<?php

}

function squirrelspell_setup(){
 // Gets added to the COMPOSE buttons row.
 if (soupNazi()) return;
 ?>
 <script type="text/javascript">
  <!--
  // using document.write to hide this functionality from people
  // with JavaScript turned off.
   document.write("<input type=\"button\" value=\"Check Spelling\" onclick=\"window.open('../plugins/squirrelspell/sqspell_interface.php', 'sqspell', 'status=yes,width=550,height=370,resizable=yes')\">");
  //-->
 </script>
 <?php
}

?>
