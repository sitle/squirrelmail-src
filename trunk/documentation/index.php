<?php
// Page header
if (! isset($_GET['iframe'])) {
  include ('../../includes/std_header.inc');
  echo '<iframe name="documentation" src="index.php?iframe=yes"'
    ."\n".' width="100%" height="100%" frameborder="0">'."\n";
} else {
  header('Content-Type: text/html');
  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'
    ."\n<html>\n<head>"
    ."\n<title>SquirrelMail documentation</title>"
    ."\n".'<link rel="stylesheet" type="text/css" href="/css/squirrelmail.css">'
    ."\n</head><body>";
}
?>
<h2 align="center">SquirrelMail documentation</h2>
<h3>SquirrelMail Administrator's Manual</h3>
<p><a href="admin/admin.html">Read it online</a></p>

<h3>SquirrelMail Developer's Manual</h3>
<p><a href="devel/devel.html">Read it online</a></p>

<h3>SquirrelMail Translator's Manual</h3>
<a href="translator/translator.html">Read it online</a>

<h3>SquirrelMail User's Manual</h3>
<p><a href="user/user.html">Read it online</a></p>

<h3>SquirrelMail API Documentation</h3>
<p><a href="phpdoc/" target="_blank">Read it online</a></p>
<?php
// page footer
if (! isset($_GET['iframe'])) {
  echo "</iframe>";
  include (INCLUDES . 'std_footer.inc');
} else {
  echo "\n</body></html>\n";
}
?>