<?php

/*
 * Squirrelmail2 API
 * Copyright (c) 2001 Partridge
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkMod_auth_imap
 *
 */
class ZkMod_auth_imap {

    var $host;
    var $port;
    var $sid;
    var $stream;
    var $info;

    /**
     * Create a new ZkMod_auth_test with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the authentication module
     */
    function ZkMod_auth_imap($options) {
        /* Instantiate the Authentication Module Here! */
        $this->host = 'localhost';
        $this->port = 143;
        $this->sid = substr( session_id(), -4 );
        $this->stream = FALSE;
        $this->info = 'UNKNOWN';
    }

    /**
     * Check a username/password pair.
     *
     * @param string $username username with which to authenticate
     * @param string $password password with which to authenticate
     * @return bool indicates correct or incorrect password
     */
    function checkPassword( $username, $password ) {
        /* Code to check for success... */
        $this->stream = fsockopen( $this->host, $this->port,
                                   $error_number, $error_string, 15);
        if( $this->stream ) {
            $this->info = fgets( $this->stream, 1024 );
        } else {
            $this->info = "Socket Error connecting to $this->host:$this->port";
            // Error handling here
        }
    }
}

?>
