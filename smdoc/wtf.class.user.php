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
wtf.class.user.php
User Class
*/

/*
 * Modified by SquirrelMail Development Team
 * $Id$
 */
$HARDCLASS[USERCLASSID] = 'user';

class user extends thing { // a user

	var $password; // md5 hashed
	var $homeid, $email;
	var $skin;
	var $groups;
	var $homeObject = 'home';
	
/*** Constructor ***/

	function user(
		&$user,
		$username = NULL,
		$password = NULL,
		$email = NULL
	) {
		track('user::user', $username, $password, $email);
		parent::thing($user, $username, USERVIEWGROUP, USEREDITGROUP, USERDELETEGROUP, USERADMINGROUP, FALSE);
		$this->password = md5(PASSWORDSALT.$password);
		$this->email = htmlspecialchars($email);
		$this->skin = DEFAULTSKIN;
		$this->groups[] = 'Everyone'; // all users are in the everyone group
		$this->indexes['updatorDatetime'] = 'DATETIME NOT NULL';
		$this->homeid = NULL;
		$this->homeObject = 'home';
		track();
	}
	
/*** Member Functions ***/

	function setHomeid(&$homeThing) {
		track('user::setHomeid', $homeThing->objectid);
		$this->homeid = $homeThing->objectid;
		track(); return TRUE;
	}
	
	function setEmail($email) {
		track('user::setEmail', $email);
		if (isset($email) && $email != NULL && preg_match('/'.EMAILMATCHREGEX.'/', $email)) {
			$this->email = htmlspecialchars($email);
			parent::update($this, FALSE);
			track(); return TRUE;
		} else {
			track(); return FALSE;
		}
	}

	function setSkin($skin) {
		track('user::setSkin', $skin);
		if (isset($skin) && $skin != NULL) {
			$this->skin = $skin;
			parent::update($this, FALSE);
			track(); return TRUE;
		} else {
			track(); return FALSE;
		}
	}
	
	function setPassword($password, $verify) {
		track('user::setPassword', $password, $verify);
		if (
			isset($password) &&
			isset($verify) &&
			$password != '' &&
			strlen($password) <= MAXPASSWORDLENGTH && 
			$password == $verify
		) {
			$this->password = md5(PASSWORDSALT.$password);
			setcookie('password', $this->password, time() + COOKIELIFE, COOKIEPATH, COOKIEDOMAIN);
			parent::update($this, FALSE);
			track(); return TRUE;
		} else {
			track(); return FALSE;
		}
	}

	function addGroup($group) {
		track('user::addGroup', $group);
		if (!in_array($group, $this->groups)) {
			$this->groups[] = htmlspecialchars($group);
		}
		track(); return TRUE;
	}

	function removeGroup($group) {
		track('user::removeGroup', $group);
		$key = array_search($group, $this->groups);
		if ($key) {
			unset($this->groups[$key]);
			track(); return TRUE;
		} else {
			track(); return FALSE;
		}
	}

	function inGroup($group) {
		track('user::inGroup', $group);
		if (is_array($this->groups) && in_array(htmlspecialchars($group), $this->groups)) {
			track(); return TRUE;
		} else {
			track(); return FALSE;
		}
	}
	
