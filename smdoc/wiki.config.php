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
wiki.config.php
WTF Wiki Extension

This extension adds a WikiPage object type to WTF. This new content class extends
the standard content type to provide Wiki mark-up parsing of content upon output
and allow the addition of a comment upon updating the object.

wiki.class.wikipage.php - The WikiPage class definition.
wiki.thing.wikipage.php - A WikiPage creation thing, view this hardthing to create
a new WikiPage object.

To enable this extension, include this file within your wtf.config.php file.

This extension was made primarily to show how to extend the WTF core to add
application specific functionality.
*/

if (!defined('MAXWIKIPAGECOMMENTLENGTH')) define('MAXWIKIPAGECOMMENTLENGTH', 80); // maximum length of a wikipage version / change comment

$INTERWIKI = array(
	'Wiki' => 'http://c2.com/cgi/wiki?',
	'PHPWiki' => 'http://phpwiki.sourceforge.net/phpwiki/',
	'ZWiki' => 'http://zwiki.org/',
	'UseMod' => 'http://www.usemod.com/cgi-bin/wiki.pl?',
	'MeatBall' => 'http://www.usemod.com/cgi-bin/mb.pl?',
	'MoinMoin' => 'http://twistedmatrix.com/users/jh.twistd/moin/moin.cgi/',
	'OpenWiki' => 'http://www.openwiki.com/?'
);

/* load classes */
include(PATH.'wiki.class.wikipage.php');

/* load things */
include(PATH.'wiki.thing.wikipage.php');

?>