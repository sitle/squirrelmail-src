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
wtf.validate.php
Input Content XML Validation
*/

/*** Content Validation ***/

$parsebody = array();
$parsebody_depth = 0;
$parsebody_error = '';
$parsebody_count = 0;

/* display validate content XML */
function validateContent(&$user, $content) {
	global $parsebody, $parsebody_count, $parsebody_depth, $parsebody_error, $ENTITY, $parsebody_user;
	track('validateContent');

	$startCountValue = $parsebody_count;
	$parsebody_user = &$user;
	$parsebody_depth++;
	$parsebody[$parsebody_depth] = '';
	$xml_parser = xml_parser_create();
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
	xml_set_element_handler($xml_parser, 'validateContentOpen', 'validateContentClose');
	xml_set_character_data_handler($xml_parser, 'validateContentCharData');
	xml_set_processing_instruction_handler($xml_parser, 'validateContentProcessingInstruction');
	xml_set_default_handler($xml_parser, "validateContentDefaultHandler");

	$xml = '<?xml version="1.0"?>';
// define entities
	if (isset($ENTITY)) {
		$xml .= '<!DOCTYPE wtf [';
		foreach ($ENTITY as $entity => $cdata) {
			$xml .= '<!ENTITY '.$entity.' "'.$cdata.'">';
		}
		$xml .= ']>';
	}
	$xml .= '<wtf>'.$content.'</wtf>'; // wrap up content in base tag.
	
	if (!xml_parse($xml_parser, $xml)) {
		$error = ucfirst(xml_error_string(xml_get_error_code($xml_parser))).' on line #'.xml_get_current_line_number($xml_parser);
	} elseif ($parsebody_error != '') {
		$error = $parsebody_error;
	} elseif ($parsebody_count != $startCountValue) {
		$error = 'Badly formed XML.';
	} else {
		$error = FALSE;
	}
	xml_parser_free($xml_parser);
	$parsebody_depth--;
	track();
	return array('content' => $parsebody[$parsebody_depth + 1], 'error' => $error);
}

/* process entities */
function validateContentDefaultHandler($parser, $data) {
	global $ENTITY, $parsebody, $parsebody_depth, $parsebody_last, $parsebody_error;
	if (substr($data, 0, 1) == "&" && substr($data, -1, 1) == ";") { // entity lookup
		$entityName = substr($data, 1, -1);
		if (isset($ENTITY[$entityName])) {
			$parsebody[$parsebody_depth] .= $data;
			$parsebody_last = $data;
		} else {
			$parsebody_error .= 'Unknown entity '.$data.'. ';		
		}
	}
}