	function userExists($userid) { // static member to see if a user exists
		global $conn;
		track('user::userExists', $userid);
		$classid = getIDFromName('user');
		$table = getTable($classid);
		$where = array(
			'objectid = '.$userid,
			'AND',
			'classid = '.$classid
		);
		$query = DBSelect($conn, $table, NULL,
			array($table.'.objectid'),
			$where,
			NULL,
			NULL,
			1
		);
		if ($query) {
			$numberOfRecords = getAffectedRows();
			if ($numberOfRecords > 0) {
				track(); return TRUE;
			}
		}
		track(); return FALSE;
	}
	
// delete
	function delete($delete_home = false) {
        track('user::delete');
        if ( $delete_home ) {
            // load home thing
            $home = &wtf::loadObject($this->homeid, 0, $classes = 'home');
            if ($home) {
                // delete home thing
                $home->delete();
            }
        }
        // delete thing
        track();
        return parent::delete();
    }

/*** Methods ***/

// display
	function method_view() {
		track('user::method::view');
		if (getValue('version', FALSE)) {
			echo '<thing_info version="'.$this->version.'" class="'.get_class($this).'"/>';
		}
		echo 'This thing can not be displayed, however you may be looking for <a href="', THINGIDURI, $this->homeid, '&amp;class=home">this page instead</a>.';
		track();
	}

// login
	function method_login($thingName = NULL) {
		global $wtf;
		track('user::method::login');
		
		if (HTTPAUTH && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) { // HTTP authentication
			$username = $_SERVER['PHP_AUTH_USER'];
			$password = $_SERVER['PHP_AUTH_PW'];
		} else { // standard form authentication
			$username = getValue('username', FALSE);
			$password = getValue('password', FALSE);
		}

		if (isset($this)) {
			$url = THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=login';
		} else {
			$url = THINGURI.$thingName.'&amp;class=hardclass';
		}
		
		if ($username && $password) {
			if (
				$username != '' &&
				strlen($username) < MAXTITLELENGTH &&
				preg_match('/'.TITLEMATCHREGEX.'/', $username) &&
				strlen($password) < MAXPASSWORDLENGTH &&
				preg_match('/'.PASSWORDMATCHREGEX.'/', $password)
			) {
				$wtf->user = &wtf::loadUser($username, $password);
				if ($wtf->user->objectid == ANONYMOUSUSERID) {
					if (HTTPAUTH) {
						header('WWW-Authenticate: Basic realm="'.HTTPAUTHREALM.'"');
						header('HTTP/1.0 401 Unauthorized');
						echo '<login_httpautherror />';
					} else {
						echo '<login_error username="', htmlspecialchars($username), '"/>';
					}
				} else {
                    header("Location: " .THINGIDURI.$wtf->user->homeid.'&class=home&show_msg=login');
				}

			} else {
				echo '<login_error username="', htmlspecialchars($username), '"/>';
			}
		} elseif (HTTPAUTH) {
			header('WWW-Authenticate: Basic realm="'.HTTPAUTHREALM.'"');
			header('HTTP/1.0 401 Unauthorized');
			echo '<login_httpautherror />';
		} elseif ($wtf->user->objectid == ANONYMOUSUSERID) {
            $msg = getValue('show_msg', FALSE);
            if ( $msg == 'logout' ) {
                echo '<logout_success/>'; 
            } 
			echo '<login_box url="', $url, '" usernamefield="username" passwordfield="password"/>';
		} else {
			echo '<login_already/>';
		}
		track();
	}
	
// logout
	function method_logout() {
		global $wtf;
		track('user::method::logout');
		if ($wtf->user->objectid == ANONYMOUSUSERID) {
			echo '<logout_already/>';
		} else {
			if (USECOOKIE) {
				setcookie('userid', '', time() - COOKIELIFE, COOKIEPATH, COOKIEDOMAIN);
				setcookie('password', '', time() - COOKIELIFE, COOKIEPATH, COOKIEDOMAIN);
			}
            header("Location: " .THINGIDURI.ANONYMOUSUSERID.'&class=user&op=login&show_msg=logout');
		}
		track();
	}
	
// create (register)
	function method_create($thingName = NULL, $objectName = 'user') {
		global $wtf;
		track('user::method::create');
		$username = getValue('username', FALSE);
		$password = getValue('password', FALSE);
		
		if (isset($this)) {
			$url = THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=create';
			$objectName = get_class($this);
		} else {
			$url = THINGURI.$thingName.'&amp;class=hardclass';
		}
		
		if ($username && $password) {
			if ($username != '' &&
				strlen($username) < MAXTITLELENGTH &&
				preg_match('/'.TITLEMATCHREGEX.'/', $username) &&
				strlen($password) < MAXPASSWORDLENGTH &&
				preg_match('/'.PASSWORDMATCHREGEX.'/', $password)
			) {
				$email = getValue('email', NULL);
				$user = new $objectName($wtf->user, $username, $password, $email);
				if ($user->objectid != 0) { // object created okay
 // set creator to self so user owns itself
					$user->creatorid = $user->objectid;
					$user->creatorName = $user->title;
// create home
					$home = new $user->homeObject($user, $user->title);
					$user->setHomeid($home);
					$home->save();
					$user->save();
					unset($home);
					unset($user);
					$wtf->user = &wtf::loadUser($username, $password);
					if ($wtf->user->objectid == ANONYMOUSUSERID) {
						echo '<register_error username="', htmlspecialchars($username), '"/>';
					} else {
                        header("Location: " .THINGIDURI.$wtf->user->homeid.'&class=home&show_msg=register');
					}
				} else {
					echo '<register_error username="', htmlspecialchars($username), '"/>';
				}
			} else {
				echo '<register_error username="', htmlspecialchars($username), '"/>';
			}
		} elseif ($wtf->user->objectid == ANONYMOUSUSERID) {
			echo '<register_box url="'.$url.'" usernamefield="username" passwordfield="password" emailfield="email"/>';
		} else {
			echo '<register_already />';
		}
		track();
	}

// edit
	function method_edit() {
		global $wtf, $SKIN;
		track('user::method::edit');
		if ($this->objectid == ANONYMOUSUSERID) {
			echo 'You must be logged in to view your profile.';
		} elseif (hasPermission($this, $wtf->user, 'editGroup')) {	// check permission
			$submit = getValue('submit', FALSE);
			if ($submit) {
				$password = getValue('password', FALSE);
				$verify = getValue('verify', FALSE);
				$email = getValue('email', FALSE);
				$skin = getValue('skin', FALSE);
				$changed = FALSE;
				if ($password != '' && md5(PASSWORDSALT.$password) != $this->password) {
					if ($this->setPassword($password, $verify)) {
						echo 'Password changed.';
						$changed = TRUE;
					} else {
						echo 'Error, password not changed.';
					}
				}
				if ($email != $this->email) {
					if ($this->setEmail($email)) {
						echo 'E-mail changed.';
						$changed = TRUE;
					} else {
						echo 'Error, e-mail not changed.';
					}
				}
				if ($skin != $this->skin) {
					if ($this->setSkin($skin)) {
						echo 'Skin changed.';
						$changed = TRUE;
					} else {
						echo 'Error, skin not changed.';
					}
				}
				if ($changed) {
					$this->update($wtf->user, FALSE);
					$this->save();
				}
			}
			echo '<profile_form url="', THINGIDURI, $this->objectid, '&amp;class=user&amp;version=', $this->version, '&amp;op=edit">';
			echo '<profile_email>', $this->email, '</profile_email>';
			echo '<profile_skin>';
			foreach($SKIN as $skin => $url) {
				if ($skin != 'xml') {
					if ($skin == $this->skin) {
						echo '<profile_skin_default value="', $skin, '">', $skin, '</profile_skin_default>';
					} else {
						echo '<profile_skin_option value="', $skin, '">', $skin, '</profile_skin_option>';
					}
				}
			}
			echo '</profile_skin>';
			echo '</profile_form>';
		} else {
			echo '<profile_permission title="'.$this->title.'"/>';
		}
		track();
	}

// delete
    function method_delete() {
        global $conn, $wtf;
        track('user::method::delete');
        if (hasPermission($this, $wtf->user, 'deleteGroup')) { // check permission
            if (getValue('confirm', FALSE) == 'true') { // do delete
                // Here, we're actually deleting the user,
                // so specify true to also clean up the home
                if ($this->delete(true)) {
                    echo '<delete_success title="', $this->title, '"/>';
                } else {
                    echo '<delete_error title="', $this->title, '"/>';
                }
            } else { // prompt
                echo '<delete_verify url="'.THINGIDURI.$this->objectid.'&amp;class='.$wtf->class.'&amp;op=delete&amp;confirm=true" class="'.$wtf->class.'" thingid="'.$this->objectid.'" title="'.$this->title.'"/>';
            }
        } else {
            echo '<thing_permissionerror method="delete" title="'.$this->title.'"/>';
        }
        track();
    }


// admin
	function admin_update() {
		global $wtf;
	// groups
		$groupsArray = array();
		for ($foo = 0; $group = getValue('group,'.$foo, FALSE), $group == TRUE; $foo++) {
			if (getValue('deletegroup,'.$foo, FALSE) != 'on' && ($wtf->user->inGroup($group) || $wtf->user->inGroup(GROUPS))){
				$groupsArray[$foo] = htmlspecialchars($group);
			}
		}
		$newgroup = getValue('newgroup', FALSE);
		if ($newgroup && $newgroup != '' && ($wtf->user->inGroup($newgroup) || $wtf->user->inGroup(GROUPS))) {
			$groupsArray[] = htmlspecialchars($newgroup);
		}
		if (array_count_values($groupsArray) > 0) {
			$this->groups = $groupsArray;
		}
	// call parent
		parent::admin_update();
	}
	
