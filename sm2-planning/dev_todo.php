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
set_title('Development Items');
set_original_author('ebullient');
set_attributes('$Author$','$Revision$','$Date$');
print_header();
?>

<UL>
<LI>UI Restructure<br />
    who: ebullient
<LI>IMAP Message/Mailbox restructure<br />
    who: stekkel
<LI>Plugin architecture revision (<a href="plugin_revisions.php">here</a>)<br />
    who: 
<LI>Config system revision ( <a href="config_revisions.php">here</a> )<br />
    who:
<LI>Remove other languages/locales from main SM download (plugins/add-ons, possibly common with stable?)<br />
    who:
</UL>

<p>Doc System:
<UL>
<LI>Plugin submit/review/approve/promote function
<LI>Additional Admin functions
<LI>Bulk categorization
<LI>Group management via DB rather than constants.
</UL>

<p> Wishlist:
<UL>
<LI>Multiple Identity - optionally verify additional identies added by user against a db
</UL>
</body>
</html>
