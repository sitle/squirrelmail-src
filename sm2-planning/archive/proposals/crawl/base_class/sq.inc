<?php
/*
 *  $Id$
 *  $Author$
 *  $Date$
 *
 *  This file is intended to help illustrate the pros and cons of
 *  using a "base" class (or plain file) that could be part of sm2.
 *  obviously there are some things you get out of a class that you don't
 *  get from an included file.
 *     -vars available without global declaration
 *     -inherited methods without other inclusions
 *  pros for either way
 *     -entire core is updated when this file is updated
 *     -standard functions for common jobs
 *     -since more code uses these functions it should be better optimized
 *  cons of a common class
 *     -naming might become a problem
 *     -calling a function is not as direct
 *
 *
 */
 
   class sq {
   
      // declare all vars which we otherwise would make global anyway
      var $username;
      var $key;
      var $squirrelmail_language;
       
      function sq() { //start constructor
         
         // obviously something that should 
         // be done anytime you call the core
         // so the rest of the core libs don't
         // have to do it. like sessions maybe
         // sessions would probably be a controller
         // thing but this is just an example
         
         //session_start(); or something
      
      } // end constructor
      
      function trustedFile($file) {  // start trustedFile
         // some other tiny function that is needed all
         // over but not complex enough to have its own
         // lib file
  
      	// only trust local files owned by ourselves
      	// maybe add the "sq_" prefix here
         if (!eregi("^([a-z]+)://", $file) && fileowner($file) == getmyuid()) {
            return true;
         }
            return false;
      } // end trustedFile
      
      function sayHi($username) {  // start sayHi
         // some other tiny function that is needed all
         // over but not complex enough to have its own
         // lib file
         echo "<h1>Hello $username</h1>\n";
         return true;
      } // end sayHi
       
   } // end sq
         
         

 
