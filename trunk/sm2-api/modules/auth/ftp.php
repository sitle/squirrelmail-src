<?php

/* modules/auth/imap.php
 * Squirrelmail2 API
 * Copyright (c) 2001 Philippe Mingo
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkMod_auth_ftp
 *
 */
class ZkMod_auth_ftp {

    var $ver = '$Id$';
    var $name = 'auth/ftp';

    var $srv;	// backward pointer to the service
    var $info;	// cargo
    var $banner; // Server banner
    
    /**
     * Create a new ZkMod_auth_test with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the authentication module
     */
    function ZkMod_auth_ftp( $options, &$srv ) {
        $this->srv = &$srv;
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
            
            $this->info = "<b>FTP Session</b><br>";
            if( $sp ) {        
                socket_set_timeout( $sp, $this->srv->connector['timeout'] );
                $ret = TRUE;
                $this->banner = fgets( $sp, 1024 );
                // Check compatibilities in here
                // Identifies the user
                if ( $ret = $this->query( $sp, 'USER ' . $username, '331' ) ) {
		    $ret = $this->query( $sp, 'PASS ' . $password, '230' );
		};
		$this->query( $sp, 'quit', '221' );
            } else {
                $ret = FALSE;
            }
            
        } else {
            $ret = FALSE;
        }
        
        return( $ret );
    }
    
    function query( $sp, $cmd, $ok_string ) {

        $buffer = '?';
        $a = array( 'AAA', 'NOPE' );
        
        $this->info .= '<p><i>' . $cmd . '</i> ';
        
        fputs( $sp, $cmd . "\r\n" );
        
        $buffer = fgets( $sp, 1024 );
        $a = explode( ' ', $buffer );
        $this->info .= $buffer . '<br></p>';
        
        return( substr( $buffer,  0, 3 ) == $ok_string );
    }    
    
}

?>
