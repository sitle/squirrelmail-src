<?php
/* etc/do_config.php
**
** Jason Bradley Nance <aitrus@tresgeek.net>
**
** Read and write the config file for SM.
*/

/* Make sure we are logged in first */
require_once( 'admin.inc.php' );
header( 'Pragma: no-cache' );
session_start( );
is_logged_in( );

/* BEFORE we include the config file, check to make 
**  sure we aren't submitting the form.  If you include 
**  the config before doing this, you will lose the 
**  changes everytime.
*/

if ( isset( $action ) && !empty( $action ) ) {
/* Logging out.  Destroy the session and redirect */
  switch( $action ) {
    case 'Logout':
      session_unregister( 'admin_is_logged_in' );
      session_destroy( );
      header( "Location: ./" );
      break;
/* Writing changes to the conf file */      
    case 'Save':
      $conf_ary = file( 'sm_config.php' )
        or die( 'Failed to read in config file' );
      foreach( $conf_ary as $index => $line ) {
        if ( strchr( $line, '=' ) ) {
          list( $var, $val ) = explode( '=', $line );
          $var = substr( $var, 1 );
/***** For some reason, $$var isn't working for me!  *****/
          echo 'DEBUG: $var = ' . $var . ', $$var = ' . $$var . '<br>';
          if ( is_string( $$var ) )
            $val = "'" . $$var . "'";
          else
            $val = $$var;
          $conf_ary[ $index ] = '$' . $var . ' = ' . $val . ";\n";
        }
      }
/* Write the config file */
      $fp = fopen( 'sm_config.php', 'w' )
        or die( 'Failed to open config file for writing' );
      foreach( $conf_ary as $line ) {
/***** Getting some weird behavior here too *****/
        echo "DEBUG:  Writing line to file: " . $line;
        fputs( $fp, $line );
      }
      fclose( $fp );
      break;
/* Who are you and what are you doing here? */
    default:
      die( "Unrecognized action" );
  }
}

require_once( 'sm_config.php' );

?>

<html>
<head>
  <title>SquirrelMail Config</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#ffffff">
<form action="<?php echo $PHP_SELF ?>" method="post">
  <table align="center" width="75%">
    <tr>
      <th colspan="3" align="center" nowrap>
        API Selections
      </th>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $mail_api = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="mail_api" value="<?php echo $mail_api ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">mail api config explanation goes here</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $pref_api = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="pref_api" value="<?php echo $pref_api ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">pref api config explanation goes here</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $abook_api = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="abook_api" value="<?php echo $abook_api ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">abook api config explanation goes here</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $log_api = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="log_api" value="<?php echo $log_api ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">log api config explanation goes here</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $send_api = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="send_api" value="<?php echo $send_api ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">send api config explanation goes here</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $auth_api = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="auth_api" value="<?php echo $auth_api ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">auth api config explanation goes here</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $session_api = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="session_api" value="<?php echo $session_api ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">session api config explanation goes here</font>
      </td>
    </tr>
  </table>
  <table align="center" width="75%">
    <tr>
      <th colspan="3" align="center">
        General Organization Configuration
      </th>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $domain = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="domain" value="<?php echo $domain ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">This is your email domain.  For instance, if your email address was "foo@bar.com", your domain would be "bar.com".</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $org_name = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="org_name" value="<?php echo $org_name ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">This is the name of your organization.</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $org_logo = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="org_logo" value="<?php echo $org_logo ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">This is the path to your organization's logo</font>
      </td>
    </tr>
  </table>
  <table align="center" width="75%">
    <tr>
      <th colspan="3" align="center" nowrap>
        API-Specific Configuration
      </th>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $pref_api_filesystem_data_dir = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="pref_api_filesystem_data_dir" value="<?php echo $pref_api_filesystem_data_dir ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">This is the path to your data directory.</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $auth_api_imap_host = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="auth_api_imap_host" value="<?php echo $auth_api_imap_host ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">This is the hostname of your imap server (usually localhost).</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $auth_api_imap_port = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="auth_api_imap_port" value="<?php echo $auth_api_imap_port ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">This is the port your imap server listens on (usually 143).</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $mail_api_host = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="mail_api_host" value="<?php echo $mail_api_host ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">This is the hostname of your mail (smtp) server (usually localhost).</font>
      </td>
    </tr>
    <tr>
      <td align="right" width="33%" nowrap>
        $mail_api_port = 
      </td>
      <td align="left" width="33%">
        <input type="text" name="mail_api_port" value="<?php echo $mail_api_port ?>">
      </td>
      <td align="center" width="33%">
        <font size="-1">This is the port your mail (smtp) server listens on (usually 25).</font>
      </td>
    </tr>
  </table>
  <table align="center" width="75%">
    <tr>
      <td align="right" width="33%">
        <input type="submit" name="action" value="Save">
      </td>
      <td align="center" width="33%">
        <input type="reset">
      </td>
      <td align="left" width="33%">
        <input type="submit" name="action" value="Logout">
      </td>
    </tr>
  </table>
</form>
</body>
</html>
