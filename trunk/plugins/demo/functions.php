<?php
/**
 * Demo plugin functions
 * Copyright (c) 2005 The SquirrelMail Project Team
 * This file is part of SquirrelMail Demo plugin.
 *
 * Demo plugin is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Demo plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Demo plugin; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @version $Id$
 * @package plugins
 * @subpackage demo
 */

/**
 * Main login_form hook function
 */
function demo_login_form_do() {
    global $color;

    // check if used color is set
    if (!isset($color[4])) $color[4]='#ffffff';

    // load check_sm_version() function
    include_once(SM_PATH.'functions/strings.php');
    // load html_tag() function
    include_once(SM_PATH.'functions/html.php');

    // switch gettext domain
    bindtextdomain('demo',SM_PATH . 'locale');
    textdomain('demo');

    // create displayed row
    $demo_row = html_tag('tr')
        .html_tag('td',_("Demo:"),'right','','width="30%"')
        .html_tag('td','<input type="text" name="demo_field" />','left','','width="*"')
            .'</tr>';

    // revert gettext domain
    bindtextdomain('squirrelmail',SM_PATH . 'locale');
    textdomain('squirrelmail');

    // workaround for 1.5.1 hook changes (#1245070).
    if (check_sm_version(1,5,1)) {
        return $demo_row;
    } else {
        echo '<table align="center" width="350" border="0" bgcolor="'.$color[4].'">'
            .$demo_row
            .'</table>';
        return null;
    }
}
?>
