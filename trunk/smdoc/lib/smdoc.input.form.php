<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 *
 * $Id$
 */

include_once(PATH.'input.form.php');

/**
 * Input form class.
 *
 * Extends form class by providing methods for retrieving form values
 *
 * @package foowd/input
 * @class input_form
 */
class smdoc_input_form extends input_form {
    
    var $formValues = array();

    function getFormValue($name, &$value, $regex = NULL, $default = NULL) {
        if ( array_key_exists($name, $this->formValues) ) {
            $value = $this->formValues[$name];
            return TRUE;
        }
        
		if (isset($_POST[$name])) {
		    $value = $_POST[$name];
		} elseif (isset($_GET[$name])) {
			$value = $_GET[$name];
		} else {
            $value = $default;
        }

		if ( $regex == NULL || preg_match($regex, $value) ) {
			if (get_magic_quotes_gpc()) 
                $value = stripslashes($value);
			$this->formValues[$name] = $value;
			return TRUE;
		} else {
			return FALSE;
		}
    }
}
