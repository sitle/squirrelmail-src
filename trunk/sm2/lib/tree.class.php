<?php

/**
 * tree.class.php
 *
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Base tree class.
 *
 * $Id$
 */


// node properties definitions
define('SM_NODE_VISIBLE',1);   // for external usage
define('SM_NODE_EXPANDED',2);
define('SM_NODE_INSERT',4);
define('SM_NODE_DELETE',8);
define('SM_NODE_EXCECUTE',16); // for external usage
define('SM_NODE_ALL',31);

/* sort constants */
define('SM_SORT_DEFAULT',0); // normal sort
define('SM_SORT_NAT',1);     // natural sort
define('SM_SORT_NAT_CASE',2);// natural case sensitive sort
define('SM_SORT_CASE',3);    // case sensitive sort
define('SM_SORT_NUMERIC',4); // numeric sort
define('SM_SORT_STRING',5);  // string sort

/* error codes */
define('SM_NODE_ACCESS_DENIED',1);


class tree extends object{
    var $name,
        $expanded = array(),
        $nodes = array(false),
        $permissions,
//        $listen = array(),
        /* events */
    /**
     * @func      tree
     * @desc      constructor
     * @param     str        $id           unique identifier
     * @param     arr        $aProps       tree properties
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
     function tree($id,$aProps=array()) {
        // defaults properties:
        $aPropsDefault = array ('security'     => false,
                          'enable_acl'   => false,
                          'permissions'  => SM_NODE_ALL, // default, non acl
                          'acl'          => false,
                          'inherit_acl'  => true,
                          'hiderootnode' => true,
                          'events'       => array(),
                          'uid'          => false,
                          'gid'          => false,
                          'renderEngine' => false   // callback function
                          )
        // merge default props with provided props
        $aProps = array_merge($aPropsDefault,$aProps);

        foreach ($aProps as $key = > $value) {
            $aPropsDefault[$key] = $value;
        }
        $this->id = $id;
        $this->p_c_rel = array(); // parent -> child relations
        $this->nodes = array();   // array with nodes accessible by the id of the node
        // use acl instead of default permissions
        if ($aProps['acl']) {
            $this->enable_acl = true;
            $this->permissions = $aProps['acl'];
        } else {
            $this->permissions = $aProps['permissions'];
            $this->enable_acl = false;
        }
        // in case of acl we nee uid and/or gid
        $this->uid = false;
        $this->gid = false;

//        $this->listeners = array(&$this->nodes);
        // create root node
        $rootnode =& new node(0,$this->permissions);
        $rootnode->id = 0;
        $this->p_c_rel[0] = array();
        $this->nextnodeid = 1;
        $this->nodes[] =& $rootnode;
        /* set the sleep notifyer */
        //$this->listen['sleep'] = array(&$this, '_sleep');

        // register the renderEngine
        $this->renderEngine = $aProps['renderEngine'];

        // register the events
        $aEventsDefault = array (
                            'beforeDelete' => false,
                            'afterDelete' => false,
                            'beforeMove' => false,
                            'afterMove' => false,
                            'beforeAdd' => false,
                            'afterAdd' => false
                        );
        // merge default props with provided props
        $this->events = array_merge($aEventsDefault,$aProps['events']);

