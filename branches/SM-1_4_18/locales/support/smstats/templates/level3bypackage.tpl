<?php 
ob_start("ob_gzhandler");
$title="KDE's GUI messages translation statistics";
$location="/ KDE Internationalization Home / GUI Statistics";
include("header.php"); 
?>

<h2>{TXT_REV} branch / {TXT_PACKAGE2} package / {TXT_TEAMNAME} team from {TXT_DATE}</h2>

<table cellpadding="1" cellspacing="0" border="0" width="100%" bgcolor="#8b898b"><tr><td>
<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#ececec"><tr><td>
<table cellpadding="2" cellspacing="0" border="0" width="100%">
<tr>
  <td><font size="2"><b>

<a class="package" href="../../../index.php">top</a> <font color="#ff0000">&gt;</font>
<a class="package" href="../../index.php">{TXT_REV}</a><font color="#ff0000"> &gt;</font>
<a class="package" href="../index.php">{TXT_PACKAGE}</a> <font color="#ff0000">&gt;</font>
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

<table cellspacing="0" cellpadding="0" border="0" bgcolor="#8b898b" width="100%"><tr><td>
<table cellspacing="1" cellpadding="2" border="0" width="100%">
<tr bgcolor="#e0e0e0">
  <td><font size="2"><nobr><b>PO file</b></font></nobr></td>
  <td><font size="2" color="#339933"><b>translated</b></font></td>
  <td align="center"><font size="2" color="#339933"><b>%</b></font></td>
  <td><font size="2" color="#333399"><b>fuzzy</b></font></td>
  <td align="center"><font size="2" color="#333399"><b>%</b></font></td>
  <td><font size="2" color="#dd3333"><b>untranslated</b></font></td>
  <td align="center"><font size="2" color="#dd3333"><b>%</b></font></td>
  <td><font size="2"><b>total</b></font></td>
  <td width="100%"><font size="2"><b>graph</b></font></td>
</tr>
{TABLE}
</table>
</td></tr>
</table>

<font size="2">
<br>
<b>Note:</b><br>
&nbsp;&nbsp;<b>1.</b> Only PO catalogs which have coresponding POT file are included in statistics.<br>
&nbsp;&nbsp;<b>2.</b> Totals count against number of messages found in PO files. For example, if, 
by error, translated POs have a different numbers of messages from coresponding POT, 
then you may observe differences.<br>
&nbsp;&nbsp;<b>3.</b> A row with red background show that the coresponding 
team have an PO file which contain at least one error.<br>
&nbsp;&nbsp;<b>4.</b> A row with blue background show that the coresponding 
PO file have no POT file associated with it.<br>

<br>
<b>Legend:</b><br>
&nbsp;&nbsp;<img src="../../../img/bar0.png" height="15" width="30"> - translated messages<br>
&nbsp;&nbsp;<img src="../../../img/bar4.png" height="15" width="30"> - fuzzy messages<br>
&nbsp;&nbsp;<img src="../../../img/bar1.png" height="15" width="30"> - untranslated messages.<br>
&nbsp;&nbsp;<img src="../../../img/bar6.png" height="15" width="30"> - info not available<br>
<br>
</font>

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
