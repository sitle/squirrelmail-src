<?php

/**
 * Message.class.php
 *
 * Copyright (c) 2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This contains functions needed to handle mime messages.
 *
 * $Id$
 */

class Message {
    /** message is the object that contains messages.  It is a recursive
        object in that through the $entities variable, it can contain
        more objects of type message.  See documentation in mime.txt for
        a better description of how this works.
    **/
    var $rfc822_header = '',
        $mime_header = '',
	$uid = '',
	$internaldate = '',
        $flags = '',
        $type0='',
        $type1='',
        $entities = array(),
	$entity_id = '',
        $parent_ent, $entity,
        $parent = '', $decoded_body='',
        $is_seen = 0, $is_answered = 0, $is_deleted = 0, $is_flagged = 0,
        $is_mdnsent = 0,
        $body_part = '',
        $offset = 0,  /* for fetching body parts out of raw messages */
        $length = 0,  /* for fetching body parts out of raw messages */
	$att_local_name = ''; /* location where the tempory attachment
	                         is stored. For future usage in smtp.php */

    function setEnt($ent) {
        $this->entity_id= $ent;
    }

    function addEntity ($msg) {
        $msg->parent = &$this;
        $this->entities[] = $msg;
    }

    function getFilename() {
        $filename = '';
        $filename = $this->header->getParameter('filename');
        if (!$filename) {
            $filename = $this->header->getParameter('name');
        }

        if (!$filename) {
            $filename = 'untitled-'.$this->entity_id;
        }
        return $filename;
    }


    function addRFC822Header($read) {
        $header = new Rfc822Header();
        $this->rfc822_header = $header->parseHeader($read);
    }

    function getEntity($ent) {
        $cur_ent = $this->entity_id;
        $msg = $this;
        if (($cur_ent == '') || ($cur_ent == '0')) {
            $cur_ent_a = array();
        } else {
            $cur_ent_a = explode('.', $this->entity_id);
        }
        $ent_a = explode('.', $ent);
        
        for ($i = 0,$entCount = count($ent_a) - 1; $i < $entCount; ++$i) {
            if (isset($cur_ent_a[$i]) && ($cur_ent_a[$i] != $ent_a[$i])) {
                $msg = $msg->parent;
                $cur_ent_a = explode('.', $msg->entity_id);
                --$i;
            } else if (!isset($cur_ent_a[$i])) {
                if (isset($msg->entities[($ent_a[$i]-1)])) {
                    $msg = $msg->entities[($ent_a[$i]-1)];
                } else {
                    $msg = $msg->entities[0];
                }
            }
            if (($msg->type0 == 'message') && ($msg->type1 == 'rfc822')) {
                /*this is a header for a message/rfc822 entity */
                $msg = $msg->entities[0];
            }
        }

        if (($msg->type0 == 'message') && ($msg->type1 == 'rfc822')) {
            /*this is a header for a message/rfc822 entity */
            $msg = $msg->entities[0];
        }

        if (isset($msg->entities[($ent_a[$entCount])-1])) {
            if (is_object($msg->entities[($ent_a[$entCount])-1])) {
                $msg = $msg->entities[($ent_a[$entCount]-1)];
            }
        }

        return $msg;
    }

    function setBody($s) {
        $this->body_part = $s;
    }

    function clean_up() {
        $msg = $this;
        $msg->body_part = '';

        foreach ($msg->entities as $m) {
            $m->clean_up();
        }
    }

    function getMailbox() {
        $msg = $this;
        while (is_object($msg->parent)) {
            $msg = $msg->parent;
        }
        return $msg->mailbox;
    }

    function calcEntity($msg) {
        if (($this->type0 == 'message') && ($this->type1 == 'rfc822')) {
            $msg->entity_id = $this->entity_id .'.0'; /* header of message/rfc822 */
        } else if (isset($this->entity_id) && ($this->entity_id != '')) {
            $entCount = count($this->entities) + 1;
            $par_ent = substr($this->entity_id, -2);
            if ($par_ent{0} == '.') {
                $par_ent = $par_ent{1};
            }
            if ($par_ent == '0') {
                if ($entCount > 0) {
                    $ent = substr($this->entity_id, 0, strrpos($this->entity_id, '.'));
                    $ent = ($ent ? $ent . '.' : '') . $entCount;
                    $msg->entity_id = $ent;
                } else {
                    $msg->entity_id = $entCount;
                }
            } else {
                $ent = $this->entity_id . '.' . $entCount;
                $msg->entity_id = $ent;
            }
        } else {
            $msg->entity_id = '0';
        }

        return $msg->entity_id;
    }



