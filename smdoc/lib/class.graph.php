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
class.graph.php
Foowd graph class
*/

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_GRAPH_OBJECT_EDIT')) define('PERMISSION_FOOWD_GRAPH_OBJECT_EDIT', 'Gods');
if (!defined('PERMISSION_FOOWD_GRAPH_OBJECT_CSV')) define('PERMISSION_FOOWD_GRAPH_OBJECT_CSV', 'Gods');

/** CLASS DESCRIPTOR **/
if (!defined('META_-1694284669_CLASSNAME')) define('META_-1694284669_CLASSNAME', 'foowd_graph');
if (!defined('META_-1694284669_DESCRIPTION')) define('META_-1694284669_DESCRIPTION', 'Graph');

/** CLASS DECLARATION **/
class foowd_graph extends foowd_object {

	var $data;
	var $description;
	var $width;
	var $height;
	var $caption_x;
	var $caption_y;
	var $red, $green, $blue;
	
/*** CONSTRUCTOR ***/

	function foowd_graph(
		&$foowd,
		$title = NULL,
		$data = array(),
		$description = NULL,
		$width = 300,
		$height = 300,
		$caption_x = NULL,
		$caption_y = NULL,
		$colour = array(255, 0, 0),
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$editGroup = NULL
	) {
		$foowd->track('foowd_graph->constructor');
	
// base object constructor
		parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

/* set object vars */
		$this->data = $data;
		$this->description = $description;
		$this->width = $width;
		$this->height = $height;
		$this->caption_x = $caption_x;
		$this->caption_y = $caption_y;
		$this->red = $colour[0];
		$this->green = $colour[1];
		$this->blue = $colour[2];
		
/* set method permissions */
		$className = get_class($this);
		$this->permissions['edit'] = getPermission($className, 'edit', 'object'. $editGroup);
		$this->permissions['csv'] = getPermission($className, 'csv', 'object'. $editGroup);

		$foowd->track();
	}

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['data'] = array(
			'x' => '/^[0-9.-]+$/',
			'y' => '/^[0-9.-]+$/'
		);
		$this->foowd_vars_meta['description'] = '/^.{1,1024}$/';
		$this->foowd_vars_meta['width'] = '/^[0-9]{1,4}$/';
		$this->foowd_vars_meta['height'] = '/^[0-9]{1,4}$/';
		$this->foowd_vars_meta['caption_x'] = '/^.*$/';
		$this->foowd_vars_meta['caption_y'] = '/^.*$/';
		$this->foowd_vars_meta['red'] = '/^[0-9]{1,3}$/';
		$this->foowd_vars_meta['green'] = '/^[0-9]{1,3}$/';
		$this->foowd_vars_meta['blue'] = '/^[0-9]{1,3}$/';
	}

