<?php
/*
 * Copyright 2003, Paul James
 * 
 * This file is part of the Framework for Object Orientated Web Development (Foowd).
 * 
 * Foowd is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * Foowd is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Foowd; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*
 * class.user.php
 * Foowd user object class
 */

/** METHOD PERMISSIONS **/
setPermission('foowd_user', 'class', 'create', 'Everyone'); // we want anyone to be able to create a user so we override this permission for this object with the empty string
setPermission('foowd_user', 'object', 'xml', 'Gods'); // we don't want just anyone being able to see our encrypted user passwords
setPermission('foowd_user', 'object', 'groups', 'Gods');
setPermission('foowd_user', 'object', 'update', 'Author');

/** CLASS DESCRIPTOR **/
setClassMeta('foowd_user', 'User');
    
setConst('USER_CLASS_ID', META_FOOWD_USER_CLASS_ID);

/** CLASS DECLARATION **/
class foowd_user extends foowd_object 
{
    var $password;
    var $email;
    var $groups = array();
    
/*** CONSTRUCTOR ***/

    function foowd_user( &$foowd,
                         $username = NULL,
                         $password = NULL,
                         $email = NULL,
                         $groups = NULL    ) 
    {
        $foowd->track('foowd_user->constructor');

// password
        if (preg_match(REGEX_PASSWORD, $password)) 
        {
            $salt = getConstOrDefault('PASSWORD_SALT', '');
            $this->password = md5($salt.strtolower($password));
        } 
        else 
        {
            trigger_error('Could not create object, password contains invalid characters.');
            $this->objectid = 0;
            $foowd->track(); 
            return FALSE;
        }

// base object constructor
        parent::foowd_object($foowd, $username, NULL, NULL, NULL, FALSE);

// email
        if (preg_match($this->foowd_vars_meta['email'], $email)) 
            $this->email = $email;
        
// make user created and owned by self
        $this->creatorid = $this->objectid; // created by self
        $this->creatorName = $this->title;

// user groups
        if (is_array($groups)) 
        {
            foreach ($groups as $group) 
            {
                if (preg_match($this->foowd_vars_meta['groups'], $group)) 
                    $this->groups[] = $group;
            }
        }

        $foowd->track();
    }

/*** SERIALIZE FUNCTIONS ***/

    function __wakeup() 
    {
        parent::__wakeup();
        $this->foowd_vars_meta['password'] = '/^[a-z0-9]{32}$/';
        $this->foowd_vars_meta['email'] = REGEX_EMAIL;
        $this->foowd_vars_meta['groups'] = REGEX_GROUP;
    }

/*** MEMBER FUNCTIONS ***/

    function inGroup($groupName, $creatorid = NULL) 
    {
        if ( $groupName == 'Everyone' )       // group is everyone, so yes
            return TRUE;
        if ( $groupName == 'Nobody' )         // group is nobody, so no (read-only)
            return FALSE;

        if ($groupName == 'Author' &&         // or group is Author, 
            $creatorid != NULL &&             // creator id is non-null
            $this->objectid == $creatorid )   // and user's id matches creator id.
            return TRUE;

        if ( is_array($this->groups) ) 
        {
            if ( in_array($groupName, $this->groups) || // user is in group
                 in_array('Gods', $this->groups) )      // or user is a god
                return TRUE;
        }
        return FALSE;             // otherwise, user not in group.
    }

