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
    
class section extends wikipage { 

    var $objectlist;

/*** Constructor ***/
	function section(
        &$user,
        $title = NULL,
        $content = '',
        $viewGroup = DEFAULTVIEWGROUP,
        $editGroup = DEFAULTEDITGROUP,
        $deleteGroup = DEFAULTDELETEGROUP,
        $adminGroup = DEFAULTADMINGROUP
	) {
		track('section::section', $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		parent::wikipage($user, $title, $content, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
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
            $obj->sectionid = $this->objectid;
            array_push($this->objectlist, 
                       array('objectid' => $obj->objectid, 
                             'classname' => get_class($obj), 
                             'title' => $obj->title));
            $this->save();
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

    function drawForm($url, $title = NULL, $titleIsEditable = FALSE, $content = NULL, 
                      $comment = NULL, $small = NULL, $loadtime = NULL ) {
        global $wtf;
        if (isset($this)) {
            $objectid = $this->objectid;
            $version = $this->version;
            $updatorid = $this->updatorid;
            $objectlist = $this->objectlist;
            if ($title == NULL) $title = $this->title;
            if ($content == NULL) $content = $this->content;
        } else {
            $objectid = '';
            $version = 1;
            $updatorid = NULL;
            $objectlist == NULL;
            if ($title == NULL) $title = '';
            if ($content == NULL) $content = $this->content;
        }
        if ($loadtime == NULL) $loadtime = time();

        echo '<content_form url="', $url, '" ';
        echo 'thingidfield="thingid" thingid="', $objectid, '" ';
        echo 'versionfield="version" version="', $version, '" ';
        echo 'loadtimefield="loadtime" loadtime="'.$loadtime.'" ';
        echo 'submit="submit" preview="preview">';

        if ($titleIsEditable) {
            echo '<content_titlebox name="title" maxlength="', MAXTITLELENGTH, '">', $title, '</content_titlebox>';
        } else {
            echo '<content_title title="', $title, '" version="', $version, '"/>';
        }

        # If this section owns something
        echo '<sectionlist_edit>';

        $num_objects = count($objectlist);
        if ( $objectlist != NULL && $num_objects > 0 ) {
            foreach ( $objectlist as $key => $obj ) {
                $value  = 'url="' . $url . '" ';
                $value .= 'key="' . $key . '"> ';
                $value .= $obj['title'];
                if ( $num_objects == 1 ) {
                    echo '<sectionlist_edit_item ' . $value . '</sectionlist_edit_item>';
                } elseif ( $key == 0 ) {
                    echo '<sectionlist_edit_item_down ' . $value . '</sectionlist_edit_item_down>';
                } elseif ( $key == $num_objects - 1 ) {
                    echo '<sectionlist_edit_item_up ' . $value . '</sectionlist_edit_item_up>';
                } else {
                    echo '<sectionlist_edit_item_updown ' . $value . '</sectionlist_edit_item_updown>';
                }
            }
        }
        echo '</sectionlist_edit>';

        echo '<wikipage_canvas name="content" maxlength="', MAXCONTENTLENGTH, '">', $content, '</wikipage_canvas>';

        # If you're the same person as the last updator and not Anonymous, 
        # give you the option of making a "small" update to cover those oopses.
        if ($wtf->user->objectid == $updatorid && $wtf->user->objectid != ANONYMOUSUSERID && isset($small)) {
            if ($small == 'on') {
                echo '<content_smallupdate checked="checked" name="small"/>';
            } else {
                echo '<content_smallupdate name="small"/>';
            }
        }
        if ($comment) {
            echo '<wikipage_comment name="comment" maxlength="', MAXWIKIPAGECOMMENTLENGTH, '"></wikipage_comment>';
        }
        echo '</content_form>';
    }

// get content
    function getContent($newContent = NULL) {
        global $wtf;
        $content = parent::getContent($newContent);

        // remove </wikipage> closing tag appended by parent
        $content = substr($content, 0, 0 - strlen('</wikipage>'));
    
        // add our section list
        $content .= '<sectionlist>';
        if (isset($this)) {
            foreach ($this->objectlist as $obj) {
                $content .= '<sectionlist_item>';
                $url = THINGIDURI.$obj['objectid'].'&amp;class='.$obj['classname'];
                $content .= '<a href="'.$url.'">'.$obj['title'].'</a>';
                $content .= '</sectionlist_item>';
            }
        }
        $content .= '</sectionlist>';

        // re-append the </wikipage> closing tag
        $content .= '</wikipage>';
        return $content;
    }

// delete
	function delete() {
        track('section::delete');
        if ( count($this->objectlist) > 0 ) {
            // loop through objects listed in the section, and remove them from the list
            foreach ( $this->objectlist as $obj_elem ) {
                $obj = wtf::loadObject($obj_elem['objectid'], 0, $obj_elem['classname']);
                if ( $this->removeFromSection($obj) ) {
                    $obj->save(); // save changes to object if successfully removed from session
                } 
            }
        }
        // delete thing
        track();
        return parent::delete();
    }


/*** Methods ****/

// create
    function method_create($thingName = NULL, $objectName = 'section') { 
    // this is both a method and a static member
        global $conn, $wtf;
        track('section::method::create');
        if ($wtf->user->inGroup(SECTIONCREATE)) {
            if (isset($this)) {
                $url = THINGIDURI.$this->objectid.'&class='.get_class($this).'&op=create';
                $objectName = get_class($this);
            } else {
                $url = THINGURI.$thingName.'&amp;class=hardclass';
            }

            $create = getValue('submit', FALSE);
            $preview = getValue('preview', FALSE);

            $title = getValue('title', FALSE);
            $content = getValue('content', FALSE);

            if ($create || $preview ) { 
                $thing = new $objectName(
                    $wtf->user,
                    $title
                );
                if ($thing && $thing->objectid != 0) { // thing created
                    if ( $create ) { // creat thing
                        $result = $thing->update($content, FALSE, FALSE);
                        if ( $result['success'] ) {
                            $thing->save();
                            header('Location: '.THINGIDURI.$thing->objectid.'&class='.get_class($thing));
                            exit;
                        } else {
                            echo '<content_create_failed message="', $result['error'], '"/>';
                            echo '<syntax>', $result['syntaxCheck'], '</syntax>';
                        }
                    } elseif ( $preview ) {
                        $result = $thing->preview($content);
                        if ( !$result['error'] ) {
                            echo '<wikipage_preview>', $result['content'], '</wikipage_preview>';
                        } else {
                            echo '<content_create_failed message="', $result['error'], '"/>';
                            echo '<syntax>', $result['syntaxCheck'], '</syntax>';
                        }
                    }
                    $title = $thing->title;
                    $content = htmlspecialchars($content);
                    section::drawForm($url, $title, TRUE, $content, FALSE, NULL);
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
            $preview = getValue('preview', FALSE);
            $small = getValue('small', FALSE);
            $content = getValue('content', FALSE);
            $comment = getValue('comment', TRUE);
            $loadtime = getValue('loadtime', NULL);
            $act = getValue('act', FALSE);

            if ( $act ) {
                $key =  getValue('key', FALSE);
                $content = $this->content;
                $obj_element = $this->objectlist[$key];

                if ( $key < 0 || $obj_element == NULL ) {
                    echo '<section_edit_failed '.
                         'message="Could not edit section elements, invalid element specified."/>';
                } elseif ( $act == 'remove' ) {
                    $obj = wtf::loadObject($obj_element['objectid'], 0, $obj_element['classname']);
                    if ( $this->removeFromSection($obj) ) {
                        $obj->save(); // save changes if successfully removed from session
                    }                     
                } elseif ( $act == 'up' && $key > 0 ) { 
                    $prev_element = $this->objectlist[$key-1];
                    $this->objectlist[$key-1] = $obj_element;
                    $this->objectlist[$key] = $prev_element;
                    $this->save();
                } elseif ( $act == 'down' && $key < count($this->objectlist) ) {
                    $next_element = $this->objectlist[$key+1];
                    $this->objectlist[$key+1] = $obj_element;
                    $this->objectlist[$key] = $next_element;
                    $this->save();
                }
            } elseif ($update) { // update thing
                // Check if we should increment the version
                $incrementVersion = TRUE;
                if ( $small == 'on' &&
                     $wtf->user->objectid == $this->updatorid &&
                     $wtf->user->objectid != ANONYMOUSUSERID) {
                    $incrementVersion = FALSE;
                }

                // Make sure content isn't locked
                if ( (!isset($loadtime) ||
                      !is_numeric($loadtime) ||
                      $loadtime < dbdate2unixtime($this->updatorDatetime))  &&
                     ($this->updatorid == ANONYMOUSUSERID || 
                      $this->updatorid != $wtf->user->objectid) ) {
                    echo '<section_edit_locked/>';
                } elseif ($content) {
                    $result = $this->update($content, $comment, $incrementVersion);
                    if ( $result['success'] ) {
                        $this->save();              // save to database
                        $this->tidyArchive();       // tidy up archived versions
                        echo '<wikipage_edit_updated message="', $comment, '"/>';
                    } else {
                        echo '<content_edit_failed message="', $result['error'], '"/>';
                        echo '<syntax>', $result['syntaxCheck'], '</syntax>';
                    }
                }
                $content = htmlspecialchars($content);
            } elseif ($preview) {
                if ( $content ) {
                    $result = $this->preview($content);
                    if ( !$result['error'] ) {
                        echo '<wikipage_preview>', $result['content'], '</wikipage_preview>';
                    } else {
                        echo '<content_edit_failed message="', $result['error'], '"/>';
                        echo '<syntax>', $result['syntaxCheck'], '</syntax>';
                    }
                }
                $content = htmlspecialchars($content);
            } else {
                $content = $this->content;
            }
            
            $title = $this->title;
            
            // Draw form, $title is not editable
            $this->drawForm(THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=edit', 
                            $title, FALSE, $content, $comment, $small, $loadtime);
        } else {
            echo '<thing_permissionerror method="edit" title="'.$this->title.'"/>';
        }
        track();
    }

}

// formatting
$FORMAT = array_merge($FORMAT, array(
// view
    'sectionlist' => '<UL>',
    '/sectionlist' => '</UL>',
    'sectionlist_item' => '<LI>',
    '/sectionlist_item' => '</LI>',

// create
    'section_create_updated' => '<h2 class="success">Create Successful</h2><p>Page created successfully.</p>',
    '/section_create_updated' => '',
    'section_create_permission' => '<p class="error">You do not have permission to create a new section.</p>',
    '/section_create_permission' => '',

// edit
    'sectionlist_edit' => '<h3>Section Contents</h3>',
    '/sectionlist_edit' => '<h3>Section Information</h3><p>The following wiki-formatted text will appear above the section list as descriptive/introductory text.',
    'sectionlist_edit_item' =>         '',
    '/sectionlist_edit_item' =>        ' [<a href="{url}&act=remove&key={key}">Remove</a>]<br />'."\n",
    'sectionlist_edit_item_up' =>      '',
    '/sectionlist_edit_item_up' =>     ' [<a href="{url}&act=up&key={key}">Up</a>|'.
                                       '<a href="{url}&act=remove&key={key}">Remove</a>]<br />'."\n",
    'sectionlist_edit_item_down' =>    '',
    '/sectionlist_edit_item_down' =>   ' [<a href="{url}&act=down&key={key}">Down</a>|'.
                                       '<a href="{url}&act=remove&key={key}">Remove</a>]<br />'."\n",
    'sectionlist_edit_item_updown' =>  '',
    '/sectionlist_edit_item_updown' => ' [<a href="{url}&act=up&key={key}">Up</a>|'.
                                         '<a href="{url}&act=down&key={key}">Down</a>|'.
                                         '<a href="{url}&act=remove&key={key}">Remove</a>]<br />'."\n",
    'section_edit_updated' => '<h2 class="success">Edit Successful</h2><p>Section updated successfully.</p>',
    '/section_edit_updated' => '',
    'section_edit_failed' => '<h2 class="fail">Edit Failed</h2><p>{message}</p>',
    '/section_edit_failed' => '',
    'section_edit_locked' => '<p class="error">This page is currently being updated by someone else, please review their changes before making your own.</p>'."\n",
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