        return true;
    }

    /**
     * @func      __sleep
     * @desc      see the php manual
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function __sleep() {
        $this->notify('sleep','',true);
    }

//    function _sleep() {
//    }



    /**
     * @func      render
     * @desc      render the tree by external callback function
     * @access    public
     * @return    rendered tree
     * @author    Marc Groot Koerkamp
     */

    // sort nodes, is it external or not ?

    function render() {
        if (is_callable($this->renderEngine)) {
            return $this->renderEngine($this->nodes, $this->p_c_rel, array($this,'sortNodes'));
        }
        return false;
    }

    /**
     * @func      addNode
     * @desc      Add a node to parent node
     * @param     obj        $node         The node object
     * @param     obj        $parent       parent node
     * @param     arr        $aProps       optional node properties
     * @return    bool                     success
     * @access     public
     * @author     Marc Groot Koerkamp
     */

    function addNode(&$oNode, $parent=false,$aProps = array()) {
        $adefaultProps=array('permissions'      => false, // acl or simple permissions, dependent of inialisation of the tree object
                             'expand_parent'    => true,
                             'sUid'              => false,  // in case of acl we need it to check perm
                             'aGid'              => array());  // in case of acl we need it to check perm
        // merge default props with provided props
        $aProps = array_merge($aPropsDefault,$aProps);

        if (!$parent) {
            $parent = $this->nodes[0][0];
        }
        $sUid = $aProps['sUid'];
        $aGid = $aProps['aGid'];
        // Parentperm is a variant, an array in case of acl, an int in case of simple perm.
        $vParentPerm =& $this->nodes[$parent->id][1];
        if ($this->_checkPermissions($vParentPerm,SM_NODE_INSERT,$sUid,$aGid)) {
            // setting default permissions
            if ($aProps['permissions'] === false) {
                $aProps['permissions'] = $this->permissions;
            }
        } else {
            //ERROR
            $this->error = SM_NODE_ACCESS_DENIED;
            return false;
        }

        if (isset($oNode->id) && $oNode->id) {
            // check unique
            if (isset($this->nodes[$oNode->id])) {
                $this->error = SMNODE_ID_NOT_UNIQUE;
                return false;
            }
            $id = $oNode->id;
        } else {
            $id = $this->nextnodeid;
            $oNode->id = $id;
        }
        ++$this->nextnodeid;
        $this->nodes[$id] = array(&$oNode,$aProps['permissions']);
        // assign the parent id to the node in order to speed up dependency checks
        $node->parent_id = $parent->id;
        // add the node_id to the p_c_rel array
        $this->p_c_rel[$parent->id][] = $id;
        // if required, expand the parent node
        $this->_alterPermissions(&$vParentPerm,SM_NODE_EXPANDED,
                 $sUid,$aGid,$aProps['expand_parent']);
        return true;
    }

    /**
     * @func      moveNode
     * @decr      move node (and child nodes) to another parent node
     * @param     obj        $oNode         The node object
     * @param     obj        $oTarget       target node
     * @param     arr        $aProps       optional node properties
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function moveNode($oNode, $oTarget,$aProps = array()) {
        $aPropsDefault = array('sUid'=>false,'aGid'=>false);
        // merge default props with provided props
        $aProps = array_merge($aPropsDefault,$aProps);

        $target_id = $oTarget;
        $node_id = $oNode->id;
        $vPerm = $this->nodes[$node_id][1];
        $vTargetPerm = $this->nodes[$target_id][1];
        $sUid = $aProps('sUid');
        $aGid = $aProps('aGid');

        // NB, only the permissions for the provided node are checked, children nodes
        // related to the provided node are not checked for delete rights.

        // check for delete permissions
        if ($this->_checkPermissions($vPerm,SM_NODE_DELETE,$sUid,$aGid)) {
            // check for insert permissions
            if ($this->_checkPermissions($vTargetPerm,SM_NODE_INSERT,$sUid,$aGid)) {
                // get the index of the node to delete
                $key = array_search($node->id,$this->p_c_rel[$parent_id],true);
                if ($key !== false && $key !== NULL) {
                    unset($this->p_c_rel[$parent_id][$key]);
                }
                // add the node_it to the target children array
                $this->p_c_rel[$target->id][] = $node_id;
                return true;
            }
        }
        // error, no sufficient rights
        return false;
    }

    /**
     * @func      deleteNode
     * @decr      delete complete branch with $node as parent
     * @param     obj        $oNode         The node object
     * @param     bool       $force         do not check children permissions
     * @param     arr        $aProps       optional node properties
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function deleteNode($oNode,$force = false,$aProps = array()) {
        $aPropsDefault = array('sUid'=>false,'aGid'=>false);
        // merge default props with provided props
        $aProps = array_merge($aPropsDefault,$aProps);

        $node_id = $oNode->id;
        $vPerm = $this->nodes[$node_id][1];
        $sUid = $aProps('sUid');
        $aGid = $aProps('aGid');
        if ($node->id !== 0 && $this->_checkPermissions($vPerm,SM_NODE_DELETE,$sUid,$aGid)) {
            $beforeDelete = $this->events['beforeDelete'];
            $afterDelete = $this->events['afterDelete'];

            $aTrash = array();
            /* retrieve child nodes */
            $this->_harvestNodes($oNode,$aTrash);

            /* delete from bottom to top */
            $aTrash = array_reverse($aTrash);
            // add the provided node to the trash array
            $aTrash[] = $oNode

            foreach ($aTrash as $oTrashNode) {
                $id = $oTrashNode->id;
                $bAllowDelete = true;
                if (!$force) {
                    $vPerm = $this->nodes[$id][1];
                    if ($this->_checkPermissions($vPerm,SM_NODE_DELETE,$sUid,$aGid)) {
                        // children nodes are not deleted due to permission problems
                        if (isset($this->p_c_rel[$id]) && count($this->p_c_rel[$id]) ) {
                            $bAllowDelete = false;
                        }
                     } else {
                        $bAllowDelete = false;
                     }
                }
                if ($bAllowDelete) {
                    if ($beforeDelete) {
                        call_user_func_array($this->events['beforeDelete'],&$this, &$oTrashNode);
                    }
                    // break child parents relations. In case of force we can delete the complete entry
                    if ($id !== $node_id && $force && isset($this->p_c_rel[$id])) {
                        unset($this->p_c_rel[$id]);
                    } else {
                        // remove node from parent
                        $key = array_search($id,$this->p_c_rel[$oTrashNode->parent_id],true);
                        if ($key !== false && $key !== NULL) {
                            unset($this->p_c_rel[$parent_id][$key]);
                        }
                    }
                    // unset the node
                    unset($this->nodes[$id]);
                    if ($afterDelete) {
                        call_user_func_array($this->events['afterDelete'],&$this, &$oTrashNode);
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @func      nodeHasChildren
     * @desc      returns true if Node has children
     * @param     obj        $oNode         The node object
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function nodeHasChildren($oNode) {
        if ($oNode && isset($this->p_c_rel[$oNode->id]) &&
            count($this->p_c_rel[$oNode->id])) {
            return true;
        } else if (!$oNode && isset($this->p_c_rel[0]) &&
            count($this->p_c_rel[0])) { /* root node */
            return true;
        }
        return false;
    }

    /**
     * @func      getChildren
     * @desc      returns array of children nodes
     * @param     obj        $node             The node object
     * @return    bool       $children/false   success/false
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function getChildren($oNode) {
        $children = array();
        if ($this->nodeHasChildren($oNode)) {
            if (!$node) {
                foreach ($this->p_c_rel[0] as $child_id) {
                    $children[$child_id] =& $this->nodes[$child_id][0];
                }
            } else {
                foreach ($this->p_c_rel[$node->id] as $child_id) {
                    $children[$child_id] =& $this->nodes[$child_id][0];
                }
            }
            return $children;
        } return false;
    }


    /**
     * @func       sortNodes
     * @desc       sort the nodes
     * @param      array    $nodes       array of node obj
     * @param      str      $sort        field to sort on
     * @param      int      $sortmethod  method to use when sorting
     *                      SM_SORT_DEFAULT,SM_SORT_NAT,SM_SORT_NAT_CASE,
     *                      SM_SORT_CASE,SM_SORT_NUMERIC,SM_SORT_STRING,
     * @param      bool     $reverse     reverse sort
     * @return     bool                  success
     * @access     public
     * @author     Marc Groot Koerkamp
     */

    function sortNodes(&$nodes, $sort, $sortmethod = SM_SORT_DEFAULT, $reverse=false) {
        // copy sort var to sort field
        foreach ($nodes as $node) {
            $node = $node[0];
            $node->sort = $node->{$sort};
        }
        switch ($sortmethod)
        {
          case SM_SORT_NAT:      uasort($nodes,array($this,'_nodecmpnat')); break;
          case SM_SORT_NAT_CASE: uasort($nodes,array($this,'_nodecmpnatcase')); break;
          case SM_SORT_CASE:     uasort($nodes,array($this,'_nodecmpcase')); break;
          case SM_SORT_NUMERIC:  uasort($nodes,array($this,'_nodecmpnumeric')); break;
          case SM_SORT_STRING:   uasort($nodes,array($this,'_nodecmpstring')); break;
          default:               uasort($nodes,array($this,'_nodecmp')); break;
        }
        if ($reverse) {
            $nodes = array_reverse($nodes);
        }
        return true;
    }

    /**
     * @func       Node compare functions
     * @desc       sort the nodes
     * @access     internal
     * @author     Marc Groot Koerkamp
     */
    function _nodecmp($a,$b) {
        if ($a->sort == $b->sort) return 0;
        return ($a->sort > $b->sort) ? -1 : 1;
    }

    function _nodecmpcase($a,$b)    { return strcasecmp($a->sort, $b->sort);   }
    function _nodecmpstring($a,$b)  { return strcmp($a->sort, $b->sort);       }
    function _nodecmpnatcase($a,$b) { return strnatcasecmp($a->sort,$b->sort); }
    function _nodecmpnat($a,$b)     { return strnatcmp($a->sort,$b->sort);     }

    function _nodecmpnumeric($a,$b) {
        if ((float) $a->sort == (float) $b->sort) return 0;
        return ((float) $a->sort > (float) $b->sort) ? -1 : 1;
    }
    /* end nodecompare functions */


    /**
     * @func       _harvestNodes
     * @desc       Retrieve all nodes belonging to a subtree
     * @param      obj      $node        The node object
     * @param      array    $nodes       array of node obj
     * @return     bool                  success
     * @access     private
     * @author     Marc Groot Koerkamp
     */
    function _harvestNodes($node, &$nodes) {
        $children = $this->getChildren($node);
        foreach ($children as $child) {
            $nodes[] = $child;
            if ($this->hasChildren($child)) {
                $this->_harvestNodes($child,$nodes);
            }
        }
        return true;
    }


    /**
     * @func      _checkPermissions
     * @desc      Check for suffient permissions
     * @param     arr|int    $vPermissions AclList or simple permissions int
     * @param     int        $iWhat        Which permissions bit are involved
     * @param     str        $sUid         uid to check
     * @param     arr        $aGid         groups to check
     * @return    bool                     success
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function _checkPermission($vPermissions, $iWhat, $sUid, $aGid) {
        $bResult = true;
        if ($this->enable_acl) {
            // use tree defaults if uid/gid are not supplied
            if (!$sUid) $sUid = $this->uid;
            if (!count($aGid)) $aGid = $this->gid;
            $bResult = acl::checkaccess($vPermissions,$sUid,$aGid,$iWhat);
        } else {
            if ($vPermissions & $iWhat == $iWhat) {
               $bResult = true;
            }
        }
        return $bResult;
    }

    /**
     * @func      _alterPermissions
     * @desc      Alter the specified permissions bits
     * @param     arr|int    $vPermissions AclList or simple permissions int
     * @param     int        $iWhat        Which permissions bit are involved
     * @param     str        $sUid         uid to check
     * @param     arr        $aGid         groups to check
     * @param     bool       $bSet         set or remove
     * @return    bool                     success
     * @access    private
     * @author    Marc Groot Koerkamp
     */
    function _alterPermission(&$vPermission,$iWhat, $sUid, $aGid, $bSet = true) {
        $bResult = true;
        if ($this->enable_acl) {
            // use tree defaults if sUid/aGid are not supplied
            if (!$sUid) $sUid = $this->uid;
            if (!count($aGid)) $aGid = $this->gid;

            if (! acl::alterMyPerm($vPermission,$iWhat, $sUid, $aGid, $bSet)) {
                return false;
            }
        } else {
            if ($bSet) {
               $vPermission = ($vPermission ^ $iWhat) + ($vPermission & $iWhat);
            } else {
               $vPermission = ($vPermission | $iWhat) - $iWhat;
            }
        }
        return $bResult;
    }


    /* events */
    function beforeMove() {
    }

    function onMove() {
    }

    function onDelete() {
    }

    function onCopy() {
    }

    function onCollapse() {
    }

    function onExpand() {
    }


}

class node extends object{

    /**
     * Contructor
     *
     * @param     int        $id            Node identifier
     *
     * @access     public
     * @author     Marc Groot Koerkamp
     */
    function node($id=0) {
        $this->id = $id;

        /* set the sleep notifyer */
        //$this->listen['sleep'] = array(&$this, '_sleep');
    }

    function _sleep() {
    }
}

?>
