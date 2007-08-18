; This is the default configuration file of SquirrelMail. <?php die(); ?>

[org]
org_name = "SquirrelMail"
org_logo = "SM_PATHimages/sm_logo.png"
org_logo_width = 308
org_logo_height = 111
org_title = "SquirrelMail"
signout_page = ""
frame_top = "_top"
provider_name = ""
provider_uri = ""

[server]
domain = "example.com"
invert_time = 0
useSendmail = 0
smtpServerAddress = "localhost"
smtpPort = 25
encode_header_key = ""
sendmail_path = "/usr/sbin/sendmail"
sendmail_args = "-i -t"
imapServerAddress = "localhost"
imapPort = 143
imap_server_type = "other"
use_imap_tls = 0
use_smtp_tls = 0
smtp_auth_mech = "none"
smtp_sitewide_user = ""
smtp_sitewide_pass = ""
imap_auth_mech = "login"
optional_delimiter = "detect"
pop_before_smtp = 0

[folder]
default_folder_prefix = ""
show_prefix_option = 0
default_move_to_trash = 1
default_move_to_sent  = 1
default_save_as_draft = 1
trash_folder = "INBOX.Trash"
sent_folder  = "INBOX.Sent"
draft_folder = "INBOX.Drafts"
auto_expunge = 1
delete_folder = 0
use_special_folder_color = 1
auto_create_special = 1
list_special_folders_first = 1
default_sub_of_inbox = 1
show_contain_subfolders_option = 0
default_unseen_notify = 2
default_unseen_type   = 1
noselect_fix_enable = 0

[general]
data_dir = "/var/local/squirrelmail/data/"
attachment_dir = "/var/local/squirrelmail/attach/"
dir_hash_level = 0
default_left_size = 150
force_username_lowercase = 0
default_use_priority = 1
hide_sm_attributions = 0
default_use_mdn = 1
edit_identity = 1
edit_name = 1
hide_auth_header = 0
disable_thread_sort = 0
disable_server_sort = 0
allow_charset_search = 1
allow_advanced_search = 0
session_name = "SQMSESSID"

[customization]
user_themes[] = "Default,none"
user_themes[] = "Blue Options,../css/blue_gradient/"
user_theme_default = 0
use_icons = 1
icon_theme_def = 1
icon_themes[] = "No Icons,none"
icon_themes[] = "Template Default Icons,template"
icon_themes[] = "Default Icon Set,../images/themes/default/"
icon_themes[] = "XP Style Icons,../images/themes/xp/"
icon_theme_fallback = 3
aTemplateSet[] = "Default,default"
aTemplateSet[] = "Advanced,default_advanced"
templateset_default = "default"
templateset_fallback = "default"
default_fontsize = ""
fontsets[] = "none,"
fontsets[] = "serif,serif"
fontsets[] = "sans,helvetica,arial,sans-serif"
fontsets[] = "comicsans,comic sans ms,sans-serif"
fontsets[] = "verasans,bitstream vera sans,verdana,sans-serif"
fontsets[] = "tahoma,tahoma,sans-serif"
default_fontset = "none"
default_use_javascript_addr_book = 0
abook_global_file = ""
abook_global_file_writeable = 0
abook_global_file_listing = 1
abook_file_line_length = 2048
motd = ""

[plugins]
plugins = ""
disable_plugins = 0
disable_plugins_user = ""

[database]
addrbook_dsn = ""
addrbook_table = "address"
prefs_dsn = ""
prefs_table = "userprefs"
prefs_key_field = "prefkey"
prefs_key_size = 64
prefs_user_field = "user"
prefs_user_size = 128
prefs_val_field = "prefval"
prefs_val_size = 65536
addrbook_global_dsn = ""
addrbook_global_table = "global_abook"
addrbook_global_writeable = 0
addrbook_global_listing = 0

[language]
squirrelmail_default_language = "en_US"
default_charset = "iso-8859-1"
show_alternative_names   = 0
aggressive_decoding = 0
lossy_encoding = 0
time_zone_type = 0
config_location_base = ""

[tweaks]
use_iframe = 0
use_php_recode = 0
use_php_iconv = 0
allow_remote_configtest = 0
no_list_for_subscribe = 0
config_use_color = 2
ask_user_info = 1

[types]
org_logo = SM_CONF_PATH",40"
plugins = SM_CONF_ARRAY","SM_CONF_ARRAY_SIMPLE
user_themes = SM_CONF_ARRAY","SM_CONF_ARRAY_KEYS",NAME,PATH"
icon_themes = SM_CONF_ARRAY","SM_CONF_ARRAY_KEYS",NAME,PATH"
aTemplateSet = SM_CONF_ARRAY","SM_CONF_ARRAY_KEYS",NAME,ID"
fontsets = SM_CONF_ARRAY","SM_CONF_ARRAY_KEYS",ID,FONT"
