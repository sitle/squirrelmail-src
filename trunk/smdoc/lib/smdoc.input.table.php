<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 *
 * $Id$
 */

/*
input.table.php
Table for organizing elements
*/
class input_table {

    var $border;
    var $cellspacing;
    var $cellpadding;
    var $width;
    var $valign;
    var $caption;
    var $objects = array();

    function input_table($caption = NULL,  $border = 0,
                         $cellspacing = 0, $cellpadding = 0, $width = NULL, $valign = NULL) {
        $this->caption = $caption;
        $this->width = $width;
        $this->border = $border;
        $this->cellspacing = $cellspacing;
        $this->cellpadding = $cellpadding;
        $this->valign = $valign;
    }

    function grabObjects(&$form) {
        if ( is_array($form->objects) && count($form->objects) > 0 ) {
            foreach ( $form->objects as $obj ) {
                $this->addObject($obj);
            }
        } 
    }

    function addToForm(&$form) {
        $form->objects = array();
        $form->addObject($this);
    }

    function addObject(&$object, $options = NULL) {
        if ( isset($object->caption) ) {
            $caption = $object->caption;
            $object->caption = '';
            $options['caption'] = $caption;
        }

        if ( is_string($object) )
            $obj = array('string' => $object);
        else
            $obj = array('object' => $object);

        if ( $options != NULL && is_array($options) )
            $obj = array_merge($obj, $options);
        $this->objects[] = $obj;
    }

    function addSpace() {
        $this->objects[] = array('space' => '');
    }

    function insertSpace($index) {
        $count = count($this->objects);
        if ( $index >= 0 && $index < $count ) {
            $end = array_splice($this->objects, $index);
            $this->addSpace();
            $this->objects = array_merge($this->objects, $end);
        }
    }

    function insertObject($index, &$object, $options = NULL) {
        $count = count($this->objects);
        if ( $index >= 0 && $index < $count ) {
            $end = array_splice($this->objects, $index);
            $this->addObject($object, $options);
            $this->objects = array_merge($this->objects, $end);
        }
    }

    function setOption($index, $option, $value) {
        if ( $index >= 0 && $index < count($this->objects) ) {
            $this->objects[$index][$option] = $value;
        }
    }

    function display() {
        if ( $this->caption )
            echo '<h3>',$this->caption,'</h3>';

        $width =  ( $this->width == NULL )  ? '' : ' width="'  . $this->width  . '"';
        $valign = ( $this->valign == NULL ) ? '' : ' valign="' . $this->valign . '"';

        $border =  ' border="' . $this->border . '"';
        $spacing = ' cellspacing="' . $this->cellspacing . '"';
        $padding = ' cellpadding="' . $this->cellpadding . '"';
        

        echo '<table class="indexitem"', $border, $spacing, $padding, $width, ">\n";

        foreach ($this->objects as $obj) {
            if ( isset($obj['space']) )
                echo '<tr><td colspan="3">&nbsp;</td></tr>'. "\n";
            else {
                $caption = ( isset($obj['caption']) ) ? $obj['caption'] . '&nbsp;' : '';

                if ( isset($obj['onecell']) ) {
                    echo '<tr><td',$valign,' colspan="3">',$caption;
                } else {
                    echo '<tr><td',$valign,'>',$caption,'</td><td>&nbsp;</td><td',$valign,'>';
                }
                    
                if ( isset($obj['object']) )
                    echo $obj['object']->display();
                if ( isset($obj['string']) )
                    echo $obj['string'];
                echo '</td></tr>'."\n";
            }
        }

        echo "</table>\n";
    }
}

?>
