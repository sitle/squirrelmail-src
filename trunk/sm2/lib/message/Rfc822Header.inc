<?php

/**
 * Rfc822Header.class.php
 *
 * Copyright (c) 2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This contains functions needed to handle mime messages.
 *
 * $Id$
 */

/*
 * rdc822_header class
 * input: header_string or array
 */
class Rfc822Header extends Header {

	function parseField($sField, $sValue) {
		$sField = strtolower($sField);
		//	echo "$sField: ".htmlspecialchars($sValue)."<BR>";
		switch($sField)
		{
		case 'date':
			$d = strtr($sValue, array('  ' => ' '));
			$d = explode(' ', $d);
//			$this->date = getTimeStamp($d);
			break;
		case 'subject':
			$this->subject = $sValue;
			break;
		case 'from':
			$sValue = $this->stripComments($sValue);
			$this->from = $this->parseHeaderAddress($sValue,true);
			break;
		case 'sender':
			$sValue = $this->stripComments($sValue);
			$this->sender = $this->parseHeaderAddress($sValue, false);
			break;
		case 'reply-to':
			$sValue = $this->stripComments($sValue);
			$this->reply_to = $this->parseHeaderAddress($sValue, true);
			break;
		case 'to':
			$sValue = $this->stripComments($sValue);
			$this->to = $this->parseHeaderAddress($sValue, true);
			break;
		case 'cc':
			$sValue = $this->stripComments($sValue);
			$this->cc = $this->parseHeaderAddress($sValue, true);
			break;
		case 'bcc':
			$sValue = $this->stripComments($sValue);
			$this->bcc = $this->parseHeaderAddress($sValue, true);
			break;
		case 'in-reply-to':
			$sValue = $this->stripComments($sValue);
			$this->in_reply_to = $sValue;
			break;
		case 'message-id':
			$sValue = $this->stripComments($sValue);
			$this->message_id = $sValue;
			break;
		case 'references':
			$sValue = $this->stripComments($sValue);
			$this->references = $sValue;
			break;
		case 'disposition-notification-to':
			$sValue = $this->stripComments($sValue);
			$this->dnt = $this->parseHeaderAddress($sValue);
			break;
		case 'mime-version':
			$sValue = $this->stripComments($sValue);
			$sValue = str_replace(' ', '', $sValue);
			$this->mime = ($sValue == '1.0' ? true : $this->mime);
			break;
		case 'content-type':
			$sValue = $this->stripComments($sValue);
			$this->content_type = $this->parseContentType($sValue);
			break;
		case 'content-disposition':
			$sValue = $this->stripComments($sValue);
			$this->disposition =  $this->parseDisposition($sValue);
			break;
		case 'user-agent':
		case 'x-mailer':
			$this->xmailer = $sValue;
			break;
		case 'x-priority':
			$sValue = $this->stripComments($sValue);
			$this->priority = $sValue;
			break;
		case 'list-post':
			$sValue = $this->stripComments($sValue);
			$this->mlist['post'] = $this->parseMlist($sValue);
			break;
		case 'list-reply':
			$sValue = $this->stripComments($sValue);
			$this->mlist['reply'] = $this->parseMlist($sValue);
			break;
		case 'list-subscribe':
			$sValue = $this->stripComments($sValue);
			$this->mlist['subscribe'] = $this->parseMlist($sValue);
			break;
		case 'list-unsubscribe':
			$sValue = $this->stripComments($sValue);
			$this->mlist['unsubscribe'] = $this->parseMlist($sValue);
			break;
		case 'list-archive':
			$sValue = $this->stripComments($sValue);
			$this->mlist['archive'] = $this->parseMlist($sValue);
			break;
		case 'list-owner':
			$sValue = $this->stripComments($sValue);
			$this->mlist['owner'] = $this->parseMlist($sValue);
			break;
		case 'list-help':
			$sValue = $this->stripComments($sValue);
			$this->mlist['help'] = $this->parseMlist($sValue);
			break;
		case 'list-id':
			$sValue = $this->stripComments($sValue);
			$this->mlist['id'] = $this->parseMlist($sValue);
			break;
		default:
			break;
		}
	}

