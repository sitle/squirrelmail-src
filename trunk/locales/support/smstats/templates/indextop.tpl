<?php
ob_start("ob_gzhandler");
$title="Translation statistics";
$location="/ KDE Internationalization Home / GUI Statistics";
include("header.php");
?>

<h2>Translation statistics "TOP 10" for {TXT_REV} branch from {TXT_DATE}</h2>

<table cellpadding="1" cellspacing="0" border="0" width="100%" bgcolor="#8b898b"><tr><td>
<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#ececec"><tr><td>
<table cellpadding="2" cellspacing="0" border="0" width="100%">
<tr>
  <td><font size="2"><b>
  
  <a class="package" href="../index.php">top</a> <font color="#ff0000">&gt;</font>
  {TXT_REV} <font color="#ff0000">&gt;</font>
  
  </b></font></td>
  <td align="center"><font size="2">
    <a href="index.php">info by team</a> | 
    <a href="packages.php">info by package</a> |
    <a href="fullinfo.php">full info page</a> |
    top 10 |
    <a href="essential.php">essential</a> |
    <a href="partial/index.php">partial</a> |
    <a href="general.php">general info</a>
  </font></td>
  <td align="right"><font size="2">
    last update: <b>{TXT_RUNDATE}</b> &nbsp;
  </font></td>
</tr>
</table>
</td></tr></table>
</td></tr></table>

<img src="../img/px.png" height="5" width="1"><br>
          
<table cellspacing="0" cellpadding="0" border="0" bgcolor="#8b898b" width="100%"><tr><td>
<table cellspacing="1" cellpadding="2" border="0" width="100%">
<tr bgcolor="#e0e0e0">
  <td><font size="2"><b>position</b></font></td>
  <td><font size="2"><b>team name</b></font></td>
  <td><font size="2" color="#339933"><b>translated</b></font></td>
  <td align="center"><font size="2" color="#339933"><b>%</b></font></td>
  <td><font size="2" color="#333399"><b>fuzzy</b></font></td>
  <td align="center"><font size="2" color="#333399"><b>%</b></font></td>
  <td><font size="2" color="#dd3333"><b>untranslated</b></font></td>
  <td align="center"><font size="2" color="#dd3333"><b>%</b></font></td>
  <td><font size="2"><b>total</b></font></td>
  <td><font size="2"><b>graph</b></font></td>
</tr>
{TABLE}
</table>
</td></tr>
</table>

<font size="2">
<br>
<b>Note:</b><br>
&nbsp;&nbsp;<b>1.</b> Only translated messages count for TOP 10.<br>
&nbsp;&nbsp;<b>2.</b> Teams with same number of translated messages are alphabeticaly
sorted by team code.<br>

<br>
<b>Legend:</b><br>
&nbsp;&nbsp;<img src="../img/bar0.png" height="15" width="30"> - translated messages<br>
&nbsp;&nbsp;<img src="../img/bar4.png" height="15" width="30"> - fuzzy messages<br>
&nbsp;&nbsp;<img src="../img/bar1.png" height="15" width="30"> - untranslated messages<br>
&nbsp;&nbsp;<img src="../img/bar6.png" height="15" width="30"> - info not available<br>
<br>
</font>


<img src="../img/px.png" height="5" width="1"><br>
<table cellpadding="1" cellspacing="0" border="0" width="100%" bgcolor="#8b898b">
<tr><td>
<table cellpadding="2" cellspacing="0" border="0" width="100%" bgcolor="#ececec">
<tr align="center"><td><font size="2">

<a href="../index.php">index</a> |
<a href="../about.php">about</a> |
<a href="../help.php">help</a>

</font></td></tr></table>
</td></tr></table>

<?php include("footer.php"); ob_end_flush(); ?>
