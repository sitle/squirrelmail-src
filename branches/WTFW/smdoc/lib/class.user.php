<?php
/*
Copyright 2003, Paul James

This file is part of the Framework for Object Orientated Web Development (Foowd).

Foowd is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foowd is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foowd; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
class.user.php
Foowd user object class
*/

define('USER_CLASS_ID', 425464453);
define('ANON_USER_CLASS_ID', -1063205124);

/** CLASS DESCRIPTOR **/
$foowd_class_meta[USER_CLASS_ID]['className'] = 'foowd_user';
$foowd_class_meta[USER_CLASS_ID]['description'] = 'User Object';
	
/** CLASS METHOD PASSTHRU FUNCTION **/
function foowd_user_classmethod(&$foowd, $methodName) { foowd_user::$methodName($foowd, 'foowd_user'); }

/** CLASS DECLARATION **/
class foowd_user extends foowd_object {

	var $password;
	var $email;
	var $groups;
	
/*** CONSTRUCTOR ***/

	function foowd_user(
		&$foowd,
		$username = NULL,
		$password = NULL,
		$email = NULL,
		$groups = NULL
	) {

// object permissions	
		$view = setVarConstOrDefault($viewGroup, 'DEFAULT_USER_VIEW_GROUP', 'Everyone');
		$admin = setVarConstOrDefault($adminGroup, 'DEFAULT_USER_ADMIN_GROUP', 'Gods');
		$delete = setVarConstOrDefault($deleteGroup, 'DEFAULT_USER_DELETE_GROUP', 'Gods');

// password
		if (preg_match(REGEX_PASSWORD, $password)) {
			$salt = setConstOrDefault('PASSWORD_SALT', '');
			$this->password = md5($salt.strtolower($password));
		} else {
			return FALSE;
		}

// base object constructor
		parent::foowd_object($foowd, $username, $view, $admin, $delete, FALSE);

// email
		if (preg_match($this->foowd_vars_meta['email'], $email)) {
			$this->email = $email;
		}
		
// make user created and owned by self
		$this->creatorid = $this->objectid; // created by self
		$this->creatorName = $this->title;
		$this->permissions['update'] = 'Author'; // only user can update itself

// user groups
		$this->groups[] = 'Everyone'; // all users are in the everyone group
		if (is_array($groups)) {
			foreach ($groups as $group) {
				if (preg_match($this->foowd_vars_meta['permission'], $group)) {
					$this->groups[] = $group;
				}
			}
		}

	}

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['password'] = '/^[a-z0-9]{32}$/';
		$this->foowd_vars_meta['email'] = REGEX_EMAIL;
		$this->foowd_vars_meta['groups'] = REGEX_TITLE;
	}

