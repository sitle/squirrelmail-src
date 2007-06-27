<?php
/*******************


HORRIBLE UGLY SQM CONFIG PHP

quick and dirty port of conf.pl

organization of this file (4 parts):
I. INIT - initialization
II. FUNCTIONS - functions defined here
III. POST - handle config data from POST
IV. INTERFACE - output menu/panel interface

things to do:
-more elegant interface
    -better handling of conditional settings
    -better handling of (multiple) selection settings
-add predefined imap srv settings
-finish adding panels
-sanity check input
-internationalization
-add SQMConfigDB capabilities
-create secure method to output config file
    -cannot simply overwrite conf.php
    -take into account web server uid/gid privs


**********************/


/*********************

I. INIT

**********************/

require("config_class.php");
require("../functions/global.php");

if ( file_exists("/tmp/conf.php") ) {
    $sqmConf = new SQMConfigFile("/tmp/conf.php");
    $status_msg .= "Read temp configuration (/tmp/conf.php). ";
} elseif ( file_exists("conf.php") ) {
    $sqmConf = new SQMConfigFile("conf.php");
    $status_msg .= "Read site configuation (conf.php). ";
} elseif ( file_exists("conf_default.php") ) {
    $sqmConf = new SQMConfigFile("conf_default.php");
    $status_msg .= "Read default configuration (conf_default.php). ";
} else {
    $sqmConf = new SQMConfigFile();
    $status_msg .= "No configuration file found. ";
}

/******************

II. FUNCTIONS

functions reside below

******************/



/******************

CODE FOR CONFIG.PHP

-below code can be used to import old config.php
-code is only partially complete, needs further modification
-variables will be loaded locally into the function as they
 appear in the config.php file
-for the most part we should use SQMConfig class

*******************/

function oldConfig() {
    $confFile = fopen("config.php",r);
    if ( !$confFile ) {
        $confFile = fopen("config_default.php",r);
        if ( !$confFile ) {
            print "<font color=red>ERROR: No config_default.php or config.php found</font>";
        }
    }
       while ( $line = fgets($confFile) ) {
           $line = preg_replace('/^\s+/','',$line);
           $line = preg_replace('/^\$/','',$line);
           $var = $line;
           
           $var = preg_replace('/=/','EQUALS',$var);
           
           if ( preg_match('/^([a-z])/i',$var) ) {
                   $options = preg_split('/\s*EQUALS\s*/',$var);
                   $options[1] = preg_replace('/[\n\r]/','',$options[1]);
                   $options[1] = preg_replace('/[\'\"];\s*$/','',$options[1]);
                   $options[1] = preg_replace('/;$/','',$options[1]);
                   $options[1] = preg_replace('/^[\'\"]/','',$options[1]);
                   $options[1] = preg_replace("/\\'/","'",$options[1]);
                   $options[1] = preg_replace('/\\\\/','\\',$options[1]);

                   if ( preg_match('/^user_themes\[[0-9]+\]\[[\'"]PATH[\'"]\]/',$options[0]) ) {
                       $sub = $options[0];
                       $sub = preg_replace('/\]\[[\'"]PATH[\'"]\]/','',$sub);
                       $sub = preg_replace('/.*\[/','',$sub);
                       $options[1] = preg_replace('/^\.\.\/config/','../css',$options[1]);
                       $user_theme_path[$sub] = change_to_rel_path($options[1]);
                   } elseif ( preg_match('/^user_themes\[[0-9]+\]\[[\'"]NAME[\'"]\]/',$options[0]) ) {
                       $sub = $options[0];
                       $sub = preg_replace('/\]\[[\'"]NAME[\'"]\]/','',$sub);
                       $sub = preg_replace('/.*\[/','',$sub);
                       $user_theme_name[$sub] = $options[1];
                   } elseif ( preg_match('/^icon_themes\[[0-9]+\]\[[\'"]PATH[\'"]\]/',$options[0]) ) {
                       $sub = $options[0];
                       $sub = preg_replace('/\]\[[\'"]PATH[\'"]\]/','',$sub);
                       $sub = preg_replace('/.*\[/','',$sub);
                       $options[1] = preg_replace('/^\.\.\/config/','../images',$options[1]);
                       $icon_theme_path[$sub] = change_to_rel_path($options[1]);
                   } elseif ( preg_match('/^icon_themes\[[0-9]+\]\[[\'"]NAME[\'"]\]/',$options[0]) ) {
                       $sub = $options[0];
                       $sub = preg_replace('/\]\[[\'"]NAME[\'"]\]/','',$sub);
                       $sub = preg_replace('/.*\[/','',$sub);
                       $icon_theme_name[$sub] = $options[1];
                   } elseif ( preg_match('/^aTemplateSet\[[0-9]+\]\[[\'"]ID[\'"]\]/',$options[0]) ) {
                       $sub = $options[0];
                       $sub = preg_replace('/\]\[[\'"]ID[\'"]\]/','',$sub);
                       $sub = preg_replace('/.*\[/','',$sub);
                       $options[1] = preg_replace('/^\.\.\/config/','../templates',$options[1]);
                       $templateset_id[$sub] = $options[1];
                   } elseif ( preg_match('/^aTemplateSet\[[0-9]+\]\[[\'"]NAME[\'"]\]/',$options[0]) ) {
                       $sub = $options[0];
                       $sub = preg_replace('/\]\[[\'"]NAME[\'"]\]/','',$sub);
                       $sub = preg_replace('/.*\[/','',$sub);
                       $templateset_name[$sub] = $options[1];
                   } elseif ( preg_match('/^plugins\[[0-9]*\]/',$options[0]) ) {
                       $sub = $options[0];
                       $sub = preg_replace('/\]/','',$sub);
                       $sub = preg_replace('/^plugins\[/','',$sub);
                       if ( !$sub ) {
                           array_push($plugins, $options[1]);
                       } else {
                           $plugins[$sub] = $options[1];
                       }
                   } elseif ( preg_match("/^fontsets\[\'[a-z]*\'\]/",$options[0]) ) {
                       $sub = $options[0];
                       $sub = preg_replace("/\'\]/",'',$sub);
                       $sub = preg_replace("/^fontsets\[\'/",'',$sub);
                       $fontsets[$sub] = $options[1];
                   } elseif ( preg_match('/^fontsets$/',$options[0]) ) {
                   } elseif ( preg_match('/^theme\[[0-9]+\]\[[\'"]PATH|NAME[\'"]\]/',$options[0]) ) {
                   } elseif ( preg_match('/^ldap_server\[[0-9]+\]/',$options[0]) ) {
                       $sub = $options[0];
                       $sub = preg_replace('/\]/','',$sub);
                       $sub = preg_replace('/^ldap_server\[/','',$sub);
                       $continue = 0;

/******** FIX ME LDAP
            while ( ( $tmp = <FILE> ) && ( $continue != 1 ) ) {
                if ( $tmp =~ /\);\s*$/ ) {
                    $continue = 1;
                }

                if ( $tmp =~ /^\s*[\'\"]host[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]host[\'\"]\s*=>\s*[\'\"]//i;
                    $tmp =~ s/[\'\"],?\s*$//;
                    $tmp =~ s/[\'\"]\);\s*$//;
                    $host = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]base[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]base[\'\"]\s*=>\s*[\'\"]//i;
                    $tmp =~ s/[\'\"],?\s*$//;
                    $tmp =~ s/[\'\"]\);\s*$//;
                    $base = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]charset[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]charset[\'\"]\s*=>\s*[\'\"]//i;
                    $tmp =~ s/[\'\"],?\s*$//;
                    $tmp =~ s/[\'\"]\);\s*$//;
                    $charset = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]port[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]port[\'\"]\s*=>\s*[\'\"]?//i;
                    $tmp =~ s/[\'\"]?,?\s*$//;
                    $tmp =~ s/[\'\"]?\);\s*$//;
                    $port = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]maxrows[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]maxrows[\'\"]\s*=>\s*[\'\"]?//i;
                    $tmp =~ s/[\'\"]?,?\s*$//;
                    $tmp =~ s/[\'\"]?\);\s*$//;
                    $maxrows = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]filter[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]filter[\'\"]\s*=>\s*[\'\"]?//i;
                    $tmp =~ s/[\'\"]?,?\s*$//;
                    $tmp =~ s/[\'\"]?\);\s*$//;
                    $filter = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]name[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]name[\'\"]\s*=>\s*[\'\"]//i;
                    $tmp =~ s/[\'\"],?\s*$//;
                    $tmp =~ s/[\'\"]\);\s*$//;
                    $name = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]binddn[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]binddn[\'\"]\s*=>\s*[\'\"]//i;
                    $tmp =~ s/[\'\"],?\s*$//;
                    $tmp =~ s/[\'\"]\);\s*$//;
                    $binddn = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]bindpw[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]bindpw[\'\"]\s*=>\s*[\'\"]//i;
                    $tmp =~ s/[\'\"],?\s*$//;
                    $tmp =~ s/[\'\"]\);\s*$//;
                    $bindpw = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]protocol[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]protocol[\'\"]\s*=>\s*[\'\"]?//i;
                    $tmp =~ s/[\'\"]?,?\s*$//;
                    $tmp =~ s/[\'\"]?\);\s*$//;
                    $protocol = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]limit_scope[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]limit_scope[\'\"]\s*=>\s*[\'\"]?//i;
                    $tmp =~ s/[\'\"]?,?\s*$//;
                    $tmp =~ s/[\'\"]?\);\s*$//;
                    $limit_scope = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]listing[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]listing[\'\"]\s*=>\s*[\'\"]?//i;
                    $tmp =~ s/[\'\"]?,?\s*$//;
                    $tmp =~ s/[\'\"]?\);\s*$//;
                    $listing = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]writeable[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]writeable[\'\"]\s*=>\s*[\'\"]?//i;
                    $tmp =~ s/[\'\"]?,?\s*$//;
                    $tmp =~ s/[\'\"]?\);\s*$//;
                    $writeable = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]search_tree[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]search_tree[\'\"]\s*=>\s*[\'\"]?//i;
                    $tmp =~ s/[\'\"]?,?\s*$//;
                    $tmp =~ s/[\'\"]?\);\s*$//;
                    $search_tree = $tmp;
                } elsif ( $tmp =~ /^\s*[\'\"]starttls[\'\"]/i ) {
                    $tmp =~ s/^\s*[\'\"]starttls[\'\"]\s*=>\s*[\'\"]?//i;
                    $tmp =~ s/[\'\"]?,?\s*$//;
                    $tmp =~ s/[\'\"]?\);\s*$//;
                    $starttls = $tmp;
                }
            }
            $ldap_host[$sub]    = $host;
            $ldap_base[$sub]    = $base;
            $ldap_name[$sub]    = $name;
            $ldap_port[$sub]    = $port;
            $ldap_maxrows[$sub] = $maxrows;
            $ldap_filter[$sub]  = $filter;
            $ldap_charset[$sub] = $charset;
            $ldap_binddn[$sub]  = $binddn;
            $ldap_bindpw[$sub]  = $bindpw;
            $ldap_protocol[$sub] = $protocol;
            $ldap_limit_scope[$sub] = $limit_scope;
            $ldap_listing[$sub] = $listing;
            $ldap_writeable[$sub] = $writeable;
            $ldap_search_tree[$sub] = $search_tree;
            $ldap_starttls[$sub] = $starttls;
************/

                     } elseif ( preg_match('/^(data_dir|attachment_dir|org_logo|signout_page|icon_theme_def)$/',$options[0]) ) {
                         ${ $options[0] } = change_to_rel_path($options[1]);
                     } else {
                         ${ $options[0] } = $options[1];
                     }
            }
    }

fclose($confFile);
}


