<?php

/**
 * template_config.php
 *
 * Copyright (c) 1999-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Configuration vars template
 *
 * @version $Id$
 * @package squirrelmail
 */
define ('SM_SRC' ,  'src/');

$aModuleScriptNames = array(
                         'read_body'      => SM_SRC .'read_body.php',
                         'right_main' => SM_SRC .'right_main.php');
$aModuleLocation = array(
                         'read_body.php' => 'right',
                         'right_main.php' => 'right');

?>
