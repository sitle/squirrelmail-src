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
class.text.ubb.php
UBB code text class
*/

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_FAQ_OBJECT_QUESTION')) define('PERMISSION_FOOWD_FAQ_OBJECT_QUESTION', '');
if (!defined('PERMISSION_FOOWD_FAQ_OBJECT_ANSWER')) define('PERMISSION_FOOWD_FAQ_OBJECT_ANSWER', '');

/** CLASS DESCRIPTOR **/
if (!defined('META_1124089157_CLASSNAME')) define('META_1124089157_CLASSNAME', 'foowd_faq');
if (!defined('META_1124089157_DESCRIPTION')) define('META_1124089157_DESCRIPTION', 'FAQ Document');

/** CLASS DECLARATION **/
class foowd_faq extends foowd_text_ubb {

	var $qas = array();

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['qas'] = array(
			'q' => '/^.{1,1024}$/',
			'a' => '/^.*$/'
		);
	}

/*** MEMBER FUNCTIONS ***/


/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		$foowd->track('foowd_faq->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>', $this->getTitle(), '</h1>';
		echo $this->processCode($this->body);
		if (is_array($this->qas)) {
			$foo = 1;
			echo '<ol>';
			foreach ($this->qas as $qa) {
				echo '<li><a href="#', $foo, '">', $qa['q'], '</a></li>';
				$foo++;
			}
			echo '</ol>';
			echo '<hr />';
			$foo = 1;
			foreach ($this->qas as $qa) {
				echo '<a name="', $foo, '"></a>';
				echo '<h2><a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'question', 'number' => $foo)), '" title="Edit question ', $foo, '" class="faq">&gt;</a> ', $qa['q'], '</h2>';
				if (isset($qa['a'])) {
					echo '<p><a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'answer', 'number' => $foo)), '" title="Edit answer ', $foo, '" class="faq">&gt;</a> ', $this->processCode($qa['a']), '</p>';
				} else {
					echo '<p><a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'answer', 'number' => $foo)), '" title="Answer question ', $foo, '" class="faq">&gt;</a> <em>This question needs answering.</em></p>';
				}
				$foo++;
			}
		}
		echo '<p>Got a question that needs answering? <a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'question')), '">Click here to add it</a>.</p>';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* write question */
	function method_question(&$foowd) {
		$foowd->track('foowd_faq->method_question');
		$questionNumber = new input_querystring('number', '/^[0-9]+$/', NULL);
		$questionForm = new input_form('questionForm', NULL, 'POST', 'Save', NULL, 'Preview');
		$questionCollision = new input_hiddenbox('questionCollision', REGEX_DATETIME, time());
		if ($questionCollision->value >= $this->updated && $questionForm->submitted()) { // if we're going to update, reset collision detect
			$questionCollision->set(time());		
		}
		$questionForm->addObject($questionCollision);
		if (isset($this->qas[$questionNumber->value - 1]['q'])) {
			$questionBody = $this->qas[$questionNumber->value - 1]['q'];
		} else {
			$questionBody = '';
		}
		$questionArea = new input_textbox('questionArea', $this->foowd_vars_meta['qas']['q'], $questionBody, NULL, 100);
		$questionForm->addObject($questionArea);
		if (isset($foowd->user->objectid) && $this->updatorid == $foowd->user->objectid) { // author is same as last author and not anonymous, so can just update
			$newVersion = new input_checkbox('newVersion', TRUE, 'Do not archive previous version?');
			$questionForm->addObject($newVersion);
		}

		if ($questionForm->submitted()) {
			if ($questionCollision->value >= $this->updated) { // has not been changed since form was loaded
				if ($questionNumber->value == NULL) {
					$this->qas[]['q'] = $questionArea->value;
				} else {
					$this->qas[$questionNumber->value - 1]['q'] = $questionArea->value;
				}
				if (isset($newVersion)) {
					$createNewVersion = !$newVersion->checked;
				} else {
					$createNewVersion = TRUE;
				}
				if ($this->save($foowd, $createNewVersion)) {
					header('Location: '.getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'view')));
					exit;
				} else {
					trigger_error('Could not save FAQ.');
				}
			} else { // edit collision!
				if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
				echo '<h1>Write A Question</h1>';
				echo '<h3>Warning: This object has been updated by another user since you started editing, please reload the edit page and verify their changes before continuing to edit.</h3>';
				if (function_exists('foowd_append')) foowd_append($foowd, $this);
			}
		} else {
			if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
			echo '<h1>Write A Question</h1>';
			$questionForm->display();
			if ($questionForm->previewed()) {
				echo '<h2>Preview</h2>';
				echo '<div class="preview">';
				echo htmlspecialchars($questionArea->value);
				echo '</div>';
			}
			if (function_exists('foowd_append')) foowd_append($foowd, $this);
		}		
		$foowd->track();
	}
	
