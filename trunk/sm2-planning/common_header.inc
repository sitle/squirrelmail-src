<?php

/**
 * common_header.php - common header for planning documentation
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
global $sm_doc_title;

$sm_doc_title = 'SquirrelMail Planning';

 function setTitle($title) {
   global $sm_doc_title;
   $sm_doc_title .= ': ' . $title;
 }
 
 function print_header() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $sm_doc_title ?></title>
<link rel="stylesheet" href="sections.css" type="text/css" />
<link rel="stylesheet" href="developer.css" type="text/css" />
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td><img src="images/sm_logo.jpg" border="0"></td>
<td>

</td>
</tr>
</table>

<?php
 }
?>
