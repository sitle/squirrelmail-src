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
        $this->id = $id;
        $this->p_c_rel = array(); /* parent -> child relations */
        $this->nodes = array(); /* array with nodes accessible by the id of the node */
//        $this->listeners = array(&$this->nodes);
        /* create root node */
        $rootnode =& new node(0,array('self'=>SM_ACL_NONE));
        $rootnode->id = 0;
        $rootnode->displayname = $sAccountIdentifier;
        $this->p_c_rel[0] = array();
        $this->nextnodeid = 1;
        $this->nodes[] =& $rootnode;

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
                $this->addNode($node);
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
                                echo $aProp['delimiter'].'??';
                                $sParentMbxName = substr($sMbxName,0,$iPos);
                                $sDisplayName = substr($sMbxName,$iPos+1);
                            } else {
                                $sParentMbxName = $sName;
                                $sDisplayName = $sMbxName;
                            }
                            $oParentNode = $this->nodes[$sParentMbxName];
                            $oNode =& new node($sMbxName);
                            $oNode->displayname = $sDisplayName;
                            foreach($aProp['flags'] as $flag) {
                               switch ($flag)
                               {
                                 case '\\noselect': $oNode->is_noselect = true; break;
                                 case '\\noinferiors': $oNode->noinferiors = true; break;
                                 case '\\marked': $oNode->is_marked = true; break;
                                 case '\\haschildren': $oNode->haschildren = true; break;
                               }
                            }      
                            $this->addNode($oNode,$oParentNode);
                        }
                    }
                }
            }
            break;
        default: break;
        }
        print_r($this);
        $res = array();
        $this->getNodeArray($res,false,true,true,false,false,false,false);
//$node = false, $incl_collapsed = false, $incl_invisible = true, $sort = false, $sortmethod = false, $reverse = false,
//                           $maxdepth=0, $_depth = 0, $_visble = true, $_parent = 0) {        
        //print_r($res);
        foreach ($res as $k => $v) {
           echo str_repeat('__',$v['DEPTH']),$v[0]->displayname,'<BR>';
           //print_r($v);
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
