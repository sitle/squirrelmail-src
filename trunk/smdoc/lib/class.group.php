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
 * class.group.php
 * User group class
 */

/** METHOD PERMISSIONS **/
setPermission('foowd_group', 'class', 'add', 'Gods');
setPermission('foowd_group', 'class', 'remove', 'Gods');
setPermission('foowd_group', 'object', 'add', 'Author');
setPermission('foowd_group', 'object', 'remove', 'Author');

/** CLASS DESCRIPTOR **/
setClassMeta('foowd_group', 'User Group');

setConst('GROUP_CLASS_ID', META_FOOWD_GROUP_CLASS_ID);

/** CLASS DECLARATION **/
class foowd_group extends foowd_object {

    var $description;
    
/*** CONSTRUCTOR ***/

    function foowd_group(
        &$foowd,
        $title = NULL,
        $description = NULL,
        $viewGroup = NULL,
        $adminGroup = NULL,
        $deleteGroup = NULL,
        $addGroup = NULL,
        $removeGroup = NULL
    ) {
        $foowd->track('foowd_group->constructor');

// base object constructor
        parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

/* set object vars */
        $this->description = $description;

/* set method permissions */
        if ($addGroup != NULL) $this->permissions['add'] = $addGroup;
        if ($removeGroup != NULL) $this->permissions['remove'] = $removeGroup;

        $foowd->track();    
    }

/*** SERIALIZE FUNCTIONS ***/

    function __wakeup() {
        parent::__wakeup();
        $this->foowd_vars_meta['description'] = '/^.{1,1024}$/';
    }

/*** MEMBER FUNCTIONS ***/

    function getUsersInGroup(&$foowd, $groupString) 
    {
        $users = NULL;
        $objects = $foowd->retrieveObjects(array('classid = '.USER_CLASS_ID),
                                                 NULL,
                                                 array('title'));
        if ($objects) 
        {
            while ($object = $foowd->retrieveObject($objects)) 
            {
                if (in_array($groupString, $object->groups)) 
                    $users[$object->objectid] = $object;
            }
        }
        return $users;
    }
    
    function getUsersNotInGroup(&$foowd, $groupString) 
    {
        $users = NULL;
        $objects = $foowd->retrieveObjects(array('classid = '.USER_CLASS_ID),
                                                 NULL,
                                                 array('title'));
        if ($objects) 
        {
            while ($object = $foowd->retrieveObject($objects)) 
            {
                if (!in_array($groupString, $object->groups))
                    $users[$object->objectid] = $object;
            }
        }
        return $users;
    }
    
    function displayGroupList(&$foowd, $className, $method) 
    {
        $groups = $foowd->getUserGroups();
        echo '<ul>';
        foreach($groups as $key => $group) {
            echo '<li><a href="', 
                 getURI(array('class' => $className, 
                              'method' => $method, 
                              'group' => $key)), '">', 
                 htmlspecialchars($group), 
                 '</a></li>';
        }
        echo '</ul>';
    }
    
    function add(&$foowd, $groupString, $groupName, $className = NULL) {
        $userForm = new input_form('userForm', NULL, 'POST', _("Add"), NULL);

        if (isset($className)) {
            $users = call_user_func(array($className, 'getUsersNotInGroup'), $foowd, $groupString);
        } else {
            $users = $this->getUsersNotInGroup($foowd, $groupString);
        }
        if ($users) {
            $items = NULL;
            foreach ($users as $user) {
                $items[$user->objectid] = $user->getTitle();
            }
            if (count($items) > 10) {
                $userSelect = new input_narrowselect('userSelect', NULL, REGEX_GROUP, $items, _("Add Users"), 5, 5, TRUE, NULL, TRUE);
            } else {
                $userSelect = new input_dropdown('userSelect', NULL, $items, _("Add Users").':', 4, TRUE);
            }
            
            if ($userForm->submitted() && is_array($userSelect->value)) {
                $error = FALSE;
                foreach ($users as $user) {
                    if (!in_array($groupString, $user->groups) && in_array($user->objectid, $userSelect->value)) {
                        $user->groups[] = $groupString;
                        if ($user->save($foowd, FALSE)) {
                            printf(_("User \"%s\" added to user group \"%s\".<br />"), $user->getTitle(), $groupName);
                        } else {
                            $error = TRUE;
                            trigger_error('Could not add user "'.$user->getTitle().'" to user group.');
                        }
                    }
                }
                if ($error) {
                    trigger_error('Not all users could be added correctly.');
                } else {
                    echo '<p>', _("Users added successfully."), '</p>';
                }
            } else {
                $userForm->addObject($userSelect);
                $userForm->display();
            }
        } else {
            echo '<p>', _("There are no users outside the group to add."), '</p>';
        }
    }

