<?php
/**
 * MailboxTree.class.php
 *
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Base extended tree class.
 *
 * $Id$
 */

class mailboxtree extends tree {

    /**
     * @desc      Constructor
     * @param     string        $sName        Identifier
     * @param     array         $aProps       Properties
     * @param     imap_backend  $creator      Object to access the required backend functions
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function mailboxtree($sName, $aProps, $creator) {
        $this->tree($sName,$aProps, $creator);
        $this->events['onExpand'] = array($this,'_onExpand');
    }


    /**
     * @desc      Build a mailboxtree
     * @param     array      $aNameSpace   Namespace array
     * @param     array      $aExpand      mailboxes in expanded view array(mailbox => delimiter, ..,)
     * @param     array      $aHide        mailboxes hidden from hierarchy
     * @param     array      $aProps       Properties
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */


    function init($aNameSpace, $aExpand=array(), $aHide=array(), $aProps=array()) {
        $aPropsDefault = array( 'showlabel' => true,
                                'hideprefix' =>true,
                                'expand'    => false,
                                'type' => 'LSUB'
                                );
        $aProps = array_merge($aPropsDefault,$aProps);
        $this->props = $aProps;
        $type = $aProps['type'];
        $bPersonal = false;
        $aCommandQueue = array();
        // userspace labels


        $i = 0;
        //print_r($aNameSpace);
        foreach ($aNameSpace as $sName => $aNamespaceEntry) { // namespace catagories
            if ($aNamespaceEntry) {
                $iPermissions = SM_NODE_VISIBLE + SM_NODE_INSERT;
                $aNodeProp = array( 'permissions' => $iPermissions,
                                    'enable_acl' => false,
                                    'expand_parent' => true);
                $aTempExist[$sName] = false;
                switch ($sName)
                {
                case 'personal':
                    $nodename = _("Personal Folders");
                    // retrieve INBOX separately
                    $aCommandQueue[$i] = array('','INBOX',false,SM_MBXLIST_MBX,'LIST');
                    $aResultMap[$i]= array($sName,'INBOX',false);
                    ++$i;
                    $aTempExist['personal'] = true;
                    $aTemp['INBOX'] = array('personal',false);
                    break;
                case 'otherusers': $nodename = _("Other Users");      break;
                case 'shared':     $nodename = _("Shared Folders");   break;
                default:           $nodename = $sName;                break;
                }
                $oNode =& new label($sName,0,$nodename);
                if (!$aProps['showlabel']) {
                    $aNodeProp['permissions'] += SM_NODE_HIDE_FROM_HIERARCHY;
                }
                // add label to root node
                $succes = $this->addNode($oNode,$this->nodes[0][0],$aNodeProp);
                $sParentMbxName = $sName;
                if (!$succes) {
                    // ERROR handling
                    // FIX ME
                    echo "ERROR ADDING $nodename<BR>ERRORCODE {$this->error}<BR>";
                } else {
                    $iExpandtype = ($aProps['expand']) ? SM_MBXLIST_ALL : SM_MBXLIST_SUB;
                    foreach ($aNamespaceEntry as $aNameSpaceData) { // actual namespace
                        // retrieve subfolders of each namespace
                        $aCommandQueue[$i] = array($aNameSpaceData['namespace'],'',$aNameSpaceData['delimiter'],$iExpandtype,$type);
                        // temp array to combine the results
                        $aResultMap[$i]= array($sName,$aNameSpaceData['namespace'],$aNameSpaceData['delimiter']);
                        $aTemp[$aNameSpaceData['namespace']] = array($sName,$aNameSpaceData['delimiter']);
                        ++$i;
                    }
                }
            }
        }

        if (count($aCommandQueue)) {
            $aMailboxes = $this->creator->getMailboxList($aCommandQueue,$aProps);
            $aCommandQueue = array();
            foreach ($aMailboxes as $indx => $aList) {
                // UW adds INBOX to almost every LIST output
                if (isset($aList['INBOX']) && $aResultMap[$indx][1] != 'INBOX') {
                    unset($aList['INBOX']);
                }
                if (count($aList)) { //namespace contains folders
                    // namespace category (pers, user, others has folders
                    $sRef = $aResultMap[$indx][1];
                    $sDel = $aResultMap[$indx][2];
                    $aTempExist[$aTemp[$sRef][0]] = true;
                    // detect if $sRef is returned in results
                    if ($sRef && substr($sRef,-1) == $sDel) {
                        $sRef = substr($sRef,0,-1);
                    }
                    if (!isset($this->nodes[$sRef])) {
                        if (isset($aList[$sRef])) {
                            $aNodePropRef = $aList[$sRef];
                        } else {
                            $iPermissionsRef = SM_NODE_VISIBLE + SM_NODE_INSERT;
                            $aNodePropRef = array('pemissions' => $iPermissionsRef,
                                              'enable_acl' => false,
                                              'expand_parent' => true,
                                              'flags' => array('\\noselect'),
                                              'delimiter' => $sDel);
                        }
                        // namespace not in results, add it
                        $this->_addMailbox($sRef,$aResultMap[$indx][0],$aNodePropRef);//$aNodePropRef);
                    }

                    ksort($aMailboxes,SORT_STRING);
                    foreach($aList as $sMbxName => $aMbxProp) {
                        $oMbx = $this->_addMailbox($sMbxName,$sRef,$aMbxProp);
                        if ($oMbx && $aProps['expand'] && ((isset($oMbx->mbxflags['haschildren']) &&
                               $oMbx->mbxflags['haschildren']) ||
                               (!isset($oMbx->mbxflags['haschildren']) && $aProps['haschildren'] === false) ) ) {
                            $aCommandQueue[] = array('',$oMbx->id . $oMbx->delimiter,$oMbx->delimiter,SM_MBXLIST_ALL,$type);
                        } //if
                    } //foreach
                } //if
            } // foreach
        } // if
        // retrieve the expanded mailboxes
        if (!$aProps['expand'] && count($aExpand)) {
            $i = 0;
            foreach($aExpand as $sMbx => $sDel) {
                $aCommandQueue[$i] = array($sMbx. $sDel,'',$sDel,SM_MBXLIST_SUB,$type);
                ++$i;
            }
        }
        // retrieve the rest of the mailboxes (full expand)
        if (count($aCommandQueue)) {
            $aMailboxes = $this->creator->getMailboxList($aCommandQueue,$aProps);
            $aCommandQueue = array();
            foreach ($aMailboxes as $sRef => $aList) {
                // UW adds INBOX to almost every LIST output
                if (isset($aList['INBOX']) && $sRef != 'INBOX') {
                    unset($aList['INBOX']);
                }
                if (isset($aList[$sRef])) {
                    unset($aList[$sRef]);
                }
                if (count($aList)) { // mailbox contains subfolders
                    ksort($aMailboxes,SORT_STRING);
                    foreach($aList as $sMbxName => $aMbxProp) {
                        $this->_addMailbox($sMbxName,$sRef,$aMbxProp);
                    } //foreach
                } //if
            } // foreach
        } // if
        // finally hide the mailboxes from hierarchy
        foreach($aHide as $sMbx) {
            $vPerm =& $this->nodes[$sMbx][1];
            $this->alterPermission($vPerm,SM_NODE_HIDE_FROM_HIERARCHY,false,false,true);
        }

        return true;
    }
    /**
     * @desc      Add a Mailbox to the tree object
     * @param     string     $sMbxName        Mailbox identifier
     * @param     string     $sParentMbxName  Parent mailbox identifier
     * @param     array      $aProp           Properties
     * @return    mailbox    $oNode           Created mailbox object
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function _addMailbox($sMbxName,$sParentMbxName,$aProp) {
        $sDelimiter = $aProp['delimiter'];
        if ($sDelimiter == substr($sMbxName,-1)) {
            $sMbxName = substr($sMbxName,0,-1);
        }
        /* retrieve parent */
        if ($sDelimiter) {
            $aMbx = explode($sDelimiter,$sMbxName);
            array_pop($aMbx);
            $aStack = array();
            // check if parents are already added to the mailboxtree
            while (count($aMbx)) {
                $sParentMbxName = implode($sDelimiter,$aMbx);
                if (!isset($this->nodes[$sParentMbxName])) {
                    array_unshift($aStack,$sParentMbxName);
                } else {
                    $sParParMbxName = $sParentMbxName;
                    break;
                }
                $sParParMbxName = $sParentMbxName;
                array_pop($aMbx);
            }
            // add the non existent parent nodes
            if (count($aStack)) {
                $iPerm =SM_NODE_ALL - SM_NODE_EXCECUTE - SM_NODE_DELETE - SM_NODE_HIDE_FROM_HIERARCHY;
                $aProps = array('permissions' => $iPerm,
                            'expand_parent' => true,
                            'haschildren' => true);
                foreach ($aStack as $sParentMbxName) {
                    $oParentNode =& new mailbox($sParentMbxName,$sParParMbxName);
                    // imap_utf7_decode should be added
                    $sDisplayName = $sParentMbxName;
                    if ($sDelimiter) {
                        $i = strrpos($sParentMbxName,$sDelimiter);
                        if ($i !== false) {
                            $sDisplayName = substr($sParentMbxName,++$i);
                        }
                    }
                    $oParentNode->label = $sDisplayName; // utf7 decode !!
                    $oParentNode->encoding = 'imap-utf7';
                    $oParentNode->mbxflags['noselect'] = true;
                    $oParentNode->mbxflags['haschildren'] = true;
                    $oParParNode =& $this->nodes[$sParParMbxName][0];
                    $oParentNode->delimiter = $sDelimiter;
                    $this->addNode($oParentNode,$oParParNode,$aProps);
                    $sParParMbxName = $sParentMbxName;
                }
            }
        }

        // @todo imap_utf7_decode should be added
        $sDisplayName = $sMbxName;
        if ($sDelimiter) {
            $i = strrpos($sMbxName,$sDelimiter);
            if ($i !== false) {
                $sDisplayName = substr($sMbxName,++$i);
            }
        }

        $oParentNode =& $this->nodes[$sParentMbxName][0];
        $oNode =& new mailbox($sMbxName,$sParentMbxName);
        $oNode->label = $sDisplayName;
        $oNode->encoding = 'imap-utf7';
        $iPerm = SM_NODE_ALL - SM_NODE_EXPANDED - SM_NODE_HIDE_FROM_HIERARCHY;
        $bHasChildren = false;

        foreach($aProp['flags'] as $flag) {
            switch ($flag)
            {
            case '\\noselect':
                $oNode->mbxflags['noselect'] = true;
                $iPerm -= SM_NODE_EXCECUTE;
                break;
            case '\\noinferiors':
                $oNode->mbxflags['noinferiors'] = true;
                $iPerm -= SM_NODE_INSERT;
                break;
            case '\\marked': $oNode->mbxflags['marked'] = true; break;
            case '\\haschildren':
                $oNode->mbxflags['haschildren'] = true;
                $bHasChildren = true;
                break;
            }
        }
        // \NoInferiors implicates \NoChildren
        if ($oNode->mbxflags['noinferiors']) {
            $oNode->mbxflags['haschildren'] = false;
        }

