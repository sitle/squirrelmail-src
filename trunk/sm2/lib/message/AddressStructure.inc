<?php

/**
 * AddressStructure.class.php
 *
 * Copyright (c) 2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This contains functions needed to handle mime messages.
 *
 * $Id$
 */

class AddressStructure {

	function getAddress($full = true) {
		$result = '';
		if (is_object($this)) {
			if (!isset($this->host)) $this->host='';
			if (!isset($this->mailbox)) $this->mailbox='';
			if (!isset($this->personal)) $this->personal='';
			$email = ($this->host ? $this->mailbox.'@'.$this->host : $this->mailbox);
			if (trim($this->personal)) {
				$addr = ($email ? '"' . $this->personal . '" <' .$email.'>' : $this->personal);
				$best_dpl = $this->personal;
			} else {
				$addr = $email;
				$best_dpl = $email;
			}
			$result = ($full ? $addr : $best_dpl);
		}
		return $result;
	}

	function getField($sField) {
		if (isset($this->{$sField})) {
			return $this->{$sField};
		} else {
			return false;
		}
	}

}

?>
