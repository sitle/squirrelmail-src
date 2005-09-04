<?php
require_once('header.php');

echo "<h1>SquirrelMail i18n statistics</h1>\n";
echo "<p>Stats are available for:\n";
echo "<dl>\n";
echo "<dt><a href=\"HEAD/\">HEAD</a>\n";
echo "<dd>SquirrelMail locales cvs, HEAD branch.\n";
//echo "<dt><a href=\"SM-1_4_3/\">SM-1_4_3</a>\n";
//echo "<dd>SquirrelMail locales cvs, SM-1_4_3 branch.\n";
echo "<dt><a href=\"SM-1_4_4/\">SM-1_4_4</a>\n";
echo "<dd>SquirrelMail locales cvs, SM-1_4_4 branch.\n";
echo "<dt><a href=\"SM-1_5_0/\">SM-1_5_0</a>\n";
echo "<dd>SquirrelMail locales cvs, SM-1_5_0 branch.\n";
echo "</dl>\n";
echo "</p>\n";

?>

<p>Older stats are not available.</p>

<p>You can get copy of latest (the one that was used to generate HEAD stats) 
locales cvs by downloading file from 
<a href="data/" name="locales download">data</a> directory</p>

<p>This package contains translations that are merged with strings available in po directory
and compiled copies of translations (this is not the same thing that is available in CVS).</p>

<p>Gettext translations that are linked in these pages are merged with latest available
strings and gziped. Modern browsers should decompress them automatically. Translations
stored in cvs are not merged with latest available strings.</p>

<p><a href="plugin-tracker.txt">Information about added plugins</a></p>

<p><a href="releases/">SquirrelMail locale releases</a></p>

<p>Glitches:<br>
<ul>
    <li>string counter is broken, if translated .po has invalid header.</li>
    <li>If translation is marked as "unavailable" - there was some error while
    merging strings with .pot's.</li>
    <li>Some non-core plugins use squirrelmail gettext domain</li>
</ul>
</p>
<p>ChangeLog:</p>
<center>
<iframe src="ChangeLog.locales" width="95%"><a href="ChangeLog.locales">ChangeLog.locales</a></iframe>
</center>
<table cellpadding="2" cellspacing="0" border="0" width="100%" bgcolor="#ececec">
<tr align="center"><td><font size="2">

index |
<a href="./about.php">about</a> |
<a href="./help.php">help</a>

</font></td></tr></table>
</td></tr></table>
<?php
require_once('footer.php');
?>