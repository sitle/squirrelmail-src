<?php

/*
 * Squirrelmail2 API
 * Copyright (c) 2001 Th Squirrelmail Foundation
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkAuthImp_Test
 *
 * The ZkAuthImp_Test class purely meant for testing of the
 * ZkAuthHandler class.
 */
class ZkAuthImp_Test {
    /**
     * Create a new ZkAuthImplementor with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the authentication implementator
     */
    function ZkAuthImpl_Test($options) {
        /* Instantiate the Authentication Implementor Here! */
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
        if (($username == 'captbunzo') && ($password == 'bunz-r-us')) {
            return (true);
        }

        /* Otherwise, fail authentication. */
        return (false);
    }
}

?>
