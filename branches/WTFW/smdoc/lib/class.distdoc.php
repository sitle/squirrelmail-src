<?php
/*
Copyright 2003, Paul James

This file is part of the Framework for Object Orientated Web Development (Foowd).

Foowd is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foowd is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foowd; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
class.distdoc.php
Distributed Document text class
*/

/** CLASS DESCRIPTOR **/
if (!defined('META_-759578013_CLASSNAME')) define('META_-759578013_CLASSNAME', 'foowd_distdoc');
if (!defined('META_-759578013_DESCRIPTION')) define('META_-759578013_DESCRIPTION', 'Distributed Document');

/** CLASS DECLARATION **/
class foowd_distdoc extends foowd_text_ubb {

	var $annotations;
	
/*** CONSTRUCTOR ***/

	function foowd_distdoc(
		&$foowd,
		$title = NULL,
		$body = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$editGroup = NULL,
		$viewannGroup = NULL,
		$annotateGroup = NULL
	) {
		$foowd->track('foowd_distdoc->constructor');

// base object constructor
		parent::foowd_text_ubb($foowd, $title, $body, $viewGroup, $adminGroup, $deleteGroup);

/* set method permissions */
		$className = get_class($this);
		$this->permissions['viewann'] = getPermission($className, 'viewann', 'object'. $viewannGroup);
		$this->permissions['annotate'] = getPermission($className, 'annotate', 'object'. $annotateGroup);

		$foowd->track();
	}
	
/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['annotations'] = '/^[a-zA-Z0-9- .]+$/';
	}

/*** MEMBER FUNCTIONS ***/

	function updateAnnotations($newBody) {
		$oldBody = explode("\n", $this->body);
		$newBody = explode("\n", $newBody);
		$annotations = array();
		foreach ($this->annotations as $line => $annotation) {
			if (isset($newBody[$line]) && $oldBody[$line] == $newBody[$line]) { // same, no need to change, woo
				$annotations[$line] = $this->annotations[$line];
			} elseif (isset($oldBody[$line])) { // didn't match, lines have changed, time to try and find its correct place
				$oldLine = substr($oldBody[$line], 0, 64);
				$bestFit = 0;
				$maxSimilar = 0;
				$lineNumber = 0;
				$bestPercent = 0;
				foreach ($newBody as $newLine) {
					$newLine = substr($newLine, 0, 64);
					$leve = similar_text($oldLine, $newLine, &$percent);
					if ($leve > $maxSimilar) {
						$maxSimilar = $leve;
						$bestFit = $lineNumber;
						$bestPercent = $percent;
					}
					$lineNumber++;
				}
				if ($bestFit > 0 && $bestPercent > 50) {
					$annotations[$bestFit] = $annotation;
				} elseif (!isset($annotations[$line])) {
					$annotations[$line] = $annotation;
				}
			}
		}
		$this->annotations = $annotations;
	}

/*** METHODS ***/

/* annotation */
	function method_viewann(&$foowd) {
		$foowd->track('foowd_distdoc->method_viewann');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		$lineNumber = 0;
		foreach (explode("\n", $this->body) as $line) {
			echo '<table><tr><td valign="top"><a href="';
			echo getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'annotate', 'line' => $lineNumber + 1));
			echo '">&gt;</a></td><td valign="top">';
			echo $this->processCode($line);
			if (isset($this->annotations[$lineNumber])) {
				echo '<div class="annotation">';
				echo '&quot;', $this->processCode($this->annotations[$lineNumber]['body']), '&quot; - ';
				if ($this->annotations[$lineNumber]['authorid'] != 0) {
					echo '<a href="', getURI(array('objectid' => $this->annotations[$lineNumber]['authorid'])), '">', htmlspecialchars($this->annotations[$lineNumber]['authorName']), '</a>';
				} else {
					echo $this->annotations[$lineNumber]['authorName'];
				}
				echo ' (', date(DATETIME_FORMAT, $this->annotations[$lineNumber]['date']), ')';
				echo '</div>';
			}
			echo '</td></tr></table>';
			$lineNumber++;
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

	function method_annotate(&$foowd) {
		$foowd->track('foowd_distdoc->method_annotate');
		$line = new input_querystring('line', '/^[0-9]+$/', FALSE);
		if ($line->value) {
			$line->value--; // take off one since we start our index at 0
			if (isset($this->annotations[$line->value])) {
				$annotation = $this->annotations[$line->value]['body'];
			} else {
				$annotation = '';
			}
			$annotateForm = new input_form('annotateForm', NULL, 'POST', 'Annotate', NULL, 'Delete');
			$annotateArea = new input_textarea('annotateArea', NULL, $annotation, NULL, 80, 20);
			if ($annotateForm->submitted()) {
				$this->annotations[$line->value]['body'] = $annotateArea->value;
				$this->annotations[$line->value]['authorid'] = $foowd->user->objectid;
				$this->annotations[$line->value]['authorName'] = $foowd->user->title;
				$this->annotations[$line->value]['date'] = time();
				if ($this->save($foowd, FALSE)) {
					header('Location: '.getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'viewann'))); 
					exit;
				} else {
					trigger_error('Could not update distributed document.');
				}
			} elseif ($annotateForm->previewed()) {
				unset($this->annotations[$line->value]);
				if ($this->save($foowd, FALSE)) {
					header('Location: '.getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'viewann'))); 
					exit;
				} else {
					trigger_error('Could not update distributed document.');
				}
			} else {
				if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
				echo '<h1>Annotate line ', $line->value + 1, ' of "', $this->getTitle(), '"</h1>';
				$annotateForm->addObject($annotateArea);
				$annotateForm->display();
				echo foowd_distdoc::displayCode();
				if (function_exists('foowd_append')) foowd_append($foowd, $this);
			}
		} else {
			header('Location: '.getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'viewann'))); 
			exit;
		}
		$foowd->track();
	}

}

?>