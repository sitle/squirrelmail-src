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

class foowd_object extends foowd_base_object {

    var $url;

/*** Constructor ***/

	/**
	 * Constructs a new Foowd objcct.
	 *
	 * @constructor foowd_object
	 * @param object foowd The foowd environment object.
	 * @param optional str title The objects title.
	 * @param optional str viewGroup The user group for viewing the object.
	 * @param optional str adminGroup The user group for administrating the object.
	 * @param optional str deleteGroup The user group for deleting the object.
	 * @param optional bool allowDuplicateTitle Allow object to have the same title as another object.
	 */
	function foowd_object(
		&$foowd,
		$title = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$allowDuplicateTitle = NULL
	) {
        parent::foowd_base_object($foowd, $title,
                                  $viewGroup, $adminGroup, $deleteGroup,
                                  $allowDuplicateTitle);
        
        // check for dups among external objects
        if ( defined('EXTERNAL_CLASS_ID') ) {
            while( smdoc_external::objectExists($objectid) )
            {
			    if (!$allowDuplicateTitle) {
				    trigger_error('Could not create object, duplicate title "'.htmlspecialchars($title).'".');
				    $this->objectid = 0;
                    return FALSE;
			    }
			    $this->objectid++;
            }
        }

        $this->url = getURI(array('classid' => $this->classid,
                                  'objectid' => $this->objectid));
    }

	/**
	 * Serialization sleep method.
	 *
	 * Do not include Foowd meta arrays when serialising the object..
	 *
	 * @class foowd_object
	 * @method __sleep
	 * @return array Array of the names of the member variables to keep when serialising.
	 */
	function __sleep() {
        unset($this->url);
		return parent::__sleep();
	}

	/**
	 * Serliaisation wakeup method.
	 *
	 * Re-create Foowd meta arrays not stored when object was serialized.
	 *
	 * @class foowd_object
	 * @method __wakeup
	 */
	function __wakeup() {
        parent::__wakeup();
        $this->url = getURI(array('classid' => $this->classid,
                                  'objectid' => $this->objectid));
    }

    /**
	 * Get object content.
	 *
	 * @class foowd_text_plain
	 * @method getContent
	 * @param object foowd The foowd environment object.
	 * @return str The objects text contents processed for outputting.
	 */
	function getContent(&$foowd) {
        ob_start();                       // Start output buffering
        show($this);                      // list this object's contents
        $contents = ob_get_contents();    // Get the contents of the buffer
        ob_end_clean();  
        return $contents;
    }

	/**
	 * Output an object creation form and process its input.
	 *
	 * @class foowd_object
	 * @method private class_create
	 * @param object foowd The foowd environment object.
	 * @param str className The name of the class.
	 */
	function class_create(&$foowd, $className) {
        $foowd->track('foowd_object->class_create');

        $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
        $createForm = new input_form('createForm', NULL, 'POST', _("Create"), NULL);
        $createForm->getFormValue('createTitle', $title, REGEX_TITLE, $queryTitle->value);

        if ($createForm->submitted() && $title != '') {
            $object = new $className($foowd,$title->value);
            if ( $object->objectid != 0 && $object->save($foowd, FALSE)) {
                header('Location: ' . 
                       getURI(array('objectid' => $object->objectid,
                                    'classid' => crc32(strtolower($className)),
                                    'ok' => _("Object created and saved."))));
            } else {
                trigger_error('Could not create object.');
            }
        } else {
            $create = $foowd->tpl->factory('object.create.tpl');
            $foowd->tpl->assign('PAGE_TITLE', _("Create new object"));
        }

        $foowd->track();
    }


	/**
	 * Output the object.
	 *
	 * @class foowd_object
	 * @method private method_view
	 * @param object foowd The foowd environment object.
	 */
    function method_view(&$foowd) {     
        $foowd->track('smdoc_object->method_view');
    
        $body = $this->getContent($foowd);

        $foowd->tpl->assign('PAGE_TITLE', $this->title);
        $foowd->tpl->assign('PAGE_TITLE_URL', 
                            '<a href="'.$this->url.'">'.$this->title.'</a>');
        $foowd->tpl->assign_by_ref('CURRENT_OBJECT', $this);
        $foowd->tpl->assign_by_ref('BODY', $body);

        $foowd->track();
    }

	/**
	 * Output the objects history.
	 *
	 * @class foowd_object
	 * @method private method_history
	 * @param object foowd The foowd environment object.
	 */
	function method_history(&$foowd) {
        $foowd->track('foowd_object->method_history');
        $objArray = $foowd->getObject(array(
			'objectid' => $this->objectid,
			'classid' => $this->classid
		));

        $history = $foowd->tpl->factory('object.history.tpl');
        $history->assign_by_ref('OBJ_VERSIONS', $objArray);

        $foowd->tpl->assign('PAGE_TITLE', $this->title);
        $foowd->tpl->assign('PAGE_TITLE_URL', 
                            '<a href="'.$this->url.'">'.$this->title.'</a>');
        $foowd->tpl->assign_by_ref('CURRENT_OBJECT', $this);
 
        $foowd->tpl->assign('BODY', $history);      

        $foowd->track();
    }

} // end static class
?>
