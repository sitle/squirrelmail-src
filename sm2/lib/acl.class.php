<?php

/**
 * acl.class.php
 *
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Access Control List class.
 *
 * $Id$
 */

 /* 
   BRAINSTORMING SESSION HOW the acl class is defined (MGK) CAN BE DELETED AT A LATER TIME
 
 
   HOWTO create a common acl set whcih can be used for many purposes ?
   
   let's inventarise what kind of acl's are used in the real world. 
   
   linux rights:
   rwx
   separation to user/group/other where user/group are owner
   
   linux acl
   entry tag: entry qualifier : acl
   entry tags: acl_user_obj: rights for owner
               acl user: rights for optional user
               acl_group_obj: rights for group
               acl_group: rights for optional groups
               acl_mask: max acl for acl_user, acl_group_obj, acl_group
               acl_other: acl for everything that doesn't match above
   
   order of acl checking: acl_user_obj,
                          acl_user && acl_mask,
                          if acl_mask then (acl_group_obj && acl_mask) else acl_group_obj,
                          acl_group && acl_mask,
                          acl_other,
   entry qualifier: username/groupname/empty                      

               
               
   IMAP ACL
   l lookup
   r read
   s per user seen state
   w write flags other then seen/delete
   i insert messages into mailbox
   p post to mailbox
   c create subs / rename/delete currrent mailbox
   d store \Delete flag and expunge
   a administer acl

   individual user/group acl possible
   
   Wanted acl control in i.e. a tree view:
   v: visible: node can be viewed
   r: read:    access to node content
   w: write:   modify node content or rename a node
   d: delete:  delete move from tree
   i: insert:  insert a childnode
   x: excecute: node contains link
   a: admin:   change acl
   
   (move: combination of delete/insert)
   (copy: insert at target)

   
   Simularities:
              posix | posix acl | imap acl | tree
   visible:   -       -           l          v 
   read:      r       r           r          r
   write:     w       w           w/s/c      w
   delete:    w       w           d          d
   insert     w       w           i/p        i
   excecute   x       x           -          x
   admin      w       w           a          a
   
   SM ACL MAPPING:
   NONE:           0
   VISIBLE:        1
   READ:           2
   WRITE:          4
   WRITE SEEN      8
   WRITE FLAGS     16
   INSERT          32
   CREATE SUB      64
   DELETE          128
   EXECUTE         256
   POST            512 ???
   ADMIN           1024
    
   effective permissions posix && posix acl:
   read:     VISISBLE + READ = 3
   write:    WRITE + WRITE FLAGS + WRITE SEEN + CREATE SUB + INSERT + DELETE + ADMIN? = 1276
   excecute: EXECUTE = 256
   
   effective permisions imap acl:
   l = VISISBLE    1
   r = READ        2
   s = WRITE SEEN  8
   w = WRITE FLAGS 16
   i = INSERT      32
   p = POST        512
   c = CREATE SUB  64
   d = DELETE      128
   a = ADMIN       1024

   effective permisions tree:
   v: VISISBLE                     1
   r: READ                         2
   w: WRITE, WRITE SEEN           24
   d: DELETE                     128
   i: INSERT                      32 
   x: EXCECUTE                   256
   a: ADMIN                     1024

   Okey now I have an idea of ACL's and just came to the conclusion that ACL's should NOT be static defined.
   To much overhead for simpler forms of ACL's
   
   Each implementation is unique in required ACL's therefor each ACL implementation should define it's own
   set of required RIGHTS. If there are combined forms like displaying a mailbox list in a treeview or displaying 
   a directory-tree in a treeview then above example can be used.
   
   The ACL value is a simple integer where the availability of a certain right can be checked by a bitwise check.
   
   The posix acl implementation however can be used for other acl implementation as well.
   The complete acl list can be defined as followed:
   array(array($tag,$qualifier,$acl),...,array($tag,$qualifier,$acl)).
   $tag: 'U' || 'G' || 'u' || 'g' || 'm' || 'o'
   $tag                $qualifer    $acl
   U = acl_user_obj    uid          int
   G = acl_group_obj   gid          int
   u = acl_user        uid          int
   g = acl_group       gid          int
   m = acl_mask        empty        int
   d = acl_other       empty        int

   The interpretation of $acl should be implementation specific.      
  */