/******************

change_to_rel_path is used to compute the appropriate
relative path on the server based on SM_PATH

*******************/

function change_to_rel_path($oldPath) {

    $newPath = $oldPath;

    if ( preg_match('/^SM_PATH/',$oldPath) ) {
        $newPath = preg_replace("/^SM_PATH . \'/",'..',$oldPath);
        $newPath = preg_replace("/\.\.\/config\//",'',$newPath);
    }
    
    return $newPath;
}

/*****************

# This subroutine corrects relative paths to ensure they
# will work within the SM space. If the path falls within
# the SM directory tree, the SM_PATH variable will be
# prepended to the path, if not, then the path will be
# converted to an absolute path, e.g.
#   '../images/logo.gif'      --> SM_PATH . 'images/logo.gif'
#   '../../someplace/data'    --> '/absolute/path/someplace/data'
#   'images/logo.gif'         --> SM_PATH . 'config/images/logo.gif'
#   '/absolute/path/logo.gif' --> '/absolute/path/logo.gif'
#   'http://whatever/'        --> 'http://whatever'
#   $some_var/path            --> "$some_var/path"

*******************/


function change_to_SM_path($oldPath) {

    global $_SERVER;

    if ( $oldPath == '') { return "'".$oldPath."'"; }
    if ( preg_match('/^(\/|http)/',$oldPath) ) { return "'".$oldPath."'"; }
    if ( preg_match('/^\w:\//',$oldPath) ) { return "'".$oldPath."'"; }
    if ( preg_match('/^\'(\/|http)/',$oldPath) ) { return $oldPath; }
    if ( preg_match('/^\'\w:\//',$oldPath) ) { return $oldPath; }
    if ( preg_match('/^SM_PATH/',$oldPath) ) { return $oldPath; }
    
    if ( preg_match('/^\$/',$oldPath) ) {
        if ( preg_match('/\//',$oldPath) ) {
            return '"'.$oldPath.'"';
        }
        return $oldPath;
    }

    $oldPath = str_replace("'","",$oldPath);
    
    
    $relPath = array();
    
    $relPath = explode("../",$oldPath);
    
    if ( count($relPath) > 1 ) {
    
        $absPath = array();
        $absPath = explode("/",$_SERVER['PATH_TRANSLATED']);
        
        for ( $c = 0; $c <= count($relPath); $c++) {
            array_pop($absPath);
            array_shift($relPath);
        }
        
        $absPath = array_merge($absPath,$relPath);
        
        $newPath = "'".implode('/',$absPath)."'";
    } elseif ( count($relPath) > 0 ) {
        $newPath = $oldPath;
        $newPath = preg_replace('/^\.\.\/','SM_PATH . \'',$newPath);
        $newPath .= "'";
    } else {
        $newPath = "SM_PATH . 'config/$oldPath'";
    }
    
    return $newPath;
}