/*** MEMBER FUNCTIONS ***/

	function drawGraph() {
		$im = @imagecreate($this->width, $this->height) or trigger_error('Cannot initialize new GD image stream', E_USER_ERROR);
		$background_color = imagecolorallocate($im, 255, 255, 255);
		$line_colour = imagecolorallocate($im, $this->red, $this->green, $this->blue);
		$bar_colour = imagecolorallocate($im, 0, 0, 0);
		
		if (count($this->data) < 2) {
		
			imagestring($im, 1, 0, 0, 'There must be at least two co-ordinates', $bar_colour);
			imagestring($im, 1, 0, 10, 'defined to display a graph.', $bar_colour);
		
		} else {
		
			$width = $this->width - 40;
			$height = $this->height - 40;

			if (count($this->data) > ($height / 30)) { // don't display all values on axis if there are lots of co-ords and not much graph
				$displayAllValues = FALSE;
			} else {
				$displayAllValues = TRUE;
			}

			imageline($im, 20, 20, 20, $height + 19, $bar_colour);
			imageline($im, 20, $height + 19, $width + 19, $height + 19, $bar_colour);

			imagestring($im, 1, ($width + 20 - (strlen($this->caption_x) * 5)) / 2, $height + 30, $this->caption_x, $bar_colour);
			imagestringup($im, 1, 1, ($height + 20 + (strlen($this->caption_y) * 5)) / 2, $this->caption_y, $bar_colour);

			$firstItem = reset($this->data);
			$max_x = $firstItem['x'];	$min_x = $firstItem['x'];
			$max_y = $firstItem['y'];	$min_y = $firstItem['y'];
			foreach ($this->data as $coords) {
				if ($coords['x'] > $max_x) $max_x = $coords['x'];
				if ($coords['x'] < $min_x) $min_x = $coords['x'];
				if ($coords['y'] > $max_y) $max_y = $coords['y'];
				if ($coords['y'] < $min_y) $min_y = $coords['y'];
			}

			$scale_x = ($width - 1) / ($max_x - $min_x);
			$offset_x = $min_x - (20 / $scale_x);
			$scale_y = ($height - 1) / ($max_y - $min_y);
			$offset_y = $min_y + (20 / $scale_y);;

			$start_coords = NULL;
			foreach ($this->data as $end_coords) {
				if ($start_coords == NULL) {
					$start_coords = $end_coords;
				} else {
					$x1 = intval(($start_coords['x'] - $offset_x) * $scale_x);
					$y1 = $height - 1 - intval(($start_coords['y'] - $offset_y) * $scale_y);
					$x2 = intval(($end_coords['x'] - $offset_x) * $scale_x);
					$y2 = $height - 1 - intval(($end_coords['y'] - $offset_y) * $scale_y);
//echo '(', $x1, ', ', $y1, ')(', $x2, ', ', $y2, ')<br />';
					imageline($im, $x1, $y1, $x2, $y2, $line_colour);

					if ($displayAllValues) {
						imagestring($im, 1, $x1, $height + 20, $start_coords['x'], $bar_colour);
						imagestringup($im, 1, 10, $y1, $start_coords['y'], $bar_colour);
					}

					$start_coords = $end_coords;
				}
			}
			if ($displayAllValues) {
				imagestring($im, 1, $x2, $height + 20, $start_coords['x'], $bar_colour);
				imagestringup($im, 1, 10, $y2, $start_coords['y'], $bar_colour);
			} else {
				imagestring($im, 1, 20, $height + 20, $min_x, $bar_colour);
				imagestring($im, 1, $width + 20, $height + 20, $max_x, $bar_colour);
				imagestringup($im, 1, 10, $height + 20, $min_y, $bar_colour);
				imagestringup($im, 1, 10, 20, $max_y, $bar_colour);
			}
		}

		imagepng($im);
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_graph->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new graph object</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Title:');
		$createDescription = new input_textbox('createDescription', '/^.{1,1024}$/', NULL, 'Description:');
		$createWidth = new input_textbox('createWidth', '/^[0-9]{1,4}$/', '300', 'Width:');
		$createHeight = new input_textbox('createHeight', '/^[0-9]{1,4}$/', '300', 'Height:');
		$createCaptionX = new input_textbox('createCaptionX', '/^.*$/', 'X Axis', 'X Axis Caption:');
		$createCaptionY = new input_textbox('createCaptionY', '/^.*$/', 'Y Axis', 'Y Axis Caption:');
		$createRed = new input_textbox('createRed', '/^[0-9]{1,3}$/', 255, 'Red:');
		$createGreen = new input_textbox('createGreen', '/^[0-9]{1,3}$/', 0, 'Green:');
		$createBlue = new input_textbox('createBlue', '/^[0-9]{1,3}$/', 0, 'Blue:');
		if ($createForm->submitted() && $createTitle->value != '') {
			$object = new $className(
				$foowd,
				$createTitle->value,
				NULL,
				$createDescription->value,
				$createWidth->value,
				$createHeight->value,
				$createCaptionX->value,
				$createCaptionY->value,
				array($createRed->value, $createGreen->value, $createBlue->value)
			);
			if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
				echo '<p>Graph object created and saved.</p>';
				echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)), 'method' => 'edit')), '">Click here to add data to it now</a>.</p>';
			} else {
				trigger_error('Could not create graph object.');
			}
		} else {
			$createForm->addObject($createTitle);
			$createForm->addObject($createDescription);
			$createForm->addObject($createWidth);
			$createForm->addObject($createHeight);
			$createForm->addObject($createCaptionX);
			$createForm->addObject($createCaptionY);
			$createForm->addObject($createRed);
			$createForm->addObject($createGreen);
			$createForm->addObject($createBlue);
			$createForm->display();
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		$foowd->track('foowd_graph->method_edit');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>', $this->getTitle(), '</h1>';
		if ($this->description != NULL) {
			echo '<p>', htmlspecialchars($this->description), '</p>';
		}
		echo '<img src="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'raw')), '" width="', $this->width, '" height="', $this->height, '" alt="', $this->getTitle(), '" />';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

	function method_raw(&$foowd) {
		$foowd->debug = FALSE;
		header ("Content-type: image/png");
		$this->drawGraph();
	}

/* edit object */
	function method_edit(&$foowd) {
		$foowd->track('foowd_graph->method_edit');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Editing version ', $this->version, ' of "', $this->getTitle(), '"</h1>';
		$editForm = new input_form('editForm', NULL, 'POST', 'Save', NULL);
		$editCollision = new input_hiddenbox('editCollision', REGEX_DATETIME, time());
		if ($editCollision->value >= $this->updated && $editForm->submitted()) { // if we're going to update, reset collision detect
			$editCollision->set(time());		
		}
		$editForm->addObject($editCollision);
		$editDescription = new input_textbox('editDescription', $this->foowd_vars_meta['description'], $this->description, 'Description:');
		$editWidth = new input_textbox('editWidth', $this->foowd_vars_meta['width'], $this->width, 'Width:');
		$editHeight = new input_textbox('editHeight', $this->foowd_vars_meta['height'], $this->height, 'Height:');
		$editCaptionX = new input_textbox('editCaptionX', $this->foowd_vars_meta['caption_x'], $this->caption_x, 'X Axis Caption:');
		$editCaptionY = new input_textbox('editCaptionY', $this->foowd_vars_meta['caption_y'], $this->caption_y, 'Y Axis Caption:');
		$editRed = new input_textbox('editRed', $this->foowd_vars_meta['red'], $this->red, 'Red:');
		$editGreen = new input_textbox('editGreen', $this->foowd_vars_meta['green'], $this->green, 'Green:');
		$editBlue = new input_textbox('editBlue', $this->foowd_vars_meta['blue'], $this->blue, 'Blue:');
		$editData = new input_textarray('editData', $this->foowd_vars_meta['data'], $this->data, 'Graph Data');
		$editForm->addObject($editDescription);
		$editForm->addObject($editWidth);
		$editForm->addObject($editHeight);
		$editForm->addObject($editCaptionX);
		$editForm->addObject($editCaptionY);
		$editForm->addObject($editRed);
		$editForm->addObject($editGreen);
		$editForm->addObject($editBlue);
		$editForm->addObject($editData);
		if (isset($foowd->user->objectid) && $this->updatorid == $foowd->user->objectid) { // author is same as last author and not anonymous, so can just update
			$newVersion = new input_checkbox('newVersion', TRUE, 'Do not archive previous version?');
			$editForm->addObject($newVersion);
		}
		$editForm->display();

		if ($editForm->submitted()) {
			if ($editCollision->value >= $this->updated) { // has not been changed since form was loaded
				$this->description = $editDescription->value;
				$this->width = $editWidth->value;
				$this->height = $editHeight->value;
				$this->caption_x = $editCaptionX->value;
				$this->caption_y = $editCaptionY->value;
				$this->red = $editRed->value;
				$this->green = $editGreen->value;
				$this->blue = $editBlue->value;
				$this->data = $editData->items;
				if (isset($newVersion)) {
					$createNewVersion = !$newVersion->checked;
				} else {
					$createNewVersion = TRUE;
				}
				if ($this->save($foowd, $createNewVersion)) {
					echo '<p>Graph object updated and saved.</p>';
				} else {
					trigger_error('Could not save graph object.');
				}
			} else { // edit collision!
				echo '<h3>Warning: This object has been updated by another user since you started editing, please reload the edit page and verify their changes before continuing to edit.</h3>';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* load CSV data */
	function method_csv(&$foowd) {
		$foowd->track('foowd_graph->method_csv');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Load CSV data</h1>';
		$csvForm = new input_form('editForm', NULL, 'POST', 'Load Data', NULL);
		$csvFile = new input_file('csvFile', 'CSV File:');
		$csvForm->addObject($csvFile);
		if (isset($foowd->user->objectid) && $this->updatorid == $foowd->user->objectid) { // author is same as last author and not anonymous, so can just update
			$newVersion = new input_checkbox('newVersion', TRUE, 'Do not archive previous version?');
			$csvForm->addObject($newVersion);
		}
		if ($csvForm->submitted()) {
			if ($csvFile->isUploaded()) {
				if ($fp = fopen($csvFile->file['tmp_name'], 'r')) {
					while ($line = fgetcsv($fp, 100)) {
						if (isset($line[0]) && isset($line[1])) {
							$data[] = array(
								'x' => $line[0],
								'y' => $line[1]
							);
						}
					}
					fclose($fp);
					if (isset($data)) {
    				$this->data = $data;
						if (isset($newVersion)) {
							$createNewVersion = !$newVersion->checked;
						} else {
							$createNewVersion = TRUE;
						}
    				if ($this->save($foowd, $createNewVersion)) {
							echo '<p>Data loaded from CSV file into graph object.</p>';
						} else {
							trigger_error('Could not save graph object.');
						}
    			} else {
    				trigger_error('No co-ordinate data in CSV file.');
    			}
				} else {
					trigger_error('Could not open CSV file.');
				}
			} else {
				trigger_error($csvFile->getError());
			}
		} else {
			$csvForm->display();
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

}

?>