/* move to the specific implementation that make use of ACL's  The mapping make no sense anymore

// derived constants FS
define('SM_ACL_FS_NONE',0);
define('SM_ACL_FS_READ',3);
define('SM_ACL_FS_WRITE',1276);
define('SM_ACL_FS_EXECUTE',256);

// derived constants TREE
define('SM_ACL_TREE_NONE',0);
define('SM_ACL_TREE_VISISLE',1);
define('SM_ACL_TREE_WRITE',24);
define('SM_ACL_TREE_DELETE',128);
define('SM_ACL_TREE_INSERT',32);
define('SM_ACL_TREE_EXCECUTE',256);
define('SM_ACL_TREE_ADMIN',1024);

// derived constants MBX (mailbox)
define('SM_ACL_MBX_NONE',0);
define('SM_ACL_MBX_LOOKUP',1);
define('SM_ACL_MBX_READ',2);
define('SM_ACL_MBX_SEEN',8);
define('SM_ACL_MBX_WRITE',16);
define('SM_ACL_MBX_INSERT',32);
define('SM_ACL_MBX_POST',512);
define('SM_ACL_MBX_CREATE',64);
define('SM_ACL_MBX_DELETE',128);
define('SM_ACL_MBX_ADMIN',1024);
define('SM_ACL_MBX_FULL',1023);
define('SM_ACL_MBX_ALL',2047);

*/
class acl {

    /**
     * @func      set
     * @desc      set an acl entry in the provided acl list
     * @param     str        $tag          entry tag. possible values: U,G,u,g,m,o
     * @param     str        $qualifier    username, groupname or empty
     * @param     int        $iRights      integer represents rights
     * @param     arr        $aAclList     array with individual acl entries
     *                                     acl entry: array($tag,$qualifier,$acl)
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function set($tag,$qualifier,$iRights, &$aAclList) {
         switch ($tag)
         {
           case 'U':
           case 'G':
               if (!$qualifier) {
                   //error
                   return false;
               }
           case 'm':
           case 'o':
               // unique entries
               $i = 0;
               foreach($aAclList as $entry) {
                   if ($entry[0] == $tag) {
                       // replace unique tag
                       $aAclList[$i] = array($tag,$qualifier,$iRights);
                       break 2;
                   }
                   ++$i;
               }
               // tag didn't exist
               $aAclList[] = array($tag,$qualifier,$iRights);
               break;
           case 'u': // mask required !
           case 'g': // mask required !
               if (!$qualifier) {
                   //error
                   return false;
               }
               $bMask = $acl_entry_id = false;
               for($i=0,$iCnt=count($aAclList);$i<$iCnt;++$i) {
                   if ($entry[0] == $tag) {
                       for($j=$i;$j<$iCnt;++$j) {
                           if ($aAclList[$i][0] == $tag && $aAclList[$i][1] == $qualifier) {
                               // replace unique qualifier
                               $acl_entry_id = $j;
                               if ($bMask) { // for efficiency it's better tha the mask entry appears before u/g entries
                                   break 2;
                               }
                           }
                       }
                   } else if ($entry[0] == 'm') {
                       $bMask = true;
                   }
               }
               if ($bMask) {
                   if ($acl_entry_id !== false) {
                       $aAclList[$acl_entry_id] = array($tag,$qualifier,$iRights);
                   } else {
                       // qualifier didn't exist
                       $aAclList[] = array($tag,$qualifier,$iRights);
                   }
                   break;
               } else {
                   return false; // no mask present
               }
           default:
              // error, wrong tag
              return false;
        }
        return true;
    }

    /**
     * @func      remove
     * @desc      remove an acl entry from the provided acl list
     * @param     str        $tag          entry tag. possible values: U,G,u,g,m,o
     * @param     str        $qualifier    username, groupname or empty
     * @param     arr        $aAclList     array with individual acl entries
     *                                     acl entry: array($tag,$qualifier,$acl)
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function remove($tag,$qualifier,&$aAclList) {
         switch ($tag)
         {
           case 'U':
           case 'G':
               if (!$qualifier) {
                   //error
                   return false;
               }
           case 'o':
               // unique entries
               $i = 0;
               foreach($aAclList as $entry) {
                   if ($entry[0] == $tag) {
                       // remove unique tag
                       unset($aAclList[$i]);
                       break 2;
                   }
                   ++$i;
               }
               break;
           case 'u':
           case 'g':
               if (!$qualifier) {
                   //error
                   return false;
               }
               for($i=0,$iCnt=count($aAclList);$i<$iCnt;++$i) {
                   if ($aAclList[$i][0] == $tag) {
                       for($j=$i;$j<$iCnt;++$j) {
                           if ($aAclList[$i][0] == $tag && $aAclList[$i][1] == $qualifier) {
                               // replace unique qualifier
                               unset($aAclList[$j]);
                               break 3;
                           }
                       }
                   }
               }
               break;
           case 'm':
               $mask_del_forbidden = $acl_entry_id = false;
               for($i=0,$iCnt=count($aAclList);$i<$iCnt;++$i) {
                   if ($aAclList[$i][0] == 'm') {
                      $acl_entry_id = $i;
                   }
                   if ($tag == 'u' || $tag == 'g') {
                      $mask_del_forbidden = true;
                   }
               }
               if (!$mask_del_forbidden && $acl_entry_id !== false) {
                   unset($aAclList[$acl_entry_id]);
               }
           default:
              // error, wrong tag
              return false;
        }
        return true;
    }

    /**
     * @func      alter
     * @desc      alter an acl entry from the provided acl list
     * @param     str        $tag          entry tag. possible values: U,G,u,g,m,o
     * @param     str        $qualifier    username, groupname or empty
     * @param     int        $iRight       permissions to add/remove
     * @param     bool       $bSet         false if provided $iRighs should be removed
     * @param     arr        $aAclList     array with individual acl entries
     *                                     acl entry: array($tag,$qualifier,$acl)
     * @return    bool                     success
     * @access    public
     * @author    Marc Groot Koerkamp
     */

