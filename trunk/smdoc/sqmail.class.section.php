<?php
/*
 * Modified page index for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/* print crc32('section'); */
$HARDCLASS[SECTIONCLASSID] = 'section';

if (!defined('SECTIONCREATE')) define('SECTIONCREATE', GODS);

    /**
     * Rules for this SquirrelMail WTF Creation called a Section:
     *  Things that can not be added to sections:
     *    Users, Homes, Workspaces, Definitions
     *  Things that can be added to sections: 
     *    Content, WikiPages, Other Sections, etc.
     * 
     * The point of a section is it's $objectlist.
     * $objectlist is an array that keeps references to the 
     * pages/sections/things it contains.
     * 
     * These contained things can be ordered within the array,
     * they can be added/removed/etc.
     *
     * Any given thing can only belong to one section. 
     * Those things (listed above) that cannot be added to sections will have a section id of 0.
     * As will anything that has not been added to a section yet.
     */
    
class section extends content { 

    var $objectlist;

/*** Constructor ***/
	function section(
        &$user,
        $title = NULL,
        $viewGroup = DEFAULTVIEWGROUP,
        $editGroup = DEFAULTEDITGROUP,
        $deleteGroup = DEFAULTDELETEGROUP,
        $adminGroup = DEFAULTADMINGROUP
	) {
		track('section::section', $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		parent::thing($user, $title, '', FALSE, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		$this->objectlist = array();
		track();
	}

/*** Member Functions ***/

    /** Add object passed as parameter to this section */    
    function placeInSection(&$obj) { 
        track('section::placeInSection');
        $success = true;
        if ( $obj->sectionid != 0 ) {
            echo '<section_edit_failed '.
                 'message="Could not add thing to section, as thing already belongs to a section."/>';
            $success = false;
        } else { 
            array_push($this->objectlist, 
                       array('objectid' => $obj->objectid, 
                             'classname' => get_class($obj), 
                             'title' => $obj->title));
            $this->save();
            $obj->sectionid = $this->objectid;
        }
        track();
        return $success;
    }

    /** Remove object passed as parameter from this section */
    function removeFromSection(&$obj) {
        track('section::removeFromSection');
        $success = true;
        if ( $obj->sectionid != $this->objectid ) {
            echo '<section_edit_failed '.
                 'message="Could not remove thing that does not belong to this section."/>';
            $success = false;
        } else {
            foreach ( $this->objectlist as $key => $lstobj ) {
                if ( $obj->objectid == $lstobj['objectid'] ) {
                    break;
                }
            }
            array_splice($this->objectlist, $key, 1);
            $this->save();
            $obj->sectionid = 0; // move back to default section
        }

        track();
        return $success;
    }
    
    /** 
     * Return a list of all sections (ids and names) 
     * Only return a list including sections other than default
     * if working with a class that can use other than default.
     */
    function getSections() {
        global $conn, $wtf;
        track('section::getSections');
        $sections = array(0 => 'Default');

        $for_classid = $wtf->thing->classid;
        if ( $for_classid != USERCLASSID &&
             $for_classid != HOMECLASSID &&
             $for_classid != DEFINITIONCLASSID &&
             $for_classid != WORKSPACECLASSID ) { 
            $fields = array('objectid', 'title');
            $where = array('classid = ' . SECTIONCLASSID);
            $orderby = array('title');
            
            /* select:     connection, table,       joins, fields,  conditions, groups, orders,   limit) */
            $query = DBSelect(  $conn, OBJECTTABLE, NULL,  $fields, $where,     NULL,   $orderby, NULL);
            $numberOfRecords = getAffectedRows($conn);
            if ($numberOfRecords > 0) {
                for ($foo = 1; $foo <= $numberOfRecords; $foo++) {
                    $record = getRecord($query);
                    if ( $record['objectid'] != $wtf->thing->objectid ) {
                        $sections[intval($record['objectid'])] = $record['title'];
                    }
                }
            }
        }
        track();
        return $sections;
    }  

    function drawForm($url, $title = NULL, $titleIsEditable = FALSE, $loadtime = NULL ) {
        global $wtf;
        if (isset($this)) {
            $objectid = $this->objectid;
            $version = $this->version;
            $updatorid = $this->updatorid;
            if ($title == NULL) $title = $this->title;
        } else {
            $objectid = '';
            $version = 1;
            $updatorid = NULL;
            if ($title == NULL) $title = '';
        }
        if ($loadtime == NULL) $loadtime = time();

        echo '<section_form url="', $url, '" ';
        echo 'thingidfield="thingid" thingid="', $objectid, '" ';
        echo 'versionfield="version" version="', $version, '" ';
        echo 'loadtimefield="loadtime" loadtime="'.$loadtime.'" ';
        echo 'submit="submit">';
        if ($titleIsEditable) {
            echo '<section_titlebox name="title" maxlength="', MAXTITLELENGTH, '">', $title, '</section_titlebox>';
        } else {
            echo '<section_title title="', $title, '" version="', $version, '"/>';
        }

        echo '</section_form>';
    }

/*** Methods ****/

// view
    function method_view() {
        global $wtf;
        track('section::method::view');
        if (getValue('version', FALSE)) {
            echo '<thing_info version="'.$this->version.'" class="'.get_class($this).'"/>';
        }
        if (hasPermission($this, $wtf->user, 'viewGroup')) {
            echo 'Section content here';
        } else {
            echo '<thing_permissionerror method="view" title="'.$this->title.'"/>';
        }
        track();
    }
 
// create
    function method_create($thingName = NULL, $objectName = 'section') { // this is both a method and a static member
        global $conn, $wtf;
        track('section::method::create');
        if ($wtf->user->inGroup(SECTIONCREATE)) {
            if (isset($this)) {
                $url = THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=create';
                $objectName = get_class($this);
            } else {
                $url = THINGURI.$thingName.'&amp;class=hardclass';
            }

            $create = getValue('submit', FALSE);
            $title = getValue('title', FALSE);
            if ($create) { // if the form has been submitted, create the object
                $thing = new $objectName(
                    $wtf->user,
                    $title
                );
                if ($thing && $thing->objectid != 0) { // thing created
                    $thing->save();
                    header('Location: '.THINGIDURI.$thing->objectid.'&class='.get_class($thing));
                    exit;
                } else { 
                    echo '<create_failed message="Could not create object ', htmlspecialchars($title), '"/>';
                }
            } else { // display empty form
                section::drawForm($url, $title, TRUE);
            }
        } else {
            echo '<section_create_permission/>';
        }
        track();
   }
        
    function method_edit() { // edit thing
        global $conn, $wtf;
        track('section::method::edit');
        if (hasPermission($this, $wtf->user, 'editGroup')) {    // check permission
            $update = getValue('submit', FALSE);
            $loadtime = getValue('loadtime', NULL);

            if ($update) { // update thing
                // Make sure content isn't locked
                if ( (!isset($loadtime) ||
                      !is_numeric($loadtime) ||
                      $loadtime < dbdate2unixtime($this->updatorDatetime))  &&
                     ($this->updatorid == ANONYMOUSUSERID || 
                      $this->updatorid != $wtf->user->objectid) ) {
                    echo '<section_edit_locked/>';
                } else {
                }
            }
            
            // Draw form, $title is not editable
            $this->drawForm(THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=edit', 
                            $title, FALSE, $loadtime);
        } else {
            echo '<thing_permissionerror method="edit" title="'.$this->title.'"/>';
        }
        track();
    }

}

// formatting
$FORMAT = array_merge($FORMAT, array(
// form
    'section_form' => '<form method="post" action="{url}">'.
                      '<input type="hidden" name="{thingidfield}" value="{thingid}" />'.
                      '<input type="hidden" name="{versionfield}" value="{version}" />'.
                      '<input type="hidden" name="{loadtimefield}" value="{loadtime}" />',
    '/section_form' => '<p><input type="submit" name="{submit}" value="Save" /></p></form>',
    'section_title' => '<p>Editing section "<a href="'.THINGURI.'{title}">{title}</a>" (version {version})</p>',
    '/section_title' => '',
    'section_titlebox' => '<p>Title: <input type="text" name="{name}" size="50" maxlength="{maxlength}" value="',
    '/section_titlebox' => '"/></p>', 

// create
    'section_create_updated' => '<h2 class="success">Create Successful</h2><p>Page created successfully.</p>',
    '/section_create_updated' => '',
    'section_create_permission' => '<p class="error">You do not have permission to create a new section.</p>',
    '/section_create_permission' => '',

// edit
    'section_edit_updated' => '<h2 class="success">Edit Successful</h2><p>Section updated successfully.</p>',
    '/section_edit_updated' => '',
    'section_edit_failed' => '<h2 class="fail">Edit Failed</h2><p>{message}</p>',
    '/section_edit_failed' => '',
    'section_edit_locked' => '<p class="error">This page is currently being updated by someone else, please review their changes before making your own.</p>',
    '/section_edit_locked' => '',

// diff
    'diff' => '<h3>Differences Between Versions {old} and {new}</h3><table width="100%">',
    '/diff' => '</table>',
    'diff_add' => '<tr height="10"><th colspan="2">Added at line {position}.',
    '/diff_add' => '</th></tr>',
    'diff_remove' => '<tr height="10"><th colspan="2">Removed from line {position}.',
    '/diff_remove' => '</th></tr>',
    'diff_change' => '<tr height="10"><th colspan="2">Changed at line {remove}.',
    '/diff_change' => '</th></tr>',
    'diff_line' => '<tr><td width="50" valign="top">{linenumber}</td><td class="diffline">',
    '/diff_line' => '</td></tr>',
    'diff_plus' => '<tr><td width="50" valign="top">{linenumber} +</td><td class="diffplus">',
    '/diff_plus' => '</td></tr>',
    'diff_minus' => '<tr><td width="50" valign="top">{linenumber} -</td><td class="diffminus">',
    '/diff_minus' => '</td></tr>'
));

?>
