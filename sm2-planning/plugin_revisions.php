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
set_title('Document Index');
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


</body>
</html>