<?php
/*
** etc/index.php
**
** Jason Bradley Nance <aitrus@tresgeek.net>
**
** Web-based configuration for SM2
*/

require_once( 'admin.inc.php' );
header( 'Pragma: no-cache' );

/* Check to see if they setup someone with rights */
if ( !isset( $AdminUser ) || empty( $AdminUser ) ) 
  die( "No admin user set.  Configuration impossible." );
if ( !isset( $AdminPass ) || empty( $AdminPass ) ) 
  die( "No admin password set.  Configuration impossible." );

?>

<html>
<head>
  <title>SquirrelMail Configuration :: Login</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#ffffff">
<br>
<br>
<br>
<br>
<form action="verify.php" method="post">
  <table align="center">
    <tr>
      <th colspan="2" align="center" bgcolor="#dcdcdc">
        Squirrel Mail Configuration
      </th>
    </tr>
    <tr>
      <td align="right">
        Admin User:
      </td>
      <td align="left">
        <input type="text" name="auser">
      </td>
    </tr>
    <tr>
      <td align="right">
        Password:
      </td>
      <td align="left">
        <input type="password" name="apass">
      </td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <input type="submit" name="action" value="Login">
      </td>
    </tr>
  </table>
</form>
</body>
</html>

