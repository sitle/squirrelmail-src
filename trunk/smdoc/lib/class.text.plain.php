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
class.text.plain.php
Foowd plain text class
*/

/* Method permissions */
setPermission('foowd_text_plain', 'object', 'edit', 'Editors');

/* Class descriptor */
setClassMeta('foowd_text_plain', 'Plain Text Document');

/* Class settings */
setConst('DIFF_COMMAND', 'diff -u5');
setConst('DIFF_ADD_REGEX', '/^\+([^+].*)/');
setConst('DIFF_MINUS_REGEX', '/^\-([^-].*)/');
setConst('DIFF_SAME_REGEX', '/^ (.*)/');

/**
 * Plain text object class.
 *
 * This class defines a plain text area and methods to view and edit that area.
 *
 * @author Paul James
 * @package Foowd
 */
class foowd_text_plain extends foowd_object {

  /**
   * The text body.
   *
   * @var str
   */
  var $body;
  
  /**
   * Constructs a new plain text object.
   *
   * @param object foowd The foowd environment object.
   * @param str title The objects title.
   * @param str body The text content body.
   * @param str viewGroup The user group for viewing the object.
   * @param str adminGroup The user group for administrating the object.
   * @param str deleteGroup The user group for deleting the object.
   * @param str editGroup The user group for editing the object.
   */
  function foowd_text_plain(
    &$foowd,
    $title = NULL,
    $body = NULL,
    $viewGroup = NULL,
    $adminGroup = NULL,
    $deleteGroup = NULL,
    $editGroup = NULL
  ) {
    $foowd->track('foowd_text_plain->constructor');
  
// base object constructor
    parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

/* set object vars */
    $this->set('body', $body);

/* set method permissions */
    if ($editGroup != NULL) $this->permissions['edit'] = $editGroup;

    $foowd->track();
  }

  /**
   * Serliaisation wakeup method.
   *
   * Re-create Foowd meta arrays not stored when object was serialized.
   */
  function __wakeup() {
    parent::__wakeup();
    $this->foowd_vars_meta['body'] = '';
  }

  /**
   * Get object content.
   *
   * @return str The objects text contents processed for outputting.
   */
  function view() {
    return $this->processContent($this->body);
  }

  /**
   * Process text content. Converts special chars into HTML entities and
   * replaces new lines with BR tags.
   *
   * @param str content The text to process.
   * @return str The processed content.
   */
  function processContent($content) {
    $content = htmlspecialchars($content);
    $content = str_replace("\n", "<br />\n", $content);
    return $content;
  }

  /**
   * Update the text content.
   *
   * @param str body The new string to set the content to.
   * @param bool newVersion Create a new version of the object.
   * @param int collisionTime The time the edit form was created.
   * @return mixed FALSE = failure<br />
   *               1 = updated ok<br />
   *               2 = edit collision<br />
   */
  function edit($body, $newVersion = TRUE, $collisionTime = 0) 
  {
    if ($collisionTime >= $this->updated)  // has not been changed since form was loaded
    {
      $this->set('body', $body);
      if ( $newVersion ) 
        $this->newVersion();
      return 1;
    } 
    else 
      return 2; // edit collision!

    return FALSE;
  }

