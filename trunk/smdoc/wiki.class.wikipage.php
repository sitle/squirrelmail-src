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
wiki.class.wikipage.php
Wikipage Class
*/

/*
 * Modified by SquirrelMail Development Team
 * $Id$
 */
define('WIKIPAGECLASSID',516345167);
$HARDCLASS[WIKIPAGECLASSID] = 'wikipage';

class wikipage extends content { // a wikipage

	var $comment; // comment about this version / change

/*** Constructor ***/

	function wikipage(
		&$user,
		$title = NULL,
		$content = '',
		$viewGroup = DEFAULTVIEWGROUP,
		$editGroup = DEFAULTEDITGROUP,
		$deleteGroup = DEFAULTDELETEGROUP,
		$adminGroup = DEFAULTADMINGROUP
	) {
		track('wikipage::wikipage', $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		parent::content($user, $title, $content, FALSE, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		$this->comment = '';
		track();
	}

/*** Member Functions ***/

// update the thing
	function update($content, $comment = '', $incrementVersion = TRUE) {
		global $wtf;
		track('wikipage::update', $incrementVersion);
		parent::update($content, FALSE, $incrementVersion);
		$this->content = htmlspecialchars($content);
		if (strlen($comment) > MAXWIKIPAGECOMMENTLENGTH) {
			$comment = substr($comment, MAXWIKIPAGECOMMENTLENGTH);
		}
		$this->comment = htmlspecialchars($comment);
		track();
		return array('success' => TRUE);
	}

// preview content update
	function preview($content) {
		return array('content' => $this->getContent(htmlspecialchars($content)), 'error' => FALSE);
	}
	
// get content
	function getContent($newContent = NULL) {
		global $INTERWIKI;
		track('wikipage::getContent');

		$content = '<wikipage>';
		if (isset($newContent)) {
			$contentArray = explode("\n", $newContent);
		} else {
			$contentArray = explode("\n", $this->content);
		}

		$inTag = FALSE;
		$FILELOADED = class_exists('file');

		foreach ($contentArray as $line) {

/** do wiki regex's **/

// get type of line
			$lineType = FALSE;
			if (preg_match('/^\* ?(.+)$/', $line)) {
				$lineType = 'ul';
			}
			if (preg_match('/^\# ?(.+)$/', $line)) {
				$lineType = 'ol';
			}
			if (preg_match('/^;(.+):(.+)$/', $line)) {
				$lineType = 'dl';
			}
			if (preg_match('/^ (.+)$/', $line)) {
				$lineType = 'mono';
			}
			if (preg_match('/^:(.+)$/', $line)) {
				$lineType = 'quote';
			}

// close groups
			if ($inTag == 'ul' && $lineType != 'ul') {
				$content .= "</ul>\n";
				$inTag = FALSE;
			}
			if ($inTag == 'ol' && $lineType != 'ol') {
				$content .= "</ol>\n";
				$inTag = FALSE;
			}
			if ($inTag == 'dl' && $lineType != 'dl') {
				$content .= "</dl>\n";
				$inTag = FALSE;
			}
			if ($inTag == 'mono' && $lineType != 'mono') {
				$content .= "</monospace>\n";
				$inTag = FALSE;
			}
			if ($inTag == 'quote' && $lineType != 'quote') {
				$content .= "</quote>\n";
				$inTag = FALSE;
			}
			
// open groups

			if ((!$inTag || $inTag == 'ul') && $lineType == 'ul') { // unordered list
				if (!$inTag) {
					$content .= "<ul>\n";
					$inTag = 'ul';
				}
			}
			$line = preg_replace('/^\* ?(.+)$/', '<li>\\1</li>', $line);
			
			if ((!$inTag || $inTag == 'ol') && $lineType == 'ol') { // ordered list
				if (!$inTag) {
					$content .= "<ol>\n";
					$inTag = 'ol';
				}
			}
			$line = preg_replace('/^# ?(.+)$/', '<li>\\1</li>', $line);
			
			if ((!$inTag || $inTag == 'dl') && $lineType == 'dl') { // definition list
				if (!$inTag) {
					$content .= "<dl>\n";
					$inTag = 'dl';
				}
			}
			$line = preg_replace('/^;(.+):(.+)$/', '<dt>\\1</dt><dd>\\2</dd>', $line);

			if ((!$inTag || $inTag == 'mono') && $lineType == 'mono') { // monospace text
				if (!$inTag) {
					$content .= "<monospace>\n";
					$line = substr($line, 1);
					$inTag = 'mono';
				} else {
					$line = substr($line, 1);
				}
			}

// rule
			$line = preg_replace('/^-{4,}/', '<hr />', $line);

// subtitle
			$line = preg_replace('/^==(.+)$/', '<h3>\\1</h3>', $line);
// title
			$line = preg_replace('/^=(.+)$/', '<h2>\\1</h2>', $line);

// line break (anything after this will of had it's line breaks turned into br tags)
			if (!preg_match('/\<.+ ?\/?>/', $line) && !$inTag) {
				$line .= "<br />\n";
			}

// quote
			if ((!$inTag || $inTag == 'quote') && $lineType == 'quote') {
				if (!$inTag) {
					$content .= "<quote>\n";
					$line = substr($line, 1);
					$inTag = 'quote';
				} else {
					$line = substr($line, 1);
				}
			}

// strong
			$line = preg_replace('/\'\'\'(.+)\'\'\'/U', '<strong>\\1</strong>', $line);
// emphasis
			$line = preg_replace('/\'\'(.+)\'\'/U', '<em>\\1</em>', $line);

			$line = ' '.$line; // we do this so as to avoid having to match the start of the string as well

// files
			if ($FILELOADED) {
				$separatorPattern = '\s<>"\(\)\[\].,;:';
				$line = preg_replace_callback('/(['.$separatorPattern.'])\[file:('.TITLEMATCHREGEX.')\](['.$separatorPattern.'])/U', array($this, 'includeFileInWikiPageCallback'), $line);
			}

// automagic URLs
			$separatorPattern = '\s<>(\)\[\],;:'; // this pattern is used to find the end of things to link
			$line = preg_replace('@(['.$separatorPattern.'])('.URIMATCHREGEX.')(['.$separatorPattern.'])@U', '\\1<a href="\\2">\\2</a>\\4', $line);
			$line = preg_replace('/(['.$separatorPattern.'])('.EMAILMATCHREGEX.')(['.$separatorPattern.'])/U', '\\1<a href="mailto:\\2">\\2</a>\\3', $line);
			
// wikiwords
			$separatorPattern = '\s<>"\(\)\[\].,;:'; // this pattern is used to find the end of things to link
			$line = preg_replace('/(['.$separatorPattern.'])([A-Za-z]+([A-Z][a-z]+)+)(['.$separatorPattern.'])/U', '\\1<a href="'.THINGURI.'\\2">\\2</a>\\4', $line);
// interwiki
			foreach ($INTERWIKI as $name => $uri) {
				$line = preg_replace('/(['.$separatorPattern.'])'.$name.':([A-Za-z]+([A-Z][a-z]+)+)(['.$separatorPattern.'])/U', '\\1<a href="'.$uri.'\\2">\\2</a>\\4', $line);
			}

// brackets
			$line = preg_replace('/\[('.TITLEMATCHREGEX.')\]/U', '<a href="'.THINGURI.'\\1">\\1</a>', $line);

// escaped []'s
			$line = preg_replace('/\\\\\[/', '[', $line);
			$line = preg_replace('/\\\\\]/', ']', $line);


			$content .= substr($line, 1);
		}
		
// close left over groups
		if ($inTag == 'ul') $content .= "</ul>\n";
		if ($inTag == 'ol') $content .= "</ol>\n";
		if ($inTag == 'dl') $content .= "</dl>\n";
		if ($inTag == 'mono') $content .= "</monospace>\n";
		if ($inTag == 'quote') $content .= "</quote>\n";

		$content .= '</wikipage>';
	
		track();
		return $content;
	}

	function includeFileInWikiPageCallback($matches) {
		$obj = wtf::loadObject(getIDFromName($matches[2]), 0, 'file');
		if (is_object($obj) && method_exists($obj, 'getContent')) {
			ob_start();
			$result = $obj->getContent();
			$output = ob_get_contents().$result;
			ob_end_clean();
			return $matches[1].$output.$matches[3];
		} else {
			return $matches[0];
		}
	}

	function drawForm($url, $title = NULL, $titleIsEditable = FALSE, $content = NULL, $comment = NULL, $small = NULL, $loadtime = NULL) {
		global $wtf;
		if (isset($this)) {
			$objectid = $this->objectid;
			$version = $this->version;
			$updatorid = $this->updatorid;
			if ($title == NULL) $title = $this->title;
			if ($content == NULL) $content = $this->content;
			if ($comment == NULL) $contentIsXML = $this->comment;
		} else {
			$objectid = '';
			$version = 1;
			$updatorid = NULL;
			if ($title == NULL) $title = '';
			if ($content == NULL) $content = '';
			if ($comment == NULL) $comment = FALSE;
		}
		if ($loadtime == NULL) $loadtime = time();
		echo '<wikipage_form url="', $url, '" ';
		echo 'thingidfield="thingid" thingid="', $objectid, '" ';
		echo 'versionfield="version" version="', $version, '" ';
		echo 'loadtimefield="loadtime" loadtime="'.$loadtime.'" submit="submit" preview="preview">';
		if ($titleIsEditable) {
			echo '<content_titlebox name="title" maxlength="', MAXTITLELENGTH, '">', $title, '</content_titlebox>';
		} else {
			echo '<content_title title="', $title, '" version="', $version, '"/>';
		}
		echo '<content_canvas name="content" maxlength="', MAXCONTENTLENGTH, '">', $content, '</content_canvas>';
		if ($wtf->user->objectid == $updatorid && $wtf->user->objectid != ANONYMOUSUSERID && isset($small)) {
			if ($small == 'on') {
				echo '<content_smallupdate checked="checked" name="small"/>';
			} else {
				echo '<content_smallupdate name="small"/>';
			}
		}
		if ($comment) {
			echo '<wikipage_comment name="comment" maxlength="', MAXWIKIPAGECOMMENTLENGTH, '">', $comment, '</wikipage_comment>';
		}
		echo '</wikipage_form>';
	}

/*** Methods ***/

// create
	function method_create($thingName = NULL, $objectName = 'wikipage') { // this is both a method and a static member
		global $conn, $wtf;
		track('wikipage::method::create');

		if (isset($this)) {
			$url = THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=create';
			$objectName = get_class($this);
		} else {
			$url = THINGURI.$thingName.'&amp;class=hardclass';
		}

		$create = getValue('submit', FALSE);
		$preview = getValue('preview', FALSE);

		$title = getValue('title', FALSE);
		$content = getValue('content', FALSE);

		if ($create || $preview) { // is action to do
// create object
			$thing = new $objectName(
				$wtf->user,
				$title,
				''
			);
			if ($thing && $thing->objectid != 0) { // action to do
				if ($create) { // create thing
					$result = $thing->update($content, FALSE, FALSE);
					if ($result['success']) {
						$thing->save();
						header('Location: '.THINGIDURI.$thing->objectid.'&class='.get_class($thing));
						exit;
					} else {
						echo '<content_create_failed message="', $result['error'], '"/>';
						echo '<syntax>', $result['syntaxCheck'], '</syntax>';
					}
				} elseif ($preview) { // show preview
					$result = $thing->preview($content);
					if (!$result['error']) {
						echo '<wikipage_preview>', $result['content'], '</wikipage_preview>';
					} else {
						echo '<content_create_failed message="', $result['error'], '"/>';
						echo '<syntax>', $result['syntaxCheck'], '</syntax>';
					}
				}
				$title = $thing->title;
				$content = htmlspecialchars($content);
				wikipage::drawForm($url, $title, TRUE, $content, FALSE, NULL);
			} else {
				echo '<content_create_failed message="Could not create object ', htmlspecialchars($title), '"/>';
			}
		} else { // display empty form
			wikipage::drawForm($url, $title, TRUE, $content, FALSE, NULL);
		}
		track();
	}

	function method_edit() { // edit thing
		global $conn, $wtf;
		track('wikipage::method::edit');
		if (hasPermission($this, $wtf->user, 'editGroup')) {	// check permission
			$update = getValue('submit', FALSE);
			$preview = getValue('preview', FALSE);
			$small = getValue('small', FALSE);		
			$content = getValue('content', FALSE);
			$comment = getValue('comment', '');
			$loadtime = getValue('loadtime', NULL);

			if ($update) { // update thing
				if ($small == 'on' && $wtf->user->objectid == $this->updatorid && $wtf->user->objectid != ANONYMOUSUSERID) {
					$incrementVersion = FALSE;
				} else {
					$incrementVersion = TRUE;
				}

// check it hasn't been updated since loading the form
				if (
					(!isset($loadtime) || !is_numeric($loadtime) || $loadtime < dbdate2unixtime($this->updatorDatetime))
					&&
					($this->updatorid == ANONYMOUSUSERID || $this->updatorid != $wtf->user->objectid)
				) {
					echo '<content_edit_locked/>';
// if we have content, update the object
				} elseif ($content) {
					$result = $this->update($content, $comment, $incrementVersion);
					if ($result['success']) {
						$this->save(); // save thing to database
						$this->tidyArchive(); // tidy up archived versions of thing
						echo '<content_edit_updated/>';
					} else {
						echo '<content_edit_failed message="', $result['error'], '"/>';
						echo '<syntax>', $result['syntaxCheck'], '</syntax>';
					}
				}
				$title = $this->title;
				$content = htmlspecialchars($content);

			} elseif ($preview) { // show preview
				if ($content) {
					$result = $this->preview($content);
					if (!$result['error']) {
						echo '<wikipage_preview>', $result['content'], '</wikipage_preview>';
					} else {
						echo '<content_edit_failed message="', $result['error'], '"/>';
						echo '<syntax>', $result['syntaxCheck'], '</syntax>';
					}
				}
				$title = $this->title;
				$content = htmlspecialchars($content);

			} else { // get thing contents for editing
				$title = $this->title;
				$content = $this->content;
			}

	// display form
			$this->drawForm(THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=edit', $title, FALSE, $content, $comment, $small, $loadtime);

		} else {
			echo '<thing_permissionerror method="edit" title="'.$this->title.'"/>';
		}
		track();
	}
	
// history
	function method_history() {
		global $conn, $wtf;
		track('wikipage::method::history');

		$workspaces = workspace::getWorkspaces(); // get array of workspaces

		$where = getWhere($this->objectid, get_class($this), $wtf->user->workspaceid);

		$query = DBSelect($conn, OBJECTTABLE, NULL, array('object'), $where, NULL, array('version DESC'), NULL);
		if ($query) {
			$numberOfRecords = getAffectedRows();
			if ($numberOfRecords > 0) {
				$newer = TRUE;
				$older = TRUE;
				for ($foo = 1; $foo <= $numberOfRecords; $foo++) {
					$record = getRecord($query);
					$serializedObj = $record['object'];
					$obj = unserialize($serializedObj);
					$className = get_class($this);
					if ($foo == 1) {
						echo '<content_history thingid="'.$this->objectid.'" class="'.$className.'" url="', THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=diff" diffname="diff">';
						echo '<wikipage_history_header>';
						echo '<history_creator homeid="'.$this->creatorHomeid.'">'.$this->creatorName.'</history_creator>';
						echo '<history_created>'.date(DATEFORMAT, dbdate2unixtime($this->creatorDatetime)).'</history_created>';
						echo '<history_class>'.$className.'</history_class>';
						echo '</wikipage_history_header>';
					}
					echo '<history_item thingid="'.$obj->objectid.'" version="'.$obj->version.'">';
					echo '<history_title>'.$obj->title.'</history_title>';
					echo '<history_version thingid="'.$obj->objectid.'" class="'.$className.'" version="'.$obj->version.'"/>';
					echo '<history_updator homeid="'.$obj->updatorHomeid.'">'.$obj->updatorName.'</history_updator>';
					echo '<history_updated>'.date(DATEFORMAT, dbdate2unixtime($obj->updatorDatetime)).'</history_updated>';
					if (isset($workspaces[$obj->workspaceid])) {
						echo '<history_workspace>'.$workspaces[$obj->workspaceid].'</history_workspace>';
					} else {
						echo '<history_workspace>Unknown</history_workspace>';
					}
					echo '<history_wikicomment>'.$obj->comment.'</history_wikicomment>';
					if ($foo < $numberOfRecords) {
						if ($newer) {
							echo '<content_history_newer selected="selected" version="'.$obj->version.'" newername="version"/>';
							$newer = FALSE;
						} else {
							echo '<content_history_newer version="'.$obj->version.'" newername="version"/>';
						}
					} else {
						echo '<content_history_diff/>';
					}
					if ($foo > 1) {
						if ($older) {
							echo '<content_history_older selected="selected" version="'.$obj->version.'" oldername="previous"/>';
							$older = FALSE;
						} else {
							echo '<content_history_older version="'.$obj->version.'" oldername="previous"/>';
						}
					} else {
						echo '<content_history_diff/>';
					}
					echo '</history_item>';
				}
				echo '</content_history>';
			}
		} else {
			if ($this->workspaceid == 0) {
				echo '<history_nothinginworkspace workspaceid="'.$wtf->user->workspaceid.'"/>';	
			} else {
				echo '<history_nothinginworkspace workspaceid="'.$this->workspaceid.'"/>';
			}	
		}
		track();
	}

}

$NOPARSETAG[] = 'wikipage';
$NOPARSETAG[] = 'wikipage_preview';

$FORMAT = array_merge($FORMAT, array(
	'wikipage_form' => '<form method="post" action="{url}"><input type="hidden" name="{thingidfield}" value="{thingid}" /><input type="hidden" name="{versionfield}" value="{version}" /><input type="hidden" name="{loadtimefield}" value="{loadtime}" />',
	'/wikipage_form' => '<p><input type="submit" name="{submit}" value="Save" /> <input type="submit" name="{preview}" value="Preview" /></p></form>
<hr />
<p>
<a href="'.THINGURI.'Text%20Formatting%20Rules#wikipage">Text Formatting Rules</a> for a wikipage.<br />
<b>Emphasis:</b> \'\'italics\'\' | \'\'\'bold\'\'\' | ---- horizontal rule<br />
<b>Headings:</b> =Title | ==Subtitle<br />
<b>Lists:</b> * bullets | # numbered items | ;definition:items<br />
<b>Links:</b> JoinCapitalizedWords | [brackets] | urls automagically become links<br />
</p>',
	'wikipage_comment' => '<p>Optional comment about this change: <input type="text" name="{name}" size="50" maxlength="{maxlength}" value="',
	'/wikipage_comment' => '"/></p>',
	'wikipage_preview' => '<p>Page Preview:</p><table width="100%" class="preview"><tr><td>',
	'/wikipage_preview' => '</td></tr></table>',

	'wikipage_history_header' => '',
	'/wikipage_history_header' => '<p>Currently archived versions.</p><table><tr><th>Title</th><th>Version</th><th>Author</th><th>Created</th><th>Workspace</th><th>Comment</th><th colspan="2">Diff</th></tr>',
	'history_wikicomment' => '<td align="center">',
	'/history_wikicomment' => '</td>',

	'h2' => '<h2>',
	'/h2' => '</h2>',
	'h3' => '<h3>',
	'/h3' => '</h3>',
	'monospace' => '<pre>',
	'/monospace' => '</pre>',
	'quote' => '<blockquote>',
	'/quote' => '</blockquote>',
	'strong' => '<strong>',
	'/strong' => '</strong>',
	'em' => '<em>',
	'/em' => '</em>',
	'ul' => '<ul>',
	'/ul' => '</ul>',
	'ol' => '<ol>',
	'/ol' => '</ol>',
	'li' => '<li>',
	'/li' => '</li>',
	'dl' => '<dl>',
	'/dl' => '</dl>',
	'dt' => '<dt>',
	'/dt' => '</dt>',
	'dd' => '<dd>',
	'/dd' => '</dd>'
));

?>
