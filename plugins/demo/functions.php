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
 * Define SM_PATH constant for situations when functions are loaded 
 * directly.
 * @ignore
 */
if (! defined('SM_PATH')) define('SM_PATH','../../');

// load check_sm_version() function
include_once(SM_PATH.'functions/strings.php');
// load html_tag() function
include_once(SM_PATH.'functions/html.php');
// load error_box() function
include_once(SM_PATH.'functions/display_messages.php');

/**
 * Main login_form hook function
 */
function demo_login_form_do() {
    global $color;

    // check if used color is set
    if (!isset($color[4])) $color[4]='#ffffff';

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

/**
 * Main function attached to options_identities_process hook.
 *
 * Hook is broken in 1.4.5.
 */
function demo_options_identities_process_do(&$args) {
    global $demo_id, $data_dir, $username;
    // TODO: check sm_print_r($args). Why do we need it? It does not provide
    // enough information about processed id and plugin must use own hacks to
    // detect id.

    if (sqgetGlobalVar('demo_submit',$demo_submit,SQ_POST) &&
        is_array($demo_submit) && count($demo_submit)==1) {
        // process own buttons in form submission
        $demo_id = (int) key($demo_submit);
        setPref($data_dir,$username,'demo_id',$demo_id);
    } elseif (sqgetGlobalVar('update',$tmp,SQ_POST) && 
        sqGetGlobalVar('demo_id_select',$demo_id_number,SQ_POST)) {
        // check if form action is 'save/update', extract selected value of demo id radio box and save it
        $demo_id = (int) $demo_id_number;
        setPref($data_dir,$username,'demo_id',$demo_id);
    }
}

/**
 * Main function attached to options_identities_top hook.
 */
function demo_options_identities_top_do() {
    global $color;

    // switch gettext domain
    bindtextdomain('demo',SM_PATH . 'locale');
    textdomain('demo');

    $message = _("You have demo plugin installed.");

    // revert gettext domain
    bindtextdomain('squirrelmail',SM_PATH . 'locale');
    textdomain('squirrelmail');

    // example error box
    // put it inside the table in order to reduce box width
    echo '<table align="center"><tr><td>';
    error_box($message,$color);
    echo '</td></tr></table>';
}

/**
 * Main function attached to options_identities_renumber hook
 *
 * Process changes in identity numbers. Must handle 'move_up' 
 * and 'make default' actions.
 * Hook is broken in 1.4.5
 */
function demo_options_identities_renumber_do(&$args) {
    global $demo_id, $data_dir, $username;

    // from id
    $from_id = $args[1];

    // to_id ('default' or number);
    $to_id = $args[2];

    unset($flip_id);
    if ($from_id != $to_id) {
        if ($to_id == 'default') {
            $to_id = 0;
            // WARNING: 'make default' behaves differently in 1.4.5+ and 1.5.1+
            // plugin must handle renumbering of ids 
            // if demo_id is smaller than from_id, it must be incremented by 1
            if ((check_sm_version(1,5,1) ||
                (check_sm_version(1,4,5) && ! check_sm_version(1,5,0))) &&
                $demo_id < $from_id) {
                $flip_id = $demo_id++;
            }
        }

        if ($demo_id == $to_id) {
            $flip_id = $from_id;
        } elseif ($demo_id == $from_id) {
            $flip_id = $to_id;
        }

        if (isset($flip_id)) {
            $demo_id = (int) $flip_id;
            setPref($data_dir,$username,'demo_id',$demo_id);
        }
    }
}

/**
 * Main function attached to options_identities_table hook
 */
function demo_options_identities_table_do(&$args) {
    global $demo_id;

    // first key in $args - color or style - string type
    if (!isset($args[0]) || empty($args[0])) {
        // is not set or empty string
        $bgstyle = '';
    } elseif (check_sm_version(1,5,1) ||
              (check_sm_version(1,4,5) && ! check_sm_version(1,5,0))) {
        // row style (1.4.5+ and 1.5.1+, not in 1.5.0)
        $bgstyle=$args[0];
    } else {
        // background color (1.4.4 or older and 1.5.0) 
        $bgstyle = 'bgcolor="' . $args[0] . '"';
    }

    // switch gettext domain
    bindtextdomain('demo',SM_PATH . 'locale');
    textdomain('demo');

    // second key - is hook called by new id form or id form is empty - boolean type 
    if ($args[1]) {
        $suffix = _("This new id");
    } else {
        $suffix = _("This existing id");
    }

    // third key - identity number or null (default id) - integer type.
    $id = (int) $args[2];

    // FIXME: can be broken if SMPREF_NONE is int 0. SquirrelMail 1.4.0-1.4.5 uses string 'none'
    if ($demo_id !== SMPREF_NONE && $demo_id == $id) {
        $checked = ' checked="checked"';
    } else {
        $checked = '';
    }
    $ret = html_tag('tr',
        html_tag('td',_("Set as demo identity:"),'right').
        html_tag('td','<input type="radio" name="demo_id_select" value="'.$id.'"'.$checked.'>&nbsp;'.$suffix,'left'),
                    '','',$bgstyle);

    // revert gettext domain
    bindtextdomain('squirrelmail',SM_PATH . 'locale');
    textdomain('squirrelmail');

    return $ret;
}

/**
 * Main function attached to options_identities_buttons hook
 */
function demo_options_identities_buttons_do(&$args) {
    // Is hook called in new identity table 
    $new_id_form = (bool) $args[0];
    // get identity number
    $id = (int) $args[1];

    // Set initial return value
    $ret='';
    // Add button if it is not new id form
    if (!$new_id_form) {
        // switch gettext domain only if you need it.
        bindtextdomain('demo',SM_PATH . 'locale');
        textdomain('demo');

        $ret.= '<input type="submit" name="demo_submit['.$id.']" value="'._("Mark as Demo ID").'" />';

        // revert gettext domain
        bindtextdomain('squirrelmail',SM_PATH . 'locale');
        textdomain('squirrelmail');
    }

    return $ret;
}

/**
 * Load plugin preferences.
 */
function demo_loading_prefs_do() {
    global $demo_id, $data_dir, $username;
    $demo_id = getPref($data_dir, $username, 'demo_id', SMPREF_NONE);
}
?>