    // TODO acl admin bit and specify which bits can be altered without the admin bit

    function alter($tag,$qualifier,$iRights,$bSet = true, &$aAclList) {

        switch ($tag)
        {
          case 'U':
          case 'G':
              if (!$qualifier) {
                  //error
                  return false;
              }
          case 'o':
              // unique entries
              $i = 0;
              foreach($aAclList as $entry) {
                  if ($entry[0] == $tag) {
                      // alter unique tag
                      $iCurRights = $aAclList[$i][2];
                      if ($bSet) {
                          $iNewRights = ($iCurRights ^ $iRights) + ($iCurRights & $iRights);
                      } else {
                          $iNewRights = ($iCurRights | $iRights) - $iRights;
                      }
                      $aAclList[$i]=array($tag,$qualifier,$iNewRights);
                      break 2;
                  }
                  ++$i;
              }
              break;
          case 'u':
          case 'g':
              if (!$qualifier) {
                  //error
                  return false;
              }
              for($i=0,$iCnt=count($aAclList);$i<$iCnt;++$i) {
                  if ($aAclList[$i][0] == $tag) {
                      for($j=$i;$j<$iCnt;++$j) {
                          if ($aAclList[$i][0] == $tag && $aAclList[$i][1] == $qualifier) {
                              // alter unique qualifier
                              $iCurRights = $aAclList[$i][2];
                              if ($bSet) {
                                  $iNewRights = ($iCurRights ^ $iRights) + ($iCurRights & $iRights);
                              } else {
                                  $iNewRights = ($iCurRights | $iRights) - $iRights;
                              }
                              $aAclList[$i]=array($tag,$qualifier,$iNewRights);
                              break 3;
                          }
                      }
                  }
              }
              break;
          case 'm':
              for($i=0,$iCnt=count($aAclList);$i<$iCnt;++$i) {
                  if ($aAclList[$i][0] == 'm') {
                      $iCurRights = $aAclList[$i][2];
                      if ($bSet) {
                          $iNewRights = ($iCurRights ^ $iRights) + ($iCurRights & $iRights);
                      } else {
                          $iNewRights = ($iCurRights | $iRights) - $iRights;
                      }
                      $aAclList[$i]=array($tag,$qualifier,$iNewRights);
                      break 2;
                  }
              }
              break;
          default:
              // error, wrong tag
              return false;
        }
        return true;

    }

