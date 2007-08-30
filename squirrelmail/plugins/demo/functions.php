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



