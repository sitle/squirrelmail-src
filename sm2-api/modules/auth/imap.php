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
class ZkMod_auth_imap {

    var $ver = '$Id$';
    var $name = 'auth/imap';

    var $srv;	// backward pointer to the service
    var $info;	// cargo
    
    var $host;
    var $port;
    var $timeout;

    /**
     * Create a new ZkMod_auth_test with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the authentication module
     */
    function ZkMod_auth_imap( $options, &$srv ) {
        $this->srv = &$srv;
        $this->info = 'auth/imap init';
    }

    /**
     * Check a username/password pair.
     *
     * @param string $username username with which to authenticate
     * @param string $password password with which to authenticate
     * @return bool indicates correct or incorrect password
     */
    function checkPassword( $username, $password ) {

        if( is_array( $this->srv->connector ) ) {
            
            $sp = fsockopen( $this->srv->connector['host'], 
                             $this->srv->connector['port'],
                             $error_number, $error_string, 
                             $this->srv->connector['timeout'] );
            
            $this->info = "Connecting.";
            if( $sp ) {        
                socket_set_timeout( $sp, $this->srv->connector['timeout'] );
                $ret = TRUE;
                $this->info = fgets( $sp, 1024 );
                // Check compatibilities in here
                // Identifies the user
                $ret = $this->query( $sp, ' LOGIN "' . quoteIMAP($username) .
                                     '" "' . quoteIMAP($password) . '"' );
                $this->srv->info = "Socket connected to $this->srv->host:$this->srv->port";                                 
            } else {
                $ret = FALSE;
                $this->srv->info = "Socket Error connecting to $this->srv->host:$this->srv->port";
                // Error handling here
            }
            
        } else {
            $this->info = 'No connector defined';
            $ret = FALSE;
        }
        
        return( $ret );
    }
    
    function query( $sp, $cmd ) {

        $buffer = '?';
        $a = array( '*', 'NOPE' );
        $isid = substr( session_id(), -4 );
        if( $isid == '' ) {
            // Not sessionized
            $isid = rand( 1000, 9999 );
        }
        
        $this->info = '<blockquote>' . $cmd . ' ';
        
        fputs( $sp, $isid . ' ' . $cmd . "\r\n" );
        
        while( !eregi( ".*$a[1]-", 'OK-BAD-NO-' ) && $buffer <> '' ) {
            $buffer = fgets( $sp, 1024 );
            $a = explode( ' ', $buffer );
            $this->info .= $buffer . '<br>';
        }
        
        $this->info .= '</blockquote>';
        
        return( $a[1] == 'OK' );
    }    
    
}

?>
