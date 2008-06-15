<?php 
ob_start("ob_gzhandler");
$title="KDE's GUI messages translation statistics";
$location="/ KDE Internationalization Home / GUI Statistics";
include("header.php"); 
?>

<h2>{TXT_REV} branch /  {TXT_TEAMNAME} team partialy translated files from {TXT_DATE}</h2>

<table cellpadding="1" cellspacing="0" border="0" width="100%" bgcolor="#8b898b"><tr><td>
<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#ececec"><tr><td>
<table cellpadding="2" cellspacing="0" border="0" width="100%">
<tr>
  <td><font size="2"><b>

<a class="package" href="../../../index.php">top</a> <font color="#ff0000">&gt;</font>
<a class="package" href="../../index.php">{TXT_REV}</a><font color="#ff0000"> &gt;</font>
<a class="package" href="../index.php">partial</a> <font color="#ff0000">&gt;</font>
{TXT_TEAMCODE} <font color="#ff0000">&gt;</font>

  </b></font></td>
  <td align="right"><font size="2">
  last update: <b>{TXT_RUNDATE}</b> &nbsp; 
  </font></td>
</tr>
</table>
</td></tr></table>
</td></tr></table>

<img src="../../../img/px.png" height="5" width="1"><br>



<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr valign="top">
<td width="120">


<table cellspacing="0" cellpadding="0" border="0" bgcolor="#8b898b" width="120"><tr><td>
<table cellspacing="1" cellpadding="2" border="0" width="100%">
<tr bgcolor="#e0e0e0">
  <td><font size="2"><b>teams</b></font></td>
</tr>
{TEAMLIST}
</table>
</td></tr>
</table>


</td>
<td width="10"><img src="../../../img/px.png" height="1" width="10"></td>
<td width="100%">

<a href="#partialy">Partialy Translated PO files</a> | 
<a href="#totaly">Totaly Untranslated PO files</a>   |
<a href="#obsolete">Obsolete Translated PO files</a> |
<a href="../../{TXT_TEAMCODE}/index.php">Full statistics</a><br>
<hr noshade size="1">
{CONTENT}


</td>
</tr>
</table>


<img src="../../../img/px.png" height="5" width="1"><br>
<table cellpadding="1" cellspacing="0" border="0" width="100%" bgcolor="#8b898b">
<tr><td>
<table cellpadding="2" cellspacing="0" border="0" width="100%" bgcolor="#ececec">
<tr align="center"><td><font size="2">

<a href="../../../index.php">index</a> |
<a href="../../../about.php">about</a> |
<a href="../../../help.php">help</a>

</font></td></tr></table>
</td></tr></table>

<?php include("footer.php"); ob_end_flush(); ?>