    function passwordCheck($password, $plainText = FALSE) 
    {
        if ($plainText) {
            $password = md5(getConstOrDefault('PASSWORD_SALT', '').strtolower($password));
        }
        if ($this->password === $password || defined('AUTH_IP_'.$_SERVER['REMOTE_ADDR'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function login(&$foowd, $authType = 'http', $username = FALSE, $password = NULL) {
        if ($foowd->user->objectid == getConstOrDefault('ANONYMOUS_USER_ID', FALSE) || $foowd->user->objectid == NULL) {
            switch ($authType) {
            case 'http':
                header('WWW-Authenticate: Basic realm="'.getConstOrDefault('AUTH_REALM', 'Framework for Object Orientated Web Development').'"');
                header('HTTP/1.0 401 Unauthorized');
                return 6; // did not http auth correctly
            case 'cookie':
                if ($username) {
                    $user = $foowd->fetchObject(array(
                        'objectid' => crc32(strtolower($username)),
                        'classid' => USER_CLASS_ID
                    ));
                    if (strtolower($user->title) == strtolower($username)) {
                        $salt = getConstOrDefault('PASSWORD_SALT', '');
                        if ($user->password == md5($salt.strtolower($password))) {
                            $foowd->user = $user;
                            $cookieUsername = new input_cookie('username', REGEX_TITLE);
                            $cookiePassword = new input_cookie('password', '/^[a-z0-9]{32}$/');
                            $cookieUsername->set($user->title);
                            $cookiePassword->set($user->password);
                            return 0; // logged in successfully
                        } else {
                            return 3; // bad password
                        }
                    } else {
                        return 2; // unknown user
                    }
                } else {
                    return 1; // no user given
                }
            }
            return 4; // unknown authentication method
        } else {
            if ($authType == 'http') {
                return 0; // logged in successfully
            } else {
                return 5; // user already logged in
            }
        }
    }
    
    function logout(&$foowd, $authType = 'http') {
        if ($foowd->user->objectid == getConstOrDefault('ANONYMOUS_USER_ID', FALSE) || $foowd->user->objectid == NULL) {
            return 3; // user already logged out
        } else {
            if ($authType == 'ip' || defined('AUTH_IP_'.$_SERVER['REMOTE_ADDR'])) {
                return 2; // ip auth, can not log out
            } elseif ($authType == 'cookie') {
                $anonUserClass = getConstOrDefault('ANONYMOUS_USER_CLASS', 'foowd_anonuser');
                if (class_exists($anonUserClass)) {
                    $foowd->user = new $anonUserClass($foowd);
                }
                $cookieUsername = new input_cookie('username', REGEX_TITLE);
                $cookiePassword = new input_cookie('password', '/^[a-z0-9]{32}$/');
                $cookieUsername->delete();
                $cookiePassword->delete();
                return 0; // cookie logged out successfully
            } else {
                header('WWW-Authenticate: Basic realm="'.getConstOrDefault('AUTH_REALM', 'Framework for Object Orientated Web Development').'"');
                header('HTTP/1.0 401 Unauthorized');
                if (isset($_SERVER['PHP_AUTH_USER']) || ($foowd->user->objectid != getConstOrDefault('ANONYMOUS_USER_ID', NULL) && $foowd->user->objectid != NULL)) {
                    return 4; // http log out failed due to browser
                } else {
                    return 1; // http logged out successfully
                }
            }
        }
    }
    
/*** CLASS METHODS ***/

/* create object */

    function class_create(&$foowd, $className) {
        $foowd->track('foowd_user->class_create');
        $title = _("Create New User");
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this, $title);
        echo '<h1>'.$title.'</h1>';
        $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
        $createUsername = new input_textbox('createUsername', REGEX_TITLE, $queryTitle->value);
        $createPassword = new input_passwordbox('createPassword', REGEX_PASSWORD, NULL);
        $verifyPassword = new input_passwordbox('verifyPassword', REGEX_PASSWORD, NULL);
        $createEmail = new input_textbox('createEmail', REGEX_EMAIL, NULL, NULL, NULL, NULL, NULL, FALSE);
        $createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
        if (!$createForm->submitted()     || 
             $createUsername->value == '' || 
             $createPassword->value == '' ||
             $createPassword->value != $verifyPassword->value ) {

            $table = new input_table();
            $table->addObject(_("Username:"), $createUsername);
            $table->addObject(_("Password:"),  $createPassword);
            $table->addObject(_("Password Verify:"), $verifyPassword);
            $table->addObject(_("E-mail Address:"), $createEmail);
            $createForm->addObject($table);
            $createForm->display();
            echo '<p>You do not need to give an e-mail address, however if given it will be used to retrieve your password.</p>';
        } else {
            $object = new $className(
                $foowd,
                $createUsername->value,
                $createPassword->value,
                $createEmail->value
            );
            if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
                echo '<p>', _("User created and saved."), '</p>';
                if (getConstOrDefault('AUTH_TYPE', 'http') == 'ip') {
                    echo '<p>', sprintf(
                        _('<a href="%s">Click here to view it now</a>. Contact your system administrator (%s) to connect you to the system.'),
                        getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))),
                        getConstOrDefault('WEBMASTER_EMAIL', 'webmaster@'.$_SERVER['SERVER_NAME'])
                    ), '</p>';
                } else {
                    echo '<p>', sprintf(
                        _('<a href="%s">Click here to view it now</a> or <a href="%s">here to log in now</a>.'),
                        getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))),
                        getConstOrDefault('WEBMASTER_EMAIL', 'webmaster@'.$_SERVER['SERVER_NAME']),
                        getURI(array('class' => $className, 'method' => 'login', 'username' => $this->title))
                    ), '</p>';
                }
            } else {
                trigger_error('Could not create user.');
            }
        }
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }

