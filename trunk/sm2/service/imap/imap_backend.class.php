<?php
/**
 * imap_backend.class.php
 *
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Contains functions the handle the imap backend
 *
 * $Id$
 */



class method {
    var     $parser,
            $pre = false,
            $post = false,
            $redirect = false, /* stream or 'echo ' */
            $free_after_redirect = true;
    /* constructor */
    function method($parser) {
        $this->parser =& $parser;
    }

    function redirectResult(&$iBuffer) {
        if (is_resource($this->redirect)) {
            $stream = $this->redirect;
            fputs($stream,$iBuffer);
        } elseif ($this->redirect == 'echo') {
            ob_start();
            echo $data;
            ob_end_flush();
        }
        if ($this->free_after_redirect) {
            $data = '';
        }
    }
    /*
     * Hook function for parsing
      */
    function parser(&$sRead,&$i,&$iCnt, $sValue='') {
        /* Note: vars need to be passed by reference */
        return call_user_func_array($this->parser,array(&$sRead,&$i,&$iCnt, $sValue));
    }
}

class imap_backend extends parser {
    var    $logged_in = false,
        $host, $port = 143, $user, $pass,
        $session_id = 1,
        $resource,
        $selected_mailbox = '',
        $error = false,
        $message,
        $response='',
        $methods = array(),
        $capability, $sm_capability,
        $iTot_cnt = 0,
        $fetchMapping = false,
        $alerts = array(),
        $stack = array(); /* stack for serverresponses */

    function print_var($var) {
        echo "\n<pre>\n";
        print_r($var);
        echo "</pre><br>";
    }


    /**
     * @func      imap_backend
     * @desc      constructor 
     * @param     stream     $resource       resource stream to imap connection
     * @access    public
     * @author    Marc Groot Koerkamp
     */          
    function imap_backend($resource) {
    $this->stack['OK'] = array();
    $this->stack['NO'] = array();
    $this->stack['BAD'] = array();
    $this->stack['SERVER'] = array();

    $this->initHooks();
       
        $this->resource =& $resource;
    }
 