/* answer question */
	function method_answer(&$foowd) {
		$foowd->track('foowd_faq->method_answer');

		$answerNumber = new input_querystring('number', '/^[0-9]+$/', NULL);
		if ($answerNumber->value == NULL) {
			if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
			echo '<h1>Write An Answer</h1>';
			echo '<p>Use the link next to the question you want to answer.</p>';
			echo '<p><a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid)), '">Click here to view the FAQ now</a>.</p>';
			if (function_exists('foowd_append')) foowd_append($foowd, $this);
		} else {
			$answerForm = new input_form('answerForm', NULL, 'POST', 'Save', NULL, 'Preview');
			$answerCollision = new input_hiddenbox('answerCollision', REGEX_DATETIME, time());
			if ($answerCollision->value >= $this->updated && $answerForm->submitted()) { // if we're going to update, reset collision detect
				$answerCollision->set(time());		
			}
			$answerForm->addObject($answerCollision);
			if (isset($this->qas[$answerNumber->value - 1]['a'])) {
				$answerBody = $this->qas[$answerNumber->value - 1]['a'];
			} else {
				$answerBody = '';
			}
			$answerArea = new input_textarea('answerArea', $this->foowd_vars_meta['qas']['a'], $answerBody, NULL, 80, 20);
			$answerForm->addObject($answerArea);
			if (isset($foowd->user->objectid) && $this->updatorid == $foowd->user->objectid) { // author is same as last author and not anonymous, so can just update
				$newVersion = new input_checkbox('newVersion', TRUE, 'Do not archive previous version?');
				$answerForm->addObject($newVersion);
			}

			if ($answerForm->submitted()) {
				if ($answerCollision->value >= $this->updated) { // has not been changed since form was loaded
					$this->qas[$answerNumber->value - 1]['a'] = $answerArea->value;
					if (isset($newVersion)) {
						$createNewVersion = !$newVersion->checked;
					} else {
						$createNewVersion = TRUE;
					}
					if ($this->save($foowd, $createNewVersion)) {
						header('Location: '.getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'view')));
						exit;
					} else {
						trigger_error('Could not save FAQ.');
					}
				} else { // edit collision!
					if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
					echo '<h1>Write An Answer</h1>';
					echo '<h3>Warning: This object has been updated by another user since you started editing, please reload the edit page and verify their changes before continuing to edit.</h3>';
					if (function_exists('foowd_append')) foowd_append($foowd, $this);
				}
			} else {
				if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
				echo '<h1>Write An Answer</h1>';
				echo '<p><em>', $this->qas[$answerNumber->value - 1]['q'], '</em></p>';
				$answerForm->display();
				if ($answerForm->previewed()) {
					echo '<h2>Preview</h2>';
					echo '<div class="preview">';
					echo htmlspecialchars($answerArea->value);
					echo '</div>';
				}
				if (function_exists('foowd_append')) foowd_append($foowd, $this);
			}
		}
		$foowd->track();
	}
}

?>