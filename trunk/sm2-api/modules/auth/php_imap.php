<?php

/* modules/auth/imap.php
 * Squirrelmail2 API
 * Copyright (c) 2001 Philippe Mingo
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkMod_auth_imap
 *
 */
class ZkMod_auth_php_imap {

    var $ver = '$Id$';
    var $name = 'auth/php_imap';

    var $srv;	// backward pointer to the service
    var $info;	// cargo
    var $banner; // Server banner

    /**
     * Create a new ZkMod_auth_test with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the authentication module
     */
    function ZkMod_auth_php_imap( $options, &$srv ) {
        $this->srv = &$srv;
        $this->banner = 'Unnavalaible';
    }

    /**
     * Check a username/password pair.
     *
     * @param string $username username with which to authenticate
     * @param string $password password with which to authenticate
     * @return bool indicates correct or incorrect password
     */
    function checkPassword( $username, $password ) {

        /*
        $zkld = &$this->srv->zkld->modhome;
        require_once( $zkld->modhome . '/' . $this->name . '.inc' );
        */

        if( is_array( $this->srv->connector ) ) {
            $ip = @imap_open ('{' . $this->srv->connector['host'] . ':' . $this->srv->connector['port'] . '}', $username, $password );

            $this->info = "<b>PHP-Imap Session</b><br>";
            if( $ip ) {
                imap_close( $ip );
                $ret = TRUE;
                $this->info .= 'Successfull';
            } else {
                $ret = FALSE;
                $this->info = 'Unsuccessfull';
            }

        } else {
            $ret = FALSE;
        }

        return( $ret );
    }
    
}

?>