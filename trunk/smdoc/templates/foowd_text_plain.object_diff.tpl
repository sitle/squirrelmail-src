<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for performing diff between text pages
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */

$t['body_function'] = 'text_plain_view_body';

/** Include base template */
include_once(TEMPLATE_PATH.'index.tpl');

/**
 * Base template will call back to this function
 *
 * @param smdoc $foowd Reference to the foowd environment object.
 * @param string $className String containing invoked className.
 * @param string $method String containing called method name.
 * @param smdoc_user $user Reference to active user.
 * @param object $object Reference to object being invoked.
 * @param mixed $t Reference to array filled with template parameters.
 */
function text_plain_view_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  if ( isset($t['failure']) )
  {
    echo $object->view();
    return;
  }

  echo '<p>', sprintf(_("Differences between versions %d and %d of \"%s\":"), $t['version1'], $t['version2'], $t['title']), '</p>';

  echo '<table>';
  $i = 1;
  foreach ($t['diff'] as $diff)
  {
    if ( count($diff) == 0 )
      continue;

    if ( isset($diff['add']) )
    {
      $sym = '+';
      $type = 'add';
    }
    elseif ( isset($diff['minus']) )
    {
      $sym = '-';
      $type = 'minus';
    }
    else
    {
      $sym = '&nbsp;';
      $type = 'same';
    }

    $diff[$type] = rtrim($diff[$type]);
    printf("<tr class=\"diff_%s\"><td>%s %s</td></tr>\n", $type, $sym, $diff[$type]);
  }
  echo '</table>';
}


