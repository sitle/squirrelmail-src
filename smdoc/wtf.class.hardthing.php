<?php
/*
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
*/

/*
wtf.class.hardthing.php
Hardthing Class
*/

/*
 * Modified by SquirrelMail Development Team
 * $Id$
 */

/* HARDTHINGCLASSID defined in wtf.config.php */
$HARDCLASS[HARDTHINGCLASSID] = 'hardthing';

class hardthing extends content {

/*** Constructor ***/

	function hardthing(
		&$user,
		$objectid = NULL
	) {
		global $HARDTHING;
		track('hardthing::hardthing', $objectid);
		
		$this->objectid = $objectid;
		$this->classid = getIDFromName(get_class($this));
		
		$this->title = htmlspecialchars($HARDTHING[$objectid]['title']);
		$this->version = 1;
		if (isset($HARDTHING[$objectid]['group'])) {
			$this->viewGroup = htmlspecialchars($HARDTHING[$objectid]['group']);
		} else {
			$this->viewGroup = DEFAULTVIEWGROUP;
		}
		$this->editGroup = GODS;
		$this->deleteGroup = GODS;
		$this->adminGroup = GODS;

		$this->creatorid = HARDTHINGAUTHORUSERID;
		$this->creatorName = HARDTHINGAUTHORNAME;
		$this->creatorHomeid = HARDTHINGAUTHORHOMEID;
		$this->creatorDatetime = HARDTHINGCREATED;
		$this->updatorid = HARDTHINGAUTHORUSERID;
		$this->updatorName = HARDTHINGAUTHORNAME;
		$this->updatorHomeid = HARDTHINGAUTHORHOMEID;
		$this->updatorDatetime = HARDTHINGCREATED;

		ob_start();
		$result = eval('return('.$HARDTHING[$objectid]['func'].'());');
		$hardThingResult = ob_get_contents().$result; // capture code output and append return results
		ob_end_clean();
		if ($hardThingResult === FALSE) {
			$this->content = '<error>'.preg_replace('|<b>Parse error</b>:  parse error, (.+) in <b>.+\([0-9]+\) : eval\(\)\'d code</b> on line <b>([0-9]+)</b>|', 'Hard thing parse error: \\1 on line \\2', $hardThingResult).'</error>';
		} else {
			$this->content = $hardThingResult; // do not validate, we are expecting the content of a hard thing to behave
		}
		
		$this->contentIsXML = TRUE;
		$this->attributes = array();
		track();
	}

/*** Member Functions ***/

	function update($content, $contentIsXML = NULL, $incrementVersion = TRUE) { // update thing
		return array('success' => FALSE, 'error' => 'You can not update a hard thing.');
	}

	function preview($content, $contentIsXML = NULL) { // return preview but don't actually update thing
		return array('content' => $content, 'error' => 'You can not update a hard thing.');
	}

	function save() {
		return FALSE;
	}
	
/*** Methods ***/
	
	function method_view() {
		global $wtf;
		track('hardthing::method::view');
		if (hasPermission($this, $wtf->user, 'viewGroup')) {
			echo $this->getContent();
		} else {
			echo '<thing_permissionerror method="view" title="'.$this->title.'"/>';
		}
		track();
	}
	
	function method_edit() {
		echo 'You can not edit a hard thing.';
	}

	function method_delete() {
		echo 'You can not delete a hard thing.';
	}

	function method_history() {
		echo 'A hard thing has no history.';
	}
	
}
?>
