<?php

/*
 *  This function tell other modules what users have access
 *  to the plugin.
 *  
 *  Philippe Mingo
 *  
 *  $Id$
 */
function adm_check_user() {
    if ( (float)substr(PHP_VERSION,0,3) < 4.1) {
        global $_SESSION, $_SERVER;
    }
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    }
    else {
        $username = "";
    }
    $PHP_SELF = $_SERVER['PHP_SELF'];

    if ( strpos( 'options.php', $PHP_SELF ) ) {
        $auth = FALSE;
    } else if ( file_exists( '../plugins/administrator/admins' ) ) {
        $auths = file( '../plugins/administrator/admins' );
        $auth = in_array( "$username\n", $auths );
    } else if ( file_exists( '../config/admins' ) ) {
        $auths = file( '../config/admins' );
        $auth = in_array( "$username\n", $auths );
    } else if ( $adm_id = fileowner('../config/config.php') ) {
        $adm = posix_getpwuid( $adm_id );
        $auth = ( $username == $adm['name'] );
    }
    else {
        $auth = FALSE;
    }

    return( $auth );

}

?>
