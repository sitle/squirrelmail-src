<!DOCTYPE HTML PUBLIC "-//W3C/DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <title>SquirrelMail - <?php echo $org_title ?></title>
  </head>
  <body bgcolor="#ffffff" text="#000000" link="#0000cc" vlink="0000cc" alink="0000cc">
    <form action="post_login.php" method="post" name="login_form">
      <center>
      <img src="<?php echo $org_logo ?>"><br>
      
      <small>SquirrelMail version <?php echo $sm_version ?><br>
      By the SquirrelMail Development Team<br></small>
      
      <table width="350">
        <tr>
          <td bgcolor="#dcdcdc">
            <b><center><?php echo $org_name ?> Login</center></b>
          </td>
        </tr><tr>
<?php
	
	if (isset($st)) {
		echo "            <td>\n";
		echo "              <center>\n";
		echo "              <font color=\"#cc0000\"><b>";
		if ($st == 'invalid') {
			echo translate("Invalid username or password");
		} else if ($st == 'expired') {
			echo translate("Your session has expired");
		} else {
			echo translate("An unknown error occurred while logging in.");
		}
		echo "              </b></font>\n";
		echo "              </center>\n";
		echo "            </td>\n";
		echo "          </tr><tr>\n";
	}
	
?>
          <td bgcolor="#ffffff">
            <table width="100%">
              <tr>
                <td width="30%" align="right">
                  Username:
                </td><td width="%">
                  <input type="text" name="login_username">
                </td>
              </tr><tr>
                <td width="30%" align="right">
                  Password:
                </td><td width="%">
                  <input type="password" name="login_password">
                </td>
              </tr>
            </table>
          </td>
        </tr><tr>
          <td align="center">
            <input type="submit" name="login_submit" value="Login">
          </td>
        </tr>
      </table>
      </center>
    </form>
  </body>
</html>
