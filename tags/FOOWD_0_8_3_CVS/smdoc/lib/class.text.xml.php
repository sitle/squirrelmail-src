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
class.text.xml.php
Foowd XML text class
*/

/** CLASS DESCRIPTOR **/
if (!defined('META_863176601_CLASSNAME')) define('META_863176601_CLASSNAME', 'foowd_text_xml');
if (!defined('META_863176601_DESCRIPTION')) define('META_863176601_DESCRIPTION', 'XML Text Document');

/** CLASS DECLARATION **/
class foowd_text_xml extends foowd_text_plain {

	var $schema;

/*** CONSTRUCTOR ***/

	function foowd_text_xml(
		&$foowd,
		$title = NULL,
		$schema = NULL,
		$body = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$editGroup = NULL
	) {
		$foowd->track('foowd_text_xml->constructor');
	
// base object constructor
		parent::foowd_text_plain($foowd, $title, $body, $viewGroup, $adminGroup, $deleteGroup);

/* set object vars */
		$this->schema = $schema;

		$foowd->track();
	}

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['schema'] = REGEX_TITLE;
	}

/*** MEMBER FUNCTIONS ***/

	function validateXML($xml, $schema) {
		global $foowd_text_xml_scheme, $foowd_text_xml_error, $foowd_text_xml_parent;
		
		function startElement($parser, $name, $attrs) {
			global $foowd_text_xml_scheme, $foowd_text_xml_error, $foowd_text_xml_parent;

			if (isset($foowd_text_xml_scheme[$name])) {
				$attributes = explode(' ', $foowd_text_xml_scheme[$name]['attributes']);
				foreach ($attrs as $attribute => $value) {
					if (!in_array($attribute, $attributes)) {
						$foowd_text_xml_error = 'Unknown attribute "'.htmlspecialchars($attribute).'" for tag "'.htmlspecialchars($name).'"';
						return;
					}
				}
				$parents = explode(' ', $foowd_text_xml_scheme[$name]['parents']);
				if ($foowd_text_xml_parent[0] != 0 && !in_array($foowd_text_xml_parent[$foowd_text_xml_parent[0]], $parents)) {
					$foowd_text_xml_error = 'Parent "'.htmlspecialchars($foowd_text_xml_parent[$foowd_text_xml_parent[0]]).'" not allowed for tag "'.htmlspecialchars($name).'"';
					return;
				}
			} else {
				$foowd_text_xml_error = 'Unknown tag "'.htmlspecialchars($name).'"';
				return;
			}
			$foowd_text_xml_parent[0] = $foowd_text_xml_parent[0] + 1;
			$foowd_text_xml_parent[$foowd_text_xml_parent[0]] = $name;
		}

		function endElement($parser, $name) {
			global $foowd_text_xml_parent;
			$foowd_text_xml_parent[0]--;
		}

		$xmlParser = xml_parser_create();
		xml_parser_set_option($xmlParser, XML_OPTION_CASE_FOLDING, FALSE);
		xml_set_element_handler($xmlParser, "startElement", "endElement");

		$foowd_text_xml_error = FALSE;
		$foowd_text_xml_parent[0] = 0;
		$foowd_text_xml_scheme = $schema;
		
		if (!xml_parse($xmlParser, $xml)) {
			$foowd_text_xml_error = xml_error_string(xml_get_error_code($xmlParser)).' at line '.xml_get_current_line_number($xmlParser);
		}
		xml_parser_free($xmlParser);
		
		return $foowd_text_xml_error;
	}
	
	function display($content) {
		return str_replace("\n", "<br />\n", htmlspecialchars($content));
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_text_xml->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new XML object</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL, 'Preview');
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'XML Title:');
		$schemas = $foowd->getObjects(array('classid = 1579579674'));
		if ($schemas) {
			foreach ($schemas as $schema) {
				$items[$schema->objectid] = $schema->getTitle();
			}
			$createSchema = new input_dropdown('createSchema', NULL, $items, 'XML Schema:');
		} else {
			$createSchema = new input_hiddenbox('createSchema');
		}
		$createBody = new input_textarea('createBody', '', NULL, NULL, 80, 20);
		if ($createForm->submitted() || $createForm->previewed()) {
			$scheme = NULL;
			if ($schemas) {
				foreach ($schemas as $schema) {
					if ($schema->objectid == intval($createSchema->value)) {
						$scheme = $schema->tags;
						break;
					}
				}
			}
		}
		if ($createForm->submitted() && $createTitle->value != '') {
			if ($XMLError = foowd_text_xml::validateXML($createBody->value, $scheme)) {
				trigger_error('XML Error: '.$XMLError);
			} else {
				$object = new $className(
					$foowd,
					$createTitle->value,
					$createSchema->value,
					$createBody->value
				);
				if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
					echo '<p>XML object created and saved.</p>';
					echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))), '">Click here to view it now</a>.</p>';
				} else {
					trigger_error('Could not create XML object.');
				}
			}
		} elseif ($createForm->previewed() && $createTitle->value != '') {
			$createForm->addObject($createTitle);
			$createForm->addObject($createSchema);
			$createForm->addObject($createBody);
			$createForm->display();
			echo '<h1>Preview</h1>';
			if ($XMLError = foowd_text_xml::validateXML($createBody->value, $scheme)) {
				trigger_error('XML Error: '.$XMLError);
			} else {
				echo '<p class="preview">';
				echo foowd_text_xml::display($createBody->value);
				echo '</p>';
			}
		} else {
			$createForm->addObject($createTitle);
			$createForm->addObject($createSchema);
			$createForm->addObject($createBody);
			$createForm->display();
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/*** METHODS ***/

/* view object */

	function method_view(&$foowd) {
		$foowd->track('foowd_text_xml->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Viewing XML object</h1>';
		echo $this->display($this->body);
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* edit object */
	function method_edit(&$foowd) {
		$foowd->track('foowd_text_xml->method_edit');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Editing version ', $this->version, ' of "', $this->getTitle(), '"</h1>';
		$editForm = new input_form('editForm', NULL, 'POST', 'Save', NULL, 'Preview');
		$editCollision = new input_hiddenbox('editCollision', REGEX_DATETIME, time());
		if ($editCollision->value >= $this->updated && $editForm->submitted()) { // if we're going to update, reset collision detect
			$editCollision->set(time());		
		}
		$editForm->addObject($editCollision);
		$schemas = $foowd->getObjects(array('classid = 1579579674'));
		if ($schemas) {
			foreach ($schemas as $schema) {
				$items[$schema->objectid] = $schema->getTitle();
			}
			$editSchema = new input_dropdown('editSchema', NULL, $items, 'XML Schema:');
		} else {
			$editSchema = new input_hiddenbox('editSchema');
		}
		$editForm->addObject($editSchema);
		$editArea = new input_textarea('editArea', NULL, $this->body, NULL, 80, 20);
		$editForm->addObject($editArea);
		if (isset($foowd->user->objectid) && $this->updatorid == $foowd->user->objectid) { // author is same as last author and not anonymous, so can just update
			$newVersion = new input_checkbox('newVersion', TRUE, 'Do not archive previous version?');
			$editForm->addObject($newVersion);
		}
		$editForm->display();

		if ($editForm->submitted() || $editForm->previewed()) {
			$scheme = NULL;
			if ($schemas) {
				foreach ($schemas as $schema) {
					if ($schema->objectid == intval($editSchema->value)) {
						$scheme = $schema->tags;
						break;
					}
				}
			}
		}
		if ($editForm->submitted()) {
			if ($editCollision->value >= $this->updated) { // has not been changed since form was loaded
				if ($XMLError = $this->validateXML($editArea->value, $scheme)) {
					trigger_error('XML Error: '.$XMLError);
				} else {
					$this->body = $editArea->value;
					if (isset($newVersion)) {
						$createNewVersion = !$newVersion->checked;
					} else {
						$createNewVersion = TRUE;
					}
					if ($this->save($foowd, $createNewVersion)) {
						echo '<p>Text object updated and saved.</p>';
					} else {
						trigger_error('Could not save text object.');
					}
				}
			} else { // edit collision!
				echo '<h3>Warning: This object has been updated by another user since you started editing, please reload the edit page and verify their changes before continuing to edit.</h3>';
			}
		} elseif ($editForm->previewed()) {
			echo '<h3>Preview</h3>';
			if ($XMLError = $this->validateXML($editArea->value, $scheme)) {
				trigger_error('XML Error: '.$XMLError);
			} else {
				echo '<p class="preview">';
				echo $this->display($editArea->value);
				echo '</p>';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* raw */
	function method_raw(&$foowd) {
		$foowd->debug = FALSE;
		header("Content-type: text/xml");
		if (!preg_match('/^<\?xml version="[1-9].[0-9]{1,2}"\?>/', $this->body)) {
			echo '<?xml version="1.0"?>';
		}
		echo $this->body;
	}

}

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_XML_SCHEMA_OBJECT_EDIT')) define('PERMISSION_FOOWD_XML_SCHEMA_OBJECT_EDIT', 'Gods');

/** CLASS DESCRIPTOR **/
if (!defined('META_1579579674_CLASSNAME')) define('META_1579579674_CLASSNAME', 'foowd_xml_schema');
if (!defined('META_1579579674_DESCRIPTION')) define('META_1579579674_DESCRIPTION', 'XML Schema Object');

/** CLASS DECLARATION **/
class foowd_xml_schema extends foowd_object {

	var $tags = array();

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['tags'] = array(
			'attributes' => '/^[a-z0-9-]*$/',
			'parents' => '/^[a-z0-9- ]*$/'
		);
	}

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_xml_schema->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new XML schema</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Schema Title:');
		if (!$createForm->submitted() || $createTitle->value == '') {
			$createForm->addObject($createTitle);
			$createForm->display();
		} else {
			$object = new $className(
				$foowd,
				$createTitle->value
			);
			if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
				echo '<p>XML schema created and saved.</p>';
				echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))), '">Click here to view it now</a>.</p>';
			} else {
				trigger_error('Could not create XML schema .');
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

	function method_view(&$foowd) {
		$foowd->track('foowd_xml_schema->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>XML Schema "', $this->getTitle(), '"</h1>';
		foreach($this->tags as $tag => $elements) {
			if ($tag != '') {
				echo '&lt;', $tag;
				foreach (explode(' ', $elements['attributes']) as $attribute) {
					if ($attribute != '') {
						echo ' ', $attribute, '=""';
					}
				}
				echo ' /&gt;<br />';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

	function method_edit(&$foowd) {
		$foowd->track('foowd_xml_schema->method_edit');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Editing version ', $this->version, ' of XML Schema "', $this->getTitle(), '"</h1>';
		$editForm = new input_form('editForm', NULL, 'POST', 'Save', NULL);
		$editArray = new input_textarray('editArray', $this->foowd_vars_meta['tags'], $this->tags, 'Tags:');
		$editForm->addObject($editArray);
		$editForm->display();
		if ($editForm->submitted()) {
			$this->tags = $editArray->items;
			$this->save($foowd, FALSE);
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}
	
}

?>