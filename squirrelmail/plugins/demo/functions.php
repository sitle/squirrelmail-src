<?php


/**
  * SquirrelMail Demo Plugin
  *
  * @copyright &copy; 2006-2007 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id$
  * @package plugins
  * @subpackage demo
  */


/**
  * Add link to menu at top of content pane
  *
  * @return void
  *
  */
function demo_menuline_do()
{
   sq_change_text_domain('demo');
   displayInternalLink('plugins/demo/demo.php', _("Demo"), '');
   echo "&nbsp;&nbsp;\n";
   sq_change_text_domain('squirrelmail');
}



/**
  * Inserts an option block in the main SM options page
  *
  */
function demo_option_link_do()
{

   global $optpage_blocks;

   sq_change_text_domain('demo');

   $optpage_blocks[] = array(
      'name' => _("Demo"),
      'url' => sqm_baseuri() . 'plugins/demo/demo.php',
      'desc' => _("This is where you would describe what your plugin does."),
      'js' => FALSE
   );

   sq_change_text_domain('squirrelmail');

}



