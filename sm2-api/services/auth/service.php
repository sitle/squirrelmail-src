<?php

/**
 * Zookeeper: service/auth/service.php
 * Copyright (c) 2001-2002 The Zookeeper Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkSvc_auth
 *
 * The ZkSvc_auth class manages user authentication for a web application.
 */
class ZkSvc_auth {
    var $name;       /* str  - name of this service (auth)            */
    var $mod;        /* obj  - authentication module for this service */
    var $maxidle;    /* int  - max time login session can remain idle */
    var $maxlogin;   /* int  - max time login session can last        */
    var $loggedin;   /* bool - is the user logged in?                 */
    var $idled;      /* bool - has this login session idled?          */
    var $expired;    /* bool - is this login session expired?         */

    /**
     * Create a new ZkSvc_auth with the given module.
     *
     * @param object $module module to use for authentication
     * @param array  $options options to pass to ZkAuthHandler
     */
    function ZkSvc_auth($opts) {
        $name = 'auth';

        /* Register the login session variable. */
        global $zkauth;
        session_register('zkauth');

        /* Set the default values for this login session. */
        $this->loggedin = FALSE;
        $this->idled    = FALSE;
        $this->expired  = FALSE;

        /* Set values for maxlogin and maxidle. */
        $this->maxidle  = ($opts['maxidle']  == '' ? 0 : $opts['maxidle']);
        $this->maxlogin = ($opts['maxlogin'] == '' ? 0 : $opts['maxlogin']);

        /* Only do authentication checks if we have a login session. */
        if ($zkauth['username'] != '') {
            /* To start off, set loggedin as TRUE! */
            $this->loggedin = TRUE;

            /* Check if our login session idled out. */
            if (($this->maxidle > 0) && ($zkauth['idleTime'] < time())) {
                $this->idle();
            }

            /* Check if our login session has expired. */
            if (($this->maxlogin > 0) && ($zkauth['expireTime'] < time())) {
                $this->expire();

            }
        }
    }

    /**
     * Return the name of this service.
     *
     * @return string the name of this service
     */
    function getServiceName() {
        return( $this->name );
    }

    /**
     * Set the Zookeeper authentication module being used for this service,
     * replacing the current one (if need be).
     *
     * @param object $mod module to load for this authentication service
     */
    function loadModule( &$mod, $opts ) {
        $this->mod =& $mod;
    }
    
    /**
     * Attempt to login a user.
     *
     * @param string username username with which to authenticate
     * @param string password password with which to authenticate
     */
    function login($username, $password) {
        global $zkauth;

        /* Attempting to login again - log the user out, first. */
        $this->logout();

        /* Use the authentication module to check the user and pass. */
        $result = $this->mod->checkPassword($username, $password);
        if ($result) {
            /* Save the current login information. */
            $zkauth['username'] = $username;
            $zkauth['password'] = $password;

            /* Set the expiration and idling data for this login session. */
            $this->setExpiration();
            $this->setIdling();
        }

        /* Return the password checking result. */
        return ($result);
    }

    /**
     * Logout a user from his current login session.
     */
    function logout() {
        global $zkauth;

        /* Clear the username and password for this login session. */
        $zkauth['username'] = '';
        $zkauth['password'] = '';

        /* Set this login session as no longer logged in. */
        $this->loggedin = FALSE;
    }

    /**
     * Check to see if this user has a valid login session.
     *
     * @return bool indicates if user has a valid login session
     */
    function isLoggedIn() {
        /* Return the result. */
        return ($this->loggedin);
    }

    /**
     * Has this login session been idle too long?
     *
     * @return bool indicates if this login session has idled out
     */
    function isIdled() {
        return ($this->idled);
    }

    /**
     * Has this login session expired?
     *
     * @return bool indicates if this login session has expired
     */
    function isExpired() {
        return ($this->expired);
    }
    
    /**
     * Get the username for this login session.
     *
     * @return string the username for this login session
     */
    function getUsername() {
        global $zkauth;
        return ($zkauth['username']);
    }

    /**
     * Get the password for this login session.
     *
     * @return string the password for this login session
     */
    function getPassword() {
        global $zkauth;
        return ($zkauth['password']);
    }

    /**
     * Set this login session as idled.
     */
    function idle() {
        $this->logout();
        $this->idled = TRUE;
    }
    /**
     * Set this login session as expired.
     */
    function expire() {
        $this->logout();
        $this->expired = TRUE;
    }

    /**
     * Set the idling data for this login session.
     */
    function setIdling() {
        global $zkauth;
        
        /* Set the idleTime and idled to false. */
        $zkauth['idleTime'] = time() + $this->maxidle;
        $this->idled = FALSE;
    }

    /**
     * Set the expiration data for this login session.
     */
    function setExpiration() {
        global $zkauth;

        /* Set the expireTime and expired to false. */
        $zkauth['expireTime'] = time() + $this->maxlogin;
        $this->expired = FALSE;
    }
}

?>
