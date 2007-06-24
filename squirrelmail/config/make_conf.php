<?php
/*******************


HORRIBLE UGLY SQM CONFIG PHP

quick and dirty port of conf.pl

things to do:
-more elegant interface
    -better handling of conditional settings
    -better handling of (multiple) selection settings
-add predefined imap srv settings
-finish adding panels
-sanity check input


**********************/


require("config_class.php");

if ( file_exists("conf.php") ) {
    $sqmConf = new SQMConfigFile("conf.php");
} elseif ( file_exists("conf_default.php") ) {
    $sqmConf = new SQMConfigFile("conf_default.php");
} else {
    $sqmConf = new SQMConfigFile();
}

/******************

CODE FOR CONFIG.PHP

below code can be used to import old config.php
however for the most part we use SQMConfig class
*******************
*******************
*******************/


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

function change_to_rel_path($oldPath) {

    $newPath = $oldPath;

    if ( preg_match('/^SM_PATH/',$oldPath) ) {
        $newPath = preg_replace("/^SM_PATH . \'/",'..',$oldPath);
        $newPath = preg_replace("/\.\.\/config\//",'',$newPath);
    }
    
    return $newPath;
}



/**************************

BEGIN INTERFACE OUTPUT

***************************/

?>
<h1>SquirrelMail Configuration</h1>
<br><hr>
<p>
<?
if ( isset($_GET['OrgPrefs']) ) {
?>
<h3>Organization Preferences</h3>
<p>
<table>
<tr><td>1. Organization Name</td><td width=20></td><td><input type=text size=50 value="<? echo $sqmConf->org_name; ?>"></input></td></tr>
<tr><td>2. Organization Logo</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->org_logo; ?>"></input></td></tr>
<tr><td>3. Logo Width</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->org_logo_width; ?>"></input></td></tr>
<tr><td>4. Logo Height</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->org_logo_height; ?>"></input></td></tr>
<tr><td>5. Organization Title</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->org_title; ?>"></input></td></tr>
<tr><td>6. Top Frame</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->frame_top; ?>"></input></td></tr>
<tr><td>7. Provider link</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->provider_uri; ?>"></input></td></tr>
<tr><td>8. Provider link text</td><td></td><td><input type=text size=50 value="<? echo $sqmConf->provider_name; ?>"></input></td></tr>
</table>
<p>
<hr>
<a href="make_conf.php">Back</a>

<?
} elseif ( isset($_GET['ServerSetts']) ) {
?>
<h3>Server Settings</h3>
<p>
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
<p>
<hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( isset($_GET['FoldDefaults']) ) {
?>
<h3>Folder Defaults</h3>
<p>
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
<p>
<hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( isset($_GET['GenOptions']) ) {
?>
<h3>General Options</h3>
<p>
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
<p>
<hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( isset($_GET['UserInterf']) ) {
?>
<h3>User Interface</h3>
<p>
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
<p>
<hr>
<a href="make_conf.php">Back</a>
<?
} elseif ( isset($_GET['AddyBooks']) ) {
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
} elseif ( isset($_GET['MsgOTDay']) ) {
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
} elseif ( isset($_GET['Plugns']) ) {
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
} elseif ( isset($_GET['DBase']) ) {
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
} elseif ( isset($_GET['LangSetts']) ) {
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
} elseif ( isset($_GET['Tweeks']) ) {
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
<p>
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