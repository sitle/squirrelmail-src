<?php

/*
 * Squirrelmail2 API
 * Copyright (c) 2001 Th Squirrelmail Foundation
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkAuthImplementor
 *
 * The ZkAuthImplementor class is the template for classes that provide
 * backend functionality to the Authentication API.
 */
class ZkAuthImplementor {
    /**
     * Create a new ZkAuthImplementor with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the authentication implementator
     */
    function ZkAuthImplementor($options) {
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
        return (true);

        /* Otherwise, fail authentication. */
        return (false);
    }
}

?>