    /**
     * @func      capability
     * @desc      process the capability string
     * @param     arr        $aSmCapability  array with caps provided by config
     * @return    arr        $aCaps          capabilities overided by smcapability
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function capability($aSmCapability = array()) {
         $sTag = $this->sqimap_run_command('CAPABILITY');
         $aResult = $this->_sqimap_process_stream($sTag);
         $aCaps = array('LITERAL+' => false, 'CHILDREN' => false, 'NAMESPACE' => false);
         if ($aResult['RESPONSE'] == 'OK') {
             foreach($aResult['CAPABILITY'] as $value) {
                 $aCaps[$value] = true;
             }
         }
         // override capability and add internal capabilities
         foreach($aSmCapability as $key => $value) {
             $aCaps[$key] = $value;
         }
         $this->capability = $aCaps;
         return $aCaps;
    }

        
    function login($username, $password, $host, $sm_caps=array(), &$errno, &$errmsg) {
        if (!isset($this->capability)) {
             $aCaps = $this->capability($sm_caps);
        } else {
             $aCaps = $this->capability;
        }
        /* supported authentication methods */
        $aAuth = array('CRAM-MD5' =>false,'DIGEST-MD5'=>false); //,'PLAIN'=>false);
        $aResult = false;
        /* get the authentication methods */
        foreach ($aCaps as $key => $value) {
            if (substr($key,0,5) == 'AUTH=') {
                $aAuth[substr($key,5)] = true;
            }
        }
        /* start with the save authentication methods */
        switch ($aAuth)
        {
        case $aAuth['DIGEST-MD5']:
            $query = "AUTHENTICATE DIGEST-MD5";
            $sTag = $this->sqimap_run_command($query);
            /* we expect a command continuation request with challenge*/
            $aRes = explode(' ',$this->_sqimap_process_stream($sTag));
            $challenge = $aRes[0];
            /* create the digest-md5 response */
            $reply = digest_md5_response($username,$password,$challenge,'imap',$host);
            fputs($this->resource,$reply);
            /* we expect a command continuation request */
            $this->_sqimap_process_stream($sTag);
            /* need testing !! probably just check for the OK response */
            $aresult = $this->_sqimap_process_stream($sTag);
            if ($aResult['RESPONSE'] == 'OK') {
                break;
            } 
        case $aAuth['CRAM-MD5']:
            $query = "AUTHENTICATE CRAM-MD5";
            $sTag = $this->sqimap_run_command($query);
            /* we expect a command continuation request with challenge*/
            $aRes = explode(' ',$this->_sqimap_process_stream($sTag));
            $challenge = $aRes[0];  
            /* create the cram-md5 reply */
            $reply = cram_md5_response($username,$password,$challenge,'imap',$host);
            fputs($this->resource,$reply);
            /* need testing !! probably just check for the OK response */
            $aResult = $this->_sqimap_process_stream($sTag);
            if ($aResult['RESPONSE'] == 'OK') {
                break;
            }
        //case $aAuth['PLAIN']:
        default:
            if (isset($aCaps['LOGINDISABLED']) && $aCaps['LOGINDISABLED']) {
                /* we need to do a STARTTLS which isn't supported on a normal socket */
            
                /* error handling */
            
                $errmsg = _("Notify your system administrator, no authentication mechanism found");
                $errno = 0; /* TODO fatal error */
            
                return false;
            } else {
                /* use normal login */
            
                /* escaping  ??? */
            $user = ereg_replace('(["\\])', '\\\\1', $username);
            $pass = ereg_replace('(["\\])', '\\\\1', $password);

            $query = 'LOGIN "' . $user .  '" "' . $pass . '"';
            $sTag = $this->sqimap_run_command($query);
            $aResult = $this->_sqimap_process_stream($sTag);
        }          
            break;
        }
        if ($aResult['RESPONSE'] == 'OK') {
            return $aResult;
        } else {
            /* error handling */
            if ($aResult) {
                $errmsg = $aResult['MESSAGE'];
                /* optional, check response for NO, BAD or BYE */
                $errno = 0; /* TODO */
            } else {
                /* something went terrible wrong, notify the user */

                /* TODO */
            
               /* optionally try to initiate a tls connection if PHP version > 4.3 */
               /* reconnect and call $this->login again from here and return the result */
            }
        }
        return false;
    }


    function logout() {
        return $this->_sqimap_process_stream($this->sqimap_run_command('LOGOUT'));
    }

    /**
     * @func      getMailboxList
     * @desc      Retrieve an array with mailboxes catagorised per namespace 
     * @param     str        $type         LSUB or LIST
     * @param     arr        $aNamespace   array with namespaces, normally we have the following 
     *                                     namespaces: Personal, Other Users and Shared. The keys
     *                                     I use are 'personal', 'otherusers' and 'shared'. Each
     *                                     namespace can contain muliple definitions. A definition is
     *                                     in the form  
     *                                     array('namespace' => folderprefix, 
     *                                           'delimiter' => hierarchieseparator).
     *                                     By providing your own namespace, the system namespace is overrided.
     * @param     arr        $aProperties  array with params to manipulate default behaviour.
     *                                     expand => retrieve the whole list instead of 1 level deep
     *                                     haschildren => retrieve children information
     *                                     verifyflags => in case of LSUB retrieve the flags with
     *                                     a LIST call
     *
     * @return    arr        $aResults     array with namespaces and their mailboxes.
     * @access    public
     * @author    Marc Groot Koerkamp
     */


    // $aNamespace: array with prefixes;
    function getMailboxList($type,&$aNamespace,$aProps =
             array('expand' => false, 'haschildren' => true, 'verifyflags' => true,'prefix' => '')) {
        $type = strtoupper ($type);
        if (isset($aProps['prefix'])) {
            $sPrefix = $aProps['prefix'];
        } else {
            $sPrefix = '';
        }
        if (!isset($aProps['haschildren'])) {
            $aProps['haschildren'] = false;
        }

        if (!count($aNamespace)) {
           /* no namespace suplied, try to get one */
           if ($this->capability['NAMESPACE']) {
               $aNamespace = $this->_namespace();
           } else {
               $aNamespace = array(array('namespace' => ''), false, false); /* we do not use the delimiter */
           }
        }
        //print_r($aNamespace);
        $aTags = array();
        if (!$aProps['expand']) {
           $sSearch = '%%';
        } else {
           $sSearch = '*%';
        }
        $aResults = array();
        if (isset($aNamespace['personal'])) {
            foreach ($aNamespace['personal'] as $key => $definition) {
                if (substr(strtoupper($definition['namespace']),0,5) == 'INBOX' &&
                    substr($definition['namespace'],-1) == $definition['delimiter']) {
                    /* remove the delimiter in order to achieve that INBOX is displayed */
//                    $aNamespace['personal'][$key]['namespace'] = substr($definition['namespace'],0,-1);
                }
            }
        }
        /* pipelined server requests for LSUB OR LIST */
        foreach ($aNamespace as $key => $aSubNamespace) {
            if ($aSubNamespace) {
                foreach ($aSubNamespace as $aReference) {
                    $query = strtoupper($type).' ' . $this->_getString($aReference['namespace']) . " \"$sSearch\"";
                    /* store the tags temporarely in the result */
                    $aResults[$key][$aReference['namespace']] = $this->sqimap_run_command($query);
                }
            }
        }
        /* retrieve the results */
        foreach ($aResults as $key => $value) {
            $i = 0;
            foreach($value as $sReference => $sTag) {
                $aRes = $this->_sqimap_process_stream($sTag);
                if ($aRes['RESPONSE'] == 'OK' && isset($aRes[$type])) {
                    $aResults[$key][$sReference] = $aRes[$type];
                    if ($sReference == 'INBOX.') {
                         $aResults[$key][$sReference]['INBOX'] =
                            array('flags' => array(),'delimiter' => $aNamespace[$key][$i]['delimiter']);
                    }
                } else {
                   $aResults[$key][$sReference] = false;
                }
                ++$i;
            }
        }
        if ($aProps['verifyflags'] && $type = 'LSUB') {
            foreach ($aResults as $key => $value) {
                foreach($value as $sReference => $aList) {
                    if ($aList) {
                        foreach($aList as $sMbx => $aProperties) {
                            $query = 'LIST ' . $this->_getString($sPrefix) . " \"$sMbx\"";
                            $aResults[$key][$sReference][$sMbx]['TAG'] = $this->sqimap_run_command($query);
                        }
                    }
                }
            }
            foreach ($aResults as $key => $value) {
                foreach($value as $sReference => $aList) {
                    if ($aList) {
                        foreach($aList as $sMbx => $aProperties ) {
                            $aRes = $this->_sqimap_process_stream($aProperties['TAG']);
                            if ($aRes['RESPONSE'] == 'OK') {
                                if (isset($aRes['LIST'][$sMbx])) {
                                    $aResults[$key][$sReference][$sMbx]['flags'] = $aRes['LIST'][$sMbx]['flags'];
                                } else {
                                    $aResults[$key][$sReference][$sMbx]['flags'][] = '\\nonexistent';
                                }
                            } else {
                                $aResults[$key][$sReference][$sMbx]['flags'][] = '\\nonexistent';
                            }
                            unset($aResults[$key][$sReference][$sMbx]['TAG']);
                        }
                    }
                }
            }
        }
        print_r($aProps);
        if ($aProps['haschildren'] && !$this->capability['CHILDREN']) {
            foreach ($aResults as $key => $value) {
                foreach($value as $sReference => $aList) {
                    if ($aList) {
                        foreach($aList as $sMbx => $aProperties) {
                            $sDelimiter = $aProperties['delimiter'];
                            if (substr($sMbx,-1) != $sDelimiter) {
                               $sPrefix = $sMbx . $sDelimiter;
                            } else {
                               $sPrefix = $sMbx;
                            }
                            $query = strtoupper($type).' ' . $this->_getString($sPrefix) . ' "%"';
                            $aResults[$key][$sReference][$sMbx]['TAG'] = $this->sqimap_run_command($query);
                        }
                    }
                }
            }
            foreach ($aResults as $key => $value) {
                foreach($value as $sReference => $aList) {
                    if ($aList) {
                        foreach($aList as $sMbx => $aProperties ) {
                            $aRes = $this->_sqimap_process_stream($aProperties['TAG']);
                            if ($aRes['RESPONSE'] == 'OK') {
                                if (count($aRes[strtoupper($type)])) {
                                    $aResults[$key][$sReference][$sMbx]['flags'][] = '\\HasChildren';
                                }
                            }
                            unset($aResults[$key][$sReference][$sMbx]['TAG']);
                        }
                    }
                }
            }
        }
        //echo 'RESULT:<br>';
        //print_r($aResults);
        return $aResults;
    }

    /**
     * @func      _parseLiteral
     * @desc      retrieve literal string
     * @param     str        $sRead          imap response string
     * @return    int        $i              offset inside $sRead
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function _parseLiteral(&$sRead, &$i) {
        ++$i;
        $iLiteral = (int) substr($sRead,$i,strpos($sRead,'}',$i) - $i);
        $sRead = '';
        $s = ($lit_cnt ? $this->sqimap_fread($iLiteral) : '');
        $i = 0;
        $this->sqimap_fgets();
        return $s;
    }

    
    /**
     * @func      _getString
     * @desc      prepare a string ready for an imap query 
     * @param     str        $string         string to prepare
     * @return    str        $string         quoted or literal string
     * @access    private
     * @author    Marc Groot Koerkamp
     */          
    function _getString($string) {
    // alex ["\\\\\r\n\x80-\xff]
        if (preg_match('/[\\\\\r\n"0x00-0x7f]/', $string)) { /* check for literal string */
            $iLitLength = strlen($string);
            $sLitString = "\{$iLitLength";
            if ($this->capability['LITERAL+']) {
               $sLitString .= "+}\r\n";
            } else {
               $sLitString .="}\r\n";
            }
            $string = $sLitString . $string;
        } else {
            $string = '"' . $string . '"';
        }
        return $string;
    }
    
    /**
     * @func      namespace
     * @desc      receive the namespace 
     * @return    arr|bool       $aResult|false          namespace|false on error
     * @access    private
     * @author    Marc Groot Koerkamp
     */          
    function _namespace() {
         $sTag = $this->sqimap_run_command('NAMESPACE');
         $aResult = $this->_sqimap_process_stream($sTag);
         if ($aResult['RESPONSE'] == 'OK') {
             return $aResult['NAMESPACE'];
         }
         return false;
    }
    
    function getValidCommandArguments($sCommand) {
        switch ($sCommand)
        {
        case 'SEARCH':
              return array('BEFORE', 'ON', 'SINCE','SENTBEFORE','SENTON',
                                                'SENTSINCE','KEYWORD','UNKEYWORD','ANSWERED',
                                                'DELETED','DRAFT','RECENT','SEEN','UNANSWERED',
                                                'UNDELETED','UNDRAFT','UNFLAGGED','UNRECENT','UNSEEN',
                                                'NEW','OLD','ALL','SMALLER','LARGER','UID','SET','OR',
                                                'NOT','BODY','TEXT','TO','CC','BCC','FROM','SUBJECT',
                                                'HEADER');
                case 'SORT':
                        return array('ARRIVAL','CC','DATE','FROM','REVERSE','SIZE',
                                                'SUBJECT','TO');
                default:
                        return false;
                }
        }

    function __sleep() {
        unset($this->imapstream);
        $this->selected_mailbox = false;
        $this->fetchMapping = false;
        $this->alerts = array();
        $this->stack = array();
    }

    function __wakeup() {
    }
    // obsolete
    /*
     * register section for default methods
     * By manipilating the methods array  you can customize the behaviour of this class
     */
    function initHooks() {

        /* fetch methods  (kind of hooks) */
        //$function = &$this->fetchArgument;
//    $fetch_method = new method('fetchArgument', $this);
        $fetch_method = new method(array(&$this,'fetchArgument'));
        $this->parserFunction['FETCH']['BODYPART'] = $fetch_method;
        $this->parserFunction['FETCH']['INTERNALDATE'] = $fetch_method;
        $this->parserFunction['FETCH']['RFC822'] = $fetch_method;
        $this->parserFunction['FETCH']['RFC822.TEXT'] = $fetch_method;
//    $fetch_method = new method('parseInteger',$this);
        $fetch_method = new method(array(&$this,'parseInteger'));
                        $this->parserFunction['FETCH']['UID'] = $fetch_method;
        $this->parserFunction['FETCH']['RFC822.SIZE'] = $fetch_method;
//    $fetch_method = new method('parseEnvelope',$this);
//    $this->parserFunction['FETCH']['ENVELOPE'] = $fetch_method;
                        $fetch_method = new method(array(&$this,'parseEnvelope'));
        $this->parserFunction['FETCH']['ENVELOPE'] = $fetch_method;
                        $fetch_method = new method(array(&$this,'parseStructure'));
        $this->parserFunction['FETCH']['BODYSTRUCTURE'] = $fetch_method;
        $fetch_method = new method(array(&$this,'parseFlags') );
        $this->parserFunction['FETCH']['FLAGS'] = $fetch_method;
                        $fetch_method = new method(array(&$this,'parseMimeHeader') );
        $this->parserFunction['FETCH']['MIME'] = $fetch_method;
                        $fetch_method = new method(array(&$this,'parseRfc822Header') );
        $this->parserFunction['FETCH']['HEADER'] = $fetch_method;

        /*
        * example for method->pre:
        * 1: a function that echo's headers for downloading bodyparts
        * 2: set script timeout to 0 for large attachments
        *
        * example for method->post:
        * 1: charset decoding
        * 2: base64 encoding
        *
        * example for redirect:
        * 1: redirect to ecreen for immediate display instead of buffering
        * 2: copy messages between servers
        */
    }
        
    /* move to messages class */
    function sqimap_getHeaderList($oMailbox, $aFields, $sSort, $bDir,$iOffset, $iCnt) {
        $this->sqimap_select($oMailbox);
        $cacheInfo = $oMailbox->getCacheInfo;
        if ($cacheInfo['UIDVALIDITY'] != $oMailbox->uidvalidity) {
        /* cache is invalid */

        } elseif ($cacheInfo['EXISTS'] != $oMailbox->exists &&
            $cacheInfo['UIDNEXT'] != $oMailbox->uidnext) {
            /* update flags, uid's and retrieve the new headers */
            $this->sqimap_fetch("1:".$cacheInfo['UIDNEXT']-1,array(UID,FLAGS));
            $this->sqimap_fetch($oMailbox->uidnext.':*',array('FLAGS', 'BODY[HEADER.FIELDS ('.explode($aFields,' ').')]'));
        } elseif ($cacheInfo['EXISTS'] != $oMailbox->exists) {
            /* update the f;ags and the uid's */
        } else {
            /* cache is valid */
        }
    }

    function sqimap_getSortedList($aSortFields, $since) {
        if ($this->capabilities['SORT']) {
            sqimap_sort($aSortField, $charset);
        }
    }
    /****************************************************************************
    *    internal functions                             *
    ***************************************************************************/
        // obsolete, needs rewrite
    function sqimap_run_command ($query) {
        $sTag = 'SM'. str_pad($this->session_id++, 3, '0', STR_PAD_LEFT);
        $this->stack[$sTag]['RESPONSE'] = false;
        $this->stack[$sTag]['OK'] = false;
        $this->stack[$sTag]['NO'] = false;
        $this->stack[$sTag]['BAD'] = false;
        //$this->print_var($query);
        if (!is_array($query)) {
            fputs ($this->resource, $sTag . ' ' . $query . "\r\n");
        } else {
            $query[0][0] = $sTag . ' ' . $query[0][0];
            for ($i=0,$iCnt=count($query);$i<$iCnt;++$i) {
                $line = $query[$i];
                while (true) {
                    if (!$query[$i][1]) {
                        if ($i<($iCnt-1)) {
                            $query[$i+1][0] = $query[$i][0] . $query[$i+1][0];
                            ++$i;
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                }
//                echo $query[$i][0] .'<BR>';
                //exit;
                fputs ($this->resource, $query[$i][0] . "\r\n");
                if ($query[$i][1] && !$this->capabilities['LITERAL+']) {
                    //$this->print_var($this->stack[$sTag]);
                    if (!$this->getCommandContinuationRequest($sTag)) {
                        $this->error = true;
                        return false;
                    }
                }
            }
        }
//        echo "<b>$sTag</b><BR>";
//        $this->print_var($this->stack[$sTag]);
        return $sTag;
    }

    function getCommandContinuationRequest($sTag) {
        if ($this->_sqimap_process_stream($sTag) === true) {
            return true;
    } else {
            return false;
        }
    }

        function getmicrotime(){
                list($usec, $sec) = explode(" ",microtime());
                return ((float)$usec + (float)$sec);
        }

        
    function sqimap_fgets() {
        $resource = $this->resource;
        $read = '';
        $buffer = 4096;
        $results = '';
        $offset = 0;
        while (strrpos($results, "\n") === false) {
            if (!($read = fgets($resource, $buffer))) {
            /* this happens in case of an error */
            /* reset $results because it's useless */
            $results = false;
                break;
            }
            if ( $results != '' ) {
                $offset = strlen($results) - 1;
            }
            $results .= $read;
        }
        //echo $results;
        return $results;
    }
    // check how sm 1.5 fread can be used here
    function sqimap_fread($iCnt) {
        $buffer = 4096;
        $s = '';
        $resource = $this->resource;
        $i = 0;
        while ($iRet < ($iCnt - ($i * $buffer))) {
            $sRead = fread($resource,$buffer);
            if (!$sRead) {
               return false;
            }
            ++$i;
            if (isset($this->streamfilter)) {
                 $this->streamfilter($sRead); // not tested.
            }
            $s .= $read;
        }
        $buffer = $iCnt - ($i * $buffer);
        $sRead = fread($resource,$buffer);
        ++$i;
        if (isset($this->streamfilter)) {
             call_user_func_array($this->streamfilter,&$sRead); // not tested
        }
        $s .= $sRead;
        return $read;
    }

    /* needs persistor class */
    /* currently for testing only*/
    function dumpResult($sTag, &$aResult) {
        if (isset($this->stack[$sTag]['cached'])) {
            $mode = 'a';
        } else {
            $mode = 'w';
        }
        if (isset($aResult['FETCH'])) {
            $fp = fopen("/tmp/".$sTag."_FETCH.cache",$mode);
            foreach ($aResult['FETCH'] as $k => $v) {
                $sLine = "$k = ". serialize($v)."\n";
                fputs($fp,$sLine);
            }
        } elseif(isset($aResult['LIST'])) {
            $fp = fopen("/tmp/".$sTag."_LIST.cache",$mode);
            foreach ($aResult['LIST'] as $k => $v) {
                $sLine = "$k = ". serialize($v)."\n";
                fputs($fp,$sLine);
            }
        }
        fclose($fp);
        $this->iTot_cnt = 0;
        $this->stack[$sTag]['cached'] = true;
        return true;
    }
        // get rid of the sqimap names !

    /**
     * @func      _sqimap_process_stream
     * @desc      process the returned imap response
     * @param     str        $sTag           issued Tag
     * @return    arr        $aResult
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function _sqimap_process_stream($sTag) {
        $sArg = $aVal = '';
        $key = false;
        $aResult = $aResultList = array();
        $this->iTot_Cnt = 0;
        do {
            $sRead = $this->sqimap_fgets();
                        $i = 0;
                        //echo $sRead;
            $cChar = $sRead{$i};
                        //echo "char = $cChar, $i <BR>";
            switch (true)
            {
            case $cChar == '+':
                return substr($sRead,2);
            case $cChar == '*': // untagged response
                $i+=2;
                // $sCommand, $i, $key returned by reference.
                // $key is used in case of multiple responses i.e. uid's with FETCH
                $res = $this->_parseUnTagged($sRead, $i, $sCommand, $key);
                if ($key) {
                    $aResult[$sCommand][$key] = $res;
                } else {
                    $aResult[$sCommand] = $res;
                }
                if (isset($res['BYE'])) {
                    break 2;
                }
                break;
            default:
                $sArg = $this->parseString($sRead,array(' '),$i);
                if (in_array($sArg,array_keys($this->stack))) {
                    ++$i;
                    if ($this->_parseTagged($sRead,$i,$sTag,$sResponse,$vMessage)) {
                        $this->stack[$sTag] = $aResult;
                        //print_r($aResult);
                        $this->stack[$sTag]['MESSAGE'] = $vMessage;
                        $this->stack[$sTag]['RESPONSE'] = $sResponse;
                        //echo $this->stack[$sTag]['RESPONSE'];
//                        $this->stack[$sTag]['SERVER'] = $this->stack['SERVER'];
                        $this->stack['OK'] =
                        $this->stack['NO'] =
                        $this->stack['BAD'] =
                        $this->stack['SERVER'] =
                        $aResult = array();
                        if ($sTag == $sArg) {
                            break 2;
                        }
                    } else {
                        /* error, no NO|OK|BAD response */
                    }
                } else {
                    /* error handling Tag not known*/
                    break 2;
                }
                break;
            }
        } while (!$this->stack[$sTag]['RESPONSE']);
        // retrieve result from stack and return it.
        $result = $this->stack[$sTag];
        //print_r($result);
        // remove result from stack
        unset($this->stack[$sTag]);
        return $result;
    }

    /**
     * @func      _parseUntagged
     * @desc      process untagged imap responses
     * @param     str        $sRead           string from imap buffer
     * @param     str        $i               string offset in $sRead
     * @param     str        $sCommand        issued imap command.
     * @param     str        $key             optional key for storing the info in an array
     * @return    arr        ----             result
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function _parseUnTagged($sRead, &$i, &$sCommand, &$key) {
        $sCommand = $this->parseString($sRead,array(' ',"\n"),$i);
        if (is_numeric($sCommand)) { // FETCH responses start with integer
            $iValue = $sCommand;
            ++$i;
            $sCommand = $this->parseString($sRead,array(' ',"\n"),$i);
        } else {
            $iValue = false;
        }
        switch ($sCommand)
        {
        case 'RECENT':
        case 'EXISTS':
        case 'EXPUNGE':
            return $iValue;
        case 'OK':
        case 'NO':
        case 'BAD':
        case 'PREAUTH':
        case 'BYE':
            if ($sRead{$i} == ' ') {
                ++$i;
                if ($sRead{$i} == '[') {
                    $sServerResponse = $this->parseBracket($sRead,$i);
                    return $this->_parseServerResponse($sServerResponse);
                } else {
                    return trim(substr($sRead,$i));
                }
            }
            return true;
        case 'CAPABILITY':
            ++$i;
            return $this->_parseCapability($sRead,$i);
        case 'LIST':
        case 'LSUB':
            ++$i;
            return $this->_parseList($sRead,$i,$key);
        case 'STATUS':
            ++$i;
            return $this->_parseStatus($sRead,$i);
        case 'SEARCH':
            ++$i;
            return $this->_parseUidList($sRead,$i);
        case 'FLAGS':
            ++$i;
            return $this->_parseFlags($sRead,$i);
        case 'FETCH':
            ++$i;
            return $this->_parseFetch($sRead,$i, $iValue);
                case 'NAMESPACE':
                        ++$i;
                        return $this->_parseNamespace($sRead,$i, $iValue);
        default:
            if ($this->installedExtensions[$sCommand]) {
                return $this->installedExtensions[$sCommand]($sRead,$i);
            }
            return false;
        }
    }

    /**
     * @func      _parseTagged
     * @desc      process tagged imap responses (OK BAD NO)
     * @param     str        $sRead           string from imap buffer
     * @param     str        $i               string offset in $sRead
     * @param     str        $sTag            tag where response is part of
     * @param     str        $sResponse       OK || BAD || NO
     * @param     var        $vMessage        Servermessage || Servermessage with extra response code
     * @return    bool       ----             succes
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function _parseTagged(&$sRead, &$i, $sTag, &$sResponse, &$vMessage) {
        $sArg = $this->parseString($sRead,array(' ',"\n"),$i);
        switch ($sArg)
        {
        case 'OK':
        case 'BAD':
        case 'NO':
                        $sResponse = $sArg;
              if ($sRead{$i} == ' ') {
                ++$i;
                if ($sRead{$i} == '[') {
                    $sServerResponse = $this->parseEnclosed($sRead,$i,'[',']');
                    $vMessage = $this->_parseServerResponse($sServerResponse);
                } else {
                    $vMessage = trim(substr($sRead,$i));
                }
            }
            return true;
        default: return false;
        }
    }

    /**
     * @func      _parseServerResponse
     * @desc      process server response
     * @param     str        $sResponse       string to process
     * @return    var       ----              string or array in case of optional response code
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function _parseServerResponse($sResponse) {
        if ($iPos=strpos($sResponse,' ')) {
            $sValue = trim(substr($sResponse,$iPos+1));
            $sResponse = substr($sResponse,0,$iPos);
            switch ($sArgument{0})
            {
            case '(':
                $sValue = explode(' ',substr($sArgument,1,-1));
                break;
            default: break;
            }
        } else {
            $sValue = '';
            $sResponse = $sResponse;
        }
        return array($sResponse => $sValue);
        // optional extra imap response code
        switch ($sResponse)
        {
        case 'ALERT':          /* imap object ($this) */
            $this->alerts[] = $sArgument; /* catch all alerts */
            break;
        case 'NEWNAME':        /* SELECT / EXAMINE (string)        */
        case 'PARSE':          /* RFC822 Header parsing         */
        case 'TRYCREATE':      /* COPY / APPEND (string)        */
            break;             /* not used right now                 */
        case 'PERMANENTFLAGS': /* mailbox related (array)        */
        case 'READ-ONLY':      /* mailbox related             */
        case 'READ-WRITE':     /* mailbox related             */
        case 'UIDVALIDITY':    /* mailbox related (int)        */
        case 'UNSEEN':         /* mailbox related (int)        */
        case 'UIDNEXT':        /* mailbox related (int) not rfc2060     */
            $this->stack['SERVER'][$sResponse] = $sArgument;
            break;
        default:
            break;
        }
        return array($sArgument, $sArgument);
    }

    function parseFlags(&$sRead, &$i, &$iCnt) {
            return explode($this->parseEnclosed($sRead,$i,'(',')'));
    }

    function parseUidList(&$sRead, &$i, &$iCnt) {
            return explode(' ',substr($sRead,$i,-1)); /* strip \n */
    }

    /**
     * @func      _parseCapability
     * @desc      process capability string
     * @param     str        $sRead       string to process
     * @param     int        $i           string offset
     * @return    arr        ----         array with capability elements
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function _parseCapability($sRead,&$i) {
            return explode(' ',substr($sRead,$i,-1)); /* strip \n */
    }



    /**
     * @func      _parseNamespace
     * @desc      process namespace string
     * @param     str        $sRead       string to process
     * @param     int        $i           string offset
     * @return    arr        ----         array with the found namespace
     * @access    private
     * @author    Marc Groot Koerkamp
     */
     // example response:
     // namespace ex1 (("INBOX." ".")) (("user." " ")) (("#shared/" "/") ("#news." "."))
     //           ex2 (("INBOX." ".")) NIL NIL
    function _parseNameSpace($sRead,&$i) {
        $aResults = array(false,false,false);
        $j = 0;
        $iLen = strlen($sRead);
        while ($i<$iLen) {
            $cChar = $sRead{$i}; /* set cChar otherwise the switch statement can handle a changed $i */
            switch ($cChar)
            {
            case '(': // we reach a namespace
                ++$i;
                $aNamespace = array();
                while ($sRead{$i} != ')') { // get the defined namespaces
                    $cChar = $sRead{$i};
                    switch ($cChar)
                    {
                    case '(':
                         ++$i;
                         $iEndNamespace = strpos($sRead,')',$i);
                         $sNamespace = substr($sRead,$i,$iEndNamespace-$i);
                         if (preg_match('/^\"(.+)\"\s\"(.{1})\"$/',$sNamespace,$aMatch)) {
                             $aNamespace[] = array('namespace' => $aMatch[1], 'delimiter' => $aMatch[2]);
                         }
                         $i = $iEndNamespace+1;
                         break;
                    default:
                         ++$i;
                         break;
                    }
                }
                $i = strpos($sRead,')',$i)+1;
                if (count($aNamespace)) {
                    $aResults[$j] = $aNamespace;
                }
                ++$j;
                break;
            case 'n':
            case 'N':
                $aResults[] = false; // no namespace defined
                $i+=3;
                ++$j;
            default:
                ++$i;
            }
        }
        return array('personal' => $aResults[0], 'otherusers' => $aResults[1], 'shared' =>$aResults[2]);
    }

    /**
     * @func      _parseList
     * @desc      process LIST response
     * @param     str        $sRead       string to process
     * @param     int        $i           string offset
     * @param     str        $sMbx        found mailbox name, used as array key at a later stage
     * @return    arr        $aResult         array with the found namespace
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function _parseList($sRead, &$i, &$sMbx) {
       $aResult = array();
       /* get the flags in lowercase */
       $aFlags = explode(' ',strtolower($this->parseEnclosed(&$sRead, &$i, '(', ')')));
       ++$i;
       //get the delimiter (NIL or "char")
       $sDelimiter = substr($sRead,$i,3);
       if (strtoupper($sDelimiter) === 'NIL') {
          $delimiter = false;
       } else {
          $delimiter = $sDelimiter{1};
       }
       $i +=4;
       $sMbx = false;
       while (!$sMbx) {
           switch($sRead{$i})
           {
           case '"': $sMbx = $this->parseQuote($sRead,$i); break;
           case '{': $sMbx = $this->_parseLiteral($sRead,$i); break;
           case ' ': ++$i; break;
           default: $sMbx = substr($sRead,$i,-2); break;// strip off \r\n
           }
       }
       $i = strlen($sRead);
       return array('flags' => $aFlags, 'delimiter' => $delimiter);
    }

    function processStatus($s, &$aResult) {
        if ($iPos=$strpos($s,' ')) {
            $sArg=substr($s,0,$iPos);
            $cChar = $s{$iPos+1};
            switch(true)
            {
            case $cChar='(':
                $aResult[$sArg]=explode(' ',substr($s,$iPos+2,-1));
                break;
            default:
                $aResult[$sArg]=substr($s,$iPos+1);
                break;
            }
        } else {
            $aResult[$s]=true;
        }
    }

    /*
     * Process the untagged responses
     */

    function processIntegerArguments(&$sRead, &$i, &$iCnt, $sArg,$iRes,&$aResult) {
        switch ($sArg)
        {
        case 'EXPUNGE':
            /* only allowed in selectes state */
            $msgs =& $this->selected_mailbox->msgs;
            $msgs = array_splice($msgs,$iRes -1 ,1);
            $this->selected_mailbox->exists = $this->selected_mailbox->exists -1;
            break;
        case 'RECENT':
        case 'EXISTS':
            /* could be unsolicited response */
            $aResult[$sArg] = $iRes;
            break;
        case 'FETCH':
            $iUid = '';
            $aFetchResult = $this->parseFetch($sRead,$i,$iCnt, $iUid);
            /* now do some trick with the uid keep the array keys persistent
                php sometimes reindex keys based on integers */
            $aResult[$sArg][$iUid.'.'] =& $aFetchResult;
            //return $iUid;
            echo "UID = $iUid <BR>";

            break;
        default:
            break;
        }
    }

    /*
     * find the character and return the string before the char
     * example: $i=6 $sRead = "Hello World!" $char = "!"
     * result = "World" $i = 11
     */
    function findChar(&$sRead, &$i, &$iCnt, $char) {
        $s = '';
        while ($sRead) {
            $iPos = strpos($sRead,$char,$i);
            if ($iPos === false) {
                $s .= substr($sRead,$i);
                $sRead = $this->sqimap_fgets_next($i, $iCnt);
                if (!$sRead) break;
            }
            if ($iPos || $iPos !== false) {
                $s .= substr($sRead,$i,$iPos-$i);
                $i = $iPos;
                break;
            }
        }
        return $s;
    }

    function parseFetch(&$sRead,&$i, $iUid) {
        /* first we do a failsafe action to be sure we look at the correct arguments */
        if (!$this->fetchMapping) {
            $this->fetchMapping =
                array (
                    'BODY'            => 'BODYSTRUCTURE',
                    'BODYSTRUCTURE'    => 'BODYSTRUCTURE',
                    'ENVELOPE'         => 'ENVELOPE',
                    'UID'             => 'UID',
                    'FLAGS'         => 'FLAGS',
                    'INTERNALDATE'     => 'INTERNALDATE',
                    'RFC822.SIZE'     => 'RFC822.SIZE',
                    'RFC822'         => 'RFC822',
                    'RFC822.TEXT'     => 'RFC822.TEXT',
                    'BODY[TEXT]'     => 'RFC822.TEXT',
                    'BODY[0]'         => 'HEADER',
                    'BODY[HEADER]'    => 'HEADER',
                );
        }

        $sArg = $aVal = '';
        $aFetchResult = array();
        while (isset($sRead{$i}) && $sRead{$i} != ')' ) {
            $cChar = strtoupper($sRead{$i});
//            echo "<BR> $cChar, $i<BR>";
            switch ($cChar)
            {
            case $cChar == ')': break 2 ; /* end of fetch */
            case ' ': ++$i; break;
            default:
                $sArg = strtoupper(parseString($sRead, array(' ','['), $i));
                //switch ($sArg)
                //{
                                
                //case 'BODY':
                //    if ($sRead{$i} == '[') {
                //        /* retrieve the section */
                //        $sSection = $this->parseEnclosed($sRead,$i,'[',
                        
                switch (true)
                {
                case preg_match('/^BODY\[([0-9.]+)\.HEADER\]$/',$sArg, $reg):
                    $sArg = 'HEADER';
                    $aResult[$reg[1]][$sArg] = $this->parseHookedResult('FETCH',$sArg,$sRead,$i,$iCnt);
                    break;
                case preg_match('/^BODY\[([0-9.]+)\.MIME\]$/',$sArg, $reg):
                    $sArg = 'MIME';
                    $aResult[$reg[1]][$sArg] = $this->parseHookedResult('FETCH',$sArg,$sRead,$i,$iCnt);
                    break;
                case preg_match('/^BODY\[HEADER.FIELDS$/',$sArg, $reg):
                    $sArg = 'HEADER';
                    $aResult[$sArg] = $this->parseHookedResult('FETCH',$sArg,$sRead,$i,$iCnt);
                    break;
                case preg_match('/^(BODY\[([0-9.]+)\])$/',$sArg, $reg):
                    $sArg = 'BODYPART';
                    $aResult[$reg[1]][$sArg] = $this->parseHookedResult('FETCH',$sArg,$sRead,$i,$iCnt);
                    break;
                default:
                    if (isset($this->fetchMapping[$sArg])) {
                        $sArg = $this->fetchMapping[$sArg];
                        $aResult[$sArg] = $this->parseHookedResult('FETCH',$sArg,$sRead,$i,$iCnt);
                    }
                    break;
                }
                
                $this->processFetchArguments($sRead,$i,$iCnt,$sArg,$aFetchResult);
                break;
            }

        }
        $this->streamShift($sRead,$i,$iCnt);
        $iUid = $aFetchResult['UID'];
        return $aFetchResult;
    }

    function getMessageSet($aSet) {
        sort($aSet, SORT_NUMERIC);
        $sSet = '';
        while ($aSet) {
            $iStart = array_shift($aSet);
            $iEnd = $iStart;
            while (isset($aSet[0]) && $aSet[0] == $iEnd + 1) {
                $iEnd = array_shift($aSet);
            }
            if ($sSet) {
                $sSet .= ',';
            }
            $sSet .= $iStart;
            if ($iStart != $iEnd) {
                $sSet .= ':' . $iEnd;
            }
        }
        return $sSet;
    }


    function parseHookedResult($sEntry, $sArg,&$sRead,&$i,&$iCnt, $sValue='') {
        if ($sArg) {
            $parserFunction = $this->parserFunction[$sEntry][$sArg];
        } else {
            $parserFunction = $this->parserFunction[$sEntry];
        }
//        $this->print_var($this->parserFunction);

        if ($parserFunction->pre) {
            $pre =  $parserFunction->pre(); /* returns string */
        } else {
            $pre = '';
        }
        $res = $parserFunction->parser($sRead, $i, $iCnt, $sValue);
        if ($parserFunction->post) {
            $post =  $parserFunction->post($res); /* returns string */
        } else {
            $post = '';
        }
        if (is_string($res)) {
            $res = $pre . $res . $post;
        }
        if ($parserFunction->redirect) {
            $res = $parserFunction->redirect($res);
        }
//        $this->print_var($res);
        return $res;
    }

    function parseMimeHeader(&$sRead,&$i, &$iCnt) {
        $this->findChar($sRead,$i,$iCnt,'{');
        $sHdr = $this->_parseLiteral($sRead,$i);
        $oHdr =& new MimeHeader();
        $oHdr->parseHeader($sHdr);
        return $oHdr;
    }

    function parseRfc822Header(&$sRead,&$i, &$iCnt) {
        $this->findChar($sRead,$i,$iCnt,'{');
        $sHdr = $this->_parseLiteral($sRead,$i);
        $oHdr =& new Rfc822Header();
        $oHdr->parseHeader($sHdr);
        return $oHdr;
    }

    function fetchArgument(&$sRead,&$i, &$iCnt) {
        $sArg = '';
        $cChar = $sRead{$i};
//        echo "fetchARgument:" .substr($sRead, $i);
        switch ($cChar)
        {
        case '{': /* literal */
            $sArg = $this->_parseLiteral($sRead,$i);
            break;
        case '"': /* quoted */
            $sArg = $this->parseQuote($sRead,$i);
            break;
        case 'N':
            $this->parseNil($sRead,$i,$iCnt);
            break;
        default: break; /* should never happen */
        }
        return $sArg;
    }


    function parseStructure(&$sRead, &$i, &$iCnt) {
//        echo htmlspecialchars(substr($sRead,$i)) ,'<BR>';
        if (!method_exists($this,'parseBodyStructure')) {
            ob_start();
            include_once(SM_SERVICE_PATH .'imap/bodystructure.inc.php');

            $parseBodyStructure = ob_get_contents();
            ob_end_clean();
            $function = create_function('$a,$b,$c',$parseBodyStructure);
            echo $function;
            $this->parseBodyStructure =& $function;
            $this->print_var($function);
            echo $this->parseBodyStructure;
            $msg =& $this->parseBodyStructure($sRead, $i, '');
        } else {
            $msg =& $this->parseBodyStructure($sRead, $i, '');
    //        $msg->setEntIds($msg,false,0);
        }
        return $msg;
    }

