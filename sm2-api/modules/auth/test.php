<?php

/*
 * Squirrelmail2 API
 * Copyright (c) 2001 Th Squirrelmail Foundation
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkMod_auth_test
 *
 * The ZkAuthMod_Test class purely meant for testing of the
 * ZkSvc_auth class.
 */
class ZkMod_auth_test {
    /**
     * Create a new ZkMod_auth_test with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the authentication module
     */
    function ZkMod_auth_test($options) {
        /* Instantiate the Authentication Module Here! */
    }

    /**
     * Check a username/password pair.
     *
     * @param string $username username with which to authenticate
     * @param string $password password with which to authenticate
     * @return bool indicates correct or incorrect password
     */
    function checkPassword($username, $password) {
        /* Code to check for success... */
        return( ($username == 'captbunzo') &&
                ($password == 'bunz-r-us') );
    }
}

?>
