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

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_USER_CLASS_CREATE')) define('PERMISSION_FOOWD_USER_CLASS_CREATE', ''); // we want anyone to be able to create a user so we override this permission for this object with the empty string

/** CLASS DESCRIPTOR **/
if (!defined('META_425464453_CLASSNAME')) define('META_425464453_CLASSNAME', 'foowd_user');
if (!defined('META_425464453_DESCRIPTION')) define('META_425464453_DESCRIPTION', 'User');

if (!defined('USER_CLASS_ID')) define('USER_CLASS_ID', 425464453);

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
		$foowd->track('foowd_user->constructor');

// password
		if (preg_match(REGEX_PASSWORD, $password)) {
			$salt = getConstOrDefault('PASSWORD_SALT', '');
			$this->password = md5($salt.strtolower($password));
		} else {
			trigger_error('Could not create object, password contains invalid characters.');
			$this->objectid = 0;
			$foowd->track(); return FALSE;
		}

// base object constructor
		parent::foowd_object($foowd, $username, NULL, NULL, NULL, FALSE);

// email
		if (preg_match($this->foowd_vars_meta['email'], $email)) {
			$this->email = $email;
		}
		
// make user created and owned by self
		$this->creatorid = $this->objectid; // created by self
		$this->creatorName = $this->title;
		$this->permissions['update'] = 'Author'; // only user can update itself

// user groups
		if (is_array($groups)) {
			foreach ($groups as $group) {
				if (preg_match($this->foowd_vars_meta['groups'], $group)) {
					$this->groups[] = $group;
				}
			}
		}
		
		$foowd->track();
	}

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['password'] = '/^[a-z0-9]{32}$/';
		$this->foowd_vars_meta['email'] = REGEX_EMAIL;
		$this->foowd_vars_meta['groups'] = REGEX_GROUP;
	}

