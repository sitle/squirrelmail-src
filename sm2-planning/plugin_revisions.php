<?php
/**
 * plugin_revisions.php - Discussion of changes to plugin model
 *
 * Copyright (c) 1999-2002 The SquirrelMail development team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */
include_once('common_header.inc');
set_title('Plugin Revisions');
set_original_author('ebullient');
set_attributes('$Author$','$Revision$','$Date$');
print_header();
?>

<P>The current plugin model needs to be revised, and documentation
for plugin authors updated.

<P>Plugins are a draw to SquirrelMail, and allow install-specific tweaks to be
made at well-known hook points without requiring a contributor to master the 
entire SquirrelMail codebase.

<P>However, the current implementation of plugins is not efficient for 
performance because during plugin registration, setup.php for each plugin,
is always loaded. 
<UL>
<LI>Some setup.php files are large, containing all the code for a
particular plugin, whether or not it is used in that context.
<LI>Some setup.php files trigger expensive initialization routines, 
when often the plugin will not be invoked by the current page.
</UL>
</p>

<P>Changes:
<UL>
<LI>It should be made clear to plugin developers that setup.php should
remain as small as possible.

<LI>For optimization purposes, setup.php should contain only static 
content directly related to registration of plugins with hook points.

<LI>Initialization of plugins should be context based (e.g. don't initialize
squirrel-spell unless we're in compose).
</UL>
</p>

<P>Since plugins ARE fairly static (added/removed via configuration utilitiy),
we may want to consider static hook-point registration, so that plugin 
load/setup occurs only within the context that it is used.

<div class="ebullient">
<p>ebullient 11/20/2002 1:49AM<br />
Talking with Valcor again, re: hook points. Added the following.
</p>

<UL>
<LI>If we can trim down setup.php to be only static hook point registration,
then it becomes possible to make that registration a one-time follow up to
configuration. i.e. only hit plugin setup after you've modified your
plugin configuration, rather than on every page load (see below).
<LI><b>Model for plugin modification</b><br />
Simple simple. Make sure that setup.php contains only the initial hook point
setup and wrapper functions (hook targets) which include the other plugin files
and invoke the functions that actually do things.<br />

<pre>
setup.php:

function squirrelmail_plugin_init_<plugin>() {
  global $squirrelmail_plugin_hooks;
  $squirrelmail_plugin_hooks['menuline']['&lt;plugin>'] = 
                  '&lt;plugin_menu_funtion>';
  $squirrelmail_plugin_hooks['loading_prefs']['&lt;plugin>'] = 
                  '&lt;plugin_loadpref_funtion>';
}

/* This function invokes displayInternalLink, 
 * which is already loaded. No problem with it 
 * staying in setup.php, as a menuline link is drawn in every
 * right_main.php 
 */
function &lt;plugin_menu_funtion>() {
  displayInternalLink('&lt;relpath to file>',_("Link Name"), 'right');
  echo '&nbsp;&nbsp;' . "\n";
}

/* This function, by contrast, is fairly complicated. 
 * Use an include, and keep the bulk of the function 
 * somewhere else, to keep setup.php small and quick to load. 
 */
function &lt;plugin_loadpref_funtion>() {
  include_once('&lt;other_plugin_file.php>');
  do_real_work();
}
</pre>
</li>
<li>For Devel:</br>
Once plugins are revised to keep setup really simple, then we can add
a step during save of conf.pl <br />
<pre>
Did plugins change?
Then regen config_plugins.php, 
which contains $plugins array 
           AND $squirrelmail_plugin_hooks array.
</pre>
Would be great to be able to build those arrays at config time
instead of at run time. And since arrays would still exist with 
same contained content, it shouldn't involve a substantial
code mod to get a significant performance gain.
</li>
</ul>
<p>end ebullient 11/20/2002 1:49AM</p>
</div>


<div class="ebullient">
<p>ebullient 12/4/2002</p>

<p><b>Version Checking</b></p>

<p>Version checking for plugins should look something like this 
(per discussion in devel/plugins lists ~12/2-12/4):

<p>Plugins will check for presence of global variable $sm_internal_version.
If variable is set, they can then use a function (not yet named) to 
verify that a version is > than a required major/minor/release.
If the variable is not set, then it will be assumed to be pre-significant
changes (i.e. 1.2.x), and can therefore use the old chdir etc. to set up
includes.

<p>end ebullient 12/4/2002</p>
</div>
<div class="tassium">
tassium 12/12/2002<br />
<p><b>Version Checking II</b></p>
<p>Plugins should have a standardized interface for reporting their version. I'd like to suggest that all plugins be required to implement plugin_name_version(), which should return a version string.</p>
</div>

</body>
</html>
