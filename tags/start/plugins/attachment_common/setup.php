<?php

 /* This plugin will handle the most common attachments that are
  * found -- text attachments and images that the browser can handle.
  */

function squirrelmail_plugin_init_attachment_common($debug = 0)
{
  global $squirrelmail_plugin_hooks, $HTTP_SERVER_VARS, $HTTP_ACCEPT;

  // Register this plugin for supported images  
  $types = array();
  if (isset ($HTTP_SERVER_VARS['HTTP_ACCEPT']) && 0) {
      $types = array_merge($types, 
          explode(', ', $HTTP_SERVER_VARS['HTTP_ACCEPT']));
  }
  elseif (isset($HTTP_ACCEPT))
  {
      $types = array_merge($types, explode(', ', $HTTP_ACCEPT));
  }
  elseif ($debug)
      echo "plugin attachment_common: HTTP_ACCEPT not set.<br>\n";
    
  foreach ($types as $val)
  {
     if ($debug)
         echo "plugin attachment_common:  Registering $val<br>\n";
	 
     if ($val == 'image/gif')
        $squirrelmail_plugin_hooks['attachment image/gif']['attachment_common'] = 'attachment_common_link_image';
     else if ($val == 'image/jpeg' || $val == 'image/pjpeg')
     {
        $squirrelmail_plugin_hooks['attachment image/jpeg']['attachment_common'] = 'attachment_common_link_image';
        $squirrelmail_plugin_hooks['attachment image/pjpeg']['attachment_common'] = 'attachment_common_link_image';
     }
     else if ($val == 'image/png')
        $squirrelmail_plugin_hooks['attachment image/png']['attachment_common'] = 'attachment_common_link_image';
     else if ($val == 'image/x-xbitmap')
        $squirrelmail_plugin_hooks['attachment image/x-xbitmap']['attachment_common'] = 'attachment_common_link_image';
  }
  
  // Register this plugin for text-type attachments
  $squirrelmail_plugin_hooks['attachment message/rfc822']['attachment_common'] = 'attachment_common_link_text';
  $squirrelmail_plugin_hooks['attachment text/html']['attachment_common'] = 'attachment_common_link_text';
  $squirrelmail_plugin_hooks['attachment text/plain']['attachment_common'] = 'attachment_common_link_text';
  $squirrelmail_plugin_hooks['attachment text/richtext']['attachment_common'] = 'attachment_common_link_text';
  
  // vcards
  $squirrelmail_plugin_hooks['attachment text/x-vcard']['attachment_common'] = 'attachment_common_link_vcard';
  
  // "unknown" attachments
  $squirrelmail_plugin_hooks['attachment application/octet-stream']['attachment_common'] = 'attachment_common_octet_stream';
}


function attachment_common_link_text(&$Args)
{
  // If there is a text attachment, we would like to create a 'view' button
  // that links to the text attachment viewer.
  //
  // $Args[1] = the array of actions
  //
  // Use our plugin name for adding an action
  // $Args[1]['attachment_common'] = array for href and text
  //
  // $Args[1]['attachment_common']['text'] = What is displayed
  // $Args[1]['attachment_common']['href'] = Where it links to
  //
  // This sets the 'href' of this plugin for a new link.
  $Args[1]['attachment_common']['href'] = '../src/download.php?startMessage=' . 
     $Args[2] . '&passed_id=' . $Args[3] . '&mailbox=' . $Args[4] . '&passed_ent_id=' .
     $Args[5] . '&override_type0=text&override_type1=plain';
  
  // If we got here from a search, we should preserve these variables
  if ($Args[8] && $Args[9])
     $Args[1]['attachment_common']['href'] .= '&where=' . 
     urlencode($Args[8]) . '&what=' . urlencode($Args[9]);

  // The link that we created needs a name.  "view" will be displayed for
  // all text attachments handled by this plugin.
  $Args[1]['attachment_common']['text'] = _("view");
  
  // Each attachment has a filename on the left, which is a link.
  // Where that link points to can be changed.  Just in case the link above
  // for viewing text attachments is not the same as the default link for
  // this file, we'll change it.
  //
  // This is a lot better in the image links, since the defaultLink will just
  // download the image, but the one that we set it to will format the page
  // to have an image tag in the center (looking a lot like this text viewer)
  $Args[6] = $Args[1]['attachment_common']['href'];
}


function attachment_common_link_image(&$Args)
{
  $Args[1]['attachment_common']['href'] = '../plugins/attachment_common/image.php?startMessage=' . 
     $Args[2] . '&passed_id=' . $Args[3] . '&mailbox=' . $Args[4] . '&passed_ent_id=' .
     $Args[5];
  
  if ($where && $what)
     $Args[1]['attachment_common']['href'] .= '&where=' . 
     urlencode($Args[8]) . '&what=' . urlencode($Args[9]);

  $Args[1]['attachment_common']['text'] = _("view");
  
  $Args[6] = $Args[1]['attachment_common']['href'];
}


function attachment_common_link_vcard(&$Args)
{
  $Args[1]['attachment_common']['href'] = '../plugins/attachment_common/vcard.php?startMessage=' .
     $Args[2] . '&passed_id=' . $Args[3] . '&mailbox=' . $Args[4] . '&passed_ent_id=' .
     $Args[5];

  if (isset($where) && isset($what))
     $Args[1]['attachment_common']['href'] .= '&where=' . 
     urlencode($Args[8]) . '&what=' . urlencode($Args[9]);

  $Args[1]['attachment_common']['text'] = _("Business Card");

  $Args[6] = $Args[1]['attachment_common']['href'];
}


function attachment_common_octet_stream(&$Args)
{
   ereg('\.([^\.]+)$', $Args[7], $Regs);
  
   $Ext = strtolower($Regs[1]);
   
   $Mimes = array('bmp' => 'image/x-bitmap',
                  'gif' => 'image/gif',
		  'html' => 'text/html',
		  'jpg' => 'image/jpeg',
		  'jpeg' => 'image/jpeg',
		  'php' => 'text/plain',
		  'png' => 'image/png',
		  'rtf' => 'text/richtext',
		  'txt' => 'text/plain',
		  'vcf' => 'text/x-vcard');

   if ($Ext == '' || ! isset($Mimes[$Ext]))
       return;       
   
   $Ret = do_hook('attachment ' . $Mimes[$Ext], $Args[1], $Args[2], $Args[3],
       $Args[4], $Args[5], $Args[6], $Args[7], $Args[8], $Args[9]);
       
   foreach ($Ret as $a => $b)
   {
       $Args[$a] = $b;
   }
}

?>
