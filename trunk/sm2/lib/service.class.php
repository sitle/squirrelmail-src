<?php
/**
 * service.class.php
 *
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Generic services functions.
 * 
 *
 *
 * $Id$
 */

 class Services extends tree {
 	
	function addService($ServiceNode, $visible = false) {
		$this->addNode($ServiceNode, $this->nodes[0]); 
	}
	
//	function existService($id) {
//        } 
 }
 
 class Service extends Node {
 	
	/* Constructor */
 	function Service ($id=0, $acl = false ) {
                if (!$acl) {
                        $acl =& new acl(array('self' => SM_ACL_RO));
                }
		$this->id = $id; /* unique service identifier */
		$this->acl = $acl;
		/* set the sleep notifyer */
		$this->listen['sleep'] = array(&$this, '_sleep');
	}
	
	function Initialize($properties) {
		$this->properties = $properties;
	}
	
	function Connect() {
		if (!isset($this->resource) || !$this->resource) {
			$host = $this->host;
			$port = $this->port;
			$this->resource = fsockopen($host,$port,$errno,$errstr,5);
			if (!$this->resource) {
				//sm_include('Service_error.php');
				//$this->errorHandler($errno, $errstr,SM_ERR_SERVICE_CONNECT);
				unset ($this->resource);
				return false;
			} else {
                            $this->greeting = fgets($this->resource,1024);
                        }
		}
		return true;
	}
	
	function Disconnect() {
		$ret = false;
		if (isset($this->resource) || $this->resource) {
			if (!fclose($this->resource)) {
				include('Service_error.php');
				$this->errorHandler('','',SM_ERR_SERVICE_DISCONNECT);
				$ret = true;
			}
		}
		unset ($this->resource);
		return $ret;
	}
	
	function __sleep() {
		if (isset($this->properties['password'])) {
			unset($this->properties['password']);
		}
		unset($this->resource);
	}
	
	function __wakeup() {
		if (isset($this->properties['enc_password'])) {
			$password = $this->properties['decrypt']($this->properties['enc_password']);
			if ($password) {
				$this->properties['password'] = $password;
			}
		}
	}	
 }
 
?>
