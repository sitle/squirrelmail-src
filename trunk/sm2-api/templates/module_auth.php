<?php

/*
 * Zookeeper
 * Copyright (c) 2001 Paul Joseph Thompson
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkImp_auth_module
 *
 * The implementor_auth class is the template for classes that provide
 * backend functionality to the Authentication API.
 */
class ZkImp_auth_module {
    /**
     * Create a new ZkImp_auth_module with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the module
     */
    function ZkImp_auth_module($options) {
        /* Instantiate the Module Here! */
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
