<?php

/**
 * auth.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Contains functions used to do authentication.
 *
 * $Id$
 */

function is_logged_in() {

    if ( sqsession_is_registered('user_is_logged_in') ) {
        return;
    } else {
        global $PHP_SELF, $session_expired_post, 
	       $session_expired_location;

        /*  First we store some information in the new session to prevent
         *  information-loss.
         */
	 
	$session_expired_post = $_POST;
        $session_expired_location = $PHP_SELF;
        if (!sqsession_is_registered('session_expired_post')) {    
           sqsession_register($session_expired_post,'session_expired_post');
        }
        if (!sqsession_is_registered('session_expired_location')) {
           sqsession_register($session_expired_location,'session_expired_location');
        }
        include_once( '../functions/display_messages.php' );
        logout_error( _("You must be logged in to access this page.") );
        exit;
    }
}

function cram_md5_response ($username,$password,$challenge) {

/* Given the challenge from the server, supply the response using
   cram-md5 (See RFC 2195 for details)
   NOTE: Requires mhash extension to PHP
*/
$challenge=base64_decode($challenge);
$hash=bin2hex(mhash(MHASH_MD5,$challenge,$password));
$response=base64_encode($username . " " . $hash) . "\r\n";
return $response;
}

function digest_md5_parse_challenge($challenge) {
/* This function parses the challenge sent during DIGEST-MD5 authentication and
   returns an array. See the RFC for details on what's in the challenge string.
*/
  $challenge=base64_decode($challenge);
  while (strlen($challenge)) {
    if ($challenge{0} == ',') { // First char is a comma, must not be 1st time through loop
      $challenge=substr($challenge,1);
    }
    $key=explode('=',$challenge,2);
    $challenge=$key[1];
    $key=$key[0];
    if ($challenge{0} == '"') {
      // We're in a quoted value
      // Drop the first quote, since we don't care about it
      $challenge=substr($challenge,1);
      // Now explode() to the next quote, which is the end of our value
      $val=explode('"',$challenge,2);
      $challenge=$val[1]; // The rest of the challenge, work on it in next iteration of loop
      $value=explode(',',$val[0]);
      // Now, for those quoted values that are only 1 piece..
      if (sizeof($value) == 1) {
        $value=$value[0];  // Convert to non-array
      }
    } else {
      // We're in a "simple" value - explode to next comma
      $val=explode(',',$challenge,2);
      $challenge=$val[1];
      $value=$val[0];
    }
    $parsed["$key"]=$value;
  } // End of while loop
  return $parsed;
}

?>