    /* Function to fill the message structure in case the */
    /* bodystructure is not available NOT FINISHED YET    */
    function parseMessage($read, $type0, $type1) {
        switch ($type0) {
            case 'message':
                $rfc822_header = true;
                $mime_header = false;
                break;
            case 'multipart':
                $rfc822_header = false;
                $mime_header = true;
                break;
            default: return $read;
        }

        for ($i = 1; $i < $count; ++$i) {
            $line = trim($body[$i]);
            if (($mime_header || $rfc822_header) &&
                (preg_match("/^.*boundary=\"?(.+(?=\")|.+).*/i", $line, $reg))) {
                $bnd = $reg[1];
                $bndreg = $bnd;
                $bndreg = str_replace("\\", "\\\\", $bndreg);
                $bndreg = str_replace("?", "\\?", $bndreg);
                $bndreg = str_replace("+", "\\+", $bndreg);
                $bndreg = str_replace(".", "\\.", $bndreg);
                $bndreg = str_replace("/", "\\/", $bndreg);
                $bndreg = str_replace("-", "\\-", $bndreg);
                $bndreg = str_replace("(", "\\(", $bndreg);
                $bndreg = str_replace(")", "\\)", $bndreg);
            } else if ($rfc822_header && $line == '') {
                $rfc822_header = false;
                if ($msg->type0 == 'multipart') {
                    $mime_header = true;
                }
            }

            if ((($line{0} == '-') || $rfc822_header)  && isset($boundaries[0])) {
                $cnt = count($boundaries)-1;
                $bnd = $boundaries[$cnt]['bnd'];
                $bndreg = $boundaries[$cnt]['bndreg'];

                $regstr = '/^--'."($bndreg)".".*".'/';
                if (preg_match($regstr, $line, $reg)) {
                    $bndlen = strlen($reg[1]);
                    $bndend = false;
                    if (strlen($line) > ($bndlen + 3)) {
                        if (($line{$bndlen+2} == '-') && ($line{$bndlen+3} == '-')) {
                            $bndend = true;
                        }
                    }
                    if ($bndend) {
                        /* calc offset and return $msg */
                        //$entStr = CalcEntity("$entStr", -1);
                        array_pop($boundaries);
                        $mime_header = true;
                        $bnd_end = true;
                    } else {
                        $mime_header = true;
                         $bnd_end = false;
                        //$entStr = CalcEntity("$entStr", 0);
                        ++$content_indx;
                    }
                } else {
                    if ($header) { }
                }
            }
        }
    }

