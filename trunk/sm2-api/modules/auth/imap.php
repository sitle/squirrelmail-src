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

    var $rev = '$Id$';
    var $name = 'imap';

    var $zkld;	// zkloader instance
    var $srv;	// backward pointer
    var $imap;	// Imap service
    var $info;	// cargo

    /**
     * Create a new ZkMod_auth_test with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the authentication module
     */
    function ZkMod_auth_imap($options) {
        global $zkld;

        $this->zkld = &$zkld;
        $this->imap = $this->zkld->loadService( 'messages', $options, 'imap');
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

        if( $ret = $this->imap->open( $username, $password ) ) {
	    // $this->info = $this->imap->mod->info;
            $this->imap->close();
        }
	// $this->info = $this->imap->mod->info;
	$this->info = $this->imap->mod->capability;
        return( $ret );
    }
}

?>
