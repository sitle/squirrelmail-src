<?php

/**
 * message.class.php
 *
 * Copyright (c) 2003 Marc Groot Koerkamp 
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Base messages class.
 * 
 *
 * Author: Marc Groot Koerkamp (Sourceforce username: stekkel) 2003
 *
 * $Id$
 */


class messages extends service {

    var
       $id,
       $type,
       $host,
       $port,
       $username = '',
       $password = '',
       $ignore_capabilities = array(),
       $capability = false,
       $aFolderProps,
       $cached_lsub = false,
       $cached_list = false,
       $resource = false;

    /* constructor */
    function messages($id, $properties) {
        /* initialise folderproperties */
        
        /* also set the global defaults.
           the value of the properties is in de following form:
           array('value',int) with array[0] the actual value and array[1] WRITE|READ|LOOKUP access.
           WRITE access should be set on init where the admin prefs with ACL's are read. Those
           ACL's also contains information about if it's allowed to override admin prefs by user
           preferences. If the user may change the pref then those changes are stored in the 
           userpref location. 
           In the end a simple read/write/lookup is left. 
           
           Second thought, value is not an array, those arrays with ACL should be created when
           the user hits the config module. Messages should not contain config change stuff, Just
           feed it with the configuration and handle read/write/lookup access to preferences somewhere
           else.
           
           Before the messages class is loaded the actual properties array is a mix from admin prefs
           / userprefs. The Acl's on the prefs decides which one is stored in the properties.
        */
        $aFolderProps = array (
                'prefix' => '',
                'special' => array(
                                  'SENT' => false,
                                  'DRAFTS' => false,
                                  'TRASH' => false,
                                  'CONTACTS' => false, // (future usage)
                                  'CALENDAR' => false, // (future usage)
                                  'TODO'     => false, // (future usage)
                                  'NOTES'    => false, // (future usage)
                                  'EVENTS'   => false  // (future usage)
                                  ),
                'default_sort_dir' => 0, // 0 = Ascending, 1 = Descending?
                'default_sort_hdr' => 'date',
                'default_move_to_trash' => false,
                'default_auto_expunge'  => false
                // show special first? probably we should always do that, no setting required 
                ); 
        $this->id = $id;
        $this->type = $properties['type']; /* imap, (pop3, nntp) */
        $this->host = $properties['host']; /* including ssl:// or tls:// */
        $this->port = $properties['port'];        
        $this->username = $properties['username'];
        $this->password = $properties['password'];
        if (isset($properties['decrypt']) && isset($properties['enc_password'])) { /* callback function */
            $this->password = $this->properties['decrypt']($this->properties['enc_password']);
        } else {
            if (isset($properties['password'])) {
                $this->password = $properties['password'];
            }
        }
        if (isset($properties['ignore_capability'])) {
            $this->ignore_capability = $properties['ignore_capability'];
        }
        /* folder settings merged with defaults */
        foreach($properties['folders'] as $key => $value) {
                $aFolderProps[$key] = $value;
        }
        $this->aFolderProps = $aFolderProps;

        if (isset($properties['cached_lsub'])) {
            /* cached data format: array (expirationtime, cached folders, streamformat */
            if (time < $properties['cached_lsub'][0]) {
               $lsub_tree = new mailboxlist();
               $lsub_tree->loadFromStream($properties['cached_lsub'][1],$properties['cached_lsub'][2]);
               $this->folders_lsub = $lsub_tree;
            }
        }
        if (isset($properties['cached_list'])) {
            /* cached data format: array (expirationtime, cached folders, streamformat */
            if (time < $properties['cached_list'][0]) {
               $list_tree = new mailboxlist();
               $list_tree->loadFromStream($properties['cached_list'][1],$properties['cached_list'][2]);
               $this->folders_list = $lsub_tree;
            }
        }
        /* attach the backend code */
        $backend = $this->type.'_backend';
        
        /* include the backend. dependencies are resolved with help from central file with locations
           and depencies */
        sm_include($backend);
        
        /* auto set the resource on a connect */
        $this->backend =& new $backend(&$this->resource);
        
        /* TODO:
           define a set of capabilities for the message class which can be disabled by the 
           backend class so we can decide inside messages which methods are supported.
        */   
        
        /* global permissions regarding sort, thread, filter */
        /* TODO
		DEFAULTACL
		ALLOW_THREAD
		ALLOW_SORT
		ALLOW_FILTER
		ALLOW_PREVIEW
        */
    }
    
    /* sleep, wakeup and listener code */
    
    // TODO
    
    
    function login() {
        if (!$this->backend->resource) {
                /* error handling */
                // sm_include('messages_error'); // includes error number + error message
                // TODO, what error code to return
                // error handling(errno, err message)
                echo "ERROR";
                exit;
        }
        $res = $this->backend->login($this->username,$this->password,$this->host,$this->capability,
                                     $err_no, $err_message);
        if (!$res) {
            /* do something with error */
            // err_no and err_message returned by reference
            return false;
        }
        return $res;
    }
        

}

?>
