<?php

/*
 * Squirrelmail2 API
 * Copyright (c) 2001 PM Squirrelmail Foundation
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkSvc_db
 *
 * The ZkSvc_db class manages database handling
 */
class ZkSvc_db {

    /* Constants */
    var $name = 'db';
    var $ver = '$Id$';

    /* Properties */
    var $serial;     /* what does this variable do? */
    var $bagname;    /* what does this variable do? */

    var $zkld;       /* what does this variable do? */
    var $mod;        /* what does this variable do? */

    var $host;       /* string - hostname of the database backend */
    var $port;       /* int    - port number that the database backend is listening on */
    var $socket;     /* what does this variable do? */
    var $db;         /* what does this variable do? */
    var $username;   /* string - username used to authenitcate with the database backend */
    var $password;   /* string - password used to authenitcate with the database backend */

    var $lk;         /* what does this variable do? */

    var $info;       /* what does this variable do? */
    var $connected;  /* bool   - does a connection to the database backend exist? */

    /**
     * Create a new ZkSvc_db with the given module.
     *
     * @param array  $options options to pass 
     * @param ?      $zkld ?
     * @param ?      $serial ?
     */
    function ZkSvc_db($options, &$zkld, $serial) {

        $this->serial = $serial;
        $this->zkld = &$zkld;

        $this->bag_name = $this->name . '_' . $this->serial;

        // Defaulted properties
        if( $options['host'] == '' )
            $this->host = 'localhost';
        else
            $this->host = $options['host'];
        
        // Simple properties
        $this->port = $options['port'];
        $this->socket = $options['socket'];
        $this->db = $options['db'];
        $this->connected = FALSE;
    }

    /**
     * Return the name of this service.
     *
     * @return string the name of this service
     */
    function getServiceName() {
        return($this->name);
    }

    /**
     * Replace the Zookeeper authentication module loaded for this service.
     *
     * @param object $mod module to load for this authentication service
     * @param array  $options array of options for the module
     */
    function loadModule(&$mod, $options) {
        $this->mod =&$mod;
    }
    
    /**
     * Close existing connection to database backend
     */
    function close() {
        if ($this->connected) {
            $this->mod->close();
            $this->connected = FALSE;
        }
    }

    /**
     * Attempt to connect to database backend if not already connected
     */
    function connect() {
        if (!$this->connected) {
            $host = $this->host;
            if ($this->port != '')
                $host .= ':' . $this->port;
            if ($this->socket != '')
                $host .= ':' . $this->socket;
            if ($result = $this->mod->connect($host, $this->username, $this->password) && $this->db != '') {
                $this->mod->select_db($this->db);
            }
            $this->connected = $result;
        }
        return($this->connected);
    }
    
    /**
     * Select a specific database
     */
    function select_db($db) {
        $this->mod->select_db($db);
    }

    /**
     * Execute a query
     */
    function query($sql){
        return($this->mod->query($sql));
    }

    /**
     * ?
     */
    function html_table($rs, $tt) {
        return($this->mod->html_table($rs, $tt));
    }

    /**
     * Returns a row from the given result set
     */
    function getRow($rs) {
        return($this->mod->getRow($rs));
    }

}

?>
