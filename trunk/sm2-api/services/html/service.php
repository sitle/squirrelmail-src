<?php

/*
 * Zookeeper
 * Copyright (c) 2001 Partridge
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkSvc_html
 *
 * The ZkSvc_html class manages html output.
 */
class ZkSvc_html {

    /* Constants */
    var $name = 'html'; // Module name
    var $ver = '$Id$';

    /* Properties */
    var $zkld;

    var $buffer;        /* Buffered output */
    var $htmlmod;       /* Module handler */
    var $title;         /* Page title */
    var $head_extras;   /* Extra header tags */
    var $bgcolor;       /* Background color */
    var $text;          /* Text color */
    var $link;          /* Link color */
    var $vlink;         /* Visited link color */
    var $alink;         /* Active link color */

    var $tag_options;   /* Array of tag options array */

    /**
     * Create a new ZkSvc_html with the given module.
     *
     * @param array  $options options to pass to ZkAuthHandler
     * @param ?      $zkld ?
     */
    function ZkSvc_html($options, &$zkld) {

        $this->zkld = &$zkld;

        $this->spool = FALSE;
        $this->buffer = '';
        $this->title = 'Default zkHTML Title';
        $this->head_extras = '';
        $this->bgcolor = '#FFFFFF';
        $this->text = '#000000';
        $this->link = '#3300CC';
        $this->vlink = '#993333';
        $this->alink = '#993333';

        /* To know if a tag exists we check that it has got a place in the following array */
        $this->tag_options = array(
            'table' => array(
                'tag_name' => 'table',
                'tag_closed' => TRUE
            ),
            'tr' => array(
                'tag_name' => 'tr',
                'tag_closed' => TRUE
            ),
            'th' => array(
                'tag_name' => 'th',
                'tag_closed' => TRUE
            ),
            'td' => array(
                'tag_name' => 'td',
                'tag_closed' => TRUE
            ),
            'li' => array(
                'tag_name' => 'li',
                'tag_closed' => TRUE
            ),
            'ol' => array(
                'tag_name' => 'ol',
                'tag_closed' => TRUE
            ),
            'form' => array(
                'tag_name' => 'form',
                'tag_closed' => TRUE
            ),
            'input' => array(
                'tag_name' => 'input',
                'tag_closed' => FALSE
            ),
            'br' => array(
                'tag_name' => 'br',
                'tag_closed' => FALSE
            ),
            'textarea' => array(
                'tag_name' => 'textarea',
                'tag_closed' => TRUE
            ),
            'p' => array(
                'tag_name' => 'p',
                'tag_closed' => TRUE
            ),
            'a' => array(
                'tag_name' => 'a',
                'tag_closed' => TRUE
            ),
            'center' => array(
                'tag_name' => 'center',
                'tag_closed' => TRUE
            ),
            'img' => array(
                'tag_name' => 'img',
                'tag_closed' => FALSE
            ),
            'font' => array(
                'tag_name' => 'font',
                'tag_closed' => TRUE
            ),
            'blockquote' => array(
                'tag_name' => 'blockquote',
                'tag_closed' => TRUE
            )
        );
    }

    /**
     * Return the name of this service.
     *
     * @return string the name of this service
     */
    function getServiceName() {
        return($this->name);
    }

    /**
     * Replace the Zookeeper html module loaded for this service. (no modules yet)
     */
    function loadModule(&$module) {
        $this->htmlmod = &$module;
    }

    /**
     * Outputs the buffer and re-initialize it.
     */
    function flush($string = '') {
        echo $this->buffer . $string;
        flush();
        $this->buffer = '';
    }

    /**
     * Builds a header string
     */
    function header($string = '') {
        /* It initializes the buffer. */
        $this->buffer = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">' .
                        "\n<HTML>\n";

        if($this->head_extras != '' || $this->title != '') {

            $this->buffer .= "<HEAD>\n";

            if( $this->title != '' )
                $this->buffer .= "<TITLE>$this->title</TITLE>\n";

            $this->buffer .= "$this->head_extras</HEAD>\n";
        }
        $this->buffer .= "<BODY TEXT=\"$this->text\" BGCOLOR=\"$this->bgcolor\" LINK=\"$this->link\" VLINK=\"$this->vlink\" ALINK=\"$this->alink\">\n";

        /* See if we're asking for a closed strcuture */
        if($string == '') {
            $this->flush();
        } else {
            $this->buffer .= $string . '</body></html>';
        }

    }

    /**
     * Builds a footer string
     */
    function footer() {
        $this->buffer .= "\n</body>\n</html>\n";
        $this->flush();
    }

    /**
     * Builds a tag string
     */
    function tag($tag, $string = '', $options = '') {
        $ret = '';
        if ($this->tag_options[$tag] != NULL) {
            if ($options == '') {
                $options = $this->tag_options[$tag];
            }
            $ret = zkTag_html($tag, $string, $options, $this->tag_options[$tag]['tag_closed']);
        }
        return ($ret);
    }

    /**
     * Builds a header string
     */
    function h($string, $level = '1') {
        $buffer = "<h$level>";

        /* See if we're asking for a closed strcuture */
        if ($string == '') {
            $this->$buffer .= $buffer;
        } else {
            $buffer .= $string . "</h$level>";
        }
        return ($buffer);
    }

}

/**
 * Converts an array into a parameters tag list.
 */
function zkGetParms_html($parms) {
    $buffer = '';
    foreach ($parms as $key => $opt) {
        if (substr($key, 0, 3) != 'tag') {
            $buffer .= " $key";
            if ($opt != '' ) {
                $buffer .= "=\"$opt\"";
            }
        }
    }
    return ($buffer);
}

/**
 * Composes a tag string with all its parameters.
 */
function zkTag_html($tag, $string, $options, $closed) {
    $ret = "<$tag" .
            zkGetParms_html($options) .
            '>' .
            $string;

    if ($closed)
        $ret .= "</$tag>";

    return ($ret);
}

/**
 * Builds an HTML drop-down list of options
 */
function optionize($name, $opts, $default) {
    $ret = "<select name=\"$name\">";

    foreach ($opts as $key => $opt) {
        $chk = ($opt == $default ? ' SELECTED' : '');
        $ret .= "<option value=\"$opt\"$chk>$opt</option>";
    }

    $ret .= '</select>';
    return ($ret);
}

?>