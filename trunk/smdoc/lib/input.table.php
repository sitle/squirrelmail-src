<?php
/*
Table class created from other input elements
SquirrelMail Development Team
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
                         $cellspacing = 0, $cellpadding = 0, $width = NULL, $valign = 'top') {
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
             '" cellpadding="',$this->cellpadding,'"';
        if ( $this->width != NULL )
            echo 'width="',$this->width,'"';
        echo '>';

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