/* user log in */
    function class_login(&$foowd, $className) {
        $foowd->track('foowd_user->class_login');
        $title = _("Log In");
        $url = getURI(array());
        $hiddenmsg = new input_hiddenbox('rc');

        $authType = getConstOrDefault('AUTH_TYPE', FALSE);

        if ($authType == 'cookie') {
            $usernameQuery = new input_querystring('username', REGEX_TITLE, '');
            $loginUsername = new input_textbox('loginUsername', REGEX_TITLE, $usernameQuery->value);
            $loginPassword = new input_passwordbox('loginPassword', REGEX_PASSWORD, NULL);
            $loginForm = new input_form('loginForm', NULL, 'POST', $title, NULL);
            $table = new input_table();
            $table->addObject(_("Username:"), $loginUsername);
            $table->addObject(_("Password:"), $loginPassword);
            $loginForm->addObject($table);
            $result = call_user_func(array($className, 'login'), $foowd, 'cookie', $loginUsername->value, $loginPassword->value);
        } else {
            $result = call_user_func(array($className, 'login'), $foowd, $authType);
        }
       
        if ( $result == 0 )
            header('Location: '. $url. '?objectid='.$foowd->user->objectid.'&classid='.USER_CLASS_ID
                               .'&rc=loginok');

        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this, $title);
        if ( $hiddenmsg->value == 'logout' ) 
             echo '<p class="ok">',_("You are now logged out."),'</p>';
        echo '<h1>'.$title.'</h1>';
        switch ($result) {
        case 0:
            echo '<p>', sprintf(_('User logged in. <a href="%s">Click here to continue</a>.'), getURI()), '</p>';
            break;
        case 1:
            $loginForm->display();
            echo '<p>';
            printf(_('Do not have a user account yet? <a href="%s">Click here to create one</a>.'), getURI(array('class' => $className, 'method' => 'create')));
            echo '<br />';
            printf(_('Forgotten your password? <a href="%s">Click here to retrieve it</a>.'), getURI(array('class' => $className, 'method' => 'lostpassword')));
            echo '</p>';
            break;
        case 2:
            echo '<p>', sprintf(_("Could not find %s."), htmlspecialchars($loginUsername->value)), '</p>';
            $loginForm->display();
            break;
        case 3:
            echo '<p>', sprintf(_("Password incorrect for %s."), htmlspecialchars($loginUsername->value)), '</p>';
            $loginForm->display();
            break;
        case 4:
            echo '<p>', sprintf(_("You can not log into this system. <a href=\"%s\">Click here to continue</a>."), getURI()), '</p>';
            break;
        case 5:
            echo '<p>', sprintf(_("You are already logged in. <a href=\"%s\">Click here to continue</a>."), getURI()), '</p>';
            break;
        case 6:
            echo '<p>', _("Your log in failed."), '</p>';
            break;
        }
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }

/* user log out */
    function class_logout(&$foowd, $className) {
        $foowd->track('foowd_user->class_logout');
        $title = _("Log Out");
        $url = getURI(array());

        $authType = getConstOrDefault('AUTH_TYPE', FALSE);
        
        $result = call_user_func(array($className, 'logout'), $foowd, $authType);

        if ( $result == 0 || $result == 1 )
            header('Location: '. $url. '?class=foowd_user&method=login&rc=logout');

        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this, $title);
        switch ($result) {
        case 0:
            echo '<p>', sprintf(_("You are now logged out. <a href=\"%s\">Click here to continue</a>."), getURI()), '</p>';
            break;
        case 1:
            echo '<p>', sprintf(_("You are now logged out. <a href=\"%s\">Click here to continue</a>."), getURI()), '</p>';
            break;
        case 2:
            echo '<p>', _("You can not log out from this IP address."), '</p>';
            break;
        case 3:
            echo '<p>', _("You are logged out."), '</p>';
            break;
        case 4:
            echo '<p>', _("To log out you must clear your browsers HTTP authentication information which you can do by entering nothing into the authentication box."), '</p>';
            break;
        }

        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }
    
