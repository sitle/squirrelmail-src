<?php

/**
 * tree.class.php
 *
 * Copyright (c) 2003 Marc Groot Koerkamp 
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Base tree class.
 * 
 *
 * Author: Marc Groot Koerkamp (Sourceforce username: stekkel) 2003
 *
 * $Id$
 */

/*
Tree class:

public methods:
  tree (constructor)
  addNode
  deleteNode
  moveNode
  nodeHasChilren
  getChildren
  expandNode
  collapseNode
  
  node properties:
  SM_NODE_EXPANDED 1
  SM_NODE_VISIBLE  2
  
  
*/

/* node properties definitions */
define('SM_NODE_EXPANDED',1);
define('SM_NODE_VISIBLE',2);


class tree extends object{
    var $name,
        $expanded = array(),
        $nodes = array(false),
        $listen = array(),
        /* events */
        $events = array (
            'beforeDelete' => false,
            'afterDelete' => false,
            'beforeMove' => false,
            'afterMove' => false,
            'beforeAdd' => false,
            'afterAdd' => false
            );
    /**
     * @func      tree
     * @desc      constructor 
     * @param     str        $id           unique identifier
     * @param     arr        $credentials  Experimental Testing     
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function tree($id,$credentials=array('self'=> SM_ACL_ALL)) {
        $this->id = $id;
        $this->p_c_rel = array(); /* parent -> child relations */
        $this->nodes = array(); /* array with nodes accessible by the id of the node */
        $this->listeners = array(&$this->nodes);
        /* create root node */
        $rootnode =& new node(0,array('self'=>SM_ACL_NONE));
        $rootnode->id = 0;
        $this->p_c_rel[0] = array();
        $this->nextnodeid = 1;
        $this->nodes[] =& $rootnode;
        /* set the sleep notifyer */
        $this->listen['sleep'] = array(&$this, '_sleep');
        
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
     * @func      addNode
     * @desc      Add a node to parent node
     * @param     obj        $node         The node object
     * @param     obj        $parent       parent node
     * @param     arr        $properties   optional node properties     
     * @return    bool                     success
     * @access     public
     * @author     Marc Groot Koerkamp
     */
    