	function parseHeaderAddress
	($sAddrLine, $bAr=false, $aAddr = array(), $sGroup = '', $sHost='') {
		$iPos = 0;
		$j = strlen($sAddrLine);
		$sName = '';
		$sAddr = '';
		while ($iPos < $j) {
			switch ($sAddrLine{$iPos}) {
			case '"': /* get the personal name */
				if ($sAddrLine{++$iPos} == '"') {
					++$iPos;
				} else {
					while ($iPos < $j && $sAddrLine{$iPos} != '"') {
						if ((substr($sAddrLine, $iPos, 2) == '\\"') ||
							(substr($sAddrLine, $iPos, 2) == '\\\\')) {
							$sName .= $sAddrLine{$iPos++};
						}
						$sName .= $sAddrLine{$iPos++};
					}
				}
				++$iPos;
				break;
			case '<':  /* get email address */
				$iAddrStartPos = $iPos++;
				while ($iPos < $j && $sAddrLine{$iPos} != '>') {
					$sAddr .= $sAddrLine{$iPos++};
				}
				++$iPos;
				break;
			case '(':  /* rip off comments */
				$iAddrStartPos = $iPos;
				for (++$iPos; ($iPos < $j) && ($sAddrLine{$iPos} != ')'); ++$iPos) {
					$sAddr .= $sAddrLine{$iPos};
				}
				$sAddrLine_start = substr($sAddrLine, 0, $iAddrStartPos);
				$sAddrLine_end = substr($sAddrLine, $iPos + 1);
				$sAddrLine = $iAddrLine_start . $sAddrLine_end;
				$j = strlen($sAddrLine);
				$iPos = $iAddrStartPos + 1;
				break;
			case ',':  /* we reached a delimiter */
				if ($sAddr == '') {
					$sAddr = substr($sAddrLine, 0, $iPos);
				} else if ($sName == '') {
					$sName = trim(substr($sAddrLine, 0, $iAddrStartPos));
				}
				$iAt = strpos($sAddr, '@');
				$oAddr =& new AddressStructure();
				if ($sName) $oAddr->personal = $sName;
				if ($sGroup) $oAddr->group = $sGroup;
				if ($iAt) {
					$oAddr->mailbox = substr($sAddr, 0, $iAt);
					$oAddr->host = substr($sAddr, $iAt+1);
				} else {
					$oAddr->mailbox = $sAddr;
					if ($sHost) {
						$oAddr->host = $sHost;
					}
				}
				$sAddrLine = trim(substr($sAddrLine, $iPos+1));
				$j = strlen($sAddrLine);
				$iPos = 0;
				$sName = '';
				$sAddr = '';
				$aAddr[] = $oAddr;
				break;
			case ':':  /* process the group addresses */
				/* group marker */
				$sGroup = substr($sAddrLine, 0, $iPos);
				$sAddrLine = substr($sAddrLine, $iPos+1);
				$result = $this->parseHeaderAddress($sAddrLine, $bAr, $aAddr, $sGroup);
				$aAddr = $result[0];
				$iPos = $result[1];
				$sAddrLine = substr($sAddrLine, $iPos++);
				$j = strlen($sAddrLine);
				$sGroup = '';
				break;
			case ';':
				if ($sGroup) {
					$sAddrLine = substr($sAddrLine, 0, $iPos - 1);
				}
				++$iPos;
				break;
			default:
				++$iPos;
				break;
			}
		}
		if ($sAddr == '') {
			$sAddr = substr($sAddrLine, 0, $iPos);
		} else if ($sName == '') {
			$sName = trim(substr($sAddrLine, 0, $iAddrStartPos));
		}
		$iAt = strpos($sAddr, '@');
		$oAddr =& new AddressStructure();
		if ($sGroup) $oAddr->group = $sGroup;
		if ($iAt) {
			$oAddr->mailbox = trim(substr($sAddr, 0, $iAt));
			$oAddr->host = trim(substr($sAddr, $iAt+1));
		} else {
			$oAddr->mailbox = trim($sAddr);
			if ($sHost) {
				$oAddr->host = $sHost;
			}
		}
		if ($sGroup && $sAddr == '') { /* no addresses found in group */
			$sName = "$sGroup";
			if ($sName) $oAddr->personal = $sName;
			$aAddr[] = $oAddr;
			return (array($aAddr,$iPos+1 ));
		} elseif ($sGroup) {
			if ($sName) $oAddr->personal = $sName;
			$aAddr[] = $oAddr;
			return (array($aAddr,$iPos+1 ));
		} else {
			if ($sName) $oAddr->personal = $sName;
			if ($sName || $sAddr) {
				$aAddr[] = $oAddr;
			}
		}
		if ($bAr) {
			return ($aAddr);
		} else {
			if (count($aAddr)) return $aAddr[0];
			else return false;
		}
	}

