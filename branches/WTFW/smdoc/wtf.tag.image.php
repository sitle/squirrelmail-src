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
wtf.tag.image.php
Image Pre-processed Tag Module
*/

$PPTAG['img'] = 'ImagePPT';
$PPTAGGROUP['img'] = EVERYONE;

define('MAXIMAGEWIDTH', 600); // maximum width of image
define('MAXIMAGEHEIGHT', 600); // maximum width of image

function ImagePPT($parameters) {
	track('ImagePPT');

	if (is_array($parameters)) {
		if (isset($parameters['src'])) {
			$picture = $parameters['src'];
			if ($picturesize = @getimagesize($picture)) {

				$picturewidth = $picturesize[0];
				$pictureheight = $picturesize[1];

				if ($picturewidth > MAXIMAGEWIDTH) {
					$pictureheight = (MAXIMAGEWIDTH / $picturewidth) * $pictureheight;
					$picturewidth = MAXIMAGEWIDTH;
				}
				if ($pictureheight > MAXIMAGEHEIGHT) {
					$picturewidth = (MAXIMAGEHEIGHT / $pictureheight) * $picturewidth;
					$pictureheight = MAXIMAGEHEIGHT;
				}
				
				if (isset($parameters['alt'])) {
					$altText = $parameters['alt'];
				} else {
					$altText = 'Image';
				}
				
				if (isset($parameters['align']) && ($parameters['align'] == 'left' || $parameters['align'] == 'center' || $parameters['align'] == 'right')) {
					$align = $parameters['align'];
				} else {
					$align = 'left';
				}
				
				track(); return '<img src="'.$parameters['src'].'" width="'.$picturewidth.'" height="'.$pictureheight.'" alt="'.$altText.'" align="'.$align.'"/>';

			} else {
				track(); return '<error>Could not find image \''.htmlspecialchars($picture).'\', please check the URL.</error>';
			}
		} else {
			track(); return '<error>You must specify a URL to an image.</error>';
		}
	} else {
		track(); return '<error>You must specify a URL to an image.</error>';
	}
}

?>