<?php

// Defining some hooks statically here, but should be done dynamically
global $hooks_defined;

$hooks_defined['menu_bar']['templates'] = 'template_select';
$hooks_defined['menu_bar']['messages'] = 'main_select';

function template_select()
{
    global $Page;
    if ($Page != 'templates')
        echo "<a href=\"" . getUrl('Page', 'templates') . "\">Templates</a>\n";
}

function main_select()
{
    global $Page;
    if ($Page != 'main')
        echo "<a href=\"" . getUrl('Page', 'main') . "\">Messages</a>\n";
}

function call_hook()
{
    global $hooks_defined;

    $args = func_get_args();
    
    $ref = $hooks_defined[$args[0]];
    
    if (is_array($ref))
    {
        // Multiple hook functions for a single hook
	foreach ($ref as $k => $v)
	{
	    $ret[$k] = $v($args);
	}
	return $ret;
    }
}   

?>