    /**
     * @func      effectiveRights
     * @desc      calculate the effective rights for user / groups
     * @param     arr        $aAclList     array with individual acl entries
     * @param     str        $sUid         username
     * @param     arr        $aGid         array with groups
     * @return    int                      permission
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function effectiveRights($aAclList,$sUid='',$aGid=array()) {
        $aAcl_temp = array();
        for($i=0,$iCnt=count($aAclList);$i<$iCnt;++$i) {
            $tag = $aAclList[$i][0];
            switch ($tag)
            {
              case 'u':
              case 'U':
                 if ($sUid != $aAclList[$i][1]) {
                     break;
                 } else if ($tag == 'U') { // only result that can be returned directly
                     return $aAclList[$i][2];
                 }
              case 'G':
                 if (!in_array($aAclList[$i][1],$aGid,true)) {
                     break;
                 }
              case 'o':
              case 'm':
                  $aAcl_temp[$tag] = $aAclList[$i][2];
                  break;
              case 'g': // user can be part of multiple groups
                  if (in_array($aAclList[$i][1],$aGid,true)) {
                      if (isset($aAcl_temp['g'])) {
                          // do a or comparasion between the already retrieved rights
                          $aAcl_temp['g'] = $aAcl_temp['g'] | $aAclList[$i][2];
                      } else {
                         $aAcl_temp['g'] = $aAclList[$i][2];
                      }
                  }
                  break;
              default: break;
            }
        }
        /*
        order of acl checking: acl_user_obj (U),
                               acl_user && acl_mask (u),
                               if (acl_mask (m)) then (acl_group_obj (G) && acl_mask (m)) else acl_group_obj (G),
                               acl_group (g) && acl_mask (m),
                               acl_other (o)
        */
        if (isset($aAcl_temp['u'])) {
            return ($aAcl_temp['u'] & $aAcl_temp['m']);
        } else if (isset($aAcl_temp['G'])) {
            if (isset($aAcl_temp['m'])) {
                return ($aAcl_temp['G'] & $aAcl_temp['m']);
            } else {
                return $aAcl_temp['G'];
            }
        } else if (isset($aAcl_temp['g'])) {
            return ($aAcl_temp['g'] & $aAcl_temp['m']);
        } else if (isset($aAcl_temp['o'])) {
            return $aAcl_temp['o'];
        } else {
            return false; // no perm.
        }
    }

    /**
     * @func      suffRights
     * @desc      calculate if provided rights meets requested rights
     * @param     int        $iMyRights    my rights
     * @param     int        $sReqRights   rights to check with
     * @return    bool                     sufficient rights
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function suffRights($iMyRights,$iReqRights) {
        if (($iMyRights & $iReqRights) == $iReqRights) {
            return true;
        }
        return false;
    }

    /**
     * @func      checkAccess
     * @desc      calculate if provided uid/gid has sufficient rights
     * @param     arr        $aAclList     array with individual acl entries
     * @param     str        $sUid         username
     * @param     arr        $aGid         array with groups
     * @param     int        $iRights      rights to check
     * @return    bool                     sufficient rights
     * @access    public
     * @author    Marc Groot Koerkamp
     */
    function checkAccess($aAclList,$sUid='',$aGid=array(),$iRights) {
        $iMyRights = acl::effectiveRights($aAclList,$sUid,$aGid);
        return acl::suffRights($iMyRights,$iRights);
    }

    /**
     * @func      alterMyPerm
     * @desc      alter the first possible (best) acl entry from the provided acl list
     * @param     arr        $aAclList     array with individual acl entries
     * @param     int        $iRight       permissions to add/remove
     * @param     str        $sUid         uid
     * @param     arr        $aUid         gid where uid belongs to
     * @param     bool       $bSet         set or remove the provided $iRighs
     * @return    bool       $bResult      success
     * @access    public
     * @author    Marc Groot Koerkamp
     */

    function alterMyPerm(&$aAclList,$iRights,$sUid,$aGid,$bSet = true)
        $bResult = false;
        if (! $this->alter('U',$sUid,$iWhat,$bSet,$vPermissions)) {
            // try user with sUid
            if (! $this->alter('u',$sUid,$iWhat,$bSet,$vPermissions)) {
                // try the default group
                foreach($aGid as $sGid) {
                    if ($this->alter('G',$sGid,$iWhat,$bSet,$vPermissions)) {
                        return true;
                    }
                }
                // try the groups
                foreach($aGid as $sGid) {
                    if ($this->alter('g',$sGid,$iWhat,$bSet,$vPermissions)) {
                        return true;
                    }
                }
                // try other
                if ($this->alter('o','',$iWhat,$bSet,$vPermissions)) {
                    return true;
                }
            } else {
                 return true;
            }
        } else {
            return true;
        }
        return $bResult;
    }
}

?>
