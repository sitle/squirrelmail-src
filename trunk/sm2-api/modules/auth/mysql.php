<?php

/* modules/auth/mysql.php
 * Squirrelmail2 API
 * Copyright (c) 2001 Philippe Mingo
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkMod_auth_mysql
 *
 */
class ZkMod_auth_mysql {

    var $ver = '$Id$';
    var $name = 'auth/mysql';

    var $srv;	// backward pointer to the service
    var $info;	// cargo
    var $banner; // Server banner
    
    /**
     * Create a new ZkMod_auth_test with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the authentication module
     */
    function ZkMod_auth_mysql( $options, &$srv ) {
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
            $host = $this->srv->connector['host'];
            if ( $this->srv->connector['port'] <> '' ) {
                $host .= ':' . $this->srv->connector['port'];
            }
            if ( $this->srv->connector['sql'] == '' ) {
                $sql = 'SELECT User FROM user WHERE User = \'$username\' and Password = password( \'$password\' )';
            } else {
                // Default SQL strings checks against the default mysql auth db/table
                $sql = $this->srv->connector['sql'];
            }
            $sql = str_replace( '$username', $username, $sql );
            $sql = str_replace( '$password', $password, $sql );
            if ( $this->srv->connector['db'] == '' ) {
                $db = 'mysql';
            }else {
                $db = $this->srv->connector['db'];
            }
            $db = $this->srv->connector['db'];
        	$lk = @mysql_connect( $host, 
        	                      $this->srv->connector['user'], 
        	                      $this->srv->connector['pass'] );
            $this->info = "<b>MySQL Session</b><br>Host = " . $host . 
                                              '<br>User = ' . $this->srv->connector['user'] . 
                                              '<br>Pass = ' . $this->srv->connector['pass'] . '<br>';
            if( $lk ) {
                mysql_select_db( $db, $lk );
                $rs = mysql_query( $sql, $lk );
                // Identifies the user
                if( $ret = mysql_fetch_row( $rs ) ) {
                    mysql_free_result( $rs );
                }
                mysql_close( $lk );
            } else {
                $this->info .= 'DB connection failed';
                $ret = FALSE;
            }

        } else {
            $this->info = 'Connector Missing';
            $ret = FALSE;
        }

        return( $ret );
    }

}

?>