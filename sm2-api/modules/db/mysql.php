<?php

/* modules/db/mysql.php
 * Squirrelmail2 API
 * Copyright (c) 2001 Philippe Mingo
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkMod_db_mysql
 *
 */
class ZkMod_db_mysql {

    var $ver = '$Id$';
    var $name = 'db/mysql';

    var $srv;	// backward pointer to the service
    var $info;	// cargo
    
    var $lk;
    
    /**
     * Create a new ZkMod_auth_test with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the authentication module
     */
    function ZkMod_db_mysql( $options, &$srv ) {
        $this->srv = &$srv;
    }

    function connect( $host, $username, $password ) {
    
        if( $this->lk )
            $this->close();    
            
        $this->lk = @mysql_connect( $host, $username, $password ) or 
                    die( 'DB server not connected.');
        
        return( $this->lk );
    }

    function select_db( $db ) {
        
        if ( $this->lk ) {       
            $ret = mysql_select_db( $db, $this->lk ) or die( "$db DB not selected." );
        } else {
            $ret = FALSE;
        }
        return( $ret );
        
    }
    
    function close() {
    
        mysql_close( $this->lk );
        $this->lk = FALSE;
    
    }
    
    function query( $sql ) {
 
        $ret = mysql_query( $sql, $this->lk ) or die('La cagó');
        
        return( $ret );
        
    }
    
    function html_table( $rs ) {
    
        $i = 0;
        $ret = '<table><tr>';
        while( $fn = @mysql_field_name ( $rs, $i++ ) ) {
            $ret .= "<th bgcolor=#e0e0e0>$fn</th>";
        }
        $ret .= '</tr>';
        $i = 0;
        while( $rw = mysql_fetch_row( $rs ) ) {
            if ( $i % 2 )
                $clr = '#f0f0f0';
            else
                $clr = '#ffffff';            
            $ret .= "<tr bgcolor=$clr>";
            foreach( $rw as $fl ) {
                $ret .= "<td>$fl</td>";
            }
            $ret .= '</tr>';
            $i++;
        }
        $ret .= '</table>';
    
        return( $ret );
    
    }
    
}

?>