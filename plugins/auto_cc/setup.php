<?php

/*
 *
 * auto_cc SquirrelMail Plugin
 * (c) 2001 by Benjamin Brillat <brillat-sqplugin@mainsheet.org>
 *
 * version 1.2 - Added closing ?>
 * version 1.1 - Removed javascript (fidian)
 *
 */

function squirrelmail_plugin_init_auto_cc()
{
	global $squirrelmail_plugin_hooks;

	$squirrelmail_plugin_hooks["compose_form"]["auto_cc"]    = "auto_cc_compose_form";
	$squirrelmail_plugin_hooks["options_personal_inside"]["auto_cc"] = "auto_cc_personal_inside";
	$squirrelmail_plugin_hooks["options_personal_save"]["auto_cc"] = "auto_cc_personal_save";         
	$squirrelmail_plugin_hooks["loading_prefs"]["auto_cc"] = "auto_cc_loading_prefs";
}   
   


function auto_cc_add_addresses($a, $b)
{
    if ($a != "")
        $a .= ';' . $b;
    else
        $a = $b;
    $a = parseAddrs($a);
    $a = array_keys(array_flip($a));
    return getLineOfAddrs($a);
}

function auto_cc_compose_form()
{
    global $send_to_cc, $send_to_bcc, $auto_cc_cc_addr, $auto_cc_bcc_addr;
    
    if (isset($auto_cc_cc_addr) && $auto_cc_cc_addr != "")
    {
        $send_to_cc = auto_cc_add_addresses($send_to_cc, $auto_cc_cc_addr);
    }

    if (isset($auto_cc_bcc_addr) && $auto_cc_bcc_addr != "")
    {
        $send_to_bcc = auto_cc_add_addresses($send_to_bcc, $auto_cc_bcc_addr);
    }
}

function auto_cc_personal_inside()
{
 global $username,$data_dir;
 global $auto_cc_cc_addr;
 global $auto_cc_bcc_addr;
 global $auto_cc_cc_addri, $auto_cc_bcc_addri;

 echo "<tr><td align=right>\n";
 echo "Additional default CC address for all messages:</td>\n";
 echo "<td><input type=text name=auto_cc_cc_addri value=\"$auto_cc_cc_addr\" size=50></td></tr>\n";

 echo "<tr><td align=right>\n";
 echo "Additional default BCC address for all messages:</td>\n";
 echo "<td><input type=text name=auto_cc_bcc_addri value=\"$auto_cc_bcc_addr\" size=50></td></tr>\n";
}

function auto_cc_personal_save()
{
   global $username,$data_dir;
   global $auto_cc_cc_addr;
   global $auto_cc_bcc_addr;
   global $auto_cc_cc_addri, $auto_cc_bcc_addri;

   if(isset($auto_cc_cc_addri)) {
     setPref($data_dir, $username, "auto_cc_cc_addr", $auto_cc_cc_addri);
   } else {
     setPref($data_dir, $username, "auto_cc_cc_addr", "");
   }

   if(isset($auto_cc_bcc_addri)) {
     setPref($data_dir, $username, "auto_cc_bcc_addr", $auto_cc_bcc_addri);
   } else {
     setPref($data_dir, $username, "auto_cc_bcc_addr", "");
   }
}


function auto_cc_loading_prefs()
{
   global $username,$data_dir;
   global $auto_cc_cc_addr;
   global $auto_cc_bcc_addr;
   $auto_cc_bcc_addr = getPref($data_dir, $username, "auto_cc_bcc_addr");
   $auto_cc_cc_addr = getPref($data_dir, $username, "auto_cc_cc_addr");
}

?>