	function parseMlist($sValue) {
		$aRes = array();
		$aValue = explode(',', $sValue);
		foreach ($aValue as $sVal) {
			$sVal = trim($sVal);
			if ($sVal && $sVal{0} == '<') {
				$sVal = substr($sVal, 1, -1);
			}
			if (substr($sVal, 0, 7) == 'mailto:') {
				$aRes['mailto'] = substr($sVal, 7);
			} else {
				$aRes['href'] = $sVal;
			}
		}
		return $aRes;
	}

	/*
	 * function to get the addres strings out of the header.
	 * Arguments: string or array of strings !
	 * example1: header->getAddr_s('to').
	 * example2: header->getAddr_s(array('to', 'cc', 'bcc'))
	 */
	function getAddr_s($arr, $separator = ',') {
		$s = '';
		if (is_array($arr)) {
			foreach($arr as $arg) {
				if ($this->getAddr_s($arg)) {
					$s .= $separator . $result;
				}
			}
			$s = ($s ? substr($s, 2) : $s);
		} else {
			eval('$addr = $this->' . $arr . ';') ;
			if (is_array($addr)) {
				foreach ($addr as $addr_o) {
					if (is_object($addr_o)) {
						$s .= $addr_o->getAddress() . $separator;
					}
				}
				$s = substr($s, 0, -strlen($separator));
			} else {
				if (is_object($addr)) {
					$s .= $addr->getAddress();
				}
			}
		}
		return $s;
	}

	function getAddr_a($arg, $excl_arr = array(), $arr = array()) {
		if (is_array($arg)) {
			foreach($arg as $argument) {
				$arr = $this->getAddr_a($argument, $excl_arr, $arr);
			}
		} else {
			eval('$addr = $this->' . $arg . ';') ;
			if (is_array($addr)) {
				foreach ($addr as $next_addr) {
					if (is_object($next_addr)) {
						if (isset($next_addr->host) && ($next_addr->host != '')) {
							$email = $next_addr->mailbox . '@' . $next_addr->host;
						} else {
							$email = $next_addr->mailbox;
						}
						$email = strtolower($email);
						if ($email && !isset($arr[$email]) && !isset($excl_arr[$email])) {
							$arr[$email] = $next_addr->personal;
						}
					}
				}
			} else {
				if (is_object($addr)) {
					$email  = $addr->mailbox;
					$email .= (isset($addr->host) ? '@' . $addr->host : '');
					$email  = strtolower($email);
					if ($email && !isset($arr[$email]) && !isset($excl_arr[$email])) {
						$arr[$email] = $addr->personal;
					}
				}
			}
		}
		return $arr;
	}
	
	function findAddress($address, $recurs = false) {
		$result = false;
		if (is_array($address)) {
			$i=0;
			foreach($address as $argument) {
				$match = $this->findAddress($argument, true);
				$last = end($match);
				if ($match[1]) {
					return $i;
				} else {
					if (count($match[0]) && !$result) {
						$result = $i;
					}
				}
				++$i;
			}
		} else {
			if (!is_array($this->cc)) $this->cc = array();
			$srch_addr = $this->parseAddress($address);
			$results = array();
			foreach ($this->to as $to) {
				if ($to->host == $srch_addr->host) {
					if ($to->mailbox == $srch_addr->mailbox) {
						$results[] = $srch_addr;
						if ($to->personal == $srch_addr->personal) {
							if ($recurs) {
								return array($results, true);
							} else {
								return true;
							}
						}
					}
				}
			}
			foreach ($this->cc as $cc) {
				if ($cc->host == $srch_addr->host) {
					if ($cc->mailbox == $srch_addr->mailbox) {
						$results[] = $srch_addr;
						if ($cc->personal == $srch_addr->personal) {
							if ($recurs) {
								return array($results, true);
							} else {
								return true;
							}
						}
					}
				}
			}
			if ($recurs) {
				return array($results, false);
			} elseif (count($result)) {
				return true;
			} else {
				return false;
			}	
		}
		return $result;
	}

	function getContentType($type0, $type1) {
		$type0 = $this->content_type->type0;
		$type1 = $this->content_type->type1;
		return $this->content_type->properties;
	}
}

?>