/* user get password */
    function class_lostPassword(&$foowd, $className) {
        $foowd->track('foowd_user->class_lostPassword');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
        echo '<h1>', _("Lost Password"), '</h1>';
        $usernameQuery = new input_querystring('username', REGEX_TITLE, '');
        $idQuery = new input_querystring('id', '/[a-z0-9]{32}/', '');
        $lostUsername = new input_textbox('lostUsername', REGEX_TITLE, $usernameQuery->value, _("Username").':');
        $lostForm = new input_form('lostForm', NULL, 'POST', _("Retrieve Password"), NULL);
        $webmaster = getConstOrDefault('WEBMASTER_EMAIL', 'webmaster@'.$_SERVER['SERVER_NAME']);
        if ($idQuery->value != '' && $usernameQuery->value != '') {
            $user = $foowd->fetchObject(array(
                'objectid' => crc32(strtolower($usernameQuery->value)),
                'classid' => USER_CLASS_ID
            ));
            if (
                isset($user->title) &&
                strtolower($user->title) == strtolower($lostUsername->value) &&
                $idQuery->value == md5($user->updated.$user->title)
            ) {
                $siteName = getConstOrDefault('SITE_NAME', 'FOOWD @ '.$_SERVER['SERVER_NAME']);
                $newPassword = '';
                for($foo = 0; $foo < rand(6,12); $foo++) { $newPassword .= chr(rand(97, 122)); }
                $salt = getConstOrDefault('PASSWORD_SALT', '');
                $user->password = md5($salt.$newPassword);
                $message = sprintf(
                    _("Hi,\nYour password has been changed, your new user account details are:\n\tUsername: %s\n\tPassword: %s\nPlease go to the URL below to log in using your new password:\n\t%s\nThanks.\n%s"),
                    $user->getTitle(),
                    $newPassword,
                    'http://'.$_SERVER['SERVER_NAME'].getURI(array('class' => $className, 'method' => 'lostPassword', 'username' => $user->title, 'id' => md5($user->updated.$user->title))),
                    $webmaster
                );
                if (@mail(
                    $user->email,
                    sprintf(_('%s - Password Change Request'), $siteName),
                    $message,
                    'From: '.getConstOrDefault('WEBMASTER_EMAIL', 'webmaster@'.$_SERVER['SERVER_NAME']).'\r\nReply-To: '.getConstOrDefault('NOREPLY_EMAIL', 'noreply@'.$_SERVER['SERVER_NAME'])
                ) && $user->save()) {
                    echo '<p>', sprintf(_("Your new password has been sent to %s."), htmlspecialchars($user->email)), '</p>';
                } else {
                    echo '<p>', sprintf(_('Sorry, I could not change your password for you, please contact <a href="mailto:%s">%s</a> to help you retrieve your password.'), $webmaster, $webmaster), '</p>';
                }
            } else {
                echo '<p>', sprintf(_('Sorry there was a problem requesting your new password, please contact <a href="mailto:%s">%s</a> to help you retrieve your password.'), $webmaster), '</p>';
            }
        } elseif ($lostForm->submitted() && $lostUsername->value != '') {
            $user = $foowd->fetchObject(array(
                'objectid' => crc32(strtolower($lostUsername->value)),
                'classid' => USER_CLASS_ID
            ));
            if (isset($user->title) && strtolower($user->title) == strtolower($lostUsername->value)) {
                if (isset($user->email)) {
                    $siteName = getConstOrDefault('SITE_NAME', $_SERVER['SERVER_NAME']);
                    $message = sprintf(
                        _("Hi,\nYou, or someone looking very much like you, requested a new password for the user account \"%s\" at %s\nIf this is correct, please use the following URL to finalise the new password request and have your new password e-mailed to you at this address.\n\t%s\nIf this is not correct, please just ignore this e-mail.\nThanks.\n%s"),
                        $user->getTitle(),
                        $siteName,
                        'http://'.$_SERVER['SERVER_NAME'].getURI(array('class' => $className, 'method' => 'lostPassword', 'username' => $user->title, 'id' => md5($user->updated.$user->title))),
                        $webmaster
                    );
                    if (@mail(
                        $user->email,
                        sprintf(_('%s - Password Change Request'), $siteName),
                        $message,
                        'From: '.getConstOrDefault('WEBMASTER_EMAIL', 'webmaster@'.$_SERVER['SERVER_NAME']).'\r\nReply-To: '.getConstOrDefault('NOREPLY_EMAIL', 'noreply@'.$_SERVER['SERVER_NAME'])
                    )) {
                        echo '<p>', _('An e-mail has been sent to you requesting your permission to send you a new password.'), '</p>';
                    } else {
                        echo '<p>', sprintf(_('Sorry, I could not send an e-mail to your listed address, please contact <a href="mailto:%s">%s</a> to help you retrieve your password.'), $webmaster, $webmaster), '</p>';
                    }
                } else {
                    echo '<p>', sprintf(_('User "%s" does not have an e-mail address registered, please contact <a href="mailto:%s">%s</a> to help you retrieve your password.'), htmlspecialchars($lostUsername->value), $webmaster, $webmaster), '</p>';
                }
            } else {
                echo '<p>', sprintf(_('Sorry, user "%s" not found.'), htmlspecialchars($lostUsername->value)), '</p>';
            }
        } else {
            echo '<p>', _('Enter your username below to have your password changed and the new one e-mailed to you.'), '</p>';
            $lostForm->addObject($lostUsername);
            $lostForm->display();
        }
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }

