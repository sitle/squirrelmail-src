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

class input_table 
{
  var $border;
  var $cellspacing;
  var $cellpadding;
  var $width;
  var $caption;
  var $objects = array();

  function input_table($caption = NULL,  $border = 0,
                       $cellspacing = 0, $cellpadding = 0, $width = NULL) 
  {
    $this->caption = $caption;
    $this->width = $width;
    $this->border = $border;
    $this->cellspacing = $cellspacing;
    $this->cellpadding = $cellpadding;
  }

  function grabObjects(&$form) 
  {
    if ( is_array($form->objects) && count($form->objects) > 0 ) 
    {
      foreach ( $form->objects as $obj ) 
        $this->addObject($obj);
    } 
  }

  function addToForm(&$form) 
  {
    $form->objects = array();
    $form->addObject($this);
  }

  function addObject(&$object, $options = NULL) 
  {
    if ( isset($object->caption) ) 
    {
      $caption = $object->caption;
      $object->caption = '';
      $options['caption'] = $caption;
      $options['caption_class'] = 'heading';
    }

    if ( is_string($object) )
      $obj = array('string' => $object);
    else
      $obj = array('object' => $object);

    if ( $options != NULL && is_array($options) )
      $obj = array_merge($obj, $options);

    $this->objects[] = $obj;
  }

  function addSpace($class = NULL) 
  {
    $obj = array('space' => '');

    if ( $class ) 
      $obj['class'] = $class;
            
    $this->objects[] = $obj;
  }

  function insertSpace($index, $class=NULL) 
  {
    $count = count($this->objects);
    if ( $index >= 0 && $index < $count ) 
    {
      $end = array_splice($this->objects, $index);
      $this->addSpace($class);
      $this->objects = array_merge($this->objects, $end);
    }
  }

  function insertObject($index, &$object, $options = NULL) 
  {
    $count = count($this->objects);
    if ( $index >= 0 && $index < $count ) 
    {
      $end = array_splice($this->objects, $index);
      $this->addObject($object, $options);
      $this->objects = array_merge($this->objects, $end);
    }
  }

  function setOption($index, $option, $value) 
  {
    if ( $index >= 0 && $index < count($this->objects) ) 
      $this->objects[$index][$option] = $value;
  }

  function display() 
  {
    if ( $this->caption )
      echo '<h3>',$this->caption,'</h3>';

    $width =  ( $this->width == NULL )  ? '' : ' width="'  . $this->width  . '"';

    $border =  ' border="' . $this->border . '"';
    $spacing = ' cellspacing="' . $this->cellspacing . '"';
    $padding = ' cellpadding="' . $this->cellpadding . '"';
        
    echo '<table class="indexitem"', $border, $spacing, $padding, $width, ">\n";
    echo '<tr><td></td><td rowspan="', count($this->objects) + 2, '" width="10"></td><td></td></tr>', "\n";

    foreach ($this->objects as $obj) 
    {
      if ( isset($obj['space']) )
      {
        $class = ( isset($obj['class']) ) ? ' class="'. $obj['class'] . '"' : '';
        echo '<tr><td colspan="3"'.$class.'>&nbsp;</td></tr>'. "\n";
      }
      else 
      {
        $caption_class = ( isset($obj['caption_class']) ) ? ' class="'. $obj['caption_class'] . '"' : '';
        $caption = ( isset($obj['caption']) ) ? $obj['caption'] . '&nbsp;' : '';

        $value_class = ( isset($obj['value_class']) ) ? ' class="'. $obj['value_class'] . '"' : '';

        if ( isset($obj['onecell']) )
        { 
          $class = ( isset($obj['class']) ) ? ' class="'. $obj['class'] . '"' : '';
          if ( $caption != '' )
            $caption = '<span' .$caption_class.'>'. $caption . '</span>&nbsp; ';
          echo '<tr><td colspan="3"',$class,'>',$caption,'<span', $value_class, '>';
        }
        else
          echo '<tr><td',$caption_class,'>',$caption,'</td><td',$value_class,'>';

        if ( isset($obj['object']) )  echo $obj['object']->display();
        if ( isset($obj['string']) )  echo $obj['string'];
        if ( isset($obj['onecell']) ) echo '</span>';
        echo '</td></tr>'."\n";
      }
    }

    echo "</table>\n";
  }
}

?>
