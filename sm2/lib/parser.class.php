<?php
/**
 * parser.class.inc
 *
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Generic parser functions.
 * Also allows displaying of attachments when possible.
 *
 *
 * $Id$
 */

class Parser {
	var $handlers = array();

	function setArgumentHandler($arg, $callbackfunction) {
		$this->handler[$arg] = $callbackfunction;
	}
	/**
	 * Retrieve an argument from a string
	 *
	 * @param 	str		$s		 	string to process
	 * @param 	ar		$aEndChars  stoppers
	 * @param 	int		$iOffset	offset insite $s
	 * @return 	str	 	$ret		found argument
	 * @return  bool	$ret		failure
	 *
	 * @access 	public
	 * @author	Marc Groot Koerkamp
	 */
	function parseString($s, $aEndChars, &$iOffset) {
		$ret = false;
		foreach ($aEndChars as $char) {
			if ($iPos=strpos($s, $char, $iOffset)) {
				$ret = substr($s,$iOffset,$iPos-$iOffset);
				$iOffset = $iPos;
				return $ret;
			}
		}
		/* error processing */
		$this->error['string'] = $s;
		$this->error['offset'] = $iOffset;
		$this->error['endchars'] = $aEndChar;
		$this->error['result'] = $ret;
		$this->error['function'] = 'parseString';
		return $ret;
	}

	/**
	 * Retrieve an integer from a string
	 *
	 * @param 	str		$s		 	string to process
	 * @param 	ar		$aEndChars  stoppers
	 * @param 	int		$iOffset	offset insite $s
	 * @return 	str	 	$ret		found argument
	 * @return	bool		$ret		failure
	 *
	 * @access 	public
	 * @author	Marc Groot Koerkamp
	 */
	function parseInteger($s, $aEndChars, &$iOffset) {
		$ret = false;
		foreach ($aEndChars as $char) {
			if ($iPos=strpos($s, $char, $iOffset)) {
				$ret = substr($s,$iOffset,$iOffset-$iPos);
				$iOffset += $iPos;
				break;
			}
		}
		$int = (int) $ret;
		if ($int == $ret) {
			return $int;
		}
		/* error processing */
		$this->error['string'] = $s;
		$this->error['offset'] = $iOffset;
		$this->error['endchars'] = $aEndChar;
		$this->error['result'] = $ret;
		$this->error['function'] = 'parseInteger';
		return $ret;
	}
	
	/**
	 * Retrieve an the string between the square brackets
	 *
	 * @param 	str		$s		 	string to process
	 * @param 	int		$iOffset	offset insite $s
	 * @return 	str	 	$ret		found argument
	 * @return	bool		$ret		failure
	 *
	 * @access 	public
	 * @author	Marc Groot Koerkamp
	 */
	function parseBracket(&$s, &$iOffset) {
		$ret = false;
		if ($s{$iOffset} == '[') {
			++$iOffset;
			$i_end = strpos($s,']',$iOffset);
			$ret = substr($s,$iOffset,$i_end-$iOffset);
			$iOffset = $i_end+1;
			return $ret;
		}
			/* error processing */
		$this->error['string'] = $s;
		$this->error['offset'] = $iOffset;
		$this->error['result'] = $ret;
		$this->error['function'] = 'parseBracket';
		return $ret;
	}

	/**
	 * Retrieve an the string enclosed by startchar and endchar
	 *
	 * @param 	str		$s		 	string to process
	 * @param 	int		$iOffset	offset insite $s
	 * @param 	str		$sStartchar	beginning char 
	 * @param 	str		$sEndchar	endingchar	 
	 * @return 	str	 	$ret		found argument
	 * @return	bool		$ret		failure
	 *
	 * @access 	public
	 * @author	Marc Groot Koerkamp
	 */
	function parseEnclosed(&$s, &$iOffset, $sStartchar, $sEndchar) {
		$ret = false;
		if ($s{$iOffset} == $sStartchar) {
			++$iOffset;
			$i_end = strpos($s,$sEndchar,$iOffset);
			$ret = substr($s,$iOffset,$i_end-$iOffset);
			$iOffset = $i_end+1;
			return $ret;
		}
			/* error processing */
		$this->error['string'] = $s;
		$this->error['offset'] = $iOffset;
		$this->error['startchar'] = $sStartchar;
		$this->error['endchar'] = $sEndchar;		
		$this->error['result'] = $ret;
		$this->error['function'] = 'enclosedBracket';
		return $ret;
	}	
	
	function parseNil(&$sRead, &$i) {
		$orig = $sRead;
		$orig_i = $i;
		$sNil = substr($sRead,$i,3);
		if (strtoupper($sNil) == 'NIL') {
                        $i+=3;
			return true;
		} else {
			$i++;
			return false;
		}
	}

	function parseQuote(&$sRead, &$i) {
		$s = '';
                ++$i;
		$iPos = $i;
		while (true) {
			$iPos = strpos($sRead,'"',$iPos);
			if ($iPos === false) {
                                /* error */
                                $i = strlen($read);
                                return false;
			} else {
				switch ($iPos)
				{
				case 0:
					if (!$s || ($s && substr($s,-1) != '\\')) {
						$i = $iPos;
						break 2;
					}
					break;
				default:
					if ($iPos && $sRead{$iPos -1} != '\\') {
						$s .= substr($sRead,$i,($iPos-$i));
						$i = $iPos;
						break 2;
					}
					break;
				}
			}
		}
                ++$i;
		return $s;
	}

	function parseArray(&$sRead, &$i, &$iCnt) {
		$this->findChar($sRead, $i, $iCnt, '(');
		//$this->streamShift($sRead,$i,$iCnt);
		$s = substr($this->findChar($sRead, $i, $iCnt,')'),1);
		$this->streamShift($sRead,$i,$iCnt);
		return (explode(' ',$s));
	}
}
