<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 * */

/**
 * Class for HTML and Textile formatted documents.
 *
 * $Id$
 * 
 * @package smdoc
 * @subpackage text
 */

/** Class Descriptor/Meta information */
setClassMeta('smdoc_text_textile', 'Textile');

/**
 * HTML and Textile text object class.
 *
 * This class defines a HTML/Textile text area and 
 * methods to view and edit that area.
 *
 * @package smdoc
 * @subpackage text
 * @author Erin Schnabel
 * @author Brad Choate
 */
class smdoc_text_textile extends foowd_text_plain
{
  /**
   * Process text content. Processes content to turn the textile style code 
   * into HTML.
   *
   * @param string text The text to process.
   * @return string The processed content.
   */
  function processContent($text)
  {
    /*
     * Import David Allen's Textile formatter
     * -- ONE MODIFICATION is made to the formatter, these two lines:
     *     if (get_magic_quotes_gpc()==1)
     *       $text = stripslashes($text);
     * are removed because the input library already takes care
     * of removing slashes if magic_quotes is enabled.
     */
    require_once(SM_DIR.'textism.textile.php');

    return textile($text);
  }

}
