<?php
/**
 * imap.inc
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

class imap
{
    var $logged_in = false,
	$host,
	$port = 143,
	$user,
	$pass,
	$session_id = 1,
	$imap_stream,
	$error = false,
	$message,
	$query,
	$response,
	$methods = array();
	
    
    function imap($host,$port,$user,$pass)
    {
	if (substr($host, 0, 4) != "map:")
	{
    	    $imap_server = $host;
	} 
	else
	{
    	    $function = substr($host, 4);
    	    $imap_server = $function($user);
	}

	$imap_stream = fsockopen ( $imap_server, $port, $error_number, $error_string, 15);
	if ( !$imap_stream )
	{
    	    $imap->error = true;
	    return false;
	}
	$server_info = fgets ($imap_stream, 1024);

	/* Do some error correction */
	if (!$imap_stream)
	{
    	    $this->error = array($error_number => $error_string);
    	    return false;
	}
	$this->imap_stream = $imap_stream;
	$this->user = $user;
	$this->pass = $pass;
    }

    function sqimap_login() {
        global $onetimepad;
    	include_once ('../functions/strings.php');
	/* Decrypt the password */
	$pass = OneTimePadDecrypt($this->pass, $onetimepad);
	$user = ereg_replace('(["\\])', '\\\\1', $this->user);
	$pass = ereg_replace('(["\\])', '\\\\1', $pass);

	$query = 'LOGIN "' . $user .  '" "' . $pass . '"';
	$read = $this->sqimap_run_command ($query);
        if (!$this->error) {
	    $this->logged_in = true;
	    register_shutdown_function(array($this, 'imap_logout'));
	    return true;
	} else { 
	    return false;
	}
    }
        
    function sqimap_logout()
    {
	$this->sqimap_run_command('LOGOUT');
    }
    
    /* internal functions */
    function sqimap_session_id()
    {
        $this->session_id = $this->session_id++;
	return $this->session_id;
    }
    
    function sqimap_fgets()
    {
	$read = '';
	$buffer = 4096;
	$results = '';
	while (strpos($read, "\n") === false)
	{
    	    if (!($read = fgets($this->imap_stream, $buffer))) {
		break;
	    }
    	    $results .= $read;
	}
	return $results;
    }

    function sqimap_read_data_list () {
	$read = '';
	$resultlist = array();
	$data = array();
	$read = $this->sqimap_fgets();
	$sid = 'A'.  $this->session_id;

	while (1) {
    	    switch (true) { 
    	    case preg_match("/^$sid (OK|BAD|NO)(.*)$/", $read, $regs):
    	    case preg_match('/^\* (BYE \[ALERT\])(.*)$/', $read, $regs):
        	$this->response = $regs[1];
        	$this->message = trim($regs[2]);
        	break 2;
    	    case preg_match("/^\* (OK \[PARSE\])(.*)$/", $read):
        	$read = $this->sqimap_fgets();
        	break 1;
    	    case preg_match('/^\* [0-9]+ FETCH.*/', $read):
        	$fetch_data = array();
        	$fetch_data[] = $read;
        	$read = $this->sqimap_fgets();
        	while (!preg_match('/^\* [0-9]+ FETCH.*/', $read) && 
                       !preg_match("/^$sid (OK|BAD|NO)(.*)$/", $read))
		{
            	    $fetch_data[] = $read;
            	    $read = $this->sqimap_fgets();
        	}
        	$resultlist[] = $fetch_data;
        	break 1;
    	    default:
        	$data[] = $read;
        	$read = $this->sqimap_fgets();
        	break 1;
    	    }
	}
	if (!empty($data)) {
    	    $resultlist[] = $data;
	} elseif (empty($resultlist)) {
    	    $resultlist[] = array(); 
	}
	$handle_errors = true;
	
	if ($handle_errors == false) {
    	    return( $resultlist );
	} elseif ($this->response == 'NO' || $this->response == 'BAD') {
	    $this->error = true;
	    // move to M$ exchange extend class !!!!!!!
	    /* ignore this error from M$ exchange, it is not fatal (aka bug) */
    	    if (strstr($this->message, 'command resulted in') === true) {
		$this->error = false;
    	    }
	} else	{
    	    return $resultlist;
	}
    }
    
    function sqimap_fetch($id,$aArgs) {
        require_once(SM_SERVICE_PATH . 'imap/imap_fetch');
        $sid = $this->sqimap_session_id();
	$query = $sid .' UID FETCH ('. implode(' ',$aArgs) .')';
	$this->query = $query;
	fputs ($this->imap_stream, $query . "\r\n");
	$imap_fetch = new imap_fetch;
	$res =& $imap_fetch->parseFetch();
    }

    function sqimap_parseResponse($read) {
        if (preg_match('/^$(OK|BAD|NO)(.*)$/', $read, $regs)) {
	   $this->response = $regs[1];
	   $this->message = $regs[2];
	   return true;
	} else {
	   return false;
	}
    }
        
    function sqimap_run_command_list ($query) {
	$this->query = $query;
	fputs ($this->imap_stream, $sid . ' ' . $query . "\r\n");
	$read = $this->sqimap_read_data_list ();
	return $read;
    }
    
    function sqimap_read_data () {
	$res = $this->sqimap_read_data_list();
	return $res[0];
    }

    function sqimap_run_command ($query) {
    	$this->query = $query;
	$sid = 'A'.$this->sqimap_session_id();	
	fputs ($this->imap_stream, $sid . ' ' . $query . "\r\n");
	$read = $this->sqimap_read_data ();
	return $read;
    }
    
    function get_methods() {
	$methods = array();
	$methods[] = array('list','list mailboxes');
    }
    
    function get_mbxs_list() {
    }
    
    function get_mbxs_subscribed()
    {
    }
    
    function delete_mailbox()
    {
    }
    
    function rename_mailbox()
    {
    }
    
    function create_mailbox()
    {
    }
    
    function flag_message()
    {
    }
    
    function expunge_messages()
    {
    }
    
    function get_message_header()
    {
    }
    
    function get_messages_entity()
    {
    }
}
?>