    function findDisplayEntity($entity = array(), $alt_order = array('text/plain', 'text/html'), $strict=false) {
        $found = false;
        if ($this->type0 == 'multipart') {
            if($this->type1 == 'alternative') {
                $msg = $this->findAlternativeEntity($alt_order);
                if (count($msg->entities) == 0) {
                    $entity[] = $msg->entity_id;
                } else {
                    $entity = $msg->findDisplayEntity($entity, $alt_order, $strict);
                }
                $found = true;
            } else if ($this->type1 == 'related') { /* RFC 2387 */
                $msgs = $this->findRelatedEntity();
                foreach ($msgs as $msg) {
                    if (count($msg->entities) == 0) {
                        $entity[] = $msg->entity_id;
                    } else {
                        $entity = $msg->findDisplayEntity($entity, $alt_order, $strict);
                    }
                }
                if (count($msgs) > 0) {
                    $found = true;
                }
            } else { /* Treat as multipart/mixed */
                foreach ($this->entities as $ent) {
                    if((strtolower($ent->header->disposition->name) != 'attachment') &&
                       (($ent->type0 != 'message') && ($ent->type1 != 'rfc822'))) {
                        $entity = $ent->findDisplayEntity($entity, $alt_order, $strict);
                        $found = true;
                    }
                }
            }
        } else { /* If not multipart, then just compare with each entry from $alt_order */
            $type = $this->type0.'/'.$this->type1;
            foreach ($alt_order as $alt) {
                if( ($alt == $type) && isset($this->entity_id) ) {
                    if ((count($this->entities) == 0) && 
                        (strtolower($this->header->disposition->name) != 'attachment')) {
                        $entity[] = $this->entity_id;
                        $found = true;
                    }
                }
            }
        }
        if(!$found) {
            foreach ($this->entities as $ent) {
                if((strtolower($ent->header->disposition->name) != 'attachment') &&
                   (($ent->type0 != 'message') && ($ent->type1 != 'rfc822'))) {
                    $entity = $ent->findDisplayEntity($entity, $alt_order, $strict);
                    $found = true;
                }
            }
        }
        if(!$strict && !$found) {
            if (($this->type0 == 'text') &&
                in_array($this->type1, array('plain', 'html', 'message')) &&
                isset($this->entity_id)) {
                if (count($this->entities) == 0) {
                    if (strtolower($this->header->disposition->name) != 'attachment') {
                        $entity[] = $this->entity_id;
                    }
                }
            }
        }

        return $entity;
    }

    function findAlternativeEntity($alt_order) {
        /* If we are dealing with alternative parts then we  */
        /* choose the best viewable message supported by SM. */
        $best_view = 0;
        $entity = array();
        foreach($this->entities as $ent) {
            $type = $ent->header->type0 . '/' . $ent->header->type1;
            if ($type == 'multipart/related') {
                $type = $ent->header->getParameter('type');
            }
            $altCount = count($alt_order);
            for ($j = $best_view; $j < $altCount; ++$j) {
                if (($alt_order[$j] == $type) && ($j >= $best_view)) {
                    $best_view = $j;
                    $entity = $ent;
                }
            }
        }

        return $entity;
    }

    function findRelatedEntity() {
        $msgs = array();

        $entCount = count($this->entities);
        for ($i = 0; $i < $entCount; ++$i) {
            $type = $this->entities[$i]->header->type0.'/'.$this->entities[$i]->header->type1;
            if ($this->header->getParameter('type') == $type) {
                $msgs[] = $this->entities[$i];
            }
        }

        return $msgs;
    }

    function getAttachments($exclude_id=array(), $result = array()) {
        if (($this->type0 == 'message') && ($this->type1 == 'rfc822')) {
            $this = $this->entities[0];
        }

        if (count($this->entities)) {
            foreach ($this->entities as $entity) {
                $exclude = false;

                foreach ($exclude_id as $excl) {
                    if ($entity->entity_id === $excl) {
                        $exclude = true;
                    }
                }

                if (!$exclude) {
                    if (($entity->type0 == 'multipart') &&
                        ($entity->type1 != 'related')) {
                        $result = $entity->getAttachments($exclude_id, $result);
                    } else if ($entity->type0 != 'multipart') {
                        $result[] = $entity;
                    }
                }
            }
        } else {
            $exclude = false;
            foreach ($exclude_id as $excl) {
                $exclude = $exclude || ($this->entity_id == $excl);
            }

            if (!$exclude) {
                $result[] = $this;
            }
        }

        return $result;
    }
    
    function initAttachment($type, $name, $location) {
        $attachment = new Message();
        $mime_header = new MessageHeader();
        $mime_header->setParameter('name', $name);
	$pos = strpos($type, '/');
	if ($pos > 0) {
    	    $mime_header->type0 = substr($type, 0, $pos);
    	    $mime_header->type1 = substr($type, $pos+1);
	} else {
    	    $mime_header->type0 = $type;
	}
	$attachment->att_local_name = $location;
	$disposition = new Disposition('attachment');
	$disposition->properties['filename'] = $name;
	$mime_header->disposition = $disposition;
	$attachment->mime_header = $mime_header;
	$this->entities[]=$attachment;
    }
}

?>
