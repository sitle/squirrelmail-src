<?php
/*
 * Created by SquirrelMail Development Team
 * This class created by porting the hardthing class from WTFW to FOOWD.
 *
 * Original copyright from WTFW:
	This file is part of the Wiki Type Framework (WTF).
	Copyright 2002, Paul James
	See README and COPYING for more information, or see http://wtf.peej.co.uk

	WTF is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	WTF is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with WTF; if not, write to the Free Software

	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * 
 * $Id$
 */

/*
print crc32('external');
exit;
*/

define('EXTERNAL_CLASS_ID', 1481810360);

/** 
 * ARRAY filled with external resources
 * Each static resource should supply the following:
 *   func - name of function to call to retrieve content
 *   title - title of external resource
 *   cvs_info - CVS file id string, for last modified and version (optional)
 */
if ( !isset($EXTERNAL_RESOURCES) ) $EXTERNAL_RESOURCES = array();

/** CLASS DESCRIPTOR **/
$foowd_class_meta[EXTERNAL_CLASS_ID]['className'] = 'foowd_external';
$foowd_class_meta[EXTERNAL_CLASS_ID]['description'] = _('External Resource');

/** CLASS METHOD PASSTHRU FUNCTION **/
function foowd_external_classmethod(&$foowd, $methodName) {
    track('foowd_external_classmethod', $methodName);
    foowd_external::$methodName($foowd, 'foowd_external'); 
    track();
}


class foowd_external extends foowd_object {

/*** Constructor ***/

	function foowd_external(
		&$foowd,
		$objectid = NULL
	) {
		global $EXTERNAL_RESOURCES;
        track('foowd_external::foowd_external');
		
        $this->__wakeup(); // init meta arrays

		$this->objectid = $objectid;
		$this->classid = EXTERNAL_CLASS_ID;
        $this->workspaceid = 0;
        $this->creatorid = 0;
        $this->creatorName = 'System';
        $this->updatorid = 0;
        $this->updatorName = 'System';
		
		$this->title = htmlspecialchars($EXTERNAL_RESOURCES[$objectid]['title']);

        if ( isset($EXTERNAL_RESOURCES[$objectid]['cvs_info']) ) {
            //$Id$
            preg_match("/,v ([0-9.]+) (\d+)\/(\d+)\/(\d+) (\d+):(\d+):(\d+) /", 
                       $EXTERNAL_RESOURCES[$objectid]['cvs_info'], $matches);
            $this->version = $matches[1];
            $last_modified = mktime($matches[5],$matches[6],$matches[7],
                                    $matches[3],$matches[4],$matches[2]); 
        } else {
		    $this->version = 0;
            $last_modified = time();
        }

        $this->created = $last_modified;
        $this->updated = $last_modified;

        // method permissions
        if ( isset($EXTERNAL_RESOURCES[$objectid]['group']) ) 
            $view_group = htmlspecialchars($EXTERNAL_RESOURCES[$objectid]['group']);
        else
            $view_group = setVarConstOrDefault($viewGroup, 
                                              'DEFAULT_VIEW_GROUP', 'Everyone');

        $this->permissions['view'] = $view_group;
        $this->permissions['admin'] = setConstOrDefault('DEFAULT_READONLY_GROUP', 
                                                        'Untouchable');
        $this->permissions['delete'] = setConstOrDefault('DEFAULT_READONLY_GROUP', 
                                                         'Untouchable');
        $this->permissions['clone'] = setConstOrDefault('DEFAULT_READONLY_GROUP', 
                                                        'Untouchable');
        track();
    }

    function set(&$foowd, $member, $value = NULL) {
        return FALSE;
    }

    function save(&$foowd) {
        return FALSE;
    }

    function delete(&$foowd) {
        return FALSE;
    }

/** METHODS */    
    function method_view(&$foowd) {     
        global $EXTERNAL_RESOURCES;
        track('foowd_external::method_view');

        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        $methodName = $EXTERNAL_RESOURCES[$this->objectid]['func'];

        if (function_exists($methodName)) {
            $methodName(&$foowd);
        } else {
            echo 'Request for unknown method "', $methodName ,
                 '" on external resource "' , $this->title, '"',
                 ' (object id = ', $this->objectid, ')';
        }

        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
    }

    function method_history(&$foowd) {
        track('foowd_external::method_history');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
        echo 'An external resource has no history.';
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
    }

    function method_admin(&$foowd) {
        track('foowd_external::method_admin');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
        echo 'Cannot administrate an external resource.';
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
    }
    
    function method_revert(&$foowd) {
        track('foowd_external::method_revert');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
        echo 'Cannot revert an external resource.';
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
    }

    function method_delete(&$foowd) {
        track('foowd_external::method_delete');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
        echo 'Cannot delete an external resource.';
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
    }
    
    function method_clone(&$foowd) {
        track('foowd_external::method_clone');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
        echo 'Cannot clone an external resource.';
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
    }
} // end static class
?>
