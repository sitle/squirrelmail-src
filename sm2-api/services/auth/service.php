<?php

/*
 * Squirrelmail2 API
 * Copyright (c) 2001 Th Squirrelmail Foundation
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

    var $name = 'auth';
    var $ver = '$Id$';

    var $serial;
    var $bagname;

    var $zkld;
    var $mod;

    var $username;
    var $password;

    var $maxlogin;
    var $maxidle;
    var $expired;
    var $idled;
    var $expires;
    var $idles;

    var $logged;

    var $connector;

    /** CONSTRUCTOR
     * Create a new ZkSvc_auth with the given module.
     *
     * @param object $module module to use for authentication
     * @param array  $options options to pass to ZkAuthHandler
     */
    function ZkSvc_auth( $options, &$zkld, $serial ) {

        $this->serial = $serial;
        $this->zkld = &$zkld;

        $this->bag_name = $this->name . '_' . $this->serial;

    	if( $options['maxlogin'] == '' )
    	    $this->maxlogin = 300;
    	else
    	    $this->maxlogin = $options['maxlogin'];

    	if( $options['maxidle'] == '' )
    	    $this->maxidle = 15;
    	else
    	    $this->maxidle = $options['maxidle'];

	    if( $options['connector'] == '' )
	        $this->connector = array( );
	    else
	        $this->connector = $options['connector'];

        // Check of registered variables
        if( is_array( $zkld->bag_reg[$this->bag_name] ) ) {
            // There are properties to be loaded, second time constructing
            $this->username = $zkld->bag_reg[$this->bag_name]['username'];
            $this->password = $zkld->bag_reg[$this->bag_name]['password'];
            $this->idled = $zkld->bag_reg[$this->bag_name]['idled'];
            $this->idles = $zkld->bag_reg[$this->bag_name]['idles'];
            $this->expired = $zkld->bag_reg[$this->bag_name]['expired'];
            $this->expires = $zkld->bag_reg[$this->bag_name]['expires'];
            $this->logged = $zkld->bag_reg[$this->bag_name]['logged'];
        } else {
            $zkld->bag_reg[$this->bag_name] = array();
            $this->expired = false;
            $this->idled = false;
    	    $this->activity();
    	    $this->username = '';
    	    $this->password = '';
            $this->logged = FALSE;
            
            $this->Register();
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
     * Replace the Zookeeper authentication module loaded for this service.
     *
     * @param object $module module to load for this authentication service
     */
    function loadModule( &$mod, $options ) {
        $this->mod =&$mod;
    }

    /**
     * Attempt to login a user.
     *
     * @param string username username with which to authenticate
     * @param string password password with which to authenticate
     */
    function login($username, $password) {

        $ret = ( $this->mod->checkPassword($username, $password) );

        if ( $ret ) {
            /* Save the current login information. */
    	    $this->username = $username;
    	    $this->password = $password;
    	    $this->idled = FALSE;
    	    $this->expired = FALSE;
    	    $this->activity();
    	    $this->logged = TRUE;
    	    $this->Register();
        } else {
    	    $this->logout();
        }
        
        return( $ret );
    }

    /**
     * Logout a user from his current login session.
     */
    function logout() {
    	$this->username = '';
    	$this->password = '';
    	$this->expired = TRUE;
    	$this->idled = TRUE;
    	$this->logged = FALSE;
    	$this->Register();
    }

    /**
     * Check to see if this user has a valid login session.
     *
     * @return bool indicates if user has a valid login session
     */
    function checkLogin() {

        return( $this->logged );

    }

    /**
     * Get the username for this login session.
     *
     * @return string the username for this login session
     */
    function getUsername() {
        return( $this->username );
    }

    /**
     * Get the password for this login session.
     *
     * @return string the password for this login session
     */
    function getPassword() {
        return( $this->password );
    }

    /**
     * Has this login session expired?
     *
     * @return bool indicates if this login session has expired
     */
    function loginExpired() {
    	if( !$this->expired ) {
    	    $this->expired = ( $this->expires < time() );
    	    if( $this->expired )
    	        $this->Register();
    	}
        return( $this->expired );
    }

    /**
     * Has this login session been idle too long?
     *
     * @return bool indicates if this login session has idled
     */
    function loginIdled() {

    	if( !$this->idled ) {
    	    $this->idled = ( $this->idles < time() );
    	    if( $this->idled )
    	        $this->Register();
    	}

        return( $this->idled );
    }

    function activity() {

    	if( !$this->expired ) {
    	    $this->expires = time() + $this->maxlogin;
        	if( !$this->idled )
                    $this->idles = time() + $this->maxidle;
            $this->Register();
        }

    }

    function Register() {
    
        $this->zkld->bag_reg[$this->bag_name]['username'] = $this->username;
        $this->zkld->bag_reg[$this->bag_name]['password'] = $this->password;
        $this->zkld->bag_reg[$this->bag_name]['idled'] = $this->idled ;
        $this->zkld->bag_reg[$this->bag_name]['idles'] = $this->idles;
        $this->zkld->bag_reg[$this->bag_name]['expired'] = $this->expired ;
        $this->zkld->bag_reg[$this->bag_name]['expires'] = $this->expires ;
        $this->zkld->bag_reg[$this->bag_name]['logged'] = $this->logged ;
        
        $this->zkld->Register();
    }

}

?>