    function remove(&$foowd, $groupString, $groupName, $className = NULL) {
        $userForm = new input_form('userForm', NULL, 'POST', _("Remove"), NULL);

        if (isset($className)) {
            $users = call_user_func(array($className, 'getUsersInGroup'), $foowd, $groupString);
        } else {
            $users = $this->getUsersInGroup($foowd, $groupString);
        }
        if ($users) {
            $items = NULL;
            foreach ($users as $user) {
                $items[$user->objectid] = $user->getTitle();
            }
            if (count($items) > 10) {
                $userSelect = new input_narrowselect('userSelect', NULL, REGEX_GROUP, $items, _("Remove Users"), 5, 5, TRUE, NULL, TRUE);
            } else {
                $userSelect = new input_dropdown('userSelect', NULL, $items, _("Remove Users").':', 4, TRUE);
            }
            
            if ($userForm->submitted() && is_array($userSelect->value)) {
                $error = FALSE;
                foreach ($users as $user) {
                    if ($groupString !== FALSE && in_array($user->objectid, $userSelect->value)) {
                        unset($user->groups[$groupString]);
                        if ($user->save($foowd, FALSE)) {
                            printf(_('User "%s" removed to user group "%s".<br />'), $user->getTitle(), $groupName);
                        } else {
                            $error = TRUE;
                            trigger_error('Could not remove user "'.$user->getTitle().'" from user group.');
                        }
                    }
                }
                if ($error) {
                    trigger_error('Not all users could be removed correctly.');
                } else {
                    echo '<p>', _("Users removed successfully."), '</p>';
                }
            } else {
                $userForm->addObject($userSelect);
                $userForm->display();
            }
        } else {
            echo '<p>', _("There are no users in this group to remove."), '</p>';
        }
    }

/*** CLASS METHODS ***/

/* create object */
    function class_create(&$foowd, $className) {
        $foowd->track('foowd_group->class_create');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
        echo '<h1>', _("Create new user group"), '</h1>';
        $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
        $createForm = new input_form('createForm', NULL, 'POST', _("Create"), NULL);
        $createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, _("Title").':');
        $createDescription = new input_textbox('createDescription', '/^.{1,1024}$/', NULL, _("Description").':', NULL, NULL, NULL, FALSE);
        if (!$createForm->submitted() || $createTitle->value == '') {
            $createForm->addObject($createTitle);
            $createForm->addObject($createDescription);
            $createForm->display();
        } else {
            $object = new $className(
                $foowd,
                $createTitle->value,
                $createDescription->value
            );
            if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
                echo '<p>', _("User group created and saved."), '</p>';
                echo '<p>', sprintf(_('<a href="%s">Click here to view it now</a>.'), getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className))))), '</p>';
            } else {
                trigger_error('Could not create user group.');
            }
        }
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }

/* add users to groups */
    function class_add(&$foowd, $className) {
        $foowd->track('foowd_group->class_add');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        $group = new input_querystring('group', REGEX_GROUP, NULL);
        if (!isset($foowd->groups[$group->value])) {
            echo '<h1>', _("Add user to group"), '</h1>';
            echo '<p>', _('Select the group to add users to:'), '</p>';
            call_user_func(array($className, 'displayGroupList'), $foowd, $className, 'add');
        } else {
            if ($foowd->user->inGroup($group->value)) {
                echo '<h1>', sprintf(_("Add user to group \"%s\""), htmlspecialchars($foowd->groups[$group->value])), '</h1>';
                call_user_func(array($className, 'add'), $foowd, $foowd->groups[$group->value], $group->value, $className);
            } else {
                trigger_error('You do not have permission to add users to this group.');
            }
        }

        if (function_exists('foowd_append')) foowd_append($foowd, $this);        
        $foowd->track();
    }

/* remove users to groups */
    function class_remove(&$foowd, $className) {
        $foowd->track('foowd_group->class_remove');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        $group = new input_querystring('group', REGEX_GROUP, NULL);
        if (!isset($foowd->groups[$group->value])) {
            echo '<h1>', _("Remove user from group"), '</h1>';
            echo '<p>', _('Select the group to remove users from:'), '</p>';
            call_user_func(array($className, 'displayGroupList'), $foowd, $className, 'remove');
        } else {
            if ($foowd->user->inGroup($group->value)) {
                echo '<h1>', sprintf(_("Remove User From Group %s"), htmlspecialchars($foowd->groups[$group->value])), '</h1>';
                call_user_func(array($className, 'remove'), $foowd, $foowd->groups[$group->value], $group->value, $className);
            } else {
                trigger_error('You do not have permission to remove users from this group.');
            }
        }

        if (function_exists('foowd_append')) foowd_append($foowd, $this);        
        $foowd->track();
    }

/*** METHODS ***/

/* view object */
    function method_view(&$foowd) {
        $foowd->track('foowd_group->method_view');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        echo '<table>';
        echo '<tr><th>', _("Title"), ':</th><td>', $this->getTitle(), '</td></tr>';
        echo '<tr><th>', _("Created"), ':</th><td>', date(DATETIME_FORMAT, $this->created), '</td></tr>';
        echo '<tr><th>', _("Author"), ':</th><td><a href="', getURI(array('objectid' => $this->creatorid, 'classid' => USER_CLASS_ID)), '">', $this->creatorName, '</td></tr>';
        $className = get_class($this);
        echo '<tr><th>', _("Add"), ':</th><td>', getPermission($className, 'add', 'object'), '</td></tr>';
        echo '<tr><th>', _("Remove"), ':</th><td>', getPermission($className, 'remove', 'object'), '</td></tr>';
        echo '<tr><th>', _("Users"), ':</th><td>';
        if ($users = $this->getUsersInGroup($foowd, $this->title)) {
            foreach ($users as $user) {
                echo '<a href="', getURI(array('objectid' => $user->objectid, 'classid' => $user->classid)), '">', $user->getTitle(), '</a> ';
            }
            echo '</td>';
        } else {
            echo _("None"), '</td>';
        }
        echo '</table>';
        
        echo '<p>', htmlspecialchars($this->description), '</p>';
        
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }

/* add user to group */
    function method_add(&$foowd) {
        $foowd->track('foowd_group->method_add');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        $this->add($foowd, $this->getTitle(), $this->objectid);
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }

/* remove user from group */
    function method_remove(&$foowd) {
        $foowd->track('foowd_group->method_remove');
        if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        $this->remove($foowd, $this->getTitle(), $this->objectid);        
        if (function_exists('foowd_append')) foowd_append($foowd, $this);
        $foowd->track();
    }

}

?>