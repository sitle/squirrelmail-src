<?PHP

    // Include the template class and the two example classes
include "code/action.php";
include "code/html.php";
include "code/session.php"; // Should probably make a session API
include "code/plugin.php";
include "code/templates.php";
include "code/translate.php";
include "classes/class.ptemplate.php";
include "classes/class.message.php";
include "classes/class.folder.php";
include "classes/class.server.php";

StartSession();

SetUpTemplates();

    // Create a new template
$tpl = new PTemplate("./templates");

    // Perform any actions that were requested
$Page = ActionHandler();

    // Define the files
if ($TemplateSet != 'standard')
    $tpl->define_templates('standard');
$tpl->define_templates($TemplateSet);

    // Determine if gzip is supported
if (use_gzip())
{
        // Supported -- cache page
    ob_start();
    $tpl->parse($Page);
        // If the file is less than 10k, don't bother.
        // If there were errors, we can't do it properly.
    if (strlen($output) > 10240 && ! headers_sent())
        gzip_go($output);
    else
        echo $output;
}
else
{
        // Not supported.  Output fast.
    $tpl->parse($Page, true);
}


function use_gzip()
{
    global $HTTP_SERVER_VARS, $gzip_supported, $No_GZip;
    
    if (! extension_loaded('zlib') || $No_GZip)
        return 0;
    
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
        return 0;
        
    return 1;
}


function gzip_go(&$contents)
{
    global $gzip_supported;

    // Maybe support other compression techniques in the future
    if ($gzip_supported == 'x-gzip' || $gzip_supported == 'gzip')
    {
        header("Content-Encoding: $gzip_supported");
        echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
        $Size = strlen($contents);
        $Crc = crc32($contents);
        $contents = gzcompress($contents, 9);
        $contents = substr($contents, 0, strlen($contents) - 4);
        
        echo $contents;
        
        echo pack('V', $Crc);
        echo pack('V', $Size);
    }
}


// This function is called by the download page when sending a file
function SpitOutHeaders($filename)
{
    header("Pragma: ");
    
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-type: application/octet-stream; name=\"$filename\"");
}

// Just so I can emulate the example_php.txt file
function bar()
{
    return 'FUNCTION BAR WAS CALLED THROUGH THE ALIAS<br>';
}

?>