    function addNode(&$node, $parent=false, $properties=array() ) {//(&$node, $parent = $this->nodes[0], $properties=array() ) {
        if (!$parent) {
            $parent = $this->nodes[0];
        }
        if ($node->id) {
            /* check unique */
            if (isset($this->nodes[$id])) {
                $this->error = SMNODE_ID_NOT_UNIQUE;
                return false;
            }
            $id = $node-id;
        } else {
            $id = $this->nextnodeid;
            $node->id = $id;
        }
        ++$this->nextnodeid;
        
        $this->nodes[$id] = array(&$node,$properties);
        /* assign the parent id to the node in order to speed up dependency checks */
        $node->parent_id = $parent->id;
        /* add the node_id to the p_c_rel array */
        $this->p_c_rel[$parent->id][] = $id;
    }

    
    /* obsolete */
    function setNodeProperties($properties) {
        $node_visible = $node_expanded = 0;
        foreach ($properties as $prop) {
            if (!$node_visible && ($prop & SM_NODE_VISIBLE) == SM_ACL_LOOKUP) {
                $acl_lookup = 1;
            }
            if (!$acl_read && ($perm & SM_ACL_READ) == SM_ACL_READ) {
                $acl_read = 2;
            }
    
    }
    /**
     * @func      nodeHasChildren
     * @desc      returns true if Node has children
     * @param     obj        $node         The node object
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function nodeHasChildren($node) {
        if ($node && isset($this->p_c_rel[$node->id]) &&
            count($this->p_c_rel[$node->id])) {
            return true;
        } else if (!$node && isset($this->p_c_rel[0]) &&
            count($this->p_c_rel[0])) /* root node */
            return true;
        }
        return false;
    }

    /**
     * @func      getChildren
     * @desc      returns array of children nodes
     * @param     obj        $node         The node object
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function getChildren($node) {
        $children = array();
        if ($this->nodeHasChildren($node)) {
            if (!$node) {
                foreach ($this->p_c_rel[0] as $child_id) {
                    $children[$child_id] =& $this->nodes[$child_id];
                }
            } else {
                foreach ($this->p_c_rel[$node->id] as $child_id) {
                    $children[$child_id] =& $this->nodes[$child_id];
                }
            }
            return $children;
        } return false;
    }

    function expandNode(&$node) {
        $node->expanded  = true;
    }

    function collapseNode(&$node) {
        $node->expanded = false;
    }
    

    /**
     * @func      moveNode
     * @decr      move node to another parent node
     * @param     obj        $node         The node object
     * @param     obj        $target       target node
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function moveNode($node, $target) {    
        $parent_id = $node->parent_id;
        $node_id = $node->id;
        $key = array_search($node->id,$this->p_c_rel[$parent_id],true);
        if ($key !== false && $key !== NULL) {
            unset($this->p_c_rel[$parent_id][$key]);
        } 
        $this->p_c_rel[$target->id][] = $node_id;
        return true;
    }
    
    /**
     * @func      deleteNode
     * @decr      delete complete branch with $node as parent
     * @param     obj        $node         The node object
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function deleteNode($node) {
        if ($node->id !== 0 && $this->_sufficientPerm($node,SM_PERM_DEL,false)) {
            $beforeDelete = $this->events['beforeDelete'];
            $afterDelete = $this->events['afterDelete'];
            if ($this->_sufficientPerm($node,SM_PERM_DEL,true)) {
                $trash = array();
                /* retrieve child nodes */
                $this->_harvestNodes($node,$trash);
                /* delete from bottom to top */
                $trash = array_reverse($trash);
                foreach ($trash as $trashnode) {
                    if ($beforeDelete) {
                        call_user_func_array($this->events['beforeDelete'],&$this, &$node);
                    }
                    if (isset($this->p_c_rel[$trashnode->id])) {
                        unset($this->p_c_rel[$trashnode->id]);
                    }
                    unset($this->nodes[$trashnode->id]);
                    if ($afterDelete) {
                        call_user_func_array($this->events['afterDelete'],&$this, &$node);
                    }
                }
                if ($beforeDelete) {
                    call_user_func_array($this->events['beforeDelete'],&$this, &$node);
                }
                $key = array_search($node->id,$this->p_c_rel[$node->parent_id],true);
                if ($key !== false && $key !== NULL) {
                    unset($this->p_c_rel[$parent_id][$key]);
                }
                unset($this->nodes[$node->id]); 
                if ($afterDelete) {
                    call_user_func_array($this->events['afterDelete'],&$this, &$node);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @func       _harvestNodes
     * @desc       Retrieve all child nodes
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

    /* obsolete */
    // move me to optional visualisation class that can be added as var
    function getNodeArray(&$res, $node = false, $incl_collapsed = false, $incl_invisible = true, $sort = false, $sortmethod = false, $reverse = false,
                           $maxdepth=0, $_depth = 0, $_visble = true, $_parent = 0) {
        if (!$node) {
            $node = $this->nodes[0];
        }
        $id = $node->id;
        /* get children */
        $children = $this->getChildren($node);
        if ($children && (!$maxdepth || $depth < $maxdepth)) {
            $res[$id] = array(&$node,  'HAS_CHILDREN' => true,
                                       'DEPTH' => $_depth,
                                       'VISIBLE' => $_visible,
                                       'EXPANDED' => $this->expanded[$id],
                                       'PARENT_ID' => $_parent);
            if ($sort) {
                $this->sortNodes($children,$sort, $sortmethod, $reverse);
            }
            foreach($children as $child_id => $child) {
                $visible = $this->c_p_rel[$child_id][$id];
                if ($incl_invisible && $incl_collapsed) {
                    $this->getNodeArray($res, $child, $incl_collapsed, $incl_invisible, $sort, $sortmethod, $reverse, ++$_depth, $_visible, $_parent);
                } else if ($visible && $incl_collapsed) {
                    $this->getNodeArray($res, $child, $incl_collapsed, $incl_invisible, $sort, $sortmethod, $reverse, ++$_depth, $_visible, $_parent);
                }
            }
            $firstnode = reset($children);
            $lastnode = end($children);
            $res[$firstnode->id]['FIRST'] = true;
            $res[$lastnode->id]['LAST'] = true;
        } else {
            $res[$id] = array(&$node,  'HAS_CHILDREN' => false,
                                       'DEPTH' => $_depth,
                                       'VISIBLE' => $_visible,
                                       'EXPANDED' => false,
                                       'PARENT_ID' => $_parent);
        }
        return true;
    }
        /* has_children  OK*/
        /* first  OK*/
        /* last  OK*/
        /* visible OK */
        /* depth OK*/
        /* parent_id OK */
        /* other parents ? */

    /**
     * @func       sortNodes
     * @desc       sort the nodes
     * @param      array    $nodes       array of node obj
     * @param      str      $sort        field to sort on
     * @param      int      $sortmethod  method to use when sorting
     * @param      bool     $reverse     reverse sort
     * @return     bool                  success
     * @access     public
     * @author     Marc Groot Koerkamp
     */
    function sortNodes(&$nodes, $sort, $sortmethod, $reverse) {
        foreach ($nodes as $node) {
            $node->sort = $node->{$sort};
        }
        // FIX ME, sort method should be CONSTANTS like SM_SORT_NAT
        switch ($sortmethod)
        {
        case 'nat':        uasort($nodes,array($this,'_nodecmpnat')); break;
        case 'natcase':    uasort($nodes,array($this,'_nodecmpnatcase')); break;
        case 'case':       uasort($nodes,array($this,'_nodecmpcase')); break;
        case 'numeric':    uasort($nodes,array($this,'_nodecmpnumeric')); break;
        case 'string':     uasort($nodes,array($this,'_nodecmpstring')); break;
        default:           uasort($nodes,array($this,'_nodecmp')); break;
        }
        if ($reverse) {
            $nodes = array_reverse($nodes);
        }
    }

    /**
     * Check for permissions
     *
     * @param     obj        $node        The node object
     * @param     int        $perm        Sufficient permission
     * @param     bool    $recursive    Recursive check
     * @return     bool    $ret        success
     *
     * @access     private
     * @author     Marc Groot Koerkamp
     */
    function _sufficientPerm($node,$perm, $recursive = false) {
                $ret = true;
        if (($node->perm & $perm) != $perm) {
            return false;
        }
        if ($recursive) {
            $children = $this->getChildren($node);
            foreach ($children as $child) {
                if (($child->perm & $perm) == $perm) {
                    return false;
                }
                if ($this->hasChildren($node)) {
                    $ret = $this->_sufficientPerm($child, $perm, $recursive);
                    if (!$ret) {
                        return false;
                    }
                }
            }
        }
        return $ret;
    }    
    
    
    function _nodecmp($a,$b) {
        if ($a->sort == $b->sort) return 0;
        return ($a->sort > $b->sort) ? -1 : 1;
    }

    function _nodecmpcase($a,$b) {
        return strcasecmp($a->sort, $b->sort);
    }
    
    function _nodecmpstring($a,$b) {
        return strcmp($a->sort, $b->sort);
    }

    function _nodecmpnatcase($a,$b) {
        return strnatcasecmp($a->sort,$b->sort);
    }

    function _nodecmpnat($a,$b) {
        return strnatcmp($a->sort,$b->sort);
    }

    function _nodecmpnumeric($a,$b) {
        if ((float) $a->sort == (float) $b->sort) return 0;
        return ((float) $a->sort > (float) $b->sort) ? -1 : 1;
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
     * @param    arr        $acl        Access Control List
     *
     * @access     public
     * @author     Marc Groot Koerkamp
     */
    function node($id=0, $acl = false) {
        if (!$acl) {
           $acl =& new acl(array('self' => SM_ACL_ALL));
        } 
        $this->id = $id;
        $this->acl = $acl;
        $this->own = '';
        $this->grp = '';
        /* set the sleep notifyer */
        $this->listen['sleep'] = array(&$this, '_sleep');
    }

    function _sleep() {
    }
}

/* permisions */
define('SM_ACL_LOOKUP',1);      /* user may view node */
define('SM_ACL_READ',2);        /* user may see node content */
define('SM_ACL_WRITE',4);       /* user may adapt node content */
define('SM_ACL_INSERT',8);      /* user may add child nodes */
define('SM_ACL_CREATE',16);     /* user may contain child nodes that can contain nodes */
define('SM_ACL_DEL',32);        /* user may delete node */
define('SM_ACL_ADM',64);        /* user may modify acl entry */
/* permissions macro's */
define('SM_ACL_NONE',0);
define('SM_ACL_RO',3);
define('SM_ACL_RW',7);
define('SM_ACL_FULL',63);
define('SM_ACL_ALL',127);

class acl {
    var $entries = array();

    function acl($init_acl = false) {
        if ($ini_acl) {
            $this->entries = $init_acl;
        }
    }

    function setPerm($acllist,$member_of) {
        /* check for admin */
        $perm = $this->requestPerm($member_of);
        if (($perm & SM_ACL_ADM) != SM_ACL_ADM) {
            return false;
        }
        foreach ($acllist as $member => $acl) {
            $this->entries[$member] = $acl;
        }
    }

    function hasPerm($perm, $request_perm) {
        if (($perm & $request_perm) == $request_perm) {
            return true;
        }
        return false;
    }

    function requestPerm($member_of) {
        $permissions = false;
        foreach($member_of as $member) {
            if (array_key_exists($member,$this->entries)) {
                $permissions[]= $this->entries[$member];
            }
        }
        $acl_lookup = $acl_read = $acl_write = $acl_insert = $acl_create = $acl_del = $acl_adm = 0;
        foreach ($permissions as $perm) {
            if (!$acl_lookup && ($perm & SM_ACL_LOOKUP) == SM_ACL_LOOKUP) {
                $acl_lookup = 1;
            }
            if (!$acl_read && ($perm & SM_ACL_READ) == SM_ACL_READ) {
                $acl_read = 2;
            }
            if (!$acl_write && ($perm & SM_ACL_WRITE) == SM_ACL_WRITE) {
                $acl_write = 4;
            }
            if (!$acl_insert && ($perm & SM_ACL_INSERT) == SM_ACL_INSERT) {
                $acl_insert = 8;
            }
            if (!$acl_create && ($perm & SM_ACL_CREATE) == SM_ACL_CREATE) {
                $acl_create = 16;
            }
            if (!$acl_del && ($perm & SM_ACL_DEL) == SM_ACL_DEL) {
                $acl_del = 32;
            }
            if (!$acl_adm && ($perm & SM_ACL_ADM) == SM_ACL_ADM) {
                $acl_adm = 64;
            }
        }
        $perm = $acl_lookup + $acl_read + $acl_write + $acl_insert + $acl_create + $acl_del + $acl_adm;
        return $perm;
    }
}



?>