  /**
   * Generate differences between this version and a previous version.
   *
   * @param array diffResultArray Results of difference engine.
   * @return int -1 = version is latest version, can not compare to self
   *             -2 = diffs disabled
   *             -3 = versions are the same
   *             -4 = other error
   *             +n = generation successful, returns the version number of the previous version
   */
  function diff(&$diffResultArray) {
    if (defined('DIFF_COMMAND')) {
      $object = $this->foowd->getObj(array('objectid' => $this->objectid, 'classid' => $this->classid, 'workspaceid' => $this->workspaceid));
      if ($this->version == $object->version) {
        return -1; // version is latest version, can not compare to self
      } else {

        $fileid = time();
        
        $temp_dir = getConstOrDefault('DIFF_TMPDIR', getTempDir());
        
        $oldFile = $temp_dir.'foowd_diff_'.$fileid.'-1';
        $newFile = $temp_dir.'foowd_diff_'.$fileid.'-2';

        $oldPage = $this->body;
        $newPage = $object->body;

        ignore_user_abort(TRUE); // don't halt if aborted during diff

        if (!($fp1 = fopen($oldFile, 'w')) || !($fp2 = fopen($newFile, 'w'))) {
          trigger_error('Could not create temp files in "'.$temp_dir.'" required for diff engine.');
          $returnValue = -4; // other error
        } else {

          if (fwrite($fp1, $oldPage) < 0 || fwrite($fp2, $newPage) < 0) {
            trigger_error('Could not write to temp files in "'.$temp_dir.'" required for diff engine.');
            $returnValue = -4; // other error
          } else {

            fclose($fp1);
            fclose($fp2);

            $this->foowd->track('executing external diff engine', '"'.DIFF_COMMAND.'"');
            $diffResult = shell_exec(DIFF_COMMAND.' '.$oldFile.' '.$newFile);
            $this->foowd->track();

            if ($diffResult === FALSE) {
              trigger_error('Error occured running diff engine "', DIFF_COMMAND, '".');
              $returnValue = -4; // other error
            } elseif ($diffResult == FALSE) {
              $returnValue = -3; // versions are the same
            } else { // parse output to be nice
              $diffResultArray = explode("\n", $diffResult);
              $returnValue = $object->version;
            }
          }

          unlink($oldFile);
          unlink($newFile);
        }

        ignore_user_abort(FALSE); // all done, it's ok to abort now
        
        return $returnValue;
      }
    } else {
      return -2; // diffs disabled
    }
  }

/* Class methods */

  /**
   * Output an object creation form and process its input.
   *
   * @static
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_create(&$foowd, $className) {
    $foowd->track('foowd_text_plain->class_create');
    
    include_once(INPUT_DIR.'input.querystring.php');
    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.textbox.php');
    include_once(INPUT_DIR.'input.textarea.php');
  
    $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
    $createForm = new input_form('createForm', NULL, 'POST', _("Create"), NULL);
    $createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, _("Object Title").':');
    $createBody = new input_textarea('createBody', '', NULL, NULL, 80, 20);
    if ($createForm->submitted() && $createTitle->value != '') {
      $object = &new $className(
        $foowd,
        $createTitle->value,
        $createBody->value
      );
      if ($object->objectid != 0) {
        $foowd->template->assign('success', TRUE);
        $foowd->template->assign('objectid', $object->objectid);
        $foowd->template->assign('classid', $object->classid);
      } else {
        $foowd->template->assign('success', FALSE);
      }
    } else {
      $createForm->addObject($createTitle);
      $createForm->addObject($createBody);
      $foowd->template->assign_by_ref('form', $createForm);
    }

    $foowd->track();
  }

/* Object methods */

  /**
   * Output an edit form and process its input
   */
  function method_edit() 
  {
    $this->foowd->track('foowd_text_plain->method_edit');

    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.textbox.php');
    include_once(INPUT_DIR.'input.textarea.php');
    include_once(INPUT_DIR.'input.checkbox.php');

    $editForm = new input_form('editForm', NULL, 'POST', 
                               FORM_DEFAULT_SUBMIT, NULL, FORM_DEFAULT_PREVIEW);

    $editCollision = new input_hiddenbox('editCollision', REGEX_DATETIME, time());
    $editForm->addObject($editCollision);

    $editArea = new input_textarea('editArea', NULL, $this->body, NULL);
    $editForm->addObject($editArea);

    // If author is same as last author and not anonymous, 
    // ask if they want to make a new version, or just save changes to existing version
    $noNewVersion = new input_checkbox('noNewVersion', TRUE, _("Save this as the previous version?"));
    if ( isset($this->foowd->user->objectid) &&  $this->updatorid == $this->foowd->user->objectid ) 
      $editForm->addObject($noNewVersion);
    
    $this->foowd->template->assign_by_ref('form', $editForm);

    if ($editForm->submitted()) 
    {
      // Edit will increment version if requested ($newVersion->checked),
      // And will store revised body in the object if no edit collision
      $result = $this->edit($editArea->value, !$noNewVersion->checked, $editCollision->value);

      switch ($result) 
      {
        case 1:
          $url = getURI(array('classid' => $this->classid,
                              'objectid' => $this->objectid,
                              'ok' => OBJECT_UPDATE_OK), FALSE);
          $this->save();
          header('Location: ' . $url);
          break;
        case 2:
          $this->foowd->template->assign('failure', OBJECT_UPDATE_COLLISION);
          break;
        default:
          $this->foowd->template->assign('failure', OBJECT_UPDATE_FAILED);
          break;
      }
    } 
    elseif ( $editForm->previewed() ) 
      $this->foowd->template->assign('preview', $this->processContent($editArea->value));

    $this->foowd->track();
  }


  /**
   * Output the objects history.
   *
   * We override <code>{@link foowd_object::method_history}</code> so as to add links to the diff method.
   */
  function method_history() {
    $this->foowd->track('foowd_text_plain->method_history');

    $this->foowd->template->assign('detailsTitle', $this->getTitle());
    $this->foowd->template->assign('detailsCreated', date(DATETIME_FORMAT, $this->created).' ('.timeSince($this->created).' ago)');
    $this->foowd->template->assign('detailsAuthor', htmlspecialchars($this->creatorName));
    $this->foowd->template->assign('detailsType', getClassDescription($this->classid));
    if ($this->workspaceid != 0) {
      $this->foowd->template->assign('detailsWorkspace', $this->workspaceid);
    }
    
    $foo = FALSE;
    $objArray = $this->foowd->getObjHistory(array('objectid' => $this->objectid, 'classid' => $this->classid));
    unset($objArray[0]);
    $versions = array();
    foreach ($objArray as $object) {
      $version['updated'] = date(DATETIME_FORMAT, $object->updated).' ('.timeSince($object->updated).' ago)';
      $version['author'] = htmlspecialchars($object->updatorName);
      $version['version'] = $object->version;
      $version['objectid'] = $object->objectid;
      $version['classid'] = $object->classid;
      if ($foo) {
        $version['revert'] = TRUE;
        $version['diff'] = TRUE;
      }
      $foo = TRUE;
      $this->foowd->template->append('versions', $version);
    }

    $this->foowd->track();
  }

  /**
   * Output the generated diff.
   */
  function method_diff() {
    $this->foowd->track('foowd_text_plain->method_diff');

    $diffResultArray = NULL;
    $result = $this->diff($diffResultArray);
    switch($result) {
    case -1:
      $this->foowd->template->assign('success', FALSE);
      $this->foowd->template->assign('error', 1);
      break;
    case -2:
      $this->foowd->template->assign('success', FALSE);
      $this->foowd->template->assign('error', 2);
      break;
    case -3:
      $this->foowd->template->assign('success', FALSE);
      $this->foowd->template->assign('error', 3);
      break;
    default:
      $this->foowd->template->assign('success', TRUE);
      $this->foowd->template->assign('version1', $this->version);
      $this->foowd->template->assign('version2', $result);
      $diffAddRegex = getConstOrDefault('DIFF_ADD_REGEX', '/^>(.*)$/');
      $diffMinusRegex = getConstOrDefault('DIFF_MINUS_REGEX', '/^<(.*)$/');
      $diffSameRegex = getConstOrDefault('DIFF_SAME_REGEX', '/^ (.*)$/');
      foreach($diffResultArray as $diffLine) {
        $diff = array();
        if (preg_match($diffAddRegex, $diffLine, $lineResult)) {
          $diff['add'] = htmlspecialchars($lineResult[1]);
        } elseif (preg_match($diffMinusRegex, $diffLine, $lineResult)) {
          $diff['minus'] = htmlspecialchars($lineResult[1]);
        } elseif (preg_match($diffSameRegex, $diffLine, $lineResult)) {
          $diff['same'] = htmlspecialchars($lineResult[1]);
        }
        $this->foowd->template->append('diff', $diff);
      }
      break;
    }

    $this->foowd->track();
  }

}

?>
