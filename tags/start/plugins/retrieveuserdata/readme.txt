SquirrelMail Retrieve User Data Plugin 0.4
By Ralf Kraudelt <kraude@wiwi.uni-rostock.de>

This plugin retrieves the full name and the email address of a SquirrelMail
user from an external source (currently only LDAP) and writes them to the user's
preferences file. Your users don't have to enter their name and email address
before they write their first email. If you want to prevent or reduce misuse of
your mail system, you can also forbid them to change their name and email 
address (From: line in email headers). You can also retrieve the users' data on
every login to be up to date with your external source. 

Please read the comments in setup.php for general options and in ldap.php
for LDAP options. 

If you want to access other data sources than LDAP please read the comments
in ldap.php on how to extend this plugin.

If you need help with this, or see improvements that can be made, please
email me directly at the address above.  I definately welcome suggestions
and comments.  This plugin, as is the case with all SquirrelMail plugins,
is not directly supported by the developers.  Please come to me off the
mailing list if you have trouble with it.
(Thanks to Luke Ehresmann <luke@squirrelmail.org> for this paragraph)