function tOrF($argmnt) {

    if ( $argmnt == TRUE ) { return "true"; }
    else { return "false"; }

}

/********************

write_config() writes the configuration
data to a file

-currently /tmp/ is hardcoded and this
 needs to be fixed

********************/

function write_config() {

    global $status_msg;
    global $sqmConf;

    if ( !is_writable("/tmp/") && !file_exists("/tmp/conf.php") ) {
        $status_msg .= "ERROR: /tmp/conf.php is not writable with current permissions. ";
    } else {

        $confOut = fopen('/tmp/conf.php','w');
        if ( !isset($confOut) ) {
            $status_msg .= "ERROR: could not open /tmp/conf.php for writing. ";
        } else {

            $header = "<?php\n\n/**\n * SquirrelMail Configuration File\n * Created using make_conf.php\n**/\n\n";

            fwrite($confOut,$header);
            
            $orgprefs = "\$this->org_name      = \"$sqmConf->org_name\";\n";
            $orgprefs .= "\$this->org_logo      = ".change_to_SM_path($sqmConf->org_logo).";\n";
            if ( $sqmConf->org_logo_width == '' ) { $sqmConf->org_logo_width = 0; }
            if ( $sqmConf->org_logo_height == '' ) { $sqmCOnf->org_logo_height = 0; }
            $orgprefs .= "\$this->org_logo_width  = '$sqmConf->org_logo_width';\n";
            $orgprefs .= "\$this->org_logo_height = '$sqmConf->org_logo_height';\n";
            $orgprefs .= "\$this->org_title     = \"$sqmConf->org_title\";\n";
            $orgprefs .= "\$this->signout_page  = ".change_to_SM_path($sqmConf->signout_page).";\n";
            $orgprefs .= "\$this->frame_top     = '$sqmConf->frame_top';\n\n";
            $orgprefs .= "\$this->provider_uri  = '$sqmConf->provider_uri';\n\n";
            $orgprefs .= "\$this->provider_name = '$sqmConf->provider_name';\n\n";
            
            fwrite($confOut,$orgprefs);
            
            $motd = "\$this->motd = \"$sqmConf->motd\";\n\n";
            
            fwrite($confOut,$motd);
            
            $langsetts = "";
            
            fwrite($confOut,$langsetts);
            
            $serversetts = "\$this->domain                 = '$sqmConf->domain';\n";
            $serversetts .= "\$this->imapServerAddress      = '$sqmConf->imapServerAddress';\n";
            $serversetts .= "\$this->imapPort               = $sqmConf->imapPort;\n";
            $serversetts .= "\$this->useSendmail            = ".tOrF($sqmConf->useSendmail).";\n";
            $serversetts .= "\$this->smtpServerAddress      = '$sqmConf->smtpServerAddress';\n";
            $serversetts .= "\$this->smtpPort               = $sqmConf->smtpPort;\n";
            $serversetts .= "\$this->sendmail_path          = '$sqmConf->sendmail_path';\n";
            $serversetts .= "\$this->sendmail_args          = '$sqmConf->sendmail_args';\n";
            $serversetts .= "\$this->pop_before_smtp        = ".tOrF($sqmConf->pop_before_smtp).";\n";
            $serversetts .= "\$this->imap_server_type       = '$sqmConf->imap_server_type';\n";
            $serversetts .= "\$this->invert_time            = ".tOrF($sqmConf->invert_time).";\n";
            $serversetts .= "\$this->optional_delimiter     = '$sqmConf->optional_delimiter';\n";
            $serversetts .= "\$this->encode_header_key      = '$sqmConf->encode_header_key';\n\n";
            
            fwrite($confOut,$serversetts);
            
            $folddefaults = "\$this->default_folder_prefix          = '$sqmConf->default_folder_prefix';\n";
            $folddefaults .= "\$this->trash_folder                   = '$sqmConf->trash_folder';\n";
            $folddefaults .= "\$this->sent_folder                    = '$sqmConf->sent_folder';\n";
            $folddefaults .= "\$this->draft_folder                   = '$sqmConf->draft_folder';\n";
            $folddefaults .= "\$this->default_move_to_trash          = ".tOrF($sqmConf->default_move_to_trash).";\n";
            $folddefaults .= "\$this->default_move_to_sent           = ".tOrF($sqmConf->default_move_to_sent).";\n";
            $folddefaults .= "\$this->default_save_as_draft          = ".tOrF($sqmConf->default_save_as_draft).";\n";
            $folddefaults .= "\$this->show_prefix_option             = ".tOrF($sqmConf->show_prefix_option).";\n";
            $folddefaults .= "\$this->list_special_folders_first     = ".tOrF($sqmConf->list_special_folders_first).";\n";
            $folddefaults .= "\$this->use_special_folder_color       = ".tOrF($sqmConf->use_special_folder_color).";\n";
            $folddefaults .= "\$this->auto_expunge                   = ".tOrF($sqmConf->auto_expunge).";\n";
            $folddefaults .= "\$this->default_sub_of_inbox           = ".tOrF($sqmConf->default_sub_of_inbox).";\n";
            $folddefaults .= "\$this->show_contain_subfolders_option = ".tOrF($sqmConf->show_contain_subfolders_option).";\n";
            $folddefaults .= "\$this->default_unseen_notify          = $sqmConf->default_unseen_notify;\n";
            $folddefaults .= "\$this->default_unseen_type            = $sqmConf->default_unseen_type;\n";
            $folddefaults .= "\$this->auto_create_special            = ".tOrF($sqmConf->auto_create_special).";\n";
            $folddefaults .= "\$this->delete_folder                  = ".tOrF($sqmConf->delete_folder).";\n";
            $folddefaults .= "\$this->noselect_fix_enable            = ".tOrF($sqmConf->noselect_fix_enable).";\n\n";
            
            fwrite($confOut,$folddefaults);
            
            $genoptions = "\$this->data_dir                 = ".change_to_SM_path($sqmConf->data_dir).";\n";
            $genoptions .= "\$this->attachment_dir           = ".change_to_SM_path($sqmConf->attachment_dir).";\n";
            $genoptions .= "\$this->dir_hash_level           = $sqmConf->dir_hash_level;\n";
            $genoptions .= "\$this->default_left_size        = '$sqmConf->default_left_size';\n";
            $genoptions .= "\$this->force_username_lowercase = ".tOrF($sqmConf->force_username_lowercase).";\n";
            $genoptions .= "\$this->default_use_priority     = ".tOrF($sqmConf->default_use_priority).";\n";
            $genoptions .= "\$this->hide_sm_attributions     = ".tOrF($sqmConf->hide_sm_attributions).";\n";
            $genoptions .= "\$this->default_use_mdn          = ".tOrF($sqmConf->default_use_mdn).";\n";
            $genoptions .= "\$this->edit_identity            = ".tOrF($sqmConf->edit_identity).";\n";
            $genoptions .= "\$this->edit_name                = ".tOrF($sqmConf->edit_name).";\n";
            $genoptions .= "\$this->hide_auth_header         = ".tOrF($sqmConf->hide_auth_header).";\n";
            $genoptions .= "\$this->disable_thread_sort      = ".tOrF($sqmConf->disable_thread_sort).";\n";
            $genoptions .= "\$this->disable_server_sort      = ".tOrF($sqmConf->disable_server_sort).";\n";
            $genoptions .= "\$this->allow_charset_search     = ".tOrF($sqmConf->allow_charset_search).";\n";
            $genoptions .= "\$this->allow_advanced_search    = $sqmConf->allow_advanced_search;\n\n";
            
            $genoptions .= "\$this->time_zone_type           = $sqmConf->time_zone_type;\n\n";
            
            $genoptions .= "\$this->config_location_base     = '$sqmConf->config_location_base';\n\n";
            
            fwrite($confOut,$genoptions);
            
            $disableplugins = "\$this->disable_plugins          = ".tOrF($sqmConf->disable_plugins).";\n";
            $disableplugins .= "\$this->disable_plugins_user     = '$sqmConf->disable_plugins_user';\n\n\n";
            
            fwrite($confOut,$disableplugins);
            
            
            for($c=0;$c<count($sqmConf->plugins);$c++) {
                $plugins .= "\$this->plugins[] = '$sqmConf->plugins[$c]';\n";
            }
            
            $plugins .= "\n";
            
            fwrite($confOut,$plugins);
            
            
            if($sqmConf->user_theme_default == '') { $sqmConf->user_theme_default = '0'; }
            $userthemes = "\$this->user_theme_default = $sqmConf->user_theme_default;\n";
            
            for($c=0;$c<=count($sqmConf->user_theme_name);$c++) {
                if ( $sqmConf->user_theme_path[$c] == 'none') {
                    $path = '\'none\'';
                } else {
                    $path = change_to_SM_path($sqmConf->user_theme_path[$c]);
                }
                $userthemes .= "\$this->user_themes[$c]['PATH'] = $path;\n";
                
                $esc_name = $sqmConf->user_theme_name[$c];
                $esc_name = str_replace('\\','\\\\',$esc_name);
                $esc_name = str_replace("'","\\'",$esc_name);
                
                $userthemes .= "\$this->user_themes[$c]['NAME'] = '$esc_name';\n";
            }
            
            $userthemes .= "\n";
            
            fwrite($confOut,$userthemes); 
            
            if ( $sqmConf->icon_theme_def == '' ) { $sqmConf->icon_theme_def = '0'; }
            if ( $sqmConf->icon_theme_fallback == '' ) { $sqmConf->icon_theme_fallback = '0'; }
            
            $iconthemes = "\$this->icon_theme_def = $sqmConf->icon_theme_def;\n";
            $iconthemes .= "\$this->icon_theme_fallback = $sqmConf->icon_theme_fallback;\n";
            
            for ( $c = 0 ; $c <= count($sqmConf->icon_theme_name) ; $c++ ) {
                $path = $sqmConf->icon_theme_path[$c];
                if ( $path == 'none' || $path == 'template' ) {
                    $path = "'$path'";
                } else {
                    $path = change_to_SM_path($sqmConf->icon_theme_path[$c]);
                }
                
                $iconthemes .= "\$this->icon_themes[$c]['PATH'] = $path;\n";
                
                $esc_name = $sqmConf->icon_theme_name[$c];
                $esc_name = str_replace('\\','\\\\',$esc_name);
                $esc_name = str_replace("'","\\'",$esc_name);
                
                $iconthemes .= "\$this->icon_themes[$c]['NAME'] = '$esc_name';\n";
            }
            
            $iconthemes .= "\n";
            
            fwrite($confOut,$iconthemes);
            
            if ( $sqmConf->templateset_default == '' ) { $sqmConf->templateset_default = 'default'; }
            if ( $sqmConf->templateset_fallback == '' ) { $sqmConf->templateset_fallback = 'default'; }
            
            $templateset = "\$this->templateset_default = '$sqmConf->templateset_default';\n";
            $templateset .= "\$this->templateset_fallback = '$sqmConf->templateset_fallback';\n";
            
            for ( $c = 0 ; $c <= count($sqmConf->templateset_name) ; $c++) {
                $templateset .= "\$this->aTemplateSet[$c]['ID'] = '$sqmConf->templateset_id[$c]';\n";
                
                $esc_name = $sqmConf->templateset_name[$c];
                $esc_name = str_replace('\\','\\\\',$esc_name);
                $esc_name = str_replace("'","\\'",$esc_name);
                
                $templateset .= "\$this->aTemplateSet[$c]['NAME'] = '$esc_name';\n";
            }
            
            $templateset .= "\n";
            
            fwrite($confOut,$templateset);

            $defaultfont = "\$this->default_fontsize = '$sqmConf->default_fontsize';\n";
            $defaultfont .= "\$this->default_fontset = '$sqmConf->default_fontset';\n\n";
            
            $defaultfont .= "\$this->fontsets = array();\n";
            
            $fontsets = $sqmConf->fontsets;

            while ( $fontData = each($fontsets) ) {
                $defaultfont .= "\$this->fontsets['$fontData[0]'] = '$fontData[1]';\n";
            }
            
            $defaultfont .= "\n";
            
            fwrite($confOut,$defaultfont);
            
            $addbooks = "\$this->default_use_javascript_addr_book = ".tOrF($sqmConf->default_use_javascript_addr_book).";\n";
            
            for ( $c = 0 ; $c <= count($sqmConf->ldap_host) ; $c++ ) {
                $addbooks .= "\$this->ldap_server[$c] = array(\n";
                $addbooks .= "    'host' => '$sqmConf->ldap_host[$c]',\n";
                $addbooks .= "    'base' => '$sqmConf->ldap_base[$c]'";
                if ( $sqmConf->ldap_name[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'name' => '$sqmConf->ldap_name[$c]'";
                }
                if ( $sqmConf->ldap_port[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'port' => $sqmConf->ldap_port[$c]";
                }
                if ( $sqmConf->ldap_charset[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'charset' => '$sqmConf->ldap_charset[$c]'";
                }
                if ( $sqmConf->ldap_maxrows[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'maxrows' => $sqmConf->ldap_maxrows[$c]";
                }
                if ( $sqmConf->ldap_filter[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'filter' => '$sqmConf->ldap_filter[$c]'";
                }
                if ( $sqmConf->ldap_binddn[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'binddn' => '$sqmConf->ldap_binddn[$c]'";
                    if ( $sqmConf->ldap_bindpw[$c] ) {
                        $addbooks .= ",\n";
                        $addbooks .= "    'bindpw' => '$sqmConf->ldap_bindpw[$c]'";
                    }
                }
                if ( $sqmConf->ldap_protocol[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'protocol' => $sqmConf->ldap_protocol[$c]";
                }
                if ( $sqmConf->ldap_limit_scope[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'limit_scope' => ".tOrF($sqmConf->ldap_limit_scope[$c]);
                }
                if ( $sqmConf->ldap_listing[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'listing' => ".tOrF($sqmConf->ldap_listing[$c]);
                }
                if ( $sqmConf->ldap_writeable[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'writeable' => ".tOrF($sqmConf->ldap_writeable[$c]);
                }
                if ( $sqmConf->ldap_search_tree[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'search_tree' => $sqmConf->ldap_search_tree[$c]";
                }
                if ( $sqmConf->ldap_listing[$c] ) {
                    $addbooks .= ",\n";
                    $addbooks .= "    'starttls' => $sqmConf->ldap_starttls[$c]";
                }
                
                $addbooks .= "\n);\n\n"
                
            }
            
            $addbooks .= "\$this->addrbook_dsn = '$sqmConf->addrbook_dsn';\n";
            $addbooks .= "\$this->addrbook_table = '$sqmConf->addrbook_table';\n\n";
            
            fwrite($confOut,$addbooks);
            
            



/********************************


        # string
        print CF "\$prefs_dsn = '$prefs_dsn';\n";
        # string
        print CF "\$prefs_table = '$prefs_table';\n";
        # string
        print CF "\$prefs_user_field = '$prefs_user_field';\n";
        # integer
        print CF "\$prefs_user_size = $prefs_user_size;\n";
        # string
        print CF "\$prefs_key_field = '$prefs_key_field';\n";
        # integer
        print CF "\$prefs_key_size = $prefs_key_size;\n";
        # string
        print CF "\$prefs_val_field = '$prefs_val_field';\n";
        # integer
        print CF "\$prefs_val_size = $prefs_val_size;\n\n";
        # string
        print CF "\$addrbook_global_dsn = '$addrbook_global_dsn';\n";
        # string
        print CF "\$addrbook_global_table = '$addrbook_global_table';\n";
        # boolean
        print CF "\$addrbook_global_writeable = $addrbook_global_writeable;\n";
        # boolean
        print CF "\$addrbook_global_listing = $addrbook_global_listing;\n\n";
        # string
        print CF "\$abook_global_file = '$abook_global_file';\n";
        # boolean
        print CF "\$abook_global_file_writeable = $abook_global_file_writeable;\n\n";
        # boolean
        print CF "\$abook_global_file_listing = $abook_global_file_listing;\n\n";
        # integer
        print CF "\$abook_file_line_length = $abook_file_line_length;\n\n";
        # boolean
        print CF "\$no_list_for_subscribe = $no_list_for_subscribe;\n";

        # string
        print CF "\$smtp_auth_mech        = '$smtp_auth_mech';\n";
        print CF "\$smtp_sitewide_user    = '". quote_single($smtp_sitewide_user) ."';\n";
        print CF "\$smtp_sitewide_pass    = '". quote_single($smtp_sitewide_pass) ."';\n";
        # string
        print CF "\$imap_auth_mech        = '$imap_auth_mech';\n";
        # boolean
        print CF "\$use_imap_tls          = $use_imap_tls;\n";
        # boolean
        print CF "\$use_smtp_tls          = $use_smtp_tls;\n";
        # string
        print CF "\$session_name          = '$session_name';\n";
        # boolean
        print CF "\$only_secure_cookies   = $only_secure_cookies;\n";

        print CF "\n";

        # boolean
        print CF "\$use_iframe = $use_iframe;\n";
        # boolean
        print CF "\$ask_user_info = $ask_user_info;\n";
        # boolean
        print CF "\$use_icons = $use_icons;\n";
        print CF "\n";
        # boolean
        print CF "\$use_php_recode = $use_php_recode;\n";
        # boolean
        print CF "\$use_php_iconv = $use_php_iconv;\n";
        print CF "\n";
        # boolean
        print CF "\$allow_remote_configtest = $allow_remote_configtest;\n";
        print CF "\n";

        close CF;

        print "Data saved in config.php\n";

        build_plugin_hook_array();


************************/







            $status_msg .= "Wrote configuration file /tmp/conf.php. ";
        }
    }

}

/**************************

III. POST DATA PROCESSING

**************************/

if ( sqGetGlobalVar('saveConf',$saveConf,SQ_POST) ) {

//print $_SERVER['HTTP_REFERER'];

    sqGetGlobalVar('HTTP_REFERER',$refPage,SQ_SERVER);
    $refPage = preg_replace('/^.*\?/','',$refPage);
//print $refPage;
    if ( $refPage == "OrgPrefs" ) {
        $org_name_old = $sqmConf->org_name;
        $org_logo_old = $sqmConf->org_logo;
        $org_logo_width_old = $sqmConf->org_logo_width;
        $org_logo_height_old = $sqmConf->org_logo_height;
        $org_title_old = $sqmConf->org_title;
        $signout_page_old = $sqmConf->signout_page;
        $frame_top_old = $sqmConf->frame_top;
        $provider_uri_old = $sqmConf->provider_uri;
        $provider_name_old = $sqmConf->provider_name;
        sqGetGlobalVar('org_name',$sqmConf->org_name,SQ_POST);
        sqGetGlobalVar('org_logo',$sqmConf->org_logo,SQ_POST);
        sqGetGlobalVar('org_logo_width',$sqmConf->org_logo_width,SQ_POST);
        sqGetGlobalVar('org_logo_height',$sqmConf->org_logo_height,SQ_POST);
        sqGetGlobalVar('org_title',$sqmConf->org_title,SQ_POST);
        sqGetGlobalVar('frame_top',$sqmConf->frame_top,SQ_POST);
        sqGetGlobalVar('provider_uri',$sqmConf->provider_uri,SQ_POST);
        sqGetGlobalVar('provider_name',$sqmConf->provider_name,SQ_POST);

        if ( $sqmConf->org_name == '' ) {
            $sqmConf->org_name = $org_name_old;
        } else {
            $sqmConf->org_name = preg_replace('/\"/','&quot;',$sqmConf->org_name);
        }

        if ( $sqmConf->org_logo == '' ) {
            $sqmConf->org_logo = $org_logo_old;
        }

        $sqmConf->org_logo_width = preg_replace('/[^0-9]/','',$sqmConf->org_logo_width);
        if ( $sqmConf->org_logo_width == '' ) {
            $sqmConf->org_logo_width = $org_logo_width_old;
        }

        $sqmConf->org_logo_height = preg_replace('/[^0-9]/','',$sqmConf->org_logo_height);
        if ( $sqmConf->org_logo_height == '' ) {
            $sqmConf->org_logo_width = $org_logo_height_old;
        }

        if ( $sqmConf->org_title == '' ) {
            $sqmConf->org_title = $org_title_old;
        } else {
            $sqmConf->org_title = preg_replace('/\"/','\'',$sqmConf->org_title);
        }

        if ( $sqmConf->signout_page == '' ) {
            $sqmConf->signout_page = $signout_page_old;
        } else {
            $sqmConf->signout_page = preg_replace('/^\s+$/','',$sqmConf->signout_page);
        }
        
        if ( $sqmConf->frame_top == '' ) {
            $sqmConf->frame_top = '_top';
        } else {
            $sqmConf->frame_top = preg_replace('/^\s+$/','',$sqmConf->frame_top);
        }

        if ( $sqmConf->provider_uri != '' ) {
            $sqmConf->provider_uri = preg_replace('/^\s+$/','',$sqmConf->provider_uri);
        }

        if ( $sqmConf->provider_name != '' ) {
            $sqmConf->provider_name = preg_replace('/^\s+$/','',$sqmConf->provider_name);
            $sqmConf->provider_name = preg_replace("/\'/","\\'",$sqmConf->provider_name);
        }

        $status_msg .= "Saved Organization preferences. ";

        write_config();
    }

}

/**************************

IV. INTERFACE OUTPUT

***************************/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en_US">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" ></meta>
<title>SquirrelMail Configuration</title>
</head>
<body>
<h1>SquirrelMail Configuration</h1>
<p><i><font size="-1"><? print $status_msg; ?></font></i></p>
<hr></hr>
<p></p>
<?
if ( sqGetGlobalVar('OrgPrefs',$getpage,SQ_GET) ) {
?>
<h3>Organization Preferences</h3>
<p></p>
<form name="confSubmit" method="post" action="make_conf.php">
<input type="hidden" name="saveConf" value="1" />
<table>
<tr><td>1. Organization Name</td><td width="20"></td><td><input name="org_name" type="text" size="50" value="<? echo $sqmConf->org_name; ?>"></input></td></tr>
<tr><td>2. Organization Logo</td><td></td><td><input name="org_logo" type="text" size="50" value="<? echo $sqmConf->org_logo; ?>"></input></td></tr>
<tr><td>3. Logo Width</td><td></td><td><input name="org_logo_width" type="text" size="50" value="<? echo $sqmConf->org_logo_width; ?>"></input></td></tr>
<tr><td>4. Logo Height</td><td></td><td><input name="org_logo_height" type="text" size="50" value="<? echo $sqmConf->org_logo_height; ?>"></input></td></tr>
<tr><td>5. Organization Title</td><td></td><td><input name="org_title" type="text" size="50" value="<? echo $sqmConf->org_title; ?>"></input></td></tr>
<tr><td>6. Signout Page</td><td></td><td><input name="signout_page" type="text" size="50" value="<? echo $sqmConf->signout_page; ?>"></input></td></tr>
<tr><td>7. Top Frame</td><td></td><td><input name="frame_top" type="text" size="50" value="<? echo $sqmConf->frame_top; ?>"></input></td></tr>
<tr><td>8. Provider link</td><td></td><td><input name="provider_uri" type="text" size="50" value="<? echo $sqmConf->provider_uri; ?>"></input></td></tr>
<tr><td>9. Provider link text</td><td></td><td><input name="provider_name" type="text" size="50" value="<? echo $sqmConf->provider_name; ?>"></input></td></tr>
<tr><td colspan="3" align="right"><input type="submit" value="Save"></input> <input type="reset"></input></td></tr>
</table>
</form>
<p></p>
<hr></hr>
<a href="make_conf.php">Back</a>

<?
} elseif ( sqGetGlobalVar('ServerSetts',$getpage,SQ_GET) ) {
?>
<h3>Server Settings</h3>
<p></p>
<table>
<tr><td>1. Domain</td><td width=20></td><td><input type=text size=50 value="<? echo $sqmConf->domain; ?>"></input></td></tr>
<tr><td>2. Invert Time</td><td></td><td><input type="radio" name="invert_time" value=true<? if ( $sqmConf->invert_time ) echo " checked"; ?>>True</input><input type="radio" name="invert_time" value=false<? if ( !$sqmConf->invert_time ) echo " checked"; ?>>False</input></td></tr>
<tr><td>3. Sendmail or SMTP</td><td></td><td><input type="radio" name="useSendmail" value=true<? if ( $sqmConf->useSendmail ) echo " checked"; ?>>Sendmail</input><input type="radio" name="useSendmail" value=false<? if ( !$sqmConf->useSendmail ) echo " checked"; ?>>SMTP</input></td></tr>
<tr><td colspan=3><hr></td></tr>
<tr><td>4. IMAP Server</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->imapServerAddress; ?>"></input></td></tr>
<tr><td>5. IMAP Port</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->imapPort; ?>"></input></td></tr>
<!-- fix authentication type -->
<tr><td>6. Authentication Type</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->imap_auth_mech; ?>"></input></td></tr>
<tr><td>7. Secure IMAP (TLS)</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->use_imap_tls; ?>"></input></td></tr>
<tr><td>8. Server software</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->imap_server_type; ?>"></input></td></tr>
<tr><td>9. Delimiter</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->optional_delimiter; ?>"></input></td></tr>
<tr><td colspan=3><hr></td></tr>
<tr><td>10. SMTP Server</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->smtpServerAddress; ?>"></input></td></tr>
<tr><td>11. SMTP Port</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->smtpPort; ?>"></input></td></tr>
<tr><td>12. POP before SMTP</td><td></td><td><input type="radio" name="pop_before_smtp" value=true<? if ( $sqmConf->pop_before_smtp ) echo " checked"; ?>>True</input><input type="radio" name="pop_before_smtp" value=false<? if ( !$sqmConf->pop_before_smtp ) echo " checked"; ?>>False</input></td></tr>
<tr><td>13. SMTP Authentication</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->smtp_auth_mech; ?>"></input></td></tr>
<tr><td>14. Secure SMTP (TLS)</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->use_smtp_tls; ?>"></input></td></tr>
<tr><td>15. Header encryption key</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->encode_header_key; ?>"></input></td></tr>
</table>
<p></p>
<hr></hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( sqGetGlobalVar('FoldDefaults',$getpage,SQ_GET) ) {
?>
<h3>Folder Defaults</h3>
<p></p>
<table>
<tr><td>1. Default Folder Prefix</td><td width=20></td><td><input type=text size=50 value="<? echo $sqmConf->default_folder_prefix; ?>"></input></td></tr>
<tr><td>2. Show Folder Prefix Option</td><td></td><td><input type="radio" name="show_prefix_option" value=true<? if ( $sqmConf->show_prefix_option ) echo " checked"; ?>>True</input><input type="radio" name="show_prefix_option" value=false<? if ( !$sqmConf->show_prefix_option ) echo " checked"; ?>>False</input></td></tr>
<tr><td>3. Trash Folder</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->trash_folder; ?>"></input></td></tr>
<tr><td>4. Sent Folder</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->sent_folder; ?>"></input></td></tr>
<tr><td>5. Drafts Folder</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->draft_folder; ?>"></input></td></tr>
<tr><td>6. By default, move to trash</td><td></td><td><input type="radio" name="default_move_to_trash" value=true<? if ( $sqmConf->default_move_to_trash ) echo " checked"; ?>>True</input><input type="radio" name="default_move_to_trash" value=false<? if ( !$sqmConf->default_move_to_trash ) echo " checked"; ?>>False</input></td></tr>
<tr><td>7. By default, move to sent</td><td></td><td><input type="radio" name="default_move_to_sent" value=true<? if ( $sqmConf->default_move_to_sent ) echo " checked"; ?>>True</input><input type="radio" name="default_move_to_sent" value=false<? if ( !$sqmConf->default_move_to_sent ) echo " checked"; ?>>False</input></td></tr>
<tr><td>8. By default, save as draft</td><td></td><td><input type="radio" name="default_save_as_draft" value=true<? if ( $sqmConf->default_save_as_draft ) echo " checked"; ?>>True</input><input type="radio" name="default_save_as_draft" value=false<? if ( !$sqmConf->default_save_as_draft ) echo " checked"; ?>>False</input></td></tr>
<tr><td>9. List Special Folders First</td><td></td><td><input type="radio" name="list_special_folders_first" value=true<? if ( $sqmConf->list_special_folders_first ) echo " checked"; ?>>True</input><input type="radio" name="list_special_folders_first" value=false<? if ( !$sqmConf->list_special_folders_first ) echo " checked"; ?>>False</input></td></tr>
<tr><td>10. Show Special Folders Color</td><td></td><td><input type="radio" name="use_special_folder_color" value=true<? if ( $sqmConf->use_special_folder_color ) echo " checked"; ?>>True</input><input type="radio" name="use_special_folder_color" value=false<? if ( !$sqmConf->use_special_folder_color ) echo " checked"; ?>>False</input></td></tr>
<tr><td>11. Auto Expunge</td><td></td><td><input type="radio" name="auto_expunge" value=true<? if ( $sqmConf->auto_expunge ) echo " checked"; ?>>True</input><input type="radio" name="auto_expunge" value=false<? if ( !$sqmConf->auto_expunge ) echo " checked"; ?>>False</input></td></tr>
<tr><td>12. Default Sub. of INBOX</td><td></td><td><input type="radio" name="default_sub_of_inbox" value=true<? if ( $sqmConf->default_sub_of_inbox ) echo " checked"; ?>>True</input><input type="radio" name="default_sub_of_inbox" value=false<? if ( !$sqmConf->default_sub_of_inbox ) echo " checked"; ?>>False</input></td></tr>
<tr><td>13. Show 'Contain Sub.' Option</td><td></td><td><input type="radio" name="show_contain_subfolders_option" value=true<? if ( $sqmConf->show_contain_subfolders_option ) echo " checked"; ?>>True</input><input type="radio" name="show_contain_subfolders_option" value=false<? if ( !$sqmConf->show_contain_subfolders_option ) echo " checked"; ?>>False</input></td></tr>
<tr><td>14. Default Unseen Notify</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->default_unseen_notify; ?>"></input></td></tr>
<tr><td>15. Default Unseen Type</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->default_unseen_type; ?>"></input></td></tr>
<tr><td>16. Auto Create Special Folders</td><td></td><td><input type="radio" name="auto_create_special" value=true<? if ( $sqmConf->auto_create_special ) echo " checked"; ?>>True</input><input type="radio" name="auto_create_special" value=false<? if ( !$sqmConf->auto_create_special ) echo " checked"; ?>>False</input></td></tr>
<tr><td>17. Folder Delete Bypasses Trash</td><td></td><td><input type="radio" name="delete_folder" value=true<? if ( $sqmConf->delete_folder ) echo " checked"; ?>>True</input><input type="radio" name="delete_folder" value=false<? if ( !$sqmConf->delete_folder ) echo " checked"; ?>>False</input></td></tr>
<tr><td>18. Enable /NoSelect folder fix</td><td></td><td><input type="radio" name="noselect_fix_enable" value=true<? if ( $sqmConf->noselect_fix_enable ) echo " checked"; ?>>True</input><input type="radio" name="noselect_fix_enable" value=false<? if ( !$sqmConf->noselect_fix_enable ) echo " checked"; ?>>False</input></td></tr>
</table>
<p></p>
<hr></hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( sqGetGlobalVar('GenOptions',$getpage,SQ_GET) ) {
?>
<h3>General Options</h3>
<p></p>
<table>
<tr><td>1. Data Directory</td><td width=20></td><td><input type=text size=50 value="<? echo $sqmConf->data_dir; ?>"></input></td></tr>
<tr><td>2. Attachment Directory</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->attachment_dir; ?>"></input></td></tr>
<tr><td>3. Directory Hash Level</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->dir_hash_level; ?>"></input></td></tr>
<tr><td>4. Default Left Size</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->default_left_size; ?>"></input></td></tr>
<tr><td>5. Usernames in Lowercase</td><td></td><td><input type="radio" name="force_username_lowercase" value=true<? if ( $sqmConf->force_username_lowercase ) echo " checked"; ?>>True</input><input type="radio" name="force_username_lowercase" value=false<? if ( !$sqmConf->force_username_lowercase ) echo " checked"; ?>>False</input></td></tr>
<tr><td>6. Allow use of priority</td><td></td><td><input type="radio" name="default_use_priority" value=true<? if ( $sqmConf->default_use_priority ) echo " checked"; ?>>True</input><input type="radio" name="default_use_priority" value=false<? if ( !$sqmConf->default_use_priority ) echo " checked"; ?>>False</input></td></tr>
<tr><td>7. Hide SM attributions</td><td></td><td><input type="radio" name="hide_sm_attributions" value=true<? if ( $sqmConf->hide_sm_attributions ) echo " checked"; ?>>True</input><input type="radio" name="hide_sm_attributions" value=false<? if ( !$sqmConf->hide_sm_attributions ) echo " checked"; ?>>False</input></td></tr>
<tr><td>8. Allow use of receipts</td><td></td><td><input type="radio" name="default_use_mdn" value=true<? if ( $sqmConf->default_use_mdn ) echo " checked"; ?>>True</input><input type="radio" name="default_use_mdn" value=false<? if ( !$sqmConf->default_use_mdn ) echo " checked"; ?>>False</input></td></tr>
<tr><td>9. Allow editing of identity</td><td></td><td><input type="radio" name="edit_identity" value=true<? if ( $sqmConf->edit_identity ) echo " checked"; ?>>True</input><input type="radio" name="edit_identity" value=false<? if ( !$sqmConf->edit_identity ) echo " checked"; ?>>False</input></td></tr>
<tr><td>10. Allow editing of name</td><td></td><td><input type="radio" name="edit_name" value=true<? if ( $sqmConf->edit_name ) echo " checked"; ?>>True</input><input type="radio" name="edit_name" value=false<? if ( !$sqmConf->edit_name ) echo " checked"; ?>>False</input></td></tr>
<tr><td>11. Remove username from header</td><td></td><td><input type="radio" name="hide_auth_header" value=true<? if ( $sqmConf->hide_auth_header ) echo " checked"; ?>>True</input><input type="radio" name="hide_auth_header" value=false<? if ( !$sqmConf->hide_auth_header ) echo " checked"; ?>>False</input></td></tr>
<tr><td>12. Disable server thread sort</td><td></td><td><input type="radio" name="disable_thread_sort" value=true<? if ( $sqmConf->disable_thread_sort ) echo " checked"; ?>>True</input><input type="radio" name="disable_thread_sort" value=false<? if ( !$sqmConf->disable_thread_sort ) echo " checked"; ?>>False</input></td></tr>
<tr><td>13. Disable server-side sorting</td><td></td><td><input type="radio" name="disable_server_sort" value=true<? if ( $sqmConf->disable_server_sort ) echo " checked"; ?>>True</input><input type="radio" name="disable_server_sort" value=false<? if ( !$sqmConf->disable_server_sort ) echo " checked"; ?>>False</input></td></tr>
<tr><td>14. Allow server charset search</td><td></td><td><input type="radio" name="allow_charset_search" value=true<? if ( $sqmConf->allow_charset_search ) echo " checked"; ?>>True</input><input type="radio" name="allow_charset_search" value=false<? if ( !$sqmConf->allow_charset_search ) echo " checked"; ?>>False</input></td></tr>
<tr><td>15. Allow advanced search</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->allow_advanced_search; ?>"></input></td></tr>
<tr><td>16. PHP session name</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->session_name; ?>"></input></td></tr>
<tr><td>17. Time zone configuration</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->time_zone_type; ?>"></input></td></tr>
<tr><td>18. Location base</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->config_location_base; ?>"></input></td></tr>
<tr><td>19. Only secure cookies if poss.</td><td></td><td><input type="radio" name="only_secure_cookies" value=true<? if ( $sqmConf->only_secure_cookies ) echo " checked"; ?>>True</input><input type="radio" name="only_secure_cookies" value=false<? if ( !$sqmConf->only_secure_cookies ) echo " checked"; ?>>False</input></td></tr>
</table>
<p></p>
<hr></hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( sqGetGlobalVar('UserInterf',$getpage,SQ_GET) ) {
?>
<h3>User Interface</h3>
<p></p>
<table>
<tr><td>1. Use Icons?</td><td width=20></td><td><input type="radio" name="use_icons" value=true<? if ( $sqmConf->use_icons ) echo " checked"; ?>>True</input><input type="radio" name="use_icons" value=false<? if ( !$sqmConf->use_icons ) echo " checked"; ?>>False</input></td></tr>
<tr><td>2. Default font size</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->default_font_size; ?>"></input></td></tr>
<!--
**** FIX ME
3.  Manage template sets (skins)
4.  Manage user themes
5.  Manage font sets
6.  Manage icon themes
-->
</table>
<p></p>
<hr></hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( sqGetGlobalVar('AddyBooks',$getpage,SQ_GET) ) {
?>
<h3>Address Books</h3>
<p>
<!--
**** FIX ME
add address books panel
-->
<p>
<hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( sqGetGlobalVar('MsgOTDay',$getpage,SQ_GET) ) {
?>
<h3>Message of the Day</h3>
<p>
<table>
<tr><td><textarea rows=6 cols=50><? echo $sqmConf->motd; ?></textarea></td></tr>
</table>
<p>
<hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( sqGetGlobalVar('Plugns',$getpage,SQ_GET) ) {
?>
<h3>Plugins</h3>
<p>
<!--
**** FIX ME
add plugin manager
-->
<p>
<hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( sqGetGlobalVar('DBase',$getpage,SQ_GET) ) {
?>
<h3>Database</h3>
<p>
<!--
**** FIX ME
add database panel
-->
<p>
<hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( sqGetGlobalVar('LangSetts',$getpage,SQ_GET) ) {
?>
<h3>Language Settings</h3>
<p>
<!--
**** FIX ME
add language settings panel
-->
<p>
<hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( sqGetGlobalVar('Tweeks',$getpage,SQ_GET) ) {
?>
<h3>Tweaks</h3>
<p>
<!--
**** FIX ME
add tweaks panel
-->
<p>
<hr>
<a href="make_conf.php">Back</a>
<?
} else {
?>
<h3>Main Menu</h3>
<p></p>
<ol>
<li>&nbsp;&nbsp;  <a href="make_conf.php?OrgPrefs">Organization Preferences</a></li>
<li>&nbsp;&nbsp;  <a href="make_conf.php?ServerSetts">Server Settings</a></li>
<li>&nbsp;&nbsp;  <a href="make_conf.php?FoldDefaults">Folder Defaults</a></li>
<li>&nbsp;&nbsp;  <a href="make_conf.php?GenOptions">General Options</a></li>
<li>&nbsp;&nbsp;  <a href="make_conf.php?UserInterf">User Interface</a></li>
<li>&nbsp;&nbsp;  <a href="make_conf.php?AddyBooks">Address Books</a></li>
<li>&nbsp;&nbsp;  <a href="make_conf.php?MsgOTDay">Message of the Day (MOTD)</a></li>
<li>&nbsp;&nbsp;  <a href="make_conf.php?Plugns">Plugins</a></li>
<li>&nbsp;&nbsp;  <a href="make_conf.php?DBase">Database</a></li>
<li>&nbsp;&nbsp;  <a href="make_conf.php?LangSetts">Language settings</a></li>
<li>&nbsp;&nbsp;  <a href="make_conf.php?Tweeks">Tweaks</a></li>
</ol>
D.  Set pre-defined settings for specific IMAP servers
<?
}
?>
</body>
</html>
