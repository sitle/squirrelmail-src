<?php
    /*
     * Fetch response parser
     *
     *
     */
class imap_fetch extends imap {

    function sqimap_fgets($iSize=0) {
        $buffer = 4096;
	$iCnt = $this->iTot_cnt;
	$s = 0;
	if ($iSize) {
	    while ($iCnt < $iSize) {
		if (! ($read = fgets($this->imap_stream,$buffer))) {
	    	    break;
		}
    		$s .= $read;
		$iCnt += strlen($s);
	    }
	} else {
	    $s = fgets($this->imap_stream,$buffer);
	    $iCnt += strlen($s);
	}
	$this->iTot_cnt = $iCnt;
	return $s;
    }
    
    function checkForLiteral(&$read,&$i, &$iCnt) {
	if ($read{$i} == '{') {
	    ++$i;
	    $iLit = '';
	    while ($read{$i} != '}') $iLit .= $read{$i};
	    if ($iLit) {
		$read = sqimap_fgets($iLit);
		$i = 0;
		$iCnt = strlen($read);
		return substr($read,0,$iLit);
	    }
	}
	return false;	
    }
    
    function parseFetch($raw = false) {
        $sArg = $aVal = '';
	$result = array();
	$this->iTot_Cnt = 0;
        $read = $this->sqimap_fgets();
	while (!$this->response) {
	    for ($i=0,$iCnt=strlen($read);$i<$iCnt;++$i) {
		$char = $read{$i};
		switch ($read{$i}) {
		case ' ':
		    if (!$sArg) {
		       $sArg = strtoupper($this->parseArgument($read,$i,$iCnt));
		    } else {
		       switch ($sArg) {
		         case 'INTERNALDATE':
			    $result[$sArg] = $this->parseQuote($read,$i,$iCnt);
			    $sArg = '';
			    break;
			 case 'ENVELOPE':
			    $res = $this->checkForLiteral($read,$i,$iCnt);
			    require_once(SM_LIB_PATH . 'message/Rfc822Header.inc');
			    $hdr = new Rfc822Header();
			    $hdr = $this->parseEnvelope($read,$i,$iCnt,$hdr);
			    $result[$sArg] = $hdr;
			    $sArg = '';			    
			    break;
			 case 'FLAGS':
			    $this->checkForLiteral($read,$i,$iCnt);
			    $result[$sArg] = $this->parseFlags($read,$i,$iCnt);
			    $sArg = '';			    
			    break;
			 case 'UID':
			    ++$i;
			    $result[$sArg] = $this->parseInteger($read,$i, $iCnt);
			    $sArg = '';
			 case 'RFC822.SIZE':
			    ++$i;
			    $result[$sArg] = $this->parseInteger($read,$i, $iCnt);
			    $sArg = '';
			    break;
			 case 'BODY':
			 case 'BODYSTRUCTURE':
			    ++$i;
			    $msg = $this->parseStructure($read,$i, $msg);
			    $result['BODYSTRUCTURE'] = $msg;
			    break;
			 case 'RFC822':
			 case 'BODY[]':
			    $result['RFC822'] = $this->checkForLiteral($read,$i,$iCnt);
			    $read = function sqimap_fgets_next($i, $iCnt);
			    break;
			 case 'RFC822.TEXT':
			 case 'BODY[TEXT]':
			    $result['RFC822.TEXT'] = 
			       $this->checkForLiteral($read,$i,$iCnt);
			    $read = function sqimap_fgets_next($i, $iCnt);
			    break;
			 case 'BODY[0]':    
			 case 'BODY[HEADER]':
			 /* todo BODY[ENT.HEADER] */
			    require_once(SM_LIB_PATH . 'message/Rfc822Header.inc');
			    $res = $this->checkForLiteral($read,$i,$iCnt);
			    if (!$raw) {
				$result[$sArg] = $this->parseHeader($res);
			    } else {
			        $result[$sArg] = $res;
			    }
			    $read = function sqimap_fgets_next($i, $iCnt);
			    break;
			 /* todo */			 
/*
			 case 'BODY[':
			 case 'BODY[HEADER.FIELDS 
*/
			 default: ++$i; break;
		    }
		case '*':
		    if ($i == 0) {
			$id = $this->initFetch($read, $i);
			if (count($result)) {
			    $data[] = $result;
			    $result = array();
			}
			break;
		    } else {
			++$i;
		    }
		    break;
		case $sid{0}:
		    if (substr($string,$i,strlen($sid)) == $sid) {
		        $i += strlen($sid) +1;
			if ($this->parseResponse($read)) {
			   return $result;
			}
			break;
		    }    
	        default: ++$i; break;
		}
	    }
	    $read = $this->sqimap_fgets();
	}
	if (count($result)) {
	    $data[] = $result;
        }
	
    }         	   

    function parseFlags(&$read, &$i, &$iCnt) {
        $aFlags = array();
	$s = '';
	++$i; /* skip the first ( */
        for (++$i; $read{$i} != ')'; ++$i) {
             $s .= $read{$i};
     	     if ($i == $iCnt -1) {
	        $read = $this->sqimap_fgets();
	        $i = 0;
	        $iCnt = strlen($read);
	     } 
        }
	++$i;
	return (explode(' ',$s));
    }
    
    function sqimap_fgets_next(&$i, &$iCnt) {
	$read = $this->sqimap_fgets();
	$i = 0;
	$iCnt = strlen($read);
	return $read;
    }
	
    function parseArgument(&$read, &$i, &$iCnt) {
        $s = '';
        for (++$i; $read{$i} != ' '; ++$i) {
	    switch ($read{$i}) {
	    case '[':
	        --$i;
		break;
	    default:
                $s .= $read{$i};
 	        if ($i == $iCnt -1) $read = $this->sqimap_fgets_next($i, $iCnt);
		break;
    	    }
	}
        return $s;
    }

    function parseInteger(&$read, &$i, &$iCnt) {
        $s = '';
        for (++$i; $read{$i} != ' '; ++$i) {
             $s .= $read{$i};
             if ($i == $iCnt -1) $read = $this->sqimap_fgets_next($i, $iCnt);
        }
        return $s;
    }

    function parseStructure(&$read, &$i, &$iCnt, $sub_msg = '') {
        $arg_no = 0;
        $arg_a  = array();
	if ($sub_msg) {
	    $message = $sub_msg;
	} else {
	    $message = new Message();
	}    
        for ($iCnt = strlen($read); $i < $iCnt; ++$i) {
            $char = strtoupper($read{$i});
            switch ($char) {
                case '(':
                    switch($arg_no) {
                        case 0:
                            if (!isset($msg)) {
                                $msg = new Message();
                                $hdr = new MessageHeader();
                                $hdr->type0 = 'text';
                                $hdr->type1 = 'plain';
                                $hdr->encoding = 'us-ascii';
                                $msg->entity_id = $message->calcEntity($msg);
                            } else {
                                $msg->header->type0 = 'multipart';
                                $msg->type0 = 'multipart';
                                while ($read{$i} == '(') {
                                    $msg->addEntity($this->parseStructure($read, $i, $iCnt, $msg));
                                }
                            }
                            break;
                        case 1:
                            /* multipart properties */
                            ++$i;
                            $arg_a[] = $this->parseProperties($read, $i, $iCnt);
                            ++$arg_no;
                            break;
                        case 2:
                            if (isset($msg->type0) && ($msg->type0 == 'multipart')) {
                                ++$i;
                                $arg_a[] = $this->parseDisposition($read, $i, $iCnt);
                            } else { /* properties */
                                $arg_a[] = $this->parseProperties($read, $i, $iCnt);
                            }
                            ++$arg_no;
                            break;
                        case 3:
                            if (isset($msg->type0) && ($msg->type0 == 'multipart')) {
                                ++$i;
                                $arg_a[]= $this->parseLanguage($read, $i, $iCnt);
                            }
                        case 7:
                            if (($arg_a[0] == 'message') && ($arg_a[1] == 'rfc822')) {
                                $msg->header->type0 = $arg_a[0];
                                $msg->header->type1 = $arg_a[1];
                                $msg->type0 = $arg_a[0];
                                $msg->type1 = $arg_a[1];
                                $rfc822_hdr = new Rfc822Header();
                                $msg->rfc822_header = $this->parseEnvelope($read, $i, $iCnt, $rfc822_hdr);
                                while (($i < $cnt) && ($read{$i} != '(')) {
                                    ++$i;
                                }
                                $msg->addEntity($this->parseStructure($read, $i, $iCnt, $msg));
                            }
                            break;
                        case 8:
                            ++$i;
                            $arg_a[] = $this->parseDisposition($read, $i, $iCnt);
                            ++$arg_no;
                            break;
                        case 9:
                            ++$i;
                            if (($arg_a[0] == 'text') || (($arg_a[0] == 'message') && ($arg_a[1] == 'rfc822'))) {
                                $arg_a[] = $this->parseDisposition($read, $i, $iCnt);
                            } else {
                                $arg_a[] = $this->parseLanguage($read, $i, $iCnt);
                            }
                            ++$arg_no;
                            break;
                       case 10:
                           if (($arg_a[0] == 'text') || (($arg_a[0] == 'message') && ($arg_a[1] == 'rfc822'))) {
                               ++$i;
                               $arg_a[] = $this->parseLanguage($read, $i, $iCnt);
                           } else {
                               $i = $this->parseParenthesis($read, $i, $iCnt);
                               $arg_a[] = ''; /* not yet described in rfc2060 */
                           }
                           ++$arg_no;
                           break;
                       default:
                           /* unknown argument, skip this part */
                           $i = $this->parseParenthesis($read, $i, $iCnt);
                           $arg_a[] = '';
                           ++$arg_no;
                           break;
                   } /* switch */
                   break;
                case '"':
                    /* inside an entity -> start processing */
                    $debug = substr($read, $i, 20);
                    $arg_s = $this->parseQuote($read, $i, $iCnt);
                    ++$arg_no;
                    if ($arg_no < 3) {
                        $arg_s = strtolower($arg_s); /* type0 and type1 */
                    }
                    $arg_a[] = $arg_s;
                    break;
                case 'n':
                case 'N':
                    /* probably NIL argument */
                    if (strtoupper(substr($read, $i, 4)) == 'NIL ') {
                        $arg_a[] = '';
                        ++$arg_no;
                        $i += 2;
                    }
                    break;
                case '{':
                    /* process the literal value */
                    $arg_s = $this->parseLiteral($read, $i, $iCnt);
                    ++$arg_no;
                    break;
		case '0':
                case is_numeric($read{$i}):
                    /* process integers */
                    if ($read{$i} == ' ') { break; }
                    $arg_s = $read{$i};;
                    for (++$i; preg_match('/^[0-9]{1}$/', $read{$i}); ++$i) {
                        $arg_s .= $read{$i};
                    }
                    ++$arg_no;
                    $arg_a[] = $arg_s;
                    break;
                case ')':
                    $multipart = (isset($msg->type0) && ($msg->type0 == 'multipart'));
                    if (!$multipart) {
                        $shifted_args = (($arg_a[0] == 'text') || (($arg_a[0] == 'message') && ($arg_a[1] == 'rfc822')));
                        $hdr->type0 = $arg_a[0];
                        $hdr->type1 = $arg_a[1];

                        $msg->type0 = $arg_a[0];
                        $msg->type1 = $arg_a[1];
                        $arr = $arg_a[2];
                        if (is_array($arr)) {
                            $hdr->parameters = $arg_a[2];
                        }
                        $hdr->id = str_replace('<', '', str_replace('>', '', $arg_a[3]));
                        $hdr->description = $arg_a[4];
                        $hdr->encoding = strtolower($arg_a[5]);
                        $hdr->entity_id = $msg->entity_id;
                        $hdr->size = $arg_a[6];
                        if ($shifted_args) {
                            $hdr->lines = $arg_a[7];
                            $s = 1;
                        } else {
                            $s = 0;
                        }
                        $hdr->md5 = (isset($arg_a[7+$s]) ? $arg_a[7+$s] : $hdr->md5);
                        $hdr->disposition = (isset($arg_a[8+$s]) ? $arg_a[8+$s] : $hdr->disposition);
                        $hdr->language = (isset($arg_a[9+$s]) ? $arg_a[9+$s] : $hdr->language);
                        $msg->header = $hdr;
                        if ((strrchr($msg->entity_id, '.') == '.0') && ($msg->type0 !='multipart')) {
                           $msg->entity_id = $message->entity_id . '.1';
                        }
                    } else {
                        $hdr->type0 = 'multipart';
                        $hdr->type1 = $arg_a[0];
                        $msg->type0 = 'multipart';
                        $msg->type1 = $arg_a[0];
                        $hdr->parameters = (isset($arg_a[1]) ? $arg_a[1] : $hdr->parameters);
                        $hdr->disposition = (isset($arg_a[2]) ? $arg_a[2] : $hdr->disposition);
                        $hdr->language = (isset($arg_a[3]) ? $arg_a[3] : $hdr->language);
                        $msg->header = $hdr;
                    }
                    ++$i;
                    return (array($msg, $i));
                default: break;
            } /* switch */
            if ($i == $iCnt -1) $read = $this->sqimap_fgets_next($i, $iCnt);
        } /* for */
    } /* parsestructure */

    function parseProperties(&$read, &$i, &$iCnt) {
        $properties = array();
        $prop_name = '';

        for (; $read{$i} != ')'; ++$i) {
            $arg_s = '';
            if ($read{$i} == '"') {
                $arg_s = $this->parseQuote($read, $i, $iCnt);
            } else if ($read{$i} == '{') {
                $arg_s = $this->parseLiteral($read, $i, $iCnt);
            }

            if ($arg_s != '') {
                if ($prop_name == '') {
                    $prop_name = strtolower($arg_s);
                    $properties[$prop_name] = '';
                } else if ($prop_name != '') {
                    $properties[$prop_name] = $arg_s;
                    $prop_name = '';
                }
            }
            if ($i == $iCnt -1) $read = $this->sqimap_fgets_next($i, $iCnt);
        }
        return $properties;
    }

    function parseEnvelope(&$read, &$i, &$iCnt) {
        $hdr = new Rfc822Header();
        $arg_no = 0;
        $arg_a = array();

        for ($iCnt = strlen($read); ($i < $iCnt) && ($read{$i} != ')'); ++$i) {
            ++$i;
            $char = strtoupper($read{$i});
            switch ($char) {
                case '"':
                    $arg_a[] = $this->parseQuote($read, $i, $iCnt);
                    ++$arg_no;
                    break;
                case '{':
                    $arg_a[] = $this->parseLiteral($read, $i, $iCnt);
                    ++$arg_no;
                    break;
                case 'N':
                    /* probably NIL argument */
                    if (strtoupper(substr($read, $i, 3)) == 'NIL') {
                        $arg_a[] = '';
                        ++$arg_no;
                        $i += 2;
                    }
                    break;
                case '(':
                    /* Address structure (with group support)
                     * Note: Group support is useless on SMTP connections
                     *       because the protocol doesn't support it
                     */
                    $addr_a = array();
                    $group = '';
                    $a=0;
                    for (; $i < $iCnt && $read{$i} != ')'; ++$i) {
                        if ($read{$i} == '(') {
                            $addr = $this->parseAddress($read, $i, $iCnt);
                            if (($addr->host == '') && ($addr->mailbox != '')) {
                                /* start of group */
                                $group = $addr->mailbox;
                                $group_addr = $addr;
                                $j = $a;
                            } else if ($group && ($addr->host == '') && ($addr->mailbox == '')) {
                               /* end group */
                                if ($a == ($j+1)) { /* no group members */
                                    $group_addr->group = $group;
                                    $group_addr->mailbox = '';
                                    $group_addr->personal = "$group: Undisclosed recipients;";
                                    $addr_a[] = $group_addr;
                                    $group ='';
                                }
                            } else {
                                $addr->group = $group;
                                $addr_a[] = $addr;
                            }
                            ++$a;
                        }
                    }
                    $arg_a[] = $addr_a;
                    break;
                default: break;
        	if ($i == $iCnt -1) $read = $this->sqimap_fgets_next($i, $iCnt);
            }
        }

        if (count($arg_a) > 9) {
            $d = strtr($arg_a[0], array('  ' => ' '));
            $d = explode(' ', $d);
	    if (!$arg_a[1]) $arg_1[1] = _("(no subject)");	    

            $hdr->date = getTimeStamp($d); /* argument 1: date */
            $hdr->subject = $arg_a[1];     /* argument 2: subject */
            $hdr->from = $arg_a[2][0];     /* argument 3: from        */
            $hdr->sender = $arg_a[3][0];   /* argument 4: sender      */
            $hdr->replyto = $arg_a[4][0];  /* argument 5: reply-to    */
            $hdr->to = $arg_a[5];          /* argument 6: to          */
            $hdr->cc = $arg_a[6];          /* argument 7: cc          */
            $hdr->bcc = $arg_a[7];         /* argument 8: bcc         */
            $hdr->inreplyto = $arg_a[8];   /* argument 9: in-reply-to */
            $hdr->message_id = $arg_a[9];  /* argument 10: message-id */
        }
        return $hdr;
    }
    
    function parseLiteral(&$read, &$i, &$iCnt) {
        $lit_cnt = '';
        for (++$i; $read{$i} != '}'; ++$i) {
            $lit_cnt .= $read{$i};
        }
        $read = $this->sqimap_fgets($lit_cnt);
	$i = 0;
	$iCnt = strlen($read);
	$s = substr($read,0,$lit_cnt);
        return $s;
    }

    function parseQuote(&$read, &$i, &$iCnt) {
        $s = '';
        for (++$i; $read{$i} != '"'; ++$i) {
            if ($read{$i} == '\\') {
                ++$i;
            }
            $s .= $read{$i};
    	    if ($i == $iCnt -1) $read = $this->sqimap_fgets_next($i, $iCnt);
        }
        return $s;
    }

    function parseAddress(&$read, &$i, &$iCnt) {
        $arg_a = array();

        for (; $read{$i} != ')'; ++$i) {
            $char = strtoupper($read{$i});
            switch ($char) {
                case '"':
                case '{':
                    $arg_a[] = ($char == '"' ? $this->parseQuote($read, $i) : $this->parseLiteral($read, $i));
                    break;
                case 'n':
                case 'N':
                    if (strtoupper(substr($read, $i, 3)) == 'NIL') {
                        $arg_a[] = '';
                        $i += 2;
                    }
                    break;
                default: break;
            }
    	    if ($i == $iCnt -1) $read = $this->sqimap_fgets_next($i, $iCnt);
        }

        if (count($arg_a) == 4) {
            $adr = new AddressStructure();
            $adr->personal = $arg_a[0];
            $adr->adl = $arg_a[1];
            $adr->mailbox = $arg_a[2];
            $adr->host = $arg_a[3];
        } else {
            $adr = '';
        }
        return $adr;
    }

    function parseDisposition(&$read, &$i, &$iCnt) {
        $arg_a = array();
        for (; $read{$i} != ')'; ++$i) {
            switch ($read{$i}) {
                case '"': $arg_a[] = $this->parseQuote($read, $i, &$iCnt); break;
                case '{': $arg_a[] = $this->parseLiteral($read, $i, &$iCnt); break;
                case '(': $arg_a[] = $this->parseProperties($read, $i, &$iCnt); break;
                default: break;
            }
    	    if ($i == $iCnt -1) $read = $this->sqimap_fgets_next($i, $iCnt);
	}

        if (isset($arg_a[0])) {
            $disp = new Disposition($arg_a[0]);
            if (isset($arg_a[1])) {
                $disp->properties = $arg_a[1];
            }
        }

        return (is_object($disp) ? $disp : '');
    }

    function parseLanguage(&$read, &$i, &$iCnt) {
        /* no idea how to process this one without examples */
        $arg_a = array();

        for (; $read{$i} != ')'; ++$i) {
            switch ($read{$i}) {
                case '"': $arg_a[] = $this->parseQuote($read, $i, &$iCnt); break;
                case '{': $arg_a[] = $this->parseLiteral($read, $i, &$iCnt); break;
                case '(': $arg_a[] = $this->parseProperties($read, $i, &$iCnt); break;
                default: break;
            }
    	    if ($i == $iCnt -1) $read = $this->sqimap_fgets_next($i, $iCnt);
        }

        if (isset($arg_a[0])) {
            $lang = new Language($arg_a[0]);
            if (isset($arg_a[1])) {
                $lang->properties = $arg_a[1];
            }
        }

        return (is_object($lang) ? $lang : '');
    }

    function parseParenthesis(&$read, $i) {
        for (; $read{$i} != ')'; ++$i) {
            switch ($read{$i}) {
                case '"': $this->parseQuote($read, $i); break;
                case '{': $this->parseLiteral($read, $i); break;
                case '(': $this->parseProperties($read, $i); break;
                default: break;
            }
    	    if ($i == $iCnt -1) $read = $this->sqimap_fgets_next($i, $iCnt);
        }
        return $i;
    }

    function parseHeader($read) {
        $res = new Rfc822Header();
        if (is_array($hdr)) {
            $hdr = implode('', $hdr);
        }
        /* First we unfold the header */
        $hdr = trim(str_replace(array("\r\n\t", "\r\n "),array('', ''), $hdr));

        /* Now we can make a new header array with */
        /* each element representing a headerline  */
        $hdr = explode("\r\n" , $hdr);
        foreach ($hdr as $line) {
            $pos = strpos($line, ':');
            if ($pos > 0) {
                $field = substr($line, 0, $pos);
		if (!strstr($field,' ')) { /* valid field */
            	    $value = trim(substr($line, $pos+1));
            	    if(!preg_match('/^X.*/i', $field) &&
                       !preg_match('/^Subject/i', $field)) {
                       $value = $this->stripComments($value);
                    }
            	    $this->parseField($field, $value, $res);
		}
            }
        }
        if ($res->content_type == '') {
            //$this->parseContentType('text/plain; charset=us-ascii');
        }
	return $res;
    }

    function stripComments($value) {
        $result = '';

        $cnt = strlen($value);
        for ($i = 0; $i < $cnt; ++$i) {
            switch ($value{$i}) {
                case '"':
                    $result .= '"';
                    while ((++$i < $cnt) && ($value{$i} != '"')) {
                        if ($value{$i} == '\\') {
                            $result .= '\\';
                            ++$i;
                        }
                        $result .= $value{$i};
                    }
                    $result .= $value{$i};
                    break;
                case '(':
                    $depth = 1;
                    while (($depth > 0) && (++$i < $cnt)) {
                        switch($value{$i}) {
                            case '\\':
                                ++$i;
                                break;
                            case '(':
                                ++$depth;
                                break;
                            case ')':
                                --$depth;
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                default:
                    $result .= $value{$i};
                    break;
            }
        }
        return $result;
    }

    function parseField($field, $value, &$hdr) {
        $field = strtolower($field);
        switch($field) {
            case 'date':
                $d = strtr($value, array('  ' => ' '));
                $d = explode(' ', $d);
                $this->date = getTimeStamp($d);
                break;
            case 'subject':
                $hdr->subject = $value;
                break;
            case 'from':
                $hdr->from = $this->parseAddress($value,true);
                break;
            case 'sender':
                $hdr->sender = $this->parseAddress($value);
                break;
            case 'reply-to':
                $hdr->reply_to = $this->parseAddress($value, true);
                break;
            case 'to':
                $hdr->to = $this->parseAddress($value, true);
                break;
            case 'cc':
                $hdr->cc = $this->parseAddress($value, true);
                break;
            case 'bcc':
                $hdr->bcc = $this->parseAddress($value, true);
                break;
            case 'in-reply-to':
                $hdr->in_reply_to = $value;
                break;
            case 'message-id':
                $hdr->message_id = $value;
                break;
	    case 'references':
	        $hdr->references = $value;
		break;
            case 'disposition-notification-to':
                $hdr->dnt = $this->parseAddress($value);
                break;
            case 'mime-version':
                $value = str_replace(' ', '', $value);
                $hdr->mime = ($value == '1.0' ? true : $this->mime);
                break;
            case 'content-type':
//                $hdr->parseContentType($value);
                break;
            case 'content-disposition':
//                $this->parseHeaderDisposition($value);
                break;
            case 'user-agent':
            case 'x-mailer':
                $hdr->xmailer = $value;
                break;
            case 'x-priority':
                $hdr->priority = $value;
                break;
            case 'list-post':
//                $this->mlist('post', $value);
                break;
            case 'list-reply':
//                $this->mlist('reply', $value);
                break;
            case 'list-subscribe':
//                $this->mlist('subscribe', $value);
                break;
            case 'list-unsubscribe':
//                $this->mlist('unsubscribe', $value);
                break;
            case 'list-archive':
//                $this->mlist('archive', $value);
                break;
            case 'list-owner':
//                $this->mlist('owner', $value);
                break;
            case 'list-help':
//                $this->mlist('help', $value);
                break;
            case 'list-id':
//                $this->mlist('id', $value);
                break;
            default:
                break;
        }
    }

    function parseAddress
    ($address, $ar=false, $addr_ar = array(), $group = '', $host='') {
        $pos = 0;
        $j = strlen($address);
        $name = '';
        $addr = '';
        while ($pos < $j) {
            switch ($address{$pos}) {
                case '"': /* get the personal name */
                    if ($address{++$pos} == '"') {
                        ++$pos;
                    } else {
                        while ($pos < $j && $address{$pos} != '"') {
                            if ((substr($address, $pos, 2) == '\\"') ||
                                (substr($address, $pos, 2) == '\\\\')) {
                                $name .= $address{$pos++};
                            }
                            $name .= $address{$pos++};
                        }
                    }
                    ++$pos;
                    break;
                case '<':  /* get email address */
                    $addr_start = $pos++;
                    while ($pos < $j && $address{$pos} != '>') {
                        $addr .= $address{$pos++};
                    }
                    ++$pos;
                    break;
                case '(':  /* rip off comments */
                    $addr_start = $pos;
                    for (++$pos; ($pos < $j) && ($address{$pos} != ')'); ++$pos) {
                        $addr .= $address{$pos};
                    }
                    $address_start = substr($address, 0, $addr_start);
                    $address_end   = substr($address, $pos + 1);
                    $address       = $address_start . $address_end;
                    $j = strlen($address);
                    $pos = $addr_start + 1;
                    break;
                case ',':  /* we reached a delimiter */
//case ';':
                    if ($addr == '') {
                        $addr = substr($address, 0, $pos);
                    } else if ($name == '') {
                        $name = trim(substr($address, 0, $addr_start));
                    }

                    $at = strpos($addr, '@');
                    $addr_structure = new AddressStructure();
                    $addr_structure->personal = $name;
                    $addr_structure->group = $group;
                    if ($at) {
                        $addr_structure->mailbox = substr($addr, 0, $at);
                        $addr_structure->host = substr($addr, $at+1);
                    } else {
                        $addr_structure->mailbox = $addr;
			if ($host) {
			   $addr_structure->host = $host;
			}
                    }
                    $address = trim(substr($address, $pos+1));
                    $j = strlen($address);
                    $pos = 0;
                    $name = '';
                    $addr = '';
                    $addr_ar[] = $addr_structure;
                    break;
                case ':':  /* process the group addresses */
                    /* group marker */
                    $group = substr($address, 0, $pos);
                    $address = substr($address, $pos+1);
                    $result = $this->parseAddress($address, $ar, $addr_ar, $group);
                    $addr_ar = $result[0];
                    $pos = $result[1];
                    $address = substr($address, $pos++);
                    $j = strlen($address);
                    $group = '';
                    break;
                case ';':
                    if ($group) {
                        $address = substr($address, 0, $pos - 1);
                    }
                    ++$pos;
                    break;
                default:
                    ++$pos;
                    break;
            }
        }
        if ($addr == '') {
            $addr = substr($address, 0, $pos);
        } else if ($name == '') {
            $name = trim(substr($address, 0, $addr_start));
        }
        $at = strpos($addr, '@');
        $addr_structure = new AddressStructure();
        $addr_structure->group = $group;
        if ($at) {
            $addr_structure->mailbox = trim(substr($addr, 0, $at));
            $addr_structure->host = trim(substr($addr, $at+1));
        } else {
            $addr_structure->mailbox = trim($addr);
            if ($host) {
	       $addr_structure->host = $host;
	    }
        }
        if ($group && $addr == '') { /* no addresses found in group */
            $name = "$group";
            $addr_structure->personal = $name;
            $addr_ar[] = $addr_structure;
            return (array($addr_ar,$pos+1 ));
	} elseif ($group) {
            $addr_structure->personal = $name;
            $addr_ar[] = $addr_structure;
	    return (array($addr_ar,$pos+1 ));
        } else {
            $addr_structure->personal = $name;
            if ($name || $addr) {
                $addr_ar[] = $addr_structure;
            }
        }
        if ($ar) {
            return ($addr_ar);
        }
        return ($addr_ar[0]);
    }

    function parseContentType($value) {
        $pos = strpos($value, ';');
        $props = '';
        if ($pos > 0) {
           $type = trim(substr($value, 0, $pos));
           $props = trim(substr($type, $pos+1));
        } else {
           $type = $value;
        }
        $content_type = new ContentType($type);
        if ($props) {
            $properties = $this->parseHeaderProperties($props);
            if (!isset($properties['charset'])) {
                $properties['charset'] = 'us-ascii';
            }
            $content_type->properties = $this->parseHeaderProperties($props);
        }
        $this->content_type = $content_type;
    }

    function parseHeaderProperties($value) {
        $propArray = explode(';', $value);
        $propResultArray = array();
        foreach ($propArray as $prop) {
            $prop = trim($prop);
            $pos = strpos($prop, '=');
            if ($pos > 0)  {
                $key = trim(substr($prop, 0, $pos));
                $val = trim(substr($prop, $pos+1));
                if ($val{0} == '"') {
                    $val = substr($val, 1, -1);
                }
                $propResultArray[$key] = $val;
            }
        }
        return $propResultArray;
    }

    function parseHeaderDisposition($value) {
        $pos = strpos($value, ';');
        $props = '';
        if ($pos > 0) {
            $name = trim(substr($value, 0, $pos));
            $props = trim(substr($value, $pos+1));
        } else {
            $name = $value;
        }
        $props_a = $this->parseHeaderProperties($props);
        $disp = new Disposition($name);
        $disp->properties = $props_a;
        $this->disposition = $disp;
    }

    function mlist($field, $value) {
        $res_a = array();
        $value_a = explode(',', $value);
        foreach ($value_a as $val) {
            $val = trim($val);
            if ($val{0} == '<') {
                $val = substr($val, 1, -1);
            }
            if (substr($val, 0, 7) == 'mailto:') {
                $res_a['mailto'] = substr($val, 7);
            } else {
                $res_a['href'] = $val;
            }
        }
        $this->mlist[$field] = $res_a;
    }