/*** METHODS ***/

/* view object */
    function method_view(&$foowd) {
        $foowd->track('foowd_user->method_view');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        $hiddenmsg = new input_hiddenbox('rc');
        if ( $hiddenmsg->value == 'loginok' ) 
            echo '<p class="ok">',sprintf(_("User %s logged in."), $this->getTitle()),'</p>';

        echo '<table>';
        echo '<tr><th>', _("Username"), ':</th><td>', $this->getTitle(), '</td></tr>';
        if ($this->email) {
            $email = mungEmail($this->email);
            echo '<tr><th>', _('E-mail'), ':</th><td><a href="mailto:', $email, '">', $email, '</a></td></tr>';
        }
        echo '<tr><th>', _("Created"), ':</th><td>', date(DATETIME_FORMAT, $this->created), ' (', timeSince($this->created), ' ago)</td></tr>';
        echo '<tr><th>', _("Last Visit"), ':</th><td>', date(DATETIME_FORMAT, $this->updated), ' (', timeSince($this->updated), ' ago)</td></tr>';
        echo '</table>';
        if ($foowd->user->objectid == $this->objectid) {
            echo '<p>', sprintf(_('<a href="%s">Update your profile</a>.'), getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'update'))), '</p>';
        }
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }

/* change user details */
    function method_update(&$foowd) {
        $foowd->track('foowd_user->method_update');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        $updateForm = new input_form('updateForm', NULL, 'POST', _("Update"));
        $email = new input_textbox('email', REGEX_EMAIL, $this->email, _('E-mail').': ');
        $updateForm->addObject($email);
        $updateForm->display();
        if ($updateForm->submitted()) {
            $changed = FALSE;
            if ($email->value != $this->email) {
                $this->email = $email->value;
                $changed = TRUE;
            }
            if ($changed && $this->save($foowd, FALSE)) {
                echo '<p>', _("User updated."), '</p>';
            } else {
                trigger_error('Could not update user.');
            }
        }

        $passwordForm = new input_form('passwordForm', NULL, 'POST', _("Change Password"), NULL);
        $password = new input_passwordbox('password', REGEX_PASSWORD, '', _("Password").': ');
        $password2 = new input_passwordbox('password2', REGEX_PASSWORD, '', _("Verify").': ');
        $passwordForm->addObject($password);
        $passwordForm->addObject($password2);
        $passwordForm->display();
        if ($passwordForm->submitted()) {
            if ($password->value != $password2->value) {
                echo '<p>', _('Passwords must match, please check your entries.'), '</p>';
            } elseif ($password->value != '') {
                $salt = getConstOrDefault('PASSWORD_SALT', '');
                $this->password = md5($salt.strtolower($password->value));
                if ($this->save($foowd, FALSE)) {
                    echo '<p>', sprintf(_('User password changed, you will now need to <a href="%s">log in using your new password</a>.'), getURI(array('class' => get_class($this), 'method' => 'login', 'username' => $this->title))), '</p>';
                } else {
                    trigger_error('Could not save user.');
                }
            }
        }
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }

/* user group support for users */

    function method_groups(&$foowd) {
        $foowd->track('foowd_object->method_groups');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        $permissionForm = new input_form('permissionForm', NULL, 'POST');

        $items = $foowd->getUserGroups($this->creatorid);

        $permissionBox = new input_dropdown('permissionGroups', $this->groups, $items, _("User Groups").':', count($items), TRUE);
        $permissionForm->addObject($permissionBox);
        $permissionForm->display();

        if ($permissionForm->submitted()) {
            $changed = FALSE;
            if ($permissionBox->value == $this->groups) { // box has been emptied so empty array
                $permissionBox->value = array();
            }
            foreach ($items as $group => $name) { // remove groups in list that have been unselected
                if (!in_array($group, $permissionBox->value)) {
                    $key = array_search($group, $this->groups);
                    if ($key !== FALSE) {
                        unset($this->groups[$key]);
                        $changed = TRUE;
                    }
                }
            }
            foreach ($permissionBox->value as $group) { // add groups that have been selected
                if (!in_array($group, $this->groups)) {
                    $this->groups[] = $group;
                    $changed = TRUE;
                }
            }
            if ($changed) {
                if ($this->save($foowd, FALSE)) {
                    echo '<p>', _("User permissions updated."), '</p>';
                } else {
                    trigger_error('Could not save object.');
                }
            }
        }

        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }

}

?>