/*** MEMBER FUNCTIONS ***/

	function inGroup($groupName) {
		if (is_array($this->groups) && in_array($groupName, $this->groups)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function passwordCheck($password, $plainText = FALSE) {
		if ($plainText) {
			$password = md5(getConstOrDefault('PASSWORD_SALT', '').strtolower($password));
		}
		if ($this->password === $password || defined('AUTH_IP_'.$_SERVER['REMOTE_ADDR'])) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_user->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new user</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createUsername = new input_textbox('createUsername', REGEX_TITLE, $queryTitle->value, 'Username:');
		$createPassword = new input_passwordbox('createPassword', REGEX_PASSWORD, NULL, 'Password:');
		$createEmail = new input_textbox('createEmail', REGEX_EMAIL, NULL, 'E-mail Address:');
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		if (!$createForm->submitted() || $createUsername->value == '' || $createPassword->value == '') {
			$createForm->addObject($createUsername);
			$createForm->addObject($createPassword);
			$createForm->addObject($createEmail);
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
				echo '<p>User created and saved.</p>';
				echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))), '">Click here to view it now</a>. or <a href="', getURI(array('class' => $className, 'method' => 'login', 'username' => $this->title)), '">here to log in now</a>.</p>';
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
		if ($foowd->user->objectid == getConstOrDefault('ANONYMOUS_USER_ID', FALSE) || $foowd->user->objectid == '') {
			if (getConstOrDefault('AUTH_TYPE', 'http') == 'http') {
				header('WWW-Authenticate: Basic realm="'.getConstOrDefault('AUTH_REALM', 'Framework for Object Orientated Web Development').'"');
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
						$salt = getConstOrDefault('PASSWORD_SALT', '');
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
		$foowd->track();
	}

/* user log out */
	function class_logout(&$foowd, $className) {
		$foowd->track('foowd_user->class_logout');
		if ($foowd->user->objectid == getConstOrDefault('ANONYMOUS_USER_ID', FALSE) || $foowd->user->objectid == '') {
			if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
			echo '<h1>Log Out</h1>';
			echo '<p>You are logged out.</p>';
		} else {
			$authType = getConstOrDefault('AUTH_TYPE', 'http');
			if ($authType == 'ip' || defined('AUTH_IP_'.$_SERVER['REMOTE_ADDR'])) {
				if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
				echo '<h1>Log Out</h1>';
				echo '<p>You can not log out from this IP address.</p>';
			} elseif ($authType == 'cookie') {
				$cookieUsername = new input_cookie('username', REGEX_TITLE);
				$cookiePassword = new input_cookie('password', '/^[a-z0-9]{32}$/');
				$cookieUsername->delete();
				$cookiePassword->delete();
				if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
				echo '<h1>Log Out</h1>';
				echo '<p>You are now logged out.</p>';
			} else {
				header('WWW-Authenticate: Basic realm="'.getConstOrDefault('AUTH_REALM', 'Framework for Object Orientated Web Development').'"');
				header('HTTP/1.0 401 Unauthorized');
				if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
				echo '<h1>Log Out</h1>';
				if (isset($_SERVER['PHP_AUTH_USER'])) {
					echo '<p>To log out you must clear your browsers HTTP authentication information which you can do by entering nothing into the authentication box.</p>';
				} else {
					echo '<p>You are now logged out.</p>';	
				}
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}
	
/* user get password */
	function class_lostPassword(&$foowd, $className) {
		$foowd->track('foowd_user->class_lostPassword');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Lost Password</h1>';
		$usernameQuery = new input_querystring('username', REGEX_TITLE, '');
		$idQuery = new input_querystring('id', '/[a-z0-9]{32}/', '');
		$lostUsername = new input_textbox('lostUsername', REGEX_TITLE, $usernameQuery->value, 'Username:');
		$lostForm = new input_form('lostForm', NULL, 'POST', 'Retrieve Password', NULL);
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
				$message = "
Hi,\n
Your password has been changed, your new user account details are:\n
\tUsername: ".$user->getTitle()."\n
\tPassword: ".$newPassword."\n
Please go to the URL below to log in using your new password:\n
\thttp://".$_SERVER['SERVER_NAME'].getURI(array('class' => $className, 'method' => 'lostPassword', 'username' => $user->title, 'id' => md5($user->updated.$user->title)))."\n
Thanks.\n
".$webmaster;
				if (@mail(
					$user->email,
					$siteName.' - Password Change Request',
					$message,
					'From: '.getConstOrDefault('WEBMASTER_EMAIL', 'webmaster@'.$_SERVER['SERVER_NAME']).'\r\nReply-To: '.getConstOrDefault('NOREPLY_EMAIL', 'noreply@'.$_SERVER['SERVER_NAME'])
				&&
					$user->save()
				)) {
					echo '<p>Your new password has been sent to your registered e-mail address.</p>';
				} else {
					echo '<p>Sorry, I could not change your password for you, please contact <a href="mailto:', $webmaster, '">', $webmaster, '</a> to help you retrieve your password.</p>';
				}
			} else {
				$webmaster = getConstOrDefault('WEBMASTER_EMAIL', 'webmaster@'.$_SERVER['SERVER_NAME']);
				echo '<p>Sorry there was a problem requesting your new password, please contact <a href="mailto:', $webmaster, '">', $webmaster, '</a> to help you retrieve your password.</p>';
			}
		} elseif ($lostForm->submitted() && $lostUsername->value != '') {
			$user = $foowd->fetchObject(array(
				'objectid' => crc32(strtolower($lostUsername->value)),
				'classid' => USER_CLASS_ID
			));
			if (isset($user->title) && strtolower($user->title) == strtolower($lostUsername->value)) {
				$webmaster = getConstOrDefault('WEBMASTER_EMAIL', 'webmaster@'.$_SERVER['SERVER_NAME']);
				if (isset($user->email)) {
					$siteName = getConstOrDefault('SITE_NAME', $_SERVER['SERVER_NAME']);
					$message = "
Hi,\n
You, or someone looking very much like you, requested a new password for the user account \"".$user->getTitle()."\" at ".$siteName."\n
If this is correct, please use the following URL to finalise the new password request and have your new password e-mailed to you at this address.\n
\thttp://".$_SERVER['SERVER_NAME'].getURI(array('class' => $className, 'method' => 'lostPassword', 'username' => $user->title, 'id' => md5($user->updated.$user->title)))."\n
If this is not correct, please just ignore this e-mail.\n
Thanks.\n
".$webmaster;
					if (@mail(
						$user->email,
						$siteName.' - Password Change Request',
						$message,
						'From: '.getConstOrDefault('WEBMASTER_EMAIL', 'webmaster@'.$_SERVER['SERVER_NAME']).'\r\nReply-To: '.getConstOrDefault('NOREPLY_EMAIL', 'noreply@'.$_SERVER['SERVER_NAME'])
					)) {
						echo '<p>An e-mail has been sent to you requesting your permission to send you a new password.</p>';
					} else {
						echo '<p>Sorry, I could not send an e-mail to your listed address, please contact <a href="mailto:', $webmaster, '">', $webmaster, '</a> to help you retrieve your password.</p>';
					}
				} else {
					echo '<p>User "', htmlspecialchars($lostUsername->value),'" does not have an e-mail address, please contact <a href="mailto:', $webmaster, '">', $webmaster, '</a> to help you retrieve your password.</p>';
				}
			} else {
				echo '<p>Sorry. user "', htmlspecialchars($lostUsername->value),'" not found.</p>';
			}
		} else {
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
		echo '<h1>Viewing User "', $this->getTitle(), '"</h1>';
		echo '<table>';
		echo '<tr><th>Username:</th><td>', $this->getTitle(), '</td></tr>';
		$email = mungEmail($this->email);
		echo '<tr><th>E-mail:</th><td><a href="mailto:', $email, '">', $email, '</a></td></tr>';
		echo '<tr><th>Created:</th><td>', date(DATETIME_FORMAT, $this->created), ' (', timeSince($this->created), ' ago)</td></tr>';
		echo '<tr><th>Last Visit:</th><td>', date(DATETIME_FORMAT, $this->updated), ' (', timeSince($this->updated), ' ago)</td></tr>';
		echo '</table>';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* change user details */
	function method_update(&$foowd) {
		$foowd->track('foowd_user->method_update');
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
			if ($changed && $this->save($foowd, FALSE)) {
				echo '<p>User updated.</p>';
			} else {
				trigger_error('Could not update user.');
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
				$salt = getConstOrDefault('PASSWORD_SALT', '');
				$this->password = md5($salt.strtolower($password->value));
				if ($this->save($foowd, FALSE)) {
					echo '<p>User password changed, you will now need to <a href="', getURI(array('class' => get_class($this), 'method' => 'login', 'username' => $this->title)), '">log in using your new password</a>.</p>';
				} else {
					trigger_error('Could not save user.');
				}
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

}

/*** ANONYMOUS USER ***/

/* Anonymous user class, used to instanciate bogus user for anoymous access where
   only basic user data is required and it would be a waste to pull a user from the
   database. */

class foowd_anonuser extends foowd_user {
	function foowd_anonuser(&$foowd) {
		$foowd->track('foowd_anonuser->constructor');
		if (defined('ANONYMOUS_USER_NAME')) {
			$this->title = ANONYMOUS_USER_NAME;
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$this->title = $_SERVER['REMOTE_ADDR'];
		} else {
			$this->title = 'Anonymous';
		}
    $this->objectid = NULL;
    $this->version = 1;
    $this->classid = -1063205124;
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
		if (getConstOrDefault('ANON_GOD', FALSE)) { // make anon user a god, used for site configuration
			$this->groups[] = 'Gods';
		}
		$foowd->track();
	}
	
	function save(&$foowd) { // override save function since it's not a real Foowd object and is just instanciated as needed.
		return FALSE;
	}
}

?>