//    function parseBodyStructure(&$sRead, &$i, &$iCnt, $oMsg_sub)
//        include_once(SM_SERVICE_PATH .'imap/bodystructure.inc.php');
    function parseBodyStructure(&$sRead, &$i, $oMsg_sub) {
        $aArg  = array();
        if ($oMsg_sub) {
            $oMessage = $oMsg_sub;
        } else {
            $oMessage =& new Message();
        }
        while (isset($sRead{$i}) && $sRead != ')') {
            $cChar = $sRead{$i};/* set cChar otherwise the switch statement can handle a changed $i */
            switch ($cChar)
            {
            case '(':
                $iArgNo = count($aArg);
                switch($iArgNo)
                {
                case 0:
                    if (!isset($oMsg)) {
                        $oMsg =& new Message();
                        $oMsg->mp = false; /* multipart */
                        $oHdr =& new MimeHeader();
                        $oHdr->type = 'text';
                        $oHdr->subtype = 'plain';
                        $oHdr->encoding = 'us-ascii';
                        ++$i;
                    } else {
                        $oMsg->header->type = 'multipart';
                        $oMsg->mp = true; /* multipart */
                        while ($sRead{$i} == '(') {
                            $oMsg->addEntity($this->parseBodyStructure($sRead, $i, $oMsg));
                        }
                    }
                    break;
                case 1:    $aArg[] = $this->parseProperties($sRead, $i); break; /* multipart properties */
                case 2:
                    if ($oMsg->mp) {
                        $aArg[] = $this->parseDisposition($sRead, $i);
                    } else { /* properties */
                        $aArg[] = $this->parseProperties($sRead, $i);
                    }
                    break;
                case 3: $aArg[]= $this->parseLanguage($sRead, $i); break;
                case 7:
                case 8:
                case 9:
                case 10:
                    if (!$oMsg->mp) {
                        if (($aArg[0] == 'text') || (($aArg[0] == 'message') && ($aArg[1] == 'rfc822'))) {
                            $iArgNoNotMp = $iArgNo -1;
                        } else {
                            $iArgNoNotMp = $iArgNo;
                        }
                        switch ($iArgNoNotMp)
                        {
                        case 6:
                            $oMsg->header->type = $aArg[0];
                            $oMsg->header->subtype = $aArg[1];
                            $oMsg->rfc822_header = $this->parseEnvelope($sRead, $i);
                            $this->findChar($sRead, $i, '(');
                            $oMsg->addEntity($this->parseBodyStructure($sRead, $i, $oMsg));
                            break;
                        case 8: $aArg[] = $this->parseDisposition($sRead, $i, $iCnt); break;
                        case 9: $aArg[] = $this->parseLanguage($sRead, $i); break;
                        case 10: $i = $this->parseParenthesis($sRead, $i); break;
                        }
                    } else {
                        $i = $this->parseParenthesis($sRead, $i); break;
                    }
                    break;
                default:
                    /* unknown argument, skip this part */
                    $i = $this->parseParenthesis($sRead, $i);
                    $aArg[] = '';
                    break;
                } /* switch */
                break;
            case '"':
                /* inside an entity -> start processing */
                if ($iArgNo < 2) { /* type0 and type1 */
                    $aArg[] = strtolower($this->parseQuote($sRead, $i));
                } else {
                    $aArg[] = $this->parseQuote($sRead, $i);
                }
                break;
            case 'n':
            case 'N':
                /* probably NIL argument */
                if ($this->parseNil($sRead, $i)) {
                    $aArg[] = '';
                }
                break;
            case ' ': ++$i; break;
            case '{': $sArg = $this->_parseLiteral($sRead, $i); break; /* process the literal value */
            case '0':
            case is_numeric($sRead{$i}): $aArg[] = $this->parseInteger($sRead,$i); break;
            case ')':
                if (!$oMsg->mp) {
                    $shifted_args = (($aArg[0] == 'text') || (($aArg[0] == 'message') && ($aArg[1] == 'rfc822')));
                    $oHdr->type = $aArg[0];
                    $oHdr->subtype = $aArg[1];
                    $arr = $aArg[2];
                    if (is_array($arr)) {
                        $oHdr->parameters = $aArg[2];
                    }
                    if ($aArg[3]) $oHdr->id = str_replace('<', '', str_replace('>', '', $aArg[3]));
                    if ($aArg[4]) $oHdr->description = $aArg[4];
                    if ($aArg[5]) $oHdr->encoding = strtolower($aArg[5]);
                    if ($aArg[6]) $oHdr->size = $aArg[6];
                    if ($shifted_args) {
                        $oHdr->lines = $aArg[7];
                        $iOffset = 7;
                    } else {
                        $iOffset = 6;
                    }
                    if (isset($aArg[1+$iOffset]) && $aArg[1+$iOffset]) $oHdr->md5 = $aArg[1+$iOffset];
                } else {
                    $oHdr->type = 'multipart';
                    $oHdr->subtype = $aArg[0];
                    $oMsg->mp = true;
                    if (isset($aArg[1]) && $aArg[1]) $oHdr->parameters = $aArg[1];
                    $iOffset=0;
                }
                if (isset($aArg[2+$iOffset]) && $aArg[2+$iOffset]) $oHdr->disposition = $aArg[2+$iOffset];
                if (isset($aArg[3+$iOffset]) && $aArg[3+$iOffset]) $oHdr->language =     $aArg[3+$iOffset];
                if (isset($aArg[4+$iOffset]) && $aArg[4+$iOffset]) $oHdr->location =     $aArg[4+$iOffset];
                $oMsg->header = $oHdr;
                ++$i;
                return $oMsg;
            default: ++$i; break;
            } /* switch */
        } /* while */
    } /* parsestructure */

    function parseProperties(&$sRead, &$i) {
        $properties = array();
        $prop_name = '';
        while (isset($sRead{$i}) && $sRead{$i} != ')') {
            $sArg = '';
            $cChar = $sRead{$i}; /* set cChar otherwise the switch statement can handle a changed $i */
            switch ($cChar)
            {
            case '"': $sArg = $this->parseQuote($sRead, $i);    break;
            case '{': $sArg = $this->_parseLiteral($sRead, $i); break;
            default:  $this->streamShift($sRead,$i); break;
            }
            if ($sArg) {
                if ($prop_name) {
                    $properties[$prop_name] = $sArg;
                    $prop_name = '';
                } else {
                    $prop_name = strtolower($sArg);
                    $properties[$prop_name] = '';
                }

            }
        }
        /* the last found ')' is part of the properties structure, skip it */
        ++$i;
        return $properties;
    }

    function parseEnvelope(&$sRead, &$i) {
        $this->findChar($sRead, $i, $iCnt,'(');
        ++$i;
        $oHdr =& new Rfc822Header();
        $iArgNo = 0;
        $aArg = array();
        while (isset($sRead{$i}) && $sRead{$i} != ')') {
            $cChar = strtoupper($sRead{$i}); /* set cChar otherwise the switch statement can handle a changed $i */
            switch ($cChar)
            {
            case '"':
                $aArg[] = $this->parseQuote($sRead, $i);
                ++$iArgNo;
                break;
            case '{':
                $aArg[] = $this->_parseLiteral($sRead, $i);
                ++$iArgNo;
                break;
            case 'N':
                /* probably NIL argument */
                if ($this->parseNil($sRead, $i)) {
                    $aArg[] = '';
                    ++$iArgNo;
                }
                break;
            case '(':
                /* Address structure (with group support)
                 * Note: Group support is useless on SMTP connections
                 *       because the protocol doesn't support it
                 */
                $aAddr = array();
                $sGroup = '';
                $a=0;
                while (isset($sRead{$i}) && $sRead{$i} != ')') {
                    if ($sRead{$i} == '(') {
                        $oAddr = $this->parseAddress($sRead, $i);
                        if (!$oAddr->getField('host') && $oAddr->getField('mailbox')) {
                            /* start of group */
                            $sGroup = $oAddr->mailbox;
                            $oAddrGroup = $oAddr;
                            $j = $a;
                        } else if ($sGroup && (!$oAddr->getField('host')) && (!$oAddr->getField('mailbox'))) {
                           /* end group */
                            if ($a == ($j+1)) { /* no group members */
                                $oAddrGroup->group = $sGroup;
                                $oAddrGroup->mailbox = '';
                                $oAddrGroup->personal = "$sGroup: Undisclosed recipients;";
                                $aAddr[] = $oAddrGroup;
                                $sGroup ='';
                            }
                        } else {
                            if ($sGroup) $oAddr->group = $sGroup;
                            $aAddr[] = $oAddr;
                        }
                        ++$a;
                    } else {
                        ++$i;
                    }

                }
                $aArg[] = $aAddr;
                /* the last found ')' is part of the address structure, skip it */
                ++$i;
                break;
            default: ++$i; break;
            }

        }
        /* the last found ')' is part of the envelope, skip it */
        ++$i;

        if (count($aArg) > 9) {
            if (!$aArg[1]) $aArg[1] = _("(no subject)");
                /* argument 1: date */
            if ($aArg[0]) $oHdr->date = (int)strtotime($aArg[0]) + (int)date('Z', time());
            if ($aArg[1]) $oHdr->subject = $aArg[1];             /* argument 2: subject */
            if (count($aArg[2])) $oHdr->from = $aArg[2][0];        /* argument 3: from */
            if (count($aArg[3])) $oHdr->sender = $aArg[3][0];    /* argument 4: sender */
            if (count($aArg[4])) $oHdr->replyto = $aArg[4][0];    /* argument 5: reply-to */
            if (count($aArg[5])) $oHdr->to = $aArg[5];            /* argument 6: to */
            if ($aArg[6]) $oHdr->cc = $aArg[6];            /* argument 7: cc */
            if ($aArg[7]) $oHdr->bcc = $aArg[7];            /* argument 8: bcc */
            if ($aArg[8]) $oHdr->inreplyto = $aArg[8];            /* argument 9: in-reply-to */
            if ($aArg[9]) $oHdr->message_id = $aArg[9];            /* argument 10: message-id */
        }
        return $oHdr;
    }

    function parseAddress(&$sRead, &$i, &$iCnt) {
        $aArg = array();
        while (isset($sRead{$i}) && $sRead{$i} != ')') {
            $cChar = $sRead{$i}; /* set cChar otherwise the switch statement can handle a changed $i */
            switch ($cChar)
            {
            case '"': $aArg[] = $this->parseQuote($sRead, $i); break;
            case '{': $aArg[] = $this->_parseLiteral($sRead, $i); break;
            case 'n':
            case 'N':
                if ($this->parseNil($sRead, $i)) {
                    $aArg[] = '';
                }
                break;
            default:
                ++$i;
                break;
            }
        }
        ++$i;

        if (count($aArg) == 4) {
            $adr =& new AddressStructure();
            if ($aArg[0]) $adr->personal = $aArg[0];
            if ($aArg[1]) $adr->adl = $aArg[1];
            if ($aArg[2]) $adr->mailbox = $aArg[2];
            if ($aArg[3]) $adr->host = $aArg[3];
        } else {
            $adr = '';
        }
        return $adr;
    }

    /*
      * Disposition
      * @result : array; array[0] = disposition name array[1] optional properties array
      */
    function parseDisposition(&$sRead, &$i, &$iCnt) {
        $aArg = array();
        ++$i;
        while (isset($sRead{$i}) && $sRead{$i} != ')') {
            $cChar = $sRead{$i};/* set cChar otherwise the switch statement can handle a changed $i */
            switch ($cChar)
            {
            case '"': $aArg[] = $this->parseQuote($sRead, $i); break;
            case '{': $aArg[] = $this->_parseLiteral($sRead, $i); break;
            case '(':
                if (count($aArg)) {
                    $aArg[] = $this->parseProperties($sRead, $i);
                    break;
                }
            default: ++$i; break;
            }
        }
        /* the last found ')' is part of the disposition, skip it */
        ++$i;
        return $aArg;
    }

    /*
      * Language
      * @result : array; array[0] = Language array[1..n] optional language information
      */
    function parseLanguage(&$sRead, &$i) {
        $aArg = array();
        ++$i;
        while (isset($sRead{$i}) && $sRead{$i} != ')') {
            $cChar = $sRead{$i}; /* set cChar otherwise the switch statement can handle a changed $i */
            switch ($cChar)
            {
            case '"': $aArg[] = $this->parseQuote($sRead, $i); break;
            case '{': $aArg[] = $this->_parseLiteral($sRead, $i); break;
            case '(': $aArg = $this->parseArray($sRead, $i);    break;
            default : ++$i; break;
            }
        }
        /* the last found ')' is part of the language, skip it */
        ++$i;
        return $aArg;
    }

    /*
      * Parenthessis
      * @result : integer, used for skipping unknown parts
      */
    function parseParenthesis(&$sRead, $i, &$iCnt) {
        while (isset($sRead{$i}) && $sRead{$i} != ')') {
            $cChar = $sRead{$i};
            switch ($cChar)
            {
            case '"': $this->parseQuote($sRead, $i, $iCnt); break;
            case '{': $this->_parseLiteral($sRead, $i, $iCnt); break;
            case '(': $this->parseProperties($sRead, $i, $iCnt); break;
            default : $this->streamShift($sRead,$i,$iCnt); break;
            }

        }
        /* the last found ')' is part of the parenthesis, skip it */
        $this->streamShift($sRead,$i,$iCnt);
        return $i;
    }

    /*
     * function: sqimap_fetch      reimplement this !!!!
     *
     * input: $oMailbox = mailbox object (fullname)
     *        $aArgs = array containing array elements regarding optional
     *                 parser
     *               example: array(array('FLAGS', false),
     *                        array('BODY[1]', 'body_parser'))
     *          by providing the optional parser we can override the local methods
     *        and do things like directly echo to screen = no internal buffering
     *        optional parsers are only allowed when literals are involved.
     *
     * output: array, key=UID val=(array key=ARGUMENT val=RESULT)
     */
    function sqimap_fetch($id, $aArgs) {
        if (!$this->selected_mailbox) {
            return false;
        }
        $sFetchArgs = implode($aArgs,' ');
        $query = 'UID FETCH '.$id. ' ('.$sFetchArgs.')';
        $sTag = $this->sqimap_run_command($query);
        echo "$query <BR>";
        return $this->_sqimap_process_stream($sTag);
        //$this->print_var($aResult);
    }

    function sqimap_fetch2($oMailbox,$id, $aArgs) {
        require_once(SM_SERVICE_PATH . 'imap/imap_fetch.inc');
        if ($oMailbox->name != $this->selected_mailbox) {
            $query = 'SELECT "'.$oMailbox->name.'"';
            $this->sqimap_run_command($query);
            $this->query = $query;
            $this->selected_mailbox = $oMailbox->name;
        }

        $sTag = 'A'.$this->sqimap_session_id();
        $aFetch_args = array();
        foreach ($aArgs as $k => $v) {
            $aFetch_args[] = $k;
        }
        $query = $sTag .' UID FETCH '.$id. ' ('. implode(' ',$aFetch_args) .')';
        $this->query = $query;
        echo $query;
        fputs ($this->resource, $query . "\r\n");
        $process = array('MIMEPART' => array(0,array('test',&$this),'echo',0));
        $process = array();
//        $process = array('BODY[HEADER]' => array(0,array('parseHeader2Ar',&$imap_fetch),'echo',0));
        if (!$this->imap_fetch) {
            $imap_fetch = new imap_fetch($this->resource,$process);
            $this->imap_fetch = $imap_fetch;
        } else {
            $imap_fetch = $this->imap_fetch;
        }
        $res =& $imap_fetch->parseFetch($aArgs,$sTag);

        echo '<br><b>'.(($imap_fetch->iTot_cnt)).'<br></b>' ;
//        echo '<br><b>'.strlen($imap_fetch->buffer).'<br></b>' ;
//        echo implode($imap_fetch->buffer,'<br>');
        return $res;
    }

    /*
     * function: sqimap_select
     *
     * input: $oMailbox = mailbox object (fullname)
     * output: succes (boolean)
     */

    function sqimap_select(&$oMailbox) {
        if ( !is_object($oMailbox)) {
            return false;
        }

        if ($oMailbox->name == $this->selected_mailbox->name) {
            return true;
        }

        $oldUidvalidity = $oMailbox->uidvalidity;
        $oldExists = $oMailbox->exists;
        $oldUidNext = $oMailbox->uidnext;

        $query = 'SELECT "'.$oMailbox->name.'"';
        $query = 'SELECT "'.'INBOX'.'"';
        $sTag = $this->sqimap_run_command($query);

        $aResult = $this->_sqimap_process_stream($sTag);
        //$this->print_var($aResult);
        if ($aResult['RESPONSE'] != 'OK') {
            /* error processing select */
            $this->selected_mailbox = false;
            return false;
        } else {
            $this->selected_mailbox = true;
        }

        if (isset($aResult['EXISTS'])) {
            $oMailbox->exists = $aResult['EXISTS'];
        }
        if (isset($aResult['RECENT'])) {
            $oMailbox->recent = $aResult['RECENT'];
        }
        if (isset($aResult['FLAGS'])) {
            $oMailbox->storeFlags($aResult['FLAGS']);
        }

        /* grep optional tagged    response */
        if (isset($aResult['SERVER']['READ-ONLY'])) {
            $oMailbox->ro = true;
        } else {
            $oMailbox->ro = false;
        }
        /* grep the returned untagged server responses */
        if (isset($aResult['SERVER']['UIDNEXT'])) {
            $oMailbox->uidnext = $aResult['SERVER']['UIDNEXT'];
        }
        if (isset($aResult['SERVER']['UIDVALIDITY'])) {
            $oMailbox->uidvalidity = $aResult['SERVER']['UIDVALIDITY'];
        }
        if (isset($aResult['SERVER']['PERMANENTFLAGS'])) {
            $oMailbox->permanentflags = $aResult['SERVER']['PERMANENTFLAGS'];
        }

        /* update cache information */
        if ($oMailbox->uidvalidity != $oldUidvalidity) { /* cache is invalid */
            $oMailbox->cache->invalid = true;
        } else {

        }
        $this->selected_mailbox =& $oMailbox;
        /* after select stuff */
        $res = $this->sqimap_sort(array('SUBJECT'),array('SEEN'=>'','UNANSWERED' => '','TEXT' => "Marc Groot Koerkamp\r\n"),'US-ASCII');
        echo $this->print_var($res);

//        $res = $this->sqimap_fetch('1:200',array('UID', 'FLAGS', 'ENVELOPE', 'BODYSTRUCTURE','INTERNALDATE','RFC822.SIZE') );
        $res = $this->sqimap_fetch('1:200',array('UID', 'FLAGS') );
//        $res = $this->sqimap_fetch('1:*',array('UID', 'FLAGS', 'RFC822.SIZE', 'INTERNALDATE', 'ENVELOPE', 'BODY[HEADER]', 'BODY[1.MIME]', 'BODYSTRUCTURE') );
//unset($res);
//$res = $this->sqimap_fetch('1:800',array('UID', 'FLAGS', 'RFC822.SIZE', 'INTERNALDATE', 'ENVELOPE', 'BODY[HEADER]', 'BODY[1.MIME]') );
        echo $this->print_var($res);
        echo "BOE" . $this->iTot_cnt;
//        $res = $this->sqimap_fetch('1:200',array('UID', 'ENVELOPE','INTERNALDATE','RFC822.SIZE') );
//        $res = $this->sqimap_fetch('1:*',array('UID', 'BODYSTRUCTURE','INTERNALDATE','RFC822.SIZE') );
//        if ($oMailbox->expunge) {
//            $this->sqimap_expunge($oMailbox);
//        }
exit;
        return true;
    }

    function sqimap_examine(&$oMailbox) {
        if ( !is_object($oMailbox)) {
            return false;
        }

        $oldUidvalidity = $oMailbox->uidvalidity;
        $oldExists = $oMailbox->exists;
        $oldUidNext = $oMailbox->uidnext;

        $query = 'EXAMINE "'.$oMailbox->name.'"';
        $sTag = $this->sqimap_run_command($query);

        $aResult = $this->_sqimap_process_stream($sTag);

        $this->print_var($aResult);
        if ($aResult['RESPONSE'] != 'OK') {
            /* error processing select */
            return false;
        }

        if (isset($aResult['EXISTS'])) {
            $oMailbox->exists = $aResult['EXISTS'];
        }
        if (isset($aResult['RECENT'])) {
            $oMailbox->recent = $aResult['RECENT'];
        }
        if (isset($aResult['FLAGS'])) {
            $oMailbox->flags  = $aResult['FLAGS'];
        }

        /* grep optional tagged    response */
        if (isset($aResult['SERVER']['READ-ONLY'])) {
            $oMailbox->ro = true;
        } else if (isset($aResult['SERVER']['READ-WRITE'])) {
            $oMailbox->ro = false;
        }

        /* grep the returned untagged server responses */
        if (isset($aResult['SERVER']['UIDNEXT'])) {
            $oMailbox->uidnext = $aResult['SERVER']['UIDNEXT'];
        }
        if (isset($aResult['SERVER']['UIDVALIDITY'])) {
            $oMailbox->uidvalidity = $aResult['SERVER']['UIDVALIDITY'];
        }
        if (isset($aResult['SERVER']['PERMANENTFLAGS'])) {
            $oMailbox->permanentflags = $aResult['SERVER']['PERMANENTFLAGS'];
        }

        /* update cache information */
        if ($oMailbox->uidvalidity != $oldUidvalidity) { /* cache is invalid */
            $oMailbox->cache->invalid = true;
        } else {

        }
        return true;
    }

    function sqimap_expunge(&$oMailbox) {
        $query = 'EXPUNGE';
        $sTag = $this->sqimap_run_command($query);
        $this->_sqimap_process_stream($sTag, $aResult);
    }

    function sqimap_lsub() {
    }

    function sqimap_sort($aSortField, $aSearch, $sCharset) {
        $aValidSort = $this->getValidCommandArguments('SORT');
        $sSortDef = '';
        foreach ($aSortField as $sField) {
            if (in_array($sField,$aValidSort)) {
                $sSortDef .= $sField . ' ';
            }
        }
        $sSortDef = trim($sSortDef);
        if ($sSortDef) {
            $sSortString = 'UID SORT ('.$sSortDef.')';
            $vQuery = $this->sqimap_prepare_search($sSortString,$aSearch,$sCharset);
            $sTag = $this->sqimap_run_command($vQuery);
            return $this->_sqimap_process_stream($sTag);
        } else {
            return false;
        }
    }

    function sqimap_search($aSearch, $sCharset='') {
        $vQuery = $this->sqimap_prepare_search('UID SEARCH',$aSearch, $sCharset);
        $sTag = $this->sqimap_run_command($vQuery);
        return $this->_sqimap_process_stream($sTag);
    }

    function sqimap_prepare_search($sSearchStr, $aSearch, $sCharset='') {
        $sSearchStr .= ' ';
        foreach ($aSearch as $k => $v) {
            $sSearch_key = strtoupper($k);
            $aRes = $this->sqimap_getSearchElement($k,$v);
            if ($aRes) $aNewSearch[] = $aRes;
        }
        if ($sCharset) {
            $sSearchStr .= $sCharset . ' ';
        }
        $aSearchQuery = array();
        foreach ($aNewSearch as $v) {
            switch ($v[1])
            {
            case 'q':
                $sSearchStr .= $v[0] . ' "'.$v[2].'"';
                break;
            case 'l':
                if ($this->capabilities['LITERAL+']) {
                    $sSearchStr .= $v[0] . ' {'.strlen($v[2]).'+}';
                } else {
                    $sSearchStr .= $v[0] . ' {'.strlen($v[2]).'}';
                }
                $aSearchQuery[] = array($sSearchStr,true);
                $aSearchQuery[] = array($v[2],false);
                $sSearchStr = '';
                break;
            case 'i':
                $sSearchStr .= $v[0] . ' '.$v[2];
                break;
            case '0':
                $sSearchStr .= $v[0];
                break;
            case 'a':
                $sSearchStr .= $v[0] . ' ';
                $j=1;
                foreach ($v[3] as $vv) {
                    switch ($vv[1])
                    {
                    case 'q':
                        $sSearchStr .= $vv[0] . ' "'.$vv[2].'"';
                        break;
                    case 'l':
                        if ($this->capabilities['LITERAL+']) {
                            $sSearchStr .= $vv[0] . ' {'.strlen($vv[2]).'+}';
                        } else {
                            $sSearchStr .= $vv[0] . ' {'.strlen($vv[2]).'}';
                        }
                        $aSearchQuery[] = array($sSearchStr,true);
                        $aSearchQuery[] = array($vv[2],false);
                        $sSearchStr = '';
                        break;
                    case 'i':
                        $sSearchStr .= $vv[0] . ' '.$vv[2];
                        break;
                    case '0':
                        $sSearchStr .= $vv[0];
                        break;
                    case 's':
                        if (is_array($vv[2])) {
                            $sSearchStr .= $this->getMessageSet($vv[2]);
                        } elseif (is_string($vv[2])) {
                            $sSearchStr .= $vv[2];
                        }
                        break;
                    default:
                        break;
                    }
                    if (count($vv) != $j) {
                        $sSearchStr .= ' ';
                    }
                    ++$j;
                }
                $j = 0;
                break;
            case 's':
                if (is_array($vv[2])) {
                    $sSearchStr .= $this->getMessageSet($vv[2]);
                } elseif (is_string($vv[2])) {
                    $sSearchStr .= $vv[2];
                }
                break;
            default:
                break;
            }
            $sSearchStr .= ' ';
        }
        $sSearchStr = trim($sSearchStr);
        if (count($aSearchQuery)) {
            if ($sSearchStr) {
                $aSearchQuery[] = array($sSearchStr,false);
            }
            return $aSearchQuery;
        } else {
            return $sSearchStr;
        }
    }

    function sqimap_getSearchElement($sSearchKey, $vSearchValue) {
        $aSearchElement = false;
        switch ($sSearchKey)
        {
        /* date specific */
        case 'BEFORE':
        case 'ON':
        case 'SINCE':
        case 'SENTBEFORE':
        case 'SENTON':
        case 'SENTSINCE':
        /* keyword specific */
        case 'KEYWORD':
        case 'UNKEYWORD':
            $aSearchElement = array($sSearchKey,'q',$vSearchValue);
            break;
        case 'ANSWERED':
        case 'DELETED':
        case 'DRAFT':
        case 'FLAGGED':
        case 'RECENT':
        case 'SEEN':
        case 'UNANSWERED':
        case 'UNDELETED':
        case 'UNDRAFT':
        case 'UNFLAGGED':
        case 'UNRECENT':
        case 'UNSEEN':
        case 'NEW':
        case 'OLD':
        case 'ALL':
            $aSearchElement = array($sSearchKey,'0');
            break;
        /* size related */
        case 'SMALLER':
        case 'LARGER':
        /* message set related */
        case 'UID':
            if ($vSearchValue) {
                $aSearchElement = array($sSearchKey,'i',$vSearchValue);
            }
            break;
        case 'SET': /* sm implementatation for message sets */
            if ($vSearchKey) {
                $aSearchElement = array($sSearchKey,'s',$vSearchValue);
            }
            break;
        case 'OR':
            if (is_array($vSearchValue)) {
                $aSearchOr = array();
                foreach ($vSearchValue as $k => $v) {
                    $res = $this->sqimap_getSearchStr($k,$v);
                    if ($res) $aSearchOr[] = $res;
                }
                if (count($aSearchOr) == 2) {
                    $aSearchElement = array($sSearchKey,'a',$aSearchOr);
                }
            }
            break;
        case 'NOT':
            $aSearchElement = array($sSearchKey,'a',
                $this->sqimap_getSearchStr(key($vSearchValue),current($vSearchValue)));
            break;
        /* stringspecific */
        case 'BODY':
        case 'TO':
        case 'CC':
        case 'BCC':
        case 'FROM':
        case 'SUBJECT':
        case 'TEXT':
            if (is_string($vSearchValue)) {
                if (preg_match('/[\r\n"\\x00-0x7f]/', $vSearchValue)) { /* check for literal string */
                    $aSearchElement = array($sSearchKey,'l',$vSearchValue);
                } else {
                    $aSearchElement = array($sSearchKey,'q',$vSearchValue);
                }
            }
            break;
        case 'HEADER':
            if (is_array($vSearchValue) && count($vSearchValue) ==2) {
                $sSearchStr = $sSearchKey .' '. $vSearchValue[0];
                if (preg_match('/[\r\n"\\x00-0x7f]/', $v)) { /* check for literal string */
                    $aSearchElement = array($sSearchStr,'l',$vSearchValue[1]);
                } else {
                    $aSearchElement = array($sSearchStr,'q',$vSearchValue[1]);
                }
            }
            break;
        default:
            return false;
            break;
        }
        return $aSearchElement;
    }

    function test ($data) {
    return '<BR><b>'. nl2br(($data)) .'</b><BR>';
    }
    /*
     * function for getting a
     */
    function sqimap_get_messages_hdr_list($id, $size = true, $internaldate = true,
         $flags = true,  $bodystructure = false, $optional_headers = array()) {
        $aArgs = array();
        if ($flags) $aArgs[] = 'FLAGS';
        $aArgs[] = 'UID';
        if ($size) $aArgs[] = 'RFC822.SIZE';
        if ($bodystructure) $aArgs[] = 'BODYSTRUCTURE';
        $aArgs[] = 'ENVELOPE';
        $header = '';
        if ($multipart) $header .= 'Content-Type';
        if (count($optional_headers)) {
            $header = implode(' ',$optional_headers);
            $aArgs[] = 'BODY.PEEK[HEADER.FIELDS ('.$header.')';
         }
         return $this->fetch($id,$aArgs);
    }

}
?>
