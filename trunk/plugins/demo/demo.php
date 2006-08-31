<?php
/**
 * Plugin demo page
 *
 * Is used to links that direct to some plugin page (menuline, options)
 * Copyright (c) 2006 The SquirrelMail Project Team
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
 *
 * @version $Id$
 * @package plugins
 * @subpackage demo
 */

/** SquirrelMail init */
if (file_exists('../../include/init.php')) {
    /* sm 1.5.2+*/
    include_once('../../include/init.php');
} else {
    /* sm 1.4.0+ */
    /** @ignore */
    define('SM_PATH', '../../');
    /* main init script */
    include_once(SM_PATH . 'include/validate.php');
}

displayPageHeader($color, 'None');

var_dump(defined('SMDEMO'));

if (check_sm_version(1,5,1)) {
    $oTemplate->display('footer.tpl');
} else {
    echo '</body></html>';
}
