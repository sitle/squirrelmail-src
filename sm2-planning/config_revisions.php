<?php
/**
 * config_revisions.php - description of what file is for
 *
 * Copyright (c) 1999-2002 The SquirrelMail development team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */
include_once('common_header.inc');
set_title('Config Revisions');
set_original_author('ebullient, grootkoerkamp');
set_attributes('$Author$','$Revision$','$Date$');
print_header();

function print_links() {
 echo '<p class="link-bar">';
 echo '<a href="#version">Versioning</a> | ';
 echo '<a href="#config">Configuration Process</a> | ';
 echo '<a href="#paths">Setting Paths/SM Initialization</a>';
 echo '</p>';
}

?>

<P>Modifications need to be made to our current configuration 
mechanism (conf.pl and php admin plugin both modify config.php). 

<P>Also seeking better mechanism for setting paths, especially
paths to elements potentially located outside of the SM tree.

<P><a href="plugin_revisions.php">Plugin Revisions</a> are also 
relevant to this discussion.



<?php print_links(); ?>
<a name="versioning"></a>
<H2>Versioning</H2>
Due to changes in the 1.3.x stream (SM_PATH, etc.) and upcoming changes
slated for 1.4/1.5, it's now necessary to allow plugins to check for the 
SquirrelMail version, in order to procede properly.

<p>To that end, FUTURE versions of SM (including the current dev stream), 
will define an internal global constant, $sm_internal_version, as an array
containing 3 parts (major, minor, release) (global.php).

<p>Also in global.php will be a version checking function that will take 
arguments similar to those provided for php version checking, and will return true if the current version is >= the one specified (just as check_php_version does).

<?php print_links(); ?>
<a name="config"></a>
<H2>Configuration Process</H2>

<?php print_links(); ?>
<a name="config"></a>
<H2>Setting Paths/SM Initialization - sm_init.php</H2>


</body>
</html>