/*** MEMBER FUNCTIONS ***/

	function inGroup($groupName) {
		if (is_array($this->groups) && in_array($groupName, $this->groups)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new user</h1>';
		$createUsername = new input_textbox('createUsername', REGEX_TITLE, NULL, 'Username:');
		$createPassword = new input_passwordbox('createPassword', REGEX_PASSWORD, NULL, 'Password:');
		$createEmail = new input_textbox('createEmail', REGEX_EMAIL, NULL, 'E-mail Address:');
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		if ( !$createForm->submitted()     || 
              $createUsername->value == '' || 
              $createPassword->value == ''  ) {
			$createForm->addObject($createUsername);
			$createForm->addObject($createPassword);
			$createForm->addObject($createEmail);
			$createForm->display();
		} else {
			$object = new $className(
				$foowd,
				$createUsername->value,
				$createPassword->value,
				$createEmail->value
			);
			if ($object->save($foowd, FALSE)) {
				echo '<p>User created and saved.</p>';
			} else {
				echo '<p>Could not create user.</p>';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

/* user log in */
	function class_login(&$foowd, $className) {
		if ($foowd->user->objectid == setConstOrDefault('ANONYMOUS_USER_ID', FALSE) || 
            $foowd->user->objectid == '') {
			if (setConstOrDefault('AUTH_TYPE', 'http') == 'http') {
				header('WWW-Authenticate: Basic realm="'.setConstOrDefault('AUTH_REALM', 'Framework for Object Orientated Web Development').'"');
				header('HTTP/1.0 401 Unauthorized');
				if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
				echo '<h1>Log In</h1>';
				echo '<p>User not logged in.</p>';
			} else {
				$usernameQuery = new input_querystring('username', REGEX_TITLE, '');
				$loginUsername = new input_textbox('loginUsername', REGEX_TITLE, $usernameQuery->value, 'Username:');
				$loginPassword = new input_passwordbox('loginPassword', REGEX_PASSWORD, NULL, 'Password:');
				$loginForm = new input_form('loginForm', NULL, 'POST', 'Log In', NULL);
				if (!$loginForm->submitted() || $loginUsername->value == '' || $loginPassword->value == '') {
					if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
					echo '<h1>Log In</h1>';
					$loginForm->addObject($loginUsername);
					$loginForm->addObject($loginPassword);
					$loginForm->display();
				} else {
					$user = $foowd->fetchObject(array(
						'objectid' => crc32(strtolower($loginUsername->value)),
						'classid' => USER_CLASS_ID
					));
					if (strtolower($user->title) == strtolower($loginUsername->value)) {
						$salt = setConstOrDefault('PASSWORD_SALT', '');
						if ($user->password == md5($salt.strtolower($loginPassword->value))) {
							$foowd->user = $user;
							$cookieUsername = new input_cookie('username', REGEX_TITLE);
							$cookiePassword = new input_cookie('password', '/^[a-z0-9]{32}$/');
							$cookieUsername->set($user->title);
							$cookiePassword->set($user->password);
							if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
							echo '<h1>Log In</h1>';
							echo '<p>User logged in.</p>';
						} else {
							if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
							echo '<h1>Log In</h1>';
							echo '<p>Password incorrect for user "', htmlspecialchars($loginUsername->value), '".</p>';
						}
					} else {
						if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
						echo '<h1>Log In</h1>';
						echo '<p>Could not find user "', htmlspecialchars($loginUsername->value), '".</p>';
					}
				}
			}
		} else {
			if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
			echo '<h1>Log In</h1>';
			echo '<p>You are logged in.</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

/* user log out */
	function class_logout(&$foowd, $className) {
		if ($foowd->user->objectid == setConstOrDefault('ANONYMOUS_USER_ID', FALSE) || $foowd->user->objectid == '') {
			if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
			echo '<h1>Log Out</h1>';
			echo '<p>You are logged out.</p>';
		} else {
			if (setConstOrDefault('AUTH_TYPE', 'http') == 'http') {
				header('WWW-Authenticate: Basic realm="'.setConstOrDefault('AUTH_REALM', 'Framework for Object Orientated Web Development').'"');
				header('HTTP/1.0 401 Unauthorized');
				if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
				echo '<h1>Log Out</h1>';
				if (isset($_SERVER['PHP_AUTH_USER'])) {
					echo '<p>To log out you must clear your browsers HTTP authentication information which you can do by entering nothing into the authentication box.</p>';
				} else {
					echo '<p>You are now logged out.</p>';	
				}
			} else {
				$cookieUsername = new input_cookie('username', REGEX_TITLE);
				$cookiePassword = new input_cookie('password', '/^[a-z0-9]{32}$/');
				$cookieUsername->delete();
				$cookiePassword->delete();
				if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
				echo '<h1>Log Out</h1>';
				echo '<p>You are now logged out.</p>';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Viewing User "', $this->getTitle(), '"</h1>';
		echo '<table>';
		echo '<tr><th>Username:</th><td>', $this->getTitle(), '</td></tr>';
		$email = mungEmail($this->email);
		echo '<tr><th>E-mail:</th><td><a href="mailto:', $email, '">', $email, '</a></td></tr>';
		echo '<tr><th>Created:</th><td>', date(DATETIME_FORMAT, $this->created), '</td></tr>';
		echo '<tr><th>Last Visit:</th><td>', date(DATETIME_FORMAT, $this->updated), '</td></tr>';
		echo '</table>';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

/* change user details */
	function method_update(&$foowd) {
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Update User "', $this->getTitle(), '"</h1>';
		$updateForm = new input_form('updateForm', NULL, 'POST', 'Update');
		$email = new input_textbox('email', REGEX_EMAIL, $this->email, 'E-mail: ');
		$updateForm->addObject($email);
		$updateForm->display();
		if ($updateForm->submitted()) {
			$changed = FALSE;
			if ($email->value != $this->email) {
				$this->email = $email->value;
				$changed = TRUE;
			}
			if ($changed) {
				$this->save($foowd, FALSE);
				echo '<p>User updated.</p>';
			}
		}

		$passwordForm = new input_form('passwordForm', NULL, 'POST', 'Change Password', NULL);
		$password = new input_passwordbox('password', REGEX_PASSWORD, '', 'Password: ');
		$password2 = new input_passwordbox('password2', REGEX_PASSWORD, '', 'Verify: ');
		$passwordForm->addObject($password);
		$passwordForm->addObject($password2);
		$passwordForm->display();
		if ($passwordForm->submitted()) {
			if ($password->value != $password2->value) {
				echo '<p>Passwords must match, please check your entries.</p>';
			} elseif ($password->value != '') {
				$salt = setConstOrDefault('PASSWORD_SALT', '');
				$this->password = md5($salt.strtolower($password->value));
				$this->save($foowd, FALSE);
				echo '<p>User password changed, you will now need to <a href="', getURI(array('class' => get_class($this), 'method' => 'login', 'username' => $this->title)), '">log in using your new password</a>.</p>';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

}

/*** ANONYMOUS USER ***/

/* Anonymous user class, used to instanciate bogus user for anoymous access where
   only basic user data is required and it would be a waste to pull a user from the
   database. */

class foowd_anonuser extends foowd_user {
	function foowd_anonuser() {
		if (defined('ANONYMOUS_USER_NAME')) {
			$this->title = ANONYMOUS_USER_NAME;
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$this->title = $_SERVER['REMOTE_ADDR'];
		} else {
			$this->title = 'Anonymous';
		}
        $this->objectid = NULL;
        $this->version = 1;
        $this->classid = ANON_USER_CLASS_ID;
		$this->workspaceid = 0;
		$this->created = time();
		$this->creatorid = 0;
		$this->creatorName = 'System';
		$this->updated = time();
		$this->updatorid = 0;
		$this->updatorName = 'System';
		$this->email = NULL;
        $this->permissions = NULL;
        $this->password = NULL;
        $this->email = NULL;
		$this->groups[] = 'Everyone';
		if (setConstOrDefault('ANON_GOD', FALSE)) { // make anon user a god, used for site configuration
			$this->groups[] = 'Gods';
		}
	}
	
	function save(&$foowd) { // override save function since it's not a real Foowd object and is just instanciated as needed.
		return FALSE;
	}
}

?>
