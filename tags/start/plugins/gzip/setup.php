<?php

/* Compress all output sent to the client, if they want it and if the client
   supports it. */


// This plugin requires that you either have the 'zlib' library compiled with 
// PHP (with --with-zlib), or that you specify where the 'gzip' program is.
//  * If you have zlib, you can have this be '' or the path to gzip.  It
//    doesn't matter -- the zlib will be used automatically.
//  * If you don't have zlib, this plugin must use this variable.
//  * If you don't have zlib and you set $gzip_binary to '', then the
//    plugin will not attach itself to any hooks and will not be loaded and
//    it won't produce errors either.
$gzip_binary = '/bin/gzip';


/* Initialize the plugin */
function squirrelmail_plugin_init_gzip()
{
  global $squirrelmail_plugin_hooks;

  if (! extension_loaded('zlib') && $gzip_binary = '')
    return;

  $squirrelmail_plugin_hooks['options_display_inside']['gzip'] = 'gzip_options';
  $squirrelmail_plugin_hooks['options_display_save']['gzip'] = 'gzip_save';
  $squirrelmail_plugin_hooks['loading_prefs']['gzip'] = 'gzip_load';
  
  // I would like to see standard hooks...
  // Maybe "[script]_html_top" (like 'download_html_top',
  // 'read_body_html_top', and others).
  $squirrelmail_plugin_hooks['html_top']['gzip'] = 'gzip_start';
  $squirrelmail_plugin_hooks['html_bottom']['gzip'] = 'gzip_end';
}


function gzip_save() 
{
  global $username,$data_dir;
  global $gzip_gzip_disable;
  global $gzip_gzip_size;

  if (isset($gzip_gzip_enable)) 
  {
    setPref($data_dir, $username, 'gzip_disable', '1');
  } 
  else 
  {
    setPref($data_dir, $username, 'gzip_disable', '');
  }
  setPref($data_dir, $username, 'gzip_size', $gzip_gzip_size);
}


function gzip_load()
{ 
  global $username, $data_dir;
  global $gzip_disable, $gzip_size;

  $gzip_enable = getPref($data_dir, $username, 'gzip_disable');
  $gzip_size = getPref($data_dir, $username, 'gzip_size');
  if ($gzip_size <= 0)
  {
      $gzip_size = 15;
  }
}


function gzip_options()
{
  global $gzip_size, $gzip_disable;
  
  echo "<tr><td align=right nowrap valign=top>Compressed Output:</td>\n";
  echo "<td><input name=\"gzip_gzip_disable\" type=CHECKBOX";
  if ($gzip_disable)
    echo " CHECKED";
  echo "> Disable compression<br>Compress if bigger than <input type=text name=\"gzip_gzip_size\" ";
  echo "size=4 value=\"$gzip_size\"> kilobytes</td></tr>\n";
}


function gzip_start()
{
    global $gzip_disable, $gzip_supported;
    global $HTTP_SERVER_VARS, $username;
    
    $gzip_supported = '';

    if ($gzip_disable)
    {
        return;
    }


    $methods = explode(', ', $HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING']);
    
    foreach ($methods as $val)
    {
        if ($val == 'x-gzip')
        {
            $gzip_supported = 'x-gzip';
        }
        else if ($val == 'gzip' && $gzip_supported == '')
        {
            $gzip_supported = 'gzip';
        }
    }
    
    if ($gzip_supported == '')
        return;
        
    ob_start();
    ob_implicit_flush(0);
}


function gzip_end()
{
    global $gzip_supported, $data_dir, $gzip_size;

    if ($gzip_supported == '')
        return;

    if (ob_get_length() < $gzip_size * 1024)
    {
        ob_end_flush();
	return;
    }

    // If you want to make sure that the page is being compressed,
    // just add this line:
    // echo "<P>COMPRESSED</p>\n";        

    $contents = ob_get_contents();
    ob_end_clean();

    // Maybe support other compression techniques in the future
    if ($gzip_supported == 'x-gzip' || $gzip_supported == 'gzip')
    {
        header("Content-Encoding: $gzip_supported");
        $TempFile = tempnam($data_dir, 'sm-gz');
        if (extension_loaded('zlib'))
        {
            echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
            $Size = strlen($contents);
            $Crc = crc32($contents);
            $contents = gzcompress($contents, 9);
            $contents = substr($contents, 0, strlen($contents) - 4);
        
            echo $contents;
        
            echo pack('V', $Crc);
            echo pack('V', $Size);

            $TempFile = 0;
        }
        else
        {
            $fp = popen("$gzip_binary > $TempFile", "wb");
            fwrite($fp, $contents);
            pclose($fp);
        }
    }

    // This check may seem silly here, but if we decide to add other
    // compression methods, they might not need a messed-up temporary
    // file    
    if ($TempFile)
    {
        $fp = fopen($TempFile, "rb");
        while (!feof($fp))
        {
            echo fread($fp, 1024);
        }
        fclose($fp);
        unlink($TempFile);
    }
}


?>
