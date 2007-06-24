<?php

/**
 * SquirrelMail Configuration File
 * Created using the configure script, conf.pl
 *
 * ...modified for new configuration class
 */

$this->config_version = '1.5.0';
$this->config_use_color = 2;

$this->org_name      = "SquirrelMail";
$this->org_logo      = SM_PATH . 'images/sm_logo.png';
$this->org_logo_width  = '308';
$this->org_logo_height = '111';
$this->org_title     = "SquirrelMail";
$this->signout_page  = '';
$this->frame_top     = '_top';

$this->provider_uri     = '';

$this->provider_name     = '';

$this->motd = "";

$this->squirrelmail_default_language = 'en_US';
$this->default_charset          = 'iso-8859-1';
$this->show_alternative_names   = false;
$this->aggressive_decoding   = false;
$this->lossy_encoding        = false;

$this->domain                 = 'example.com';
$this->imapServerAddress      = 'localhost';
$this->imapPort               = 143;
$this->useSendmail            = false;
$this->smtpServerAddress      = 'localhost';
$this->smtpPort               = 25;
$this->sendmail_path          = '/usr/sbin/sendmail';
$this->sendmail_args          = '-i -t';
$this->pop_before_smtp        = false;
$this->imap_server_type       = 'other';
$this->invert_time            = false;
$this->optional_delimiter     = 'detect';
$this->encode_header_key      = '';

$this->default_folder_prefix          = '';
$this->trash_folder                   = 'INBOX.Trash';
$this->sent_folder                    = 'INBOX.Sent';
$this->draft_folder                   = 'INBOX.Drafts';
$this->default_move_to_trash          = true;
$this->default_move_to_sent           = true;
$this->default_save_as_draft          = true;
$this->show_prefix_option             = false;
$this->list_special_folders_first     = true;
$this->use_special_folder_color       = true;
$this->auto_expunge                   = true;
$this->default_sub_of_inbox           = true;
$this->show_contain_subfolders_option = false;
$this->default_unseen_notify          = 2;
$this->default_unseen_type            = 1;
$this->auto_create_special            = true;
$this->delete_folder                  = false;
$this->noselect_fix_enable            = false;

$this->data_dir                 = '/var/local/squirrelmail/data/';
$this->attachment_dir           = '/var/local/squirrelmail/attach/';
$this->dir_hash_level           = 0;
$this->default_left_size        = '150';
$this->force_username_lowercase = false;
$this->default_use_priority     = true;
$this->hide_sm_attributions     = false;
$this->default_use_mdn          = true;
$this->edit_identity            = true;
$this->edit_name                = true;
$this->hide_auth_header         = false;
$this->disable_thread_sort      = false;
$this->disable_server_sort      = false;
$this->allow_charset_search     = true;
$this->allow_advanced_search    = 0;

$this->time_zone_type           = 0;

$this->config_location_base     = '';

$this->disable_plugins          = false;
$this->disable_plugins_user     = '';


$this->user_theme_default = 0;
$this->user_themes[0]['PATH'] = 'none';
$this->user_themes[0]['NAME'] = 'Default';
$this->user_themes[1]['PATH'] = SM_PATH . 'css/blue_gradient/';
$this->user_themes[1]['NAME'] = 'Blue Options';

$this->icon_theme_def = 1;
$this->icon_theme_fallback = 3;
$this->icon_themes[0]['PATH'] = 'none';
$this->icon_themes[0]['NAME'] = 'No Icons';
$this->icon_themes[1]['PATH'] = 'template';
$this->icon_themes[1]['NAME'] = 'Template Default Icons';
$this->icon_themes[2]['PATH'] = SM_PATH . 'images/themes/default/';
$this->icon_themes[2]['NAME'] = 'Default Icon Set';
$this->icon_themes[3]['PATH'] = SM_PATH . 'images/themes/xp/';
$this->icon_themes[3]['NAME'] = 'XP Style Icons';

$this->templateset_default = 'default';
$this->templateset_fallback = 'default';
$this->aTemplateSet[0]['ID'] = 'default';
$this->aTemplateSet[0]['NAME'] = 'Default';
$this->aTemplateSet[1]['ID'] = 'default_advanced';
$this->aTemplateSet[1]['NAME'] = 'Advanced';

$this->default_fontsize = '';
$this->default_fontset = '';

$this->fontsets = array();
$this->fontsets['serif'] = 'serif';
$this->fontsets['verasans'] = 'bitstream vera sans,verdana,sans-serif';
$this->fontsets['comicsans'] = 'comic sans ms,sans-serif';
$this->fontsets['sans'] = 'helvetica,arial,sans-serif';
$this->fontsets['tahoma'] = 'tahoma,sans-serif';

$this->default_use_javascript_addr_book = false;
$this->addrbook_dsn = '';
$this->addrbook_table = 'address';

$this->prefs_dsn = '';
$this->prefs_table = 'userprefs';
$this->prefs_user_field = 'user';
$this->prefs_user_size = 128;
$this->prefs_key_field = 'prefkey';
$this->prefs_key_size = 64;
$this->prefs_val_field = 'prefval';
$this->prefs_val_size = 65536;

$this->addrbook_global_dsn = '';
$this->addrbook_global_table = 'global_abook';
$this->addrbook_global_writeable = false;
$this->addrbook_global_listing = false;

$this->abook_global_file = '';
$this->abook_global_file_writeable = false;

$this->abook_global_file_listing = true;

$this->abook_file_line_length = 2048;

$this->no_list_for_subscribe = false;
$this->smtp_auth_mech        = 'none';
$this->smtp_sitewide_user    = '';
$this->smtp_sitewide_pass    = '';
$this->imap_auth_mech        = 'login';
$this->use_imap_tls          = 0;
$this->use_smtp_tls          = 0;
$this->session_name          = 'SQMSESSID';
$this->only_secure_cookies   = true;

$this->use_iframe = false;
$this->ask_user_info = true;
$this->use_icons = true;

$this->use_php_recode = false;
$this->use_php_iconv = false;

$this->allow_remote_configtest = false;