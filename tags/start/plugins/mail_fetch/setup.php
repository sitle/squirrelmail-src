<?php

function squirrelmail_plugin_init_mail_fetch() {
  global $squirrelmail_plugin_hooks;
  global $mailbox, $imap_stream, $imapConnection;

  $squirrelmail_plugin_hooks['menuline']['mail_fetch'] = 'mail_fetch_link';
  $squirrelmail_plugin_hooks["options_save"]["mail_fetch"] = "mail_fetch_save_pref";
  $squirrelmail_plugin_hooks["loading_prefs"]["mail_fetch"] = "mail_fetch_load_pref";
  $squirrelmail_plugin_hooks["options_link_and_description"]["mail_fetch"] = "mail_fetch_opt";
  $squirrelmail_plugin_hooks["login_verified"]["mail_fetch"] = "mail_fetch_login";

}

function mail_fetch_link() {
  displayInternalLink('plugins/mail_fetch/fetch.php', 'Fetch', '');
  echo '&nbsp;&nbsp;';
}

function mail_fetch_opt() {
  global $color;
  ?>
  <table width=50% cellpadding=3 cellspacing=0 border=0 align=center>
  <tr>
     <td bgcolor="<?php echo $color[9] ?>">
       <a href="../plugins/mail_fetch/options.php">Simple POP3 Fetch Mail</a>
     </td>
  </tr>
  <tr>
     <td bgcolor="<?php echo $color[0] ?>">
        This configures settings for downloading email from a pop3
        mailbox to your account on this server.
     </td>
  </tr>
  </table>
  <?php
}

function mail_fetch_load_pref() {

  global $username,$data_dir;
  global $mailfetch_server,$mailfetch_user,$mailfetch_pass;
  global $mailfetch_lmos, $mailfetch_uidl, $mailfetch_login;

  $mailfetch_server = getPref($data_dir,$username,"mailfetch_server");
  $mailfetch_user = getPref($data_dir,$username,"mailfetch_user");
  $mailfetch_pass = getPref($data_dir, $username, "mailfetch_pass");
  $mailfetch_lmos = getPref($data_dir, $username, "mailfetch_lmos");
  $mailfetch_login = getPref($data_dir, $username, "mailfetch_login");
  $mailfetch_uidl = getPref($data_dir, $username, "mailfetch_uidl");
}

function mail_fetch_save_pref() {
  global $username,$data_dir;
  global $mf_server, $mf_user, $mf_pass, $mf_lmos;
  global $mf_uidl;
  global $mf_login, $submit_mailfetch;

  if (isset($submit_mailfetch)) {

  if (isset($mf_server)) {
    setPref($data_dir,$username,"mailfetch_server",$mf_server);
  } else {
    setPref($data_dir,$username,"mailfetch_server","");
  }

  if (isset($mf_user)) {
    setPref($data_dir,$username,"mailfetch_user",$mf_user);
  } else {
    setPref($data_dir,$username,"mailfetch_user","");
  }

  if (isset($mf_pass)) {
    setPref($data_dir,$username,"mailfetch_pass",$mf_pass);
  } else {
    setPref($data_dir,$username,"mailfetch_pass","");
  }

  if (isset($mf_lmos)) {
    setPref($data_dir,$username,"mailfetch_lmos",$mf_lmos);
  } else {
    setPref($data_dir,$username,"mailfetch_lmos","");
  }

  if (isset($mf_login)) {
    setPref($data_dir,$username,"mailfetch_login",$mf_login);
  } else {
    setPref($data_dir,$username,"mailfetch_login","");
  }

  }
}

function mail_fetch_login() {
//  echo "Checking mail on login...";
}
?>