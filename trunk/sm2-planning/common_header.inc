<?php
/**
 * common_header.inc - common header for planning documentation
 *
 * Copyright (c) 1999-2002 The SquirrelMail development team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains a common header used in all planning dicumentation.
 * Include it using:   include_once('common_header.php');
 * Will include all headers through the body tag.
 *
 * $Id$
 */
global $sm_doc_title, $sm_doc_orig_author, 
       $sm_doc_editor, $sm_doc_revision, $sm_doc_date;

$sm_doc_title = 'SquirrelMail Planning';
$sm_doc_orig_author = '';
$sm_doc_editor = '';
$sm_doc_revision = '';
$sm_doc_date = '';

 function set_title($title) {
   global $sm_doc_title;
   $sm_doc_title .= ': ' . $title;
 }

 function set_original_author($author) {
   global $sm_doc_orig_author;
   $sm_doc_orig_author = $author;
 }

 function set_attributes($author, $revision, $date) {
   global $sm_doc_editor, $sm_doc_revision, $sm_doc_date;
   $sm_doc_editor = $author;
   $sm_doc_revision = $revision;
   $sm_doc_date = $date;
 }

 function print_header() {
  global $sm_doc_title, $sm_doc_orig_author, 
         $sm_doc_editor, $sm_doc_revision, $sm_doc_date;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $sm_doc_title; ?></title>
<link rel="stylesheet" href="sections.css" type="text/css" />
<link rel="stylesheet" href="developer.css" type="text/css" />
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="right-pad" rowspan="5"><img src="images/sm_logo.jpg" border="0" ></td>
<td><b><?php echo $sm_doc_title; ?></b></td>
</tr>
<tr><td>
<?php echo ($sm_doc_orig_author != '') ? '$Original: ' . $sm_doc_orig_author . ' $' : '&nbsp;'; ?>
</td></tr>
<tr><td>
<?php echo ($sm_doc_editor != '') ? $sm_doc_editor : '&nbsp;'; ?>
</td></tr>
<tr><td>
<?php echo ($sm_doc_revision != '') ? $sm_doc_revision : '&nbsp;'; ?>
</td></tr>
<tr><td>
<?php echo ($sm_doc_date != '') ? $sm_doc_date : '&nbsp;'; ?>
</td></tr>
</table>

<?php
 }
?>
