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
wtf.class.content.php
Content Class
*/

$HARDCLASS[-20631383] = 'content';

class content extends thing { // a thing

	var $content, $contentIsXML;
	var $attributes;

/*** Constructor ***/

	function content(
		&$user,
		$title = NULL,
		$content = '',
		$contentIsXML = FALSE,
		$viewGroup = DEFAULTVIEWGROUP,
		$editGroup = DEFAULTEDITGROUP,
		$deleteGroup = DEFAULTDELETEGROUP,
		$adminGroup = DEFAULTADMINGROUP
	) {
		track('content::content', $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		parent::thing($user, $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		if (strlen($content) < MAXCONTENTLENGTH) {
			$this->contentIsXML = $contentIsXML;
			if ($contentIsXML) {
				// check for valid XML
				$isValid = validateContent($user, $content);
				if (!$isValid['error']) { // content good
					$this->content = $isValid['content'];
				} else { // oops, an error, append error to content and set it as non-XML content
					$this->content = $isValid['error']."<br/><br/>".htmlspecialchars($content);
					$this->contentIsXML = FALSE;
				}
			} else {
				$this->content = htmlspecialchars($content);
			}
		}
		$this->attributes = array();
		track();
	}

/*** Member Functions ***/

// update the thing
	function update($content, $contentIsXML = NULL, $incrementVersion = TRUE) { // update thing
		global $wtf;
		track('content::update', $contentIsXML, $incrementVersion);
		if (!is_bool($contentIsXML)) {
			$contentIsXML = $this->contentIsXML;
		}
		if ($contentIsXML) {
			// check for valid XML
			$isValid = validateContent($wtf->user, $content);
			if (!$isValid['error']) { // content good
				parent::update($wtf->user, $incrementVersion);
				$this->content = $isValid['content'];
				$this->contentIsXML = TRUE;
				track(); return array('success' => TRUE);
			} else { // oops, an error, return the error message
				track(); return array('success' => FALSE, 'error' => $isValid['error'], 'syntaxCheck' => syntaxHightlight($content));
			}
		} else {
			parent::update($wtf->user, $incrementVersion);
			$this->content = htmlspecialchars($content);
			$this->contentIsXML = FALSE;
			track(); return array('success' => TRUE);
		}
	}

// preview content update
	function preview($content, $contentIsXML = NULL) { // return preview but don't actually update thing
		global $wtf;
		track('content::preview', $contentIsXML);
		if (!is_bool($contentIsXML)) {
			$contentIsXML = $this->contentIsXML;
		}
		if ($contentIsXML) {
			// check for valid XML
			$isValid = validateContent($wtf->user, $content);
			if (!$isValid['error']) { // content good
				track(); return array('content' => processContent($isValid['content']), 'error' => FALSE);
			} else { // oops, an error, return the error message
				track(); return array('content' => $content, 'error' => $isValid['error'], 'syntaxCheck' => syntaxHightlight($content));
			}
		} else {
			track(); return array('content' => replaceNewLines(htmlspecialchars($content)), 'error' => FALSE);
		}
	}

// attributes
	function addAttribute($attribute) {
		track('content::addAttribute', $attribute);
		$attribute = htmlspecialchars($attribute);
		if (!in_array($attribute, $this->attributes)) {
			$this->attributes[] = $attribute;
		}
		track(); return TRUE;
	}

	function removeAttribute($attribute) {
		track('content::removeAttribute', $attribute);
		if (is_array($this->attributes)) {
			$key = array_search(htmlspecialchars($attribute), $this->attributes);
			if ($key) {
				unset($this->attributes[$key]);
				track(); return TRUE;
			} else {
				track(); return FALSE;
			}
		} else {
			track(); return FALSE;
		}
	}

	function hasAttribute($attribute) {
		track('content::hasAttribute', $attribute);
		if (is_array($this->attributes) && in_array(htmlspecialchars($attribute), $this->attributes)) {
			track(); return TRUE;
		} else {
			track(); return FALSE;
		}
	}
	
	function getContent() {
		if ($this->contentIsXML) {
			return processContent($this->content); // process content for PIs
		} else {
			return replaceNewLines($this->content); // replace new lines with tag
		}
	}
	
	function drawForm($url, $title = NULL, $titleIsEditable = FALSE, $content = NULL, $contentIsXML = NULL, $small = NULL, $loadtime = NULL) {
		global $wtf;
		if (isset($this)) {
			$objectid = $this->objectid;
			$version = $this->version;
			$updatorid = $this->updatorid;
			if ($title == NULL) $title = $this->title;
			if ($content == NULL) $content = $this->content;
			if ($contentIsXML == NULL) $contentIsXML = $this->contentIsXML;
		} else {
			$objectid = '';
			$version = 1;
			$updatorid = NULL;
			if ($title == NULL) $title = '';
			if ($content == NULL) $content = '';
			if ($contentIsXML == NULL) $contentIsXML = FALSE;
		}
		if ($loadtime == NULL) $loadtime = time();
		echo '<content_form url="', $url, '" ';
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
		echo '<content_type name="type">';
		if ($contentIsXML) {
			echo '<content_typedefault value="xml">an XML document with formatting</content_typedefault>';
			echo '<content_typeoption value="cdata">plain text without formatting</content_typeoption>';
		} else {
			echo '<content_typeoption value="xml">an XML document with formatting</content_typeoption>';
			echo '<content_typedefault value="cdata">plain text without formatting</content_typedefault>';
		}
		echo '</content_type>';			
		echo '</content_form>';
	}
	
/*** Methods ***/
	
// view
	function method_view() {
		global $wtf;
		track('content::method::view');
		if (getValue('version', FALSE)) {
			echo '<thing_info version="'.$this->version.'" class="'.get_class($this).'"/>';
		}
		if (hasPermission($this, $wtf->user, 'viewGroup')) {
			echo $this->getContent();
		} else {
			echo '<thing_permissionerror method="view" title="'.$this->title.'"/>';
		}
		track();
	}

// create
	function method_create($thingName = NULL, $objectName = 'content') { // this is both a method and a static member
		global $conn, $wtf;
		track('content::method::create');

		if ($wtf->user->inGroup(CREATORS)) { // check permission
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
			if (getValue('type', FALSE) == 'xml') {
				$contentIsXML = TRUE;
			} else {
				$contentIsXML = FALSE;
			}

			if ($create || $preview) { // is action to do

	// create object
				$thing = new $objectName(
					$wtf->user,
					$title,
					'',
					$contentIsXML
				);
				if ($thing && $thing->objectid != 0) { // action to do
					if ($create) { // create thing
						$result = $thing->update($content, $contentIsXML, FALSE);
						if ($result['success']) {
							$thing->save();
							header('Location: '.THINGIDURI.$thing->objectid.'&class='.get_class($thing));
							exit;
						} else {
							echo '<content_create_failed message="', $result['error'], '"/>';
							echo '<syntax>', $result['syntaxCheck'], '</syntax>';
						}
					} elseif ($preview) { // show preview
						$result = $thing->preview($content, $contentIsXML);
						if (!$result['error']) {
							echo '<content_preview>', $result['content'], '</content_preview>';
						} else {
							echo '<content_create_failed message="', $result['error'], '"/>';
							echo '<syntax>', $result['syntaxCheck'], '</syntax>';
						}
					}
					$title = $thing->title;
					$content = htmlspecialchars($content);
					content::drawForm($url, $title, TRUE, $content, $contentIsXML, NULL);
				} else {
					echo '<content_create_failed message="Could not create object ', htmlspecialchars($title), '"/>';
				}
			} else { // display empty form
				content::drawForm($url, $title, TRUE, $content, $contentIsXML, NULL);
			}
		} else {
			echo '<content_create_permission/>';
		}
		track();
	}

	function method_edit() { // edit thing
		global $conn, $wtf;
		track('content::method::edit');
		if (hasPermission($this, $wtf->user, 'editGroup')) {	// check permission
			$update = getValue('submit', FALSE);
			$preview = getValue('preview', FALSE);
			$small = getValue('small', FALSE);		
			if (getValue('type', FALSE) == 'xml') {
				$contentIsXML = TRUE;
			} else {
				$contentIsXML = FALSE;
			}
			$content = getValue('content', FALSE);
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
					$result = $this->update($content, $contentIsXML, $incrementVersion);
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
					$result = $this->preview($content, $contentIsXML);
					if (!$result['error']) {
						echo '<content_preview>', $result['content'], '</content_preview>';
					} else {
						echo '<content_edit_failed message="', $result['error'], '"/>';
						echo '<syntax>', $result['syntaxCheck'], '</syntax>';
					}
				}
				$title = $this->title;
				$content = htmlspecialchars($content);

			} else { // get thing contents for editing
				$title = $this->title;
				if ($this->contentIsXML) {
					$content = htmlspecialchars($this->content);
				} else {
					$content = $this->content;
				}
				$contentIsXML = $this->contentIsXML;
			}

			$this->drawForm(THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=edit', $title, FALSE, $content, $contentIsXML, $small, $loadtime);

		} else {
			echo '<thing_permissionerror method="edit" title="'.$this->title.'"/>';
		}
		track();
	}


// history
	function method_history() {
		global $conn, $wtf;
		track('content::method::history');

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
						echo '<content_history_header>';
						echo '<history_creator homeid="'.$this->creatorHomeid.'">'.$this->creatorName.'</history_creator>';
						echo '<history_created>'.date(DATEFORMAT, dbdate2unixtime($this->creatorDatetime)).'</history_created>';
						echo '<history_class>'.$className.'</history_class>';
						echo '</content_history_header>';
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

// display diff
	function method_diff() {
		global $wtf;
		track('content::method::diff');

		if (DIFFCOMMAND) {
			if (hasPermission($this, $wtf->user, 'editGroup')) {	// check permission
				$oldVersion = getValue('previous', $this->version - 1);

				if (is_numeric($oldVersion) && $oldVersion > 0 && $oldVersion < $this->version) {

					$oldPageObj = &wtf::loadObject($this->objectid, $oldVersion, get_class($this));
					$oldPage = htmlspecialchars($oldPageObj->content)."\n";
					$newPage = htmlspecialchars($this->content)."\n";

					echo '<diff old="', $oldVersion, '" new="', $this->version, '">';

					$fileid = time();
					$oldFile = $_ENV['TEMP'].'/wtf'.$fileid.'-1';
					$newFile = $_ENV['TEMP'].'/wtf'.$fileid.'-2';

					ignore_user_abort(TRUE); // don't halt if aborted during diff

					if (!($fp1 = fopen($oldFile, 'w')) || !($fp2 = fopen($newFile, 'w'))) {
						echo '<error>Could not create temp files required for diff engine.</error>';
					} elseif (fwrite($fp1, $oldPage) < 0 || fwrite($fp2, $newPage) < 0) {
							echo '<error>Could not write to temp files required for diff engine.</error>';
					} else {

						fclose($fp1);
						fclose($fp2);

						if (!DIFFINLINE) {
							$diffResult = shell_exec(DIFFCOMMAND.' '.$oldFile.' '.$newFile);
						} else {
							$diffResult = shell_exec(DIFFINLINECOMMAND.' '.$oldFile.' '.$newFile);
						}

						if ($diffResult === FALSE) {
							echo '<error>Error occured running diff engine "', DIFFCOMMAND, '".</error>';
						} elseif ($diffResult == FALSE) {
							echo 'Versions are identical.';
						} else { // parse output to be nice

							$diffResultArray = explode("\n", $diffResult);
							$newPageArray = explode("\n", $newPage);
							$lineNumber = 0;
							$addLineNumber = 1;
							$minusLineNumber = 1;
							$outputLineCount = 0;

							foreach($diffResultArray as $diffLine) {
								if (!DIFFINLINE) { // section diff
									if (preg_match(DIFFADDIDREGEX, $diffLine)) {
										preg_match(DIFFADDIDREGEX, $diffLine, $linePosition);
										echo '<diff_add position="', $linePosition[1], '"/>';
										$addLineNumber = $linePosition[1];
									} elseif (preg_match(DIFFMINUSIDREGEX, $diffLine)) {
										preg_match(DIFFMINUSIDREGEX, $diffLine, $linePosition);
										echo '<diff_remove position="', $linePosition[1], '"/>';
										$minusLineNumber = $linePosition[1];
									} elseif (preg_match(DIFFCHANGEIDREGEX, $diffLine)) {
										preg_match(DIFFCHANGEIDREGEX, $diffLine, $linePosition);
										echo '<diff_change remove="', $linePosition[1], '" add="', $linePosition[3], '"/>';
										$addLineNumber = $linePosition[3];
										$minusLineNumber = $linePosition[1];
									} elseif (preg_match(DIFFADDREGEX, $diffLine)) {
										echo preg_replace(DIFFADDREGEX, '<diff_plus lineNumber="'.$addLineNumber.'">\\1</diff_plus>', $diffLine);
										$addLineNumber++;
										$lineNumber++;
									} elseif (preg_match(DIFFMINUSREGEX, $diffLine)) {
										echo preg_replace(DIFFMINUSREGEX, '<diff_minus lineNumber="'.$minusLineNumber.'">\\1</diff_minus>', $diffLine);
										$minusLineNumber++;
									}
								} else { // inline diff
									if ($outputLineCount > 2) {
										if (preg_match(DIFFINLINEADDREGEX, $diffLine)) {
											echo preg_replace(DIFFINLINEADDREGEX, '<diff_plus lineNumber="'.$addLineNumber.'">\\1</diff_plus>', $diffLine);
											$addLineNumber++;
											$lineNumber++;
										} elseif (preg_match(DIFFINLINEMINUSREGEX, $diffLine)) {
											echo preg_replace(DIFFINLINEMINUSREGEX, '<diff_minus lineNumber="'.$minusLineNumber.'">\\1</diff_minus>', $diffLine);
											$minusLineNumber++;
										} else {
											echo '<diff_line lineNumber="', ($lineNumber + 1), '">', $diffLine, '</diff_line>';
											$lineNumber++;
											$addLineNumber++;
											$minusLineNumber = $addLineNumber;
										}
									}
									$outputLineCount++;
								}
							}
						}

						unlink($oldFile);
						unlink($newFile);
					}

					ignore_user_abort(FALSE); // all done, it's ok to abort now

					echo '</diff>';

				} else {
					echo '<error>Can not calculate the differences between version ', htmlentities($oldVersion), ' and version ', $this->version, '.</error>';
				}
			} else {
				echo '<thing_permissionerror method="diff" title="', $this->title, '"/>';
			}
		} else {
			echo '<error>Diffs have been disabled.</error>';
		}
		track();
	}

}

$NOPARSETAG[] = 'content_canvas';
$NOPARSETAG[] = 'diff';

// formatting
$FORMAT = array_merge($FORMAT, array(

// form
	'content_form' => '<form method="post" action="{url}"><input type="hidden" name="{thingidfield}" value="{thingid}" /><input type="hidden" name="{versionfield}" value="{version}" /><input type="hidden" name="{loadtimefield}" value="{loadtime}" />',
	'/content_form' => '<p><input type="submit" name="{submit}" value="Save" /> <input type="submit" name="{preview}" value="Preview" /></p></form>',
	'content_title' => '<p>Editing thing "<a href="'.THINGURI.'{title}">{title}</a>" (version {version})</p>',
	'/content_title' => '',
	'content_titlebox' => '<p>Title: <input type="text" name="{name}" size="50" maxlength="{maxlength}" value="',
	'/content_titlebox' => '"/></p>',
	'content_canvas' => '<p><textarea name="{name}" rows="22" cols="80" maxlength="{maxlength}" style="width: 100%;" wrap="virtual">',
	'/content_canvas' => '</textarea></p>',
	'content_smallupdate' => '<p><input type="checkbox" name="{name}" id={name} /> <label for="{name}">This is a small update so don\'t archive the previous version.</label></p>',
	'content_smallupdate.checked' => '<p><input type="checkbox" name="{name}" id={name} checked="checked" /> <label for="{name}">This is a small update so don\'t archive the previous version.</label></p>',
	'/content_smallupdate' => '',
	'content_type' => '<p>Save the content as <select name="{name}">',
	'/content_type' => '</select></p>',
	'content_typeoption' => '<option value="{value}">',
	'/content_typeoption' => '</option>',
	'content_typedefault' => '<option selected="selected" value="{value}">',
	'/content_typedefault' => '</option>',
	'content_preview' => '<p>Preview:</p><table width="100%" class="preview"><tr><td>',
	'/content_preview' => '</td></tr></table>',
// edit	
	'content_edit_updated' => '<h2 class="success">Edit Successful</h2><p>Page updated successfully.</p>',
	'/content_edit_updated' => '',
	'content_edit_failed' => '<h2 class="fail">Edit Failed</h2><p>{message}</p>',
	'/content_edit_failed' => '',
	'content_edit_locked' => '<p class="error">This page is currently being updated by someone else, please review their changes before making your own.</p>',
	'/content_edit_locked' => '',
// create	
	'content_create_updated' => '<h2 class="success">Create Successful</h2><p>Page created successfully.</p>',
	'/content_create_updated' => '',
	'content_create_failed' => '<h2 class="fail">Create Failed</h2><p>{message}</p>',
	'/content_create_failed' => '',
	'content_create_preview' => '<p>Preview:</p><table width="100%" class="preview"><tr><td>',
	'/content_create_preview' => '</td></tr></table>',
	'content_create_permission' => '<p class="error">You do not have permission to create a new thing.</p>',
	'/content_create_permission' => '',
	
// syntax highlighting
	'syntax' => '<table width="100%"><tr><td class="syntax">',
	'/syntax' => '</td></tr></table>
<p>Please check your submission for the following XML errors:<br/>
<ul>
<li>Only use <code>\'&lt;\'</code> and <code>\'&gt;\'</code> for code tags.</li>
<li>Use <code>&amp;amp;</code> instead of <code>&amp;</code>.</li>
<li>Make sure all tags are lowercase.</li>
<li>Do not nest tags incorrectly, ie. <code>&lt;b&gt;&lt;i&gt;text&lt;/b&gt;&lt;/i&gt;</code> is invalid.</li>
<li>All attribute values must be enclosed in quotes, ie. <code>&lt;link url="http://www.alink.com"&gt;A Link&lt;/link&gt;</code>.</li>
<li>Close any tags you open.</li>
<li>Close empty tags with a trailing slash, ie. <code>&lt;img src="whatever.jpg"/&gt;</code>.</li>
</ul></p>',
	'syntax_tag' => '<span class="syntaxtag">',
	'/syntax_tag' => '</span>',
	'syntax_quickformat' => '<span class="syntaxquickformat">',
	'/syntax_quickformat' => '</span>',
	'syntax_pi' => '<span class="syntaxpi">',
	'/syntax_pi' => '</span>',
	'syntax_errorline' => '<table><tr><td width="20" valign="top">{linenumber}</td><td>',
	'/syntax_errorline' => '</td></tr></table>',
	
// history
	'content_history' => '<h2>History</h2><form method="post" action="{url}">',
	'/content_history' => '</table><p><input type="submit" name="{diffname}" value="Calculate Diff" /></p></form>',
	'content_history_header' => '',
	'/content_history_header' => '<p>Currently archived versions.</p><table><tr><th>Title</th><th>Version</th><th>Author</th><th>Created</th><th>Workspace</th><th colspan="2">Diff</th></tr>',
	'content_history_newer' => '<td align="center"><input type="radio" name="{newername}" value="{version}" />',
	'content_history_newer.selected' => '<td align="center"><input type="radio" name="{newername}" value="{version}" checked="checked" />',
	'/content_history_newer' => '</td>',
	'content_history_older' => '<td align="center"><input type="radio" name="{oldername}" value="{version}" />',
	'content_history_older.selected' => '<td align="center"><input type="radio" name="{oldername}" value="{version}" checked="checked" />',
	'/content_history_older' => '</td>',
	'content_history_diff' => '<td align="center">',
	'/content_history_diff' => '</td>',

// diff
	'diff' => '<h3>Differences Between Versions {old} and {new}</h3><table width="100%">',
	'/diff' => '</table>',
	'diff_add' => '<tr height="10"><th colspan="2">Added at line {position}.',
	'/diff_add' => '</th></tr>',
	'diff_remove' => '<tr height="10"><th colspan="2">Removed from line {position}.',
	'/diff_remove' => '</th></tr>',
	'diff_change' => '<tr height="10"><th colspan="2">Changed at line {remove}.',
	'/diff_change' => '</th></tr>',
	'diff_line' => '<tr><td width="50" valign="top">{linenumber}</td><td class="diffline">',
	'/diff_line' => '</td></tr>',
	'diff_plus' => '<tr><td width="50" valign="top">{linenumber} +</td><td class="diffplus">',
	'/diff_plus' => '</td></tr>',
	'diff_minus' => '<tr><td width="50" valign="top">{linenumber} -</td><td class="diffminus">',
	'/diff_minus' => '</td></tr>'
));

?>