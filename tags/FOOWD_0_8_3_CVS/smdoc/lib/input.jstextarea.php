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
input.jstextarea.php
Javascript enhanced textarea input object
*/

/*
Usage Example:

$buttons = array(
	'Paragraph' => array(
		'open' => '<p>',
		'close' => '</p>',
		'tooltip' => 'Turn selected text into a paragraph.'
	),
	'Title' => array(
		'open' => '<h1>',
		'close' => '</h1>',
		'tooltip' => 'Turn selected text into a page title.'
	),
	'Subtitle' => array(
		'open' => '<h2>',
		'close' => '</h2>',
		'tooltip' => 'Turn selected text into a subtitle.'
	),
	'Bold' => array(
		'open' => '<b>',
		'close' => '</b>',
		'tooltip' => 'Make selected text bold.'
	),
	'Italic' => array(
		'open' => '<i>',
		'close' => '</i>',
		'tooltip' => 'Make selected text italic.'
	),
	'Hyperlink' => array(
		'open' => '<a href=\"%1\">',
		'close' => '</a>',
		'parameters' => array(
			array(
				'prompt' => 'Enter the URL you want to link to:',
				'default' => 'http://'
			)
		),
		'tooltip' => 'Make selected text a hyperlink.'
	)
);
$textarea = new input_jstextarea('textarea', NULL, $this->body, NULL, 80, 20, NULL, NULL, $buttons);
*/

class input_jstextarea extends input_textarea {
	
	var $buttons;
	
	function input_jstextarea($name, $regex = NULL, $value = NULL, $caption = NULL, $width = NULL, $height = NULL, $maxlength = NULL, $class = NULL, $buttons = NULL) {
		parent::input_textarea($name, $regex, $value, $caption, $width, $height, $maxlength, $class);
		$this->buttons = $buttons;
	}
	
	function display() {
	
		if (is_array($this->buttons)) {
			echo '<p>';
			foreach ($this->buttons as $buttonName => $buttonTags) {
			
				echo '<script language="javascript">', "\n";
				echo 'function make', $buttonName, '() {', "\n";
				echo '	strSelection = document.selection.createRange().text;', "\n";
				echo '	if (strSelection == "") {', "\n";
				echo '		alert("You must select something to apply this formatting to.");', "\n";
				echo '	} else {', "\n";
				echo '		var strOpenTag = "', $buttonTags['open'], '";', "\n";
				echo '		var strCloseTag = "', $buttonTags['close'], '";', "\n";
				$foo = 1;
				if (isset($buttonTags['parameters']) && is_array($buttonTags['parameters'])) {
					foreach ($buttonTags['parameters'] as $parameter) {
						echo '		strParameter', $foo, ' = prompt("', $parameter['prompt'], '","', $parameter['default'], '")', "\n";
						echo '		if (strParameter', $foo, ' == null) return;', "\n";
						echo '		intReplace = strOpenTag.indexOf("%', $foo, '");', "\n";
						echo '		strOpenTag = strOpenTag.substring(0, intReplace) + strParameter', $foo, ' + strOpenTag.substring(intReplace + 2, strOpenTag.length);', "\n";
						$foo++;
					}
				}
				echo '', "\n";
				echo '		document.selection.createRange().text = strOpenTag + strSelection + strCloseTag;', "\n";
				echo '	}', "\n";
				echo '	return;', "\n";
				echo '}', "\n";
				echo '</script>';
			
				//echo '<a href="javascript: make', $buttonName, '();">', $buttonName, '</a> ';
				echo '<input type="button" onClick="javascript: make', $buttonName, '();" value="', $buttonName, '" ';
				if (isset($buttonTags['tooltip'])) {
					echo 'title="', $buttonTags['tooltip'], '" ';
				}
				echo '/> ';
			}
			echo '</p>';
		}
	
		echo $this->caption, ' <textarea name="', $this->name, '" cols="', $this->width, '" rows="', $this->height, '" wrap="virtual" class="', $this->class, '">', htmlentities($this->value), '</textarea>';
	}

}

?>