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
class.codedoc.php
FOOWD Code Document class
*/

if (!defined('FOOWD_CODEDOC_PATH')) define('FOOWD_CODEDOC_PATH', '');

/** CLASS DESCRIPTOR **/
if (!defined('META_-81632104_CLASSNAME')) define('META_-81632104_CLASSNAME', 'foowd_codedoc');
if (!defined('META_-81632104_DESCRIPTION')) define('META_-81632104_DESCRIPTION', 'Source Code');

/** CLASS DECLARATION **/
class foowd_codedoc extends foowd_object {

	var $filename;
	var $annotations;

/*** CONSTRUCTOR ***/

	function foowd_codedoc(
		&$foowd,
		$title = NULL,
		$filename = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$editGroup = NULL,
		$viewannGroup = NULL,
		$annotateGroup = NULL
	) {
		$foowd->track('foowd_codedoc->constructor');

// base object constructor
		parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

		$this->filename = $filename;

		$foowd->track();
	}
	
/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['filename'] = '/^.+$/';
		$this->foowd_vars_meta['annotations'] = '/^[a-zA-Z0-9- .]+$/';
	}

/*** MEMBER FUNCTIONS ***/

	function processCode($text) {
		$text = htmlspecialchars($text); // replace special HTML chars with HTML entities
		
		$text = preg_replace('!\[h([1-4])\](.*)\[/h\1\]\r?\n\r?\n!U', '<h$1>$2</h$1>', $text);
		$text = preg_replace('!\[h([1-4])\](.*)\[/h\1\]\r?\n!U', '<h$1>$2</h$1>', $text);
		$text = preg_replace('|\[h([1-4])\](.*)\[/h\1\]|U', '<h$1>$2</h$1>', $text);
		$text = preg_replace('|\[b\](.*)\[/b\]|U', '<b>$1</b>', $text);
		$text = preg_replace('|\[i\](.*)\[/i\]|U', '<i>$1</i>', $text);
		$text = preg_replace('|\[u\](.*)\[/u\]|U', '<u>$1</u>', $text);
		$text = preg_replace('/\[img\]((http|ftp):\/\/[a-zA-Z0-9._\/ -]+)\[\/img\]/U', '<img src="$1" alt="$1" />', $text);
		$text = preg_replace('/\[img\]([a-zA-Z0-9._\/ -]+)\[\/img\]/U', '<img src="http://$1" alt="$1" />', $text);
		$text = preg_replace('/\[((http|ftp|mailto):\/\/[a-zA-Z0-9._\/ -]+)\|([A-Za-z0-9 ]+)\]/U', '<a href="$1">$3</a>', $text);
		$text = preg_replace('/\[(ftp.[a-zA-Z0-9._\/ -]+)\|([A-Za-z0-9 ]+)\]/U', '<a href="ftp://$1">$2</a>', $text);
		$text = preg_replace('/\[([a-zA-Z0-9._\/ -]+)\|([A-Za-z0-9 ]+)\]/U', '<a href="http://$1">$2</a>', $text);
		$text = preg_replace('/(\s|^)((http|ftp|mailto):\/\/[a-zA-Z0-9._\/ -]+)(\s|$)/U', '$1<a href="$2">$2</a>$4', $text);
		
		$text = str_replace("\n", "<br />\n", $text); // replace new lines with BR tags
		return $text;
	}

	function displayCode() {
		echo <<<END
<p>
[h1]heading 1[/h1] [h2]heading 2[/h2] [h3]heading 3[/h3] [h4]heading 4[/h4]<br />
[b]bold[/b] [i]italic[/i] [u]underline[/u]<br />
[http://www.example.com|hyperlink]<br />
[img]http://www.example.com/image.jpg[/img]<br />
</p>
END;
	}
	
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

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_codedoc->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new distributed document</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Title:');
		$createFilename = new input_textbox('createFilename', '/^.+$/', NULL, 'Code Filename:');
		if ($createForm->submitted() && $createTitle->value != '') {
			$object = new $className(
				$foowd,
				$createTitle->value,
				$createFilename->value
			);
			if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
				echo '<p>FOOWD code document created and saved.</p>';
				echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))), '">Click here to view it now</a>.</p>';
			} else {
				trigger_error('Could not create FOOWD code document.');
			}
		} else {
			$createForm->addObject($createTitle);
			$createForm->addObject($createFilename);
			$createForm->display();
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		$foowd->track('foowd_codedoc->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>FOOWD Code "', $this->getTitle(), '"</h1>';
		echo '<p>This page shows the contents of the FOOWD source file "', $this->filename, '".</p>';
		echo '<p>You can view and create <a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'viewann')), '">annotations to this document here</a>.</p>';
		echo '<hr /><p>';
		highlight_file(FOOWD_CODEDOC_PATH.$this->filename);
		echo '</p>';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}
	
/* annotation */
	function method_viewann(&$foowd) {
		$foowd->track('foowd_codedoc->method_viewann');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>FOOWD Code "', $this->getTitle(), '"</h1>';
		echo '<p>This page shows the contents of the FOOWD source file "', $this->filename, '" with annotations. To create or amend an annotation, click the arrow to the left of the line you want to annotate.</p>';
		echo '<p>You can view this document with <a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'view')), '">syntax highlighting here</a>.</p>';
		echo '<hr /><p>';
		$lineNumber = 0;
		$handle = fopen(FOOWD_CODEDOC_PATH.$this->filename, 'r');
		while (!feof($handle)) {
			$line = fgets($handle, 4096);
			echo '<table><tr><td valign="top"><a href="';
			echo getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'annotate', 'line' => $lineNumber + 1));
			echo '" title="Annotate Line ', $lineNumber + 1, '" class="annotate">&gt;</a></td><td valign="top">';
			echo '<pre class="code">', htmlspecialchars($line), '</pre>';
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
		fclose($handle);
		echo '</p>';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

	function method_annotate(&$foowd) {
		$foowd->track('foowd_codedoc->method_annotate');
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