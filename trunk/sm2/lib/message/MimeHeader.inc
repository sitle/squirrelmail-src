<?php

/**
 * MessageHeader.class.php
 *
 * Copyright (c) 2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This contains functions needed to handle mime messages.
 *
 * $Id$
 */

class MimeHeader extends Header{
	/** msg_header contains all variables available in a bodystructure **/
	/** entity like described in rfc2060							   **/

	function parseField($sField, $sValue) {
		$sField = strtolower($sField);
		//	echo "$sField: ".htmlspecialchars($sValue)."<BR>";
		switch($sField)
		{
		case 'content-type':
			$sValue = $this->stripComments($sValue);
			$this->content_type = $this->parseContentType($sValue);
			break;
		case 'content-disposition':
			$sValue = $this->stripComments($sValue);
			$this->disposition =  $this->parseDisposition($sValue);
			break;
		default:
			break;
		}
	}


	function setVar($var, $value) {
		$this->{$var} = $value;
	}

	function getParameter($p) {
		$value = strtolower($p);
		return (isset($this->parameters[$p]) ? $this->parameters[$p] : '');
	}

	function setParameter($parameter, $value) {
		$this->parameters[strtolower($parameter)] = $value;
	}
}

?>