function validateContentOpen($parser, $name, $attrs) {
	global $TAGS, $TAGSDEFAULT, $TAGSGROUP, $PPTAG, $PPTAGGROUP;
	global $parsebody, $parsebody_depth, $parsebody_last, $parsebody_tags, $parsebody_count, $parsebody_error, $parsebody_user;
	global $wtf;

	$name = strtolower($name);

// check to see if tag is valid
	if ($name == 'wtf') {
		// do nothing, we don't want that base wrapper tag back thank you.
	} elseif (isset($PPTAG[$name])) { // if pre-process tag exists, do pre-processing
		if (isset($PPTAGGROUP[$name]) && !$wtf->user->inGroup($PPTAGGROUP[$name])) {
			$parsebody_error .= 'You do not have permission to use the tag \''.$name.'\'. ';
		} else {
			$parsebody[$parsebody_depth] .= $PPTAG[$name]($attrs);
		}
	} elseif (!isset($TAGS[$name])) {
		$parsebody_error .= 'Unknown tag \''.$name.'\'. ';
	} elseif (isset($TAGSGROUP[$name]) && !$wtf->user->inGroup($TAGSGROUP[$name])) {
		$parsebody_error .= 'You do not have permission to use the tag \''.$name.'\'. ';
	} else {

		$parsebody[$parsebody_depth] .= '<'.$name;
		$attrnum = 0;
		$foundattr = '';

		if (isset($TAGS[$name])) { // if tag exists check for valid attributes
			foreach ($attrs as $testattr => $attrcontents) {
				$testattr = strtolower($testattr);
				$attrexist = false;
				$attrlist = '';
				$foundattr .= ' '.$testattr;
				if ($TAGS[$name] != '') {
					foreach (explode(' ', $TAGS[$name]) as $correctattr) {
						if ($testattr == $correctattr) {
							$attrexist = true;
							$attrnum++;
							$parsebody[$parsebody_depth] .= ' '.$testattr.'="'.addamp($attrcontents).'"';
						}
						$attrlist .= ' "'.$correctattr.'"';
					}
				}
				if (!$attrexist) {
					if ($attrlist) {
						$parsebody_error .= 'Unknown attribute \''.$testattr.'\' for tag \''.$name.'\', valid attributes are:'.$attrlist.'. ';
					} else {
						$parsebody_error .= 'Unknown attribute \''.$testattr.'\' for tag \''.$name.'\', tag has no attributes. ';
					}
					break;
				} elseif (isset($TAGSGROUP[$name.'.'.$testattr]) && !$parsebody_user->inGroup($TAGSGROUP[$name.'.'.$testattr])) {
					$parsebody_error .= 'You do not have permission to use attribute \''.$testattr.'\' for tag "'.$name.'". ';
					break;
				}
			}

	// check to make sure all required attributes are present
			foreach (explode(' ', $TAGS[$name]) as $attr) {
				$attr = strtolower($attr);
				if ($attr != '' && strpos($foundattr, $attr) === false) {
					if (isset($TAGSDEFAULT[$name][$attr])) {
						$parsebody[$parsebody_depth] .= ' '.$attr.'="'.$TAGSDEFAULT[$name][$attr].'"';
					} else {
						$parsebody[$parsebody_depth] .= ' '.$attr.'=""';
					}
				}
			}
		}
		$parsebody[$parsebody_depth] .= '>';
	}
	$parsebody_count++;
	$parsebody_tags[$parsebody_count] = $name;
	$parsebody_last = $name;
}

function ValidateContentClose($parser, $name) {
	global $TAGS, $PPTAG, $parsebody, $parsebody_depth, $parsebody_last, $parsebody_tags, $parsebody_count, $parsebody_error;

	$name = strtolower($name);

	if (!isset($parsebody_tags[$parsebody_count]) || $parsebody_tags[$parsebody_count] != $name) {
		if ($name == 'wtf') {
			$parsebody_error .= 'Badly formed XML. ';
		} else {
			$parsebody_error .= 'Badly formed XML near \''.$name.'\'. ';
		}
	} elseif (isset($PPTAG[$name])) {
// end tag taken care of by PPTAG, do nothing
	} elseif ($parsebody_last == $name && $name != 'wtf') {
		$parsebody[$parsebody_depth] = substr($parsebody[$parsebody_depth], 0, strlen($parsebody[$parsebody_depth]) - 1).'/>';
	} elseif (isset($TAGS[$name])) {
		$parsebody[$parsebody_depth] .= '</'.$name.'>';
	}
	$parsebody_count--;
}

function ValidateContentCharData($parser, $content) {
	global $parsebody, $parsebody_depth, $parsebody_last;
	$parsebody_last = '';
	$parsebody[$parsebody_depth] .= addlt(addgt(addamp($content)));
//	$parsebody[$parsebody_depth] .= htmlspecialchars($content); // ??? not sure, test this replacement works
}

function validateContentProcessingInstruction($parser, $target, $data) {
	global $parsebody, $parsebody_depth, $parsebody_last, $parsebody_error, $PI, $PIGROUP, $PIVALIDATE, $wtf;
	$parsebody_last = '';

	if (!isset($PI[$target])) {
		$parsebody_error .= 'Unknown processing instruction \''.$target.'\'. ';
	} elseif (!$wtf->user->inGroup($PIGROUP[$target])) {
		$parsebody_error .= 'You do not have permission to use the processing instruction \''.$target.'\'. ';
	} else {

		if (isset($PIVALIDATE[$target])) {
			$validate = $PIVALIDATE[$target]($data);
			if ($validate === FALSE) {
				$parsebody[$parsebody_depth] .= '<?'.$target."\n".trim($data)."\n?>";
			} else {
				$parsebody_error .= $validate.' ';
			}
		} else {
			$parsebody[$parsebody_depth] .= '<?'.$target."\n".trim($data)."\n?>";
		}
	}
}

?>