        $aProp = array('permissions' => $iPerm,
                       'expand_parent' => true,
                       'haschildren' => $bHasChildren);
        $oNode->delimiter = $sDelimiter;
        $success = $this->addNode($oNode,$oParentNode,$aProp);
        if (!$success) {
            // ERROR
            return false;
            //echo "ERROR ADDING $sDisplayName<BR>ERRORCODE{$this->error}<BR>";
        }
        return $oNode;
    }

    function _onExpand($id,$sUid,$aGid) {
        if (!isset($this->p_c_rel[$id]) || !count($this->p_c_rel[$id])) {
            $oMbx = $this->nodes[$id][0];
            $aList = array();
            $aList[] = array($id.$oMbx->delimiter,'',$oMbx->delimiter,SM_MBXLIST_SUB,'LIST');
            $aMailboxes = $this->creator->getMailboxList($aList,array(),array(),$this->props);
            foreach ($aMailboxes as $sRef => $aList) {
                // UW adds INBOX to almost every LIST output
                if (isset($aList['INBOX']) && $sRef != 'INBOX') {
                    unset($aList['INBOX']);
                }
                if (isset($aList[$sRef])) {
                    unset($aList[$sRef]);
                }
                if (count($aList)) { // mailbox contains subfolders
                    ksort($aMailboxes,SORT_STRING);
                    foreach($aList as $sMbxName => $aMbxProp) {
                        $res = $this->_addMailbox($sMbxName,$sRef,$aMbxProp);
                    } //foreach
                } //if
            } // foreach
        }
    }

    /* aliases */
    function deleteMailbox($oParentMbx,$sName) {
        //$this->deleteNode
    }

    function createMailbox($oParentMbx,$sName) {
        //$this->addNode
    }

    // events
    function _beforeDeleteMailbox() {
    // move to trash

    }

    function _afterDeleteMailbox() {

    // persistor remove related mailbox data
    }

}

define ('SM_MBX_LIST',0);
define ('SM_MBX_FLAT',1);   // preview each message. size of preview is size of defined buffer
define ('SM_MBX_THREAD',2);
define ('SM_MBX_NESTED',3); // preview each thread message. size of preview is size of defined buffer


define ('SM_MBX_ASC', 0);
define ('SM_MBX_DEC', 1);

class mailbox extends node {
    var
        $id,
        $label,
        $delimiter,
        $status = array(
                        'UIDVALIDITY'    => false,
                        'UIDNEXT'        => false,
                        'EXISTS'         => false,
                        'UNSEEN'         => false,
                        'RECENT'         => false,
                        'FLAGS'          => false,
                        'PERMANENTFLAGS' => false,
                        'READONLY'       => false
                       ),
        $mbxflags = array(
                        'marked'         => false,
                        'noselect'       => false,
                        'haschildren'    => false,
                        'noinferiors'    => false
                       ),
        $sort   = array(
                        'direction'      => SM_MBX_ASC,
                        'field'          => false,
                        'uidlst'         => array() // 2 dimensions, field => uid list
                       ),
        $filter = array(
                        'filter_id'      => -1, // -1 = not filtered
                        'filters'        => array()
                       ),
        $view   = array(
                        'mode'           => SM_MBX_LIST,
                        'number'         => 15,
                        'headers'        => array(), // column headers
                        'order'          => array(), // order of headers
                        'buffer'         => 0  // numer of octets to preview
                       ),
        $cache  = array(
                        'uidlst'         => array(), // array with uid's accessible by non uid sequence number (for unsollicited responses)
                        'flags'          => array(),
                        'headers'        => array() // array(UID => array(header, flags), UID2 => array()) number of fields in array should match $view['headers']
                       ),
        $options = array(
                        'movetotrash'    => false,
                        'autoexpunge'    => false
                        );

    function loadFromStream($format,$data) {
    }

}

class mailboxcontainer extends node {

}


?>
