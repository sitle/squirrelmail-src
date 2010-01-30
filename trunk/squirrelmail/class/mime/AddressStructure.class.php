<?php

/**
 * AddressStructure.class.php
 *
 * This file contains functions needed to extract email address headers from
 * mime messages.
 *
 * @copyright 2003-2010 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 * @subpackage mime
 * @since 1.3.2
 */

/**
 * Class used to work with email address headers
 * @package squirrelmail
 * @subpackage mime
 * @since 1.3.2
 */
class AddressStructure {
    /**
     * Personal information
     * @var string
     */
    var $personal = '';
    /**
     * @todo check use of this variable. var is not used in class.
     * @var string
     */
    var $adl      = '';
    /**
     * Mailbox name.
     * @var string
     */
    var $mailbox  = '';
    /**
     * Server address.
     * @var string
     */
    var $host     = '';
    /**
     * @todo check use of this variable. var is not used in class.
     * @var string
     */
    var $group    = '';

    /**
     * Return address information from mime headers.
     * @param boolean $full return full address (true) or only personal if it exists, otherwise email (false)
     * @param boolean $encoded (since 1.4.0) return rfc2047 encoded address (true) or plain text (false).
     * @return string
     */
    function getAddress($full = true, $encoded = false) {
        $result = '';
        if (is_object($this)) {
            $email = ($this->host ? $this->mailbox.'@'.$this->host
                                  : $this->mailbox);
            $personal = trim($this->personal);
            $is_encoded = false;
            if (preg_match('/(=\?([^?]*)\?(Q|B)\?([^?]*)\?=)(.*)/i',$personal,$reg)) {
                $is_encoded = true;
            }
            if ($personal) {
                if ($encoded && !$is_encoded) {
                    $personal_encoded = encodeHeader('"' . $personal . '"');
                    if ($personal !== $personal_encoded) {
                        $personal = $personal_encoded;
                    } else {
                        $personal = '"' . $this->personal . '"';
                    }
                } else {
                    if (!$is_encoded) {
                        $personal = '"' . $this->personal . '"';
                    }
                }
                $addr = ($email ? $personal . ' <' .$email.'>'
                        : $this->personal);
                $best_dpl = $this->personal;
            } else {
                $addr = $email;
                $best_dpl = $email;
            }
            $result = ($full ? $addr : $best_dpl);
        }
        return $result;
    }

    /**
     * Shorter version of getAddress() function
     * Returns full encoded address.
     * @return string
     * @since 1.4.0
     */
    function getEncodedAddress() {
        return $this->getAddress(true, true);
    }
    
    /**
     * Return just the email portion of this address
     * @return string
     * @since 1.5.2
     */
    function getEmail () {
        $r = '';
        if (is_object($this)) {
            $r = $this->host ? $this->mailbox.'@'.$this->host : $this->mailbox;
        }
        return $r;
    }
}
