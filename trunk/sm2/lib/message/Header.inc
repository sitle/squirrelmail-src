<?php

class Header {

	function parseHeader($sHdr) {
		/* First we unfold the header */
		$sHdr = trim(str_replace(array("\r\n\t", "\r\n "),array('', ''), $sHdr));
		/* Now we can make a new header array with */
		/* each element representing a headerline  */
		$aHdr = explode("\r\n" , $sHdr);
		foreach ($aHdr as $sLine) {
			$iPos = strpos($sLine, ':');
			if ($iPos > 0) {
				$sField = substr($sLine, 0, $iPos);
				if (!strstr($sField,' ')) { /* valid field */
					$sValue = trim(substr($sLine, $iPos+1));
					$this->parseField($sField, $sValue);
				}
			}
		}
	}

	function stripComments($sValue) {
		$sResult = '';
		$iPO = strpos($sValue,'(');
		if ($iPO) {
			while ($sValue) {
				$iQ = strpos($sValue,'"');
				$iPO = strpos($sValue,'(');
				switch (true)
				{
				case ($iQ<$iPO && $iQ !==false):
					$sResult .= substr($sValue,0,$iQ+1);
					$sValue = substr($sValue,$iQ+1);
					$iQ = strpos($sValue,'"');
					if ($iQ) {
						while ($iQ && $sValue{$iQ-1} != '\\') {
							$sResult .= substr($sValue,0,$iQ+1);
							$sValue = substr($sValue,$iQ+1);
							$iQ = strpos($sValue,'"');
						}
					} else { /* strpos '"' = 0 */
						$sResult .= substr($sValue,0,1);
						$sValue = substr($sValue,1);
					}
					break;
				case ($iPO !== false && $iQ === false):
				case ($iQ>$iPO && $iQ !== false):
					$iDepth = 1;
					$sResult .= substr($sValue,0,$iPO);
					$sValue = substr($sValue,$iPO+1);
					$iPC = strpos($sValue,')');
						$iPO = strpos($sValue,'(');
					while (($iDepth > 0) && $iPC !==false) {
						switch (true)
						{
						case ($iPO && $iPO < $iPC && $sValue{$iPO-1} != '\\'): 
							++$iDepth;
							$sValue = substr($sValue,$iPO+1);
							break;
						case ($iPC && $iPO > $iPC && $sValue{$iPC-1} != '\\'): 
							$iDepth;
							$sValue = substr($sValue,$iPC+1);
							break;
						default :
							$sValue = substr($sValue,$iPC+1);
							$iDepth; 
							break;
						}
						$iPC = strpos($sValue,')');
						$iPO = strpos($sValue,'(');
					}
					if ($iPO !== false) {
						$sResult .= substr($sValue,0,$iPO-1);
						$sValue = substr($sValue,$iPO);
					}
					break;
				default:
					$sResult .= $sValue;
					$sValue = '';
					break;
				}
			}
		} else {
			$sResult = $sValue;
		}
		return $sResult;
	}
		
	function parseContentType($sValue) {
		$iPos = strpos($sValue, ';');
		$sProps = '';
		$aRes = array();
		if ($iPos > 0) {
		   $aRes[] = trim(substr($sValue, 0, $iPos)); 
		   $aRes[] = $this->parseProperties(trim(substr($sValue, $iPos+1)));
		} else {
			$aRes[] = $sValue;
		}
		return $aRes;
	}

	function parseProperties($sValue) {
		$aProps = explode(';', $sValue);
		$aPropsResult = array();
		foreach ($aProps as $sProp) {
			$sProp = trim($sProp);
			$iPos = strpos($sProp, '=');
			if ($iPos > 0)  {
				$sKey = trim(substr($sProp, 0, $iPos));
				$sVal = trim(substr($sProp, $iPos+1));
				if ($sVal{0} == '"') {
					$sVal = substr($sVal, 1, -1);
				}
				$aPropsResult[$sKey] = $sVal;
			}
		}
		return $aPropsResult;
	}

	function parseDisposition($sValue) {
		$iPos = strpos($sValue, ';');
		$sProps = '';
		$aRes = array();
		if ($iPos > 0) {
			$aRes[] = trim(substr($sValue, 0, $iPos));
			$aRes[] = $this->parseProperties(trim(substr($sValue, $iPos+1)));
		} else {
			$aRes[] = trim(substr($sValue, 0, $iPos));
		}
		return $aRes;
	}

}


?>
