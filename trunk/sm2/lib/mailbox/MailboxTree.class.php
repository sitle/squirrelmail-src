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


    // Constructor
    function mailboxtree($sAccountIdentifier) {
        $this->id = $sAccountIdentifier;
        $this->p_c_rel = array(); /* parent -> child relations */
        $this->nodes = array(); /* array with nodes accessible by the id of the node */
//        $this->listeners = array(&$this->nodes);
        /* create root node */
        $rootnode =& new node(0,array('self'=>SM_ACL_NONE));
        $rootnode->id = 0;
        $rootnode->displayname = $sAccountIdentifier;
        $this->p_c_rel[0] = array();
        $this->nextnodeid = 1;
        // NB: root node should always have SM_NODE_INSERT as mask otherwise it's not
        // possible to add nodes
        $this->nodes[] = array(&$rootnode,SM_NODE_EXPANDED + SM_NODE_VISIBLE + SM_NODE_INSERT);

        // events
        $this->events['beforeDelete'] = array($this,'_beforeDeleteMailbox');
        $this->events['afterDelete']  = array($this,'_afterDeleteMailbox');
        return true;

    }
    /**
     * @func      loadFromStream
     * @desc      Build a tree from provided data
     * @param     str        $format       stream format
     * @param     var        $data         data containing the nodes
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function loadFromStream($format,$data) {
        switch ($format)
        {
          case 'namespace':
            // mailboxes catagorised per namespace. format:
            // array(
            //       'personal'=>array(
            //                      'INBOX'=> array(
            //                                      'flags' => array('\haschildren),
            //                                      'delimiter' => '.'
            //                                     )
            //                         ),
            //       'otherusers' => false
            //      )
            $iMask = SM_NODE_VISIBLE + SM_NODE_INSERT;
            $aProp = array('mask' => $iMask,
                           'enable_acl' => false,
                           'expand_parent' => true);

            foreach ($data as $sName => $aNamespace) { // namespace catagories
                switch ($sName)
                {
                case 'personal':   $nodename = _("Personal Folders"); break;
                case 'otherusers': $nodename = _("Other Users");      break;
                case 'shared':     $nodename = _("Shared Folders");   break;
                default:           $nodename = $sName;                break;
                }
                $node =& new node($sName, SM_ACL_LOOKUP);
                $node->displayname = $nodename;
                $succes = $this->addNode($node,$this->nodes[0][0],$aProp);
                if (!$succes) {
                   // ERROR handling
                   // FIX ME
                   echo "ERROR ADDING $nodename<BR>ERRORCODE {$this->error}<BR>";
                } else {
                    foreach ($aNamespace as $sPrefix => $aMailboxes) { // actual namespace
                        if (is_array($aMailboxes)) {
                            ksort($aMailboxes,SORT_STRING);
                            foreach ($aMailboxes as $sMbxName => $aProp) {
                                if ($aProp['delimiter'] == substr($sMbxName,-1)) {
                                    $sMbxName = substr($sMbxName,0,-1);
                                }
                                /* retrieve parent */
                                $iPos = strrpos($sMbxName,$aProp['delimiter']);
                                if ($iPos && $aProp['delimiter']) {
                                    $sParentMbxName = substr($sMbxName,0,$iPos);
                                    $sDisplayName = substr($sMbxName,$iPos+1);
                                } else {
                                    $sParentMbxName = $sName;
                                    $sDisplayName = $sMbxName;
                                }
//                                print_r($this->nodes);
                                $oParentNode = $this->nodes[$sParentMbxName][0];
                                $oNode =& new node($sMbxName);
                                $oNode->displayname = $sDisplayName;
                                $iMask = SM_NODE_VISIBLE + SM_NODE_EXCECUTE + SM_NODE_INSERT;
                                foreach($aProp['flags'] as $flag) {
                                    switch ($flag)
                                    {
                                      case '\\noselect':
                                        $oNode->is_noselect = true;
                                        $iMask -= SM_NODE_EXCECUTE;
                                        break;
                                      case '\\noinferiors':
                                        $oNode->noinferiors = true;
                                        $iMask -= SM_NODE_INSERT;
                                        break;
                                      case '\\marked': $oNode->is_marked = true; break;
                                      case '\\haschildren': $oNode->haschildren = true; break;
                                    }
                                }
                                $aProp = array('mask' => $iMask,
                                           'enable_acl' => false,
                                           'expand_parent' => true);
                                $success = $this->addNode($oNode,$oParentNode,$aProp);
                                if (!$success) {
                                    // ERROR
                                    echo "ERROR ADDING $sDisplayName<BR>ERRORCODE{$this->error}<BR>";
                                }
                            } //foreach
                        } //if
                    } //foreach
                } //if
            } // foreach
            break;
          default: break;
        }
    }

    /* aliases */
    function deleteMailbox($oParentMbx,$sName) {
        //$this->deleteNode
    }

    function createMailbox($oParentMbx,$sName) {
    }

    // events
    function _beforeDeleteMailbox() {
    // move to trash    
        
    }
    
    function _afterDeleteMailbox() {
    
    // persistor remove related mailbox data
    }

}

define ('SM_MBX_UNSEEN', 0);
define ('SM_MBX_RECENT', 1);
define ('SM_MBX_EXISTS', 2);
define ('SM_MBX_VIEW_FLAT',0);
define ('SM_MBX_VIEW_THREAD',1);

class mailbox extends node {
    var 
        $status = array(false,false,false),
        $uidnext = 0,
        $uidvalidity = 0,
        $sortfield = false,
        $sort_direction = 0,
        $msgs_list,
        $is_marked = false,
        $is_noselect = false,
        $haschildren = false ,
        $noinferiors = false,
        $delimiter,
        $acl,
        $view = SM_VIEW_FLAT;
    
    function loadFromStream($format,$data) {
    }    
                
}



?>
