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
wtf.class.thread.php
Thread Class
*/

$HARDCLASS[824200323] = 'thread';

define('COMMENTTABLE', 'tblComment');

class thread extends content {

/*** Constructor ***/

	function thread(
		&$user,
		$title,
		$content = '',
		$contentIsXML = FALSE,
		$viewGroup = DEFAULTVIEWGROUP,
		$editGroup = DEFAULTEDITGROUP,
		$deleteGroup = DEFAULTDELETEGROUP,
		$adminGroup = DEFAULTADMINGROUP
	) {
		track('thread::thread', $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		parent::content($user, $title, $content, $contentIsXML, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		track();
	}

/*** Member Functions ***/

	function comments_view() {
		track('thread::comments_view');
		track();
	}
	
	function comment_add() {
		track('thread::comment_add');
		track();
	}

	function comment_delete() {
		track('thread::comment_delete');
		track();
	}

/*** Methods ***/

	function method_view() {
		track('thread::method::view');
		if (hasPermission($this, $wtf->user, 'viewGroup')) {
			parent::method_view();
			comments_view();
		} else {
			echo '<thing_permissionerror method="view" title="'.$this->title.'"/>';
		}
		track();
	}
	
	function method_addComment() {
	
	}
	
}
?>