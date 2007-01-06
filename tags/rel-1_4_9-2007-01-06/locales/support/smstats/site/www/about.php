<?php
require_once('header.php');

echo "<h2>About SquirrelMail statistics</h2>\n";

echo "<p>These statistics are generated with KDE's <a href=\"http://i18n.kde.org/stats/gui/\">guistats</a> package, grab-stats.php, html-stats.php and history-stats.php scripts.</p>\n";

echo "<p>Scripts were provided by Claudiu Costin &lt;claudiuc at kde.org&gt;</p>\n";

echo "<p>Changes that where made:<br>\n";
echo "<ul>\n";
echo "<li>Changed <tt>adminemail</tt> variable in grab-config.php, html-config.php and history-config.php</li>\n";
echo "<li>help.html and about.html links were converted to help.php and about.php in order to use header.php and footer.php includes.</li>\n";
echo "<li>Changed static images from .gif to .png</li>\n";
echo "<li>Adjusted paths in grab-config.php, grab-stats.php, history-config.php, and html-config.php</li>\n";
echo "<li>Adjusted location of messages directory hard-coded in grab-stats.php and html-functions.php</li>\n";
echo "<li>Added shell scripts that grab copy of SquirrelMail cvs.</li>\n";
echo "<li>Set <tt>kdefake</tt> variable to 'squirrelmail' in grab-config.php</li>\n";
echo "<li>Adjusted layout of SquirrelMail locales cvs in order to have same layout as in older SquirrelMail packages.</li>\n";
echo "<li>Added tests that check for MySQL support in php</li>\n";
echo "<li>Removed references to 'KDE packages'. It is just 'Packages'.</li>\n";
echo "</ul>\n";
echo "</p>\n";

echo "<p>Why png images are used instead of gif:<br>\n";
echo "<ul>\n";
echo "<li>LZW patent is still valid in EU and in some other countries</li>\n";
echo "<li>PNG format has more features that GIF. If some browser does not support these features, png images can be created with same limitations as GIF and then \"the browser\" :) is able to read them.</li>";
echo "<li>I don't like the history of LZW. If details about some software algorithm were publicly available, this algorithm became de facto standard of small images and animations, and then some company decided to collect fines for that patent... Sorry, but this is not the way of doing business.</li>\n";
echo "</ul>\n";
echo "</p>\n";

echo "<hr>\n";
echo "<p></p>\n";
?>
<table cellpadding="2" cellspacing="0" border="0" width="100%" bgcolor="#ececec">
<tr align="center"><td><font size="2">

<a href="./index.php">index</a> |
about |
<a href="./help.php">help</a>

</font></td></tr></table>
</td></tr></table>
<?php
require_once('footer.php');
?>