	function admin_fields($className, $workspaces) {
		parent::admin_fields($className, $workspaces);
		if (isset($this->groups)) {
			echo '<admin_group name="newgroup">';
			if (is_array($this->groups) && $this->groups != NULL) {
				foreach ($this->groups as $key => $group) {
					echo '<admin_groupitem name="group,'.$key.'" cbname="deletegroup,'.$key.'" group="'.$group.'"/>';
				}
			}
			echo '</admin_group>';
		}
	}


}

/* HOMECLASSID defined in wtf.config.php */
$HARDCLASS[HOMECLASSID] = 'home';

class home extends content { // a users home thing

/*** Constructor ***/

	function home(
		&$user,
		$username = NULL
	) {
		track('home::home', $username);
		parent::content($user, $username, USERTHINGBODY, TRUE, USERVIEWGROUP, USEREDITGROUP, USERDELETEGROUP, USERADMINGROUP);
		track();
	}

/*** Methods ***/

// display
	function method_view() { // output home thing
		global $wtf;
		track('home::method::view');
		if (hasPermission($this, $wtf->user, 'viewGroup')) {	// check permission
			$user = &wtf::loadObject($this->objectid, 0, 'user');
			if ($user) {
				if ($this->contentIsXML) {
					$newLine = CONVERTNEWLINESTO;
				} else {
					$newLine = "\n";
				}
                
                $username =  htmlspecialchars($user->title);
                $msg = getValue('show_msg', FALSE);
                if ( $msg == 'login' ) {
                  echo '<login_success username="', $username, '"/>'; 
                } elseif ( $msg == 'register' ) {
                  echo '<register_success username="', $username, '"/>';
                } 

				echo 'Username: '.htmlspecialchars($user->title). $newLine;
	//			echo 'E-mail: '.htmlspecialchars(encodeEmail($user->email)), $newLine;
				echo 'Registered: '.date(DATEFORMAT, dbdate2unixtime($user->creatorDatetime)), $newLine;
				echo 'Last Visited: '.date(DATEFORMAT, dbdate2unixtime($user->updatorDatetime)), $newLine;
				echo 'User Groups: ';
				if (isset($user->groups) && is_array($user->groups)) {
					foreach ($user->groups as $group) {
						echo htmlspecialchars($group), ' ';
					}
				} else {
					echo 'Unknown';
				}
				echo $newLine;
				if ($user->objectid == $wtf->user->objectid) {
					echo $newLine;
					echo '<a href="', THINGIDURI, $user->objectid, '&amp;class=user&amp;op=edit">Update your profile</a>.', $newLine;
				}
			}
			unset($user);
			parent::method_view(); // append home thing content
			track();
		} else {
			echo '<error>You do not have permission to view "'.$this->title.'".</error>';
		}
	}
	
// create
	function method_create() { // create thing
		echo 'You can not make a thing of this type, however you may be looking for <a href="', THINGURI, DEFAULTPAGENAME, '&amp;class=content&amp;op=create">this page instead</a>.';
	}

}

// formatting
$FORMAT = array_merge($FORMAT, array(
// login
	'login_box' => '
<form method="post" action="{url}"><p>
Username: <input type="text" name="{usernamefield}" maxlength="'.MAXTITLELENGTH.'" value="" /><br />
Password: <input type="password" name="{passwordfield}" maxlength="'.MAXPASSWORDLENGTH.'" value="" /><br />
<input type="submit" name="login" value="Log in" /><br />
</p></form>',
	'/login_box' => '',
	'login_success' => '<p class="success">You are now logged in as "{username}".</p>',
	'/login_success' => '',
	'login_error' => '<p class="error">There was an error logging into account "{username}".</p>',
	'/login_error' => '',
	'login_httpautherror' => '<p class="error">You must enter a valid username and password.</p>',
	'/login_httpautherror' => '',
	'login_already' => '<p class="error">You are already logged in.</p>',
	'/login_already' => '',

// logout
	'logout_success' => '<p class="success">You are now logged out.</p>',
	'/logout_success' => '',
	'logout_already' => '<p class="error">You are already logged out.</p>',
	'/logout_already' => '',

// register
	'register_box' => '
<form method="post" action="{url}"><p>
Username: <input type="text" name="{usernamefield}" maxlength="'.MAXTITLELENGTH.'" value="" /><br />
Password: <input type="password" name="{passwordfield}" maxlength="'.MAXPASSWORDLENGTH.'" value="" /><br />
E-mail: <input type="text" name="{emailfield}" value="" size="40" /><br />
<input type="submit" name="register" value="Create Account" /><br />
</p></form>',
	'/register_box' => '',
	'register_success' => '<p class="success">You are now registered and logged in as "{username}".</p>',
	'/register_success' => '',
	'register_error' => '<p class="error">There was an error creating account "{username}".</p>',
	'/register_error' => '',
	'register_already' => '<p class="error">You\'re logged in, you don\'t need to create an account, you already have one.</p>',
	'/register_already' => '',

// edit
	'profile_form' => '
<form method="post" action="{url}"><p>
Password: <input type="password" name="password" maxlength="'.MAXPASSWORDLENGTH.'" value="" /><br />
Verify: <input type="password" name="verify" maxlength="'.MAXPASSWORDLENGTH.'" value="" /><br />
	',
	'/profile_form' => '
<input type="submit" name="submit" value="Update Profile" /><br />
</p></form>
	',
	'profile_email' => 'E-mail: <input type="text" name="email" maxlength="255" value="',
	'/profile_email' => '" /><br />',
	'profile_skin' => 'Skin: <select name="skin">',
	'/profile_skin' => '</select><br />',
	'profile_skin_option' => '<option value="{value}">',
	'/profile_skin_option' => '</option>',
	'profile_skin_default' => '<option value="{value}" selected="selected">',
	'/profile_skin_default' => '</option>',
	'profile_permission' => '<p class="error">You do not have permission to edit "{title}".</p>',
	'/profile_permission' => '',

// admin	
	'admin_group' => 'Groups: ',
	'/admin_group' => '<input type="text" value="" size="10" name="{name}" title="Create a new group" /><br />',
	'admin_groupitem' => '<input type="checkbox" name="{cbname}" title="Check to delete group \'{group}\'" /><input type="text" value="{group}" name="{name}" size="10" /> ',
	'/admin_groupitem' => ''
));

?>
