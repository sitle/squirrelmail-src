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
                         $cellspacing = 0, $cellpadding = 0, $width = '100%', $valign = 'top') {
        $this->caption = $caption;
        $this->width = $width;
        $this->border = $border;
        $this->cellspacing = $cellspacing;
        $this->cellpadding = $cellpadding;
        $this->valign = $valign;
    }

    function addObject($caption, &$object) {
        $this->objects[] = array('caption' => $caption, 'object' => $object); 
    }

    function addSpace() {
        $this->objects[] = 'space';
    }

    function display() {
        if ( $this->caption )
            echo '<h3>',$this->caption,'</h3>';
        echo '<table class="indexitem" border="',$this->border,'" cellspacing="',$this->cellspacing,
             '" cellpadding="',$this->cellpadding,'" width="',$this->width,'">';

        foreach ($this->objects as $obj) {
            if ( $obj == 'space' )
                echo '<tr><td colspan="3">&nbsp;</td></tr>';
            else
                echo '<tr><td valign="',$this->valign,'">', $obj['caption'], 
                     '</td><td>&nbsp;</td><td valign="',$this->valign,'">', 
                     $obj['object']->display(), '</td></tr>';
        }

        echo '</table>';
    }
}

?>
