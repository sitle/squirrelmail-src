%define basedir     /var/www/squirrelmail
%define varlibdir   %{_libdir}/squirrelmail
%define prefsdir    %{varlibdir}/prefs
%define varspooldir /var/spool/squirrelmail
%define attdir      %{varspooldir}/attach
%define webdir      /var/www/html
%define etcdir      %{_sysconfdir}/squirrelmail
%define crondir     %{_sysconfdir}/cron.daily

Summary: Squirrelmail is a webmail client for PHP4.
Name: squirrelmail
Version: 1.2.7_cvs
Release: 1
License: GPL
Vendor: http://www.squirrelmail.org/
Group: Applications/Internet
Source0: %{name}-%{version}.tar.gz
Packager: Konstantin Riabitsev <icon@duke.edu>
BuildRoot: /var/tmp/%{name}-%{version}-root
BuildArch: noarch
Requires: apache >= 1.3.19 php >= 4.0.4 perl tmpwatch >= 2.8
Requires: /usr/sbin/sendmail

%description
SquirrelMail is a standards-based webmail package written in PHP4. It
includes built-in pure PHP support for the IMAP and SMTP protocols,
and all pages render in pure HTML 4.0 (with no Javascript) for maximum
compatibility across browsers.  It has very few requirements and is
very easy to configure and install. SquirrelMail has all the
functionality you would want from an email client, including strong
MIME support, address books, and folder manipulation.

%package poutils
Summary: Some i18n development tools for SquirrelMail.
Group: Applications/Internet
Requires: %{name} = %{version}

%description poutils
This package includes some development tools for squirrelmail
i18n, including the main po file and some compilation scripts.

%prep
%setup -q -n %{name}-%{version}
# organize the docs nicely!
mv AUTHORS ChangeLog COPYING INSTALL MIRRORS README UPGRADE doc/
mv ReleaseNotes doc/ReleaseNotes.txt
mv themes/README.themes doc/
mkdir -p -m0755 doc/plugins/administrator
mkdir -p -m0755 doc/plugins/bug_report
mkdir -p -m0755 doc/plugins/calendar
mkdir -p -m0755 doc/plugins/delete_move_next
mkdir -p -m0755 doc/plugins/filters/bulkquery
mkdir -p -m0755 doc/plugins/info
mkdir -p -m0755 doc/plugins/listcommands
mkdir -p -m0755 doc/plugins/mail_fetch
mkdir -p -m0755 doc/plugins/newmail
mkdir -p -m0755 doc/plugins/sent_subfolders
mkdir -p -m0755 doc/plugins/spamcop
mkdir -p -m0755 doc/plugins/squirrelspell
mkdir -p -m0755 doc/plugins/translate
mv plugins/README.plugins doc/plugins/
pushd plugins/administrator
	mv INSTALL ../../doc/plugins/administrator 
popd
pushd plugins/bug_report
	mv INSTALL README ../../doc/plugins/bug_report
popd
pushd plugins/calendar
	mv README ../../doc/plugins/calendar
popd
pushd plugins/delete_move_next
	mv README ../../doc/plugins/delete_move_next
popd
pushd plugins/filters
	mv CHANGES README ../../doc/plugins/filters
	mv bulkquery/INSTALL bulkquery/README \
		../../doc/plugins/filters/bulkquery
popd
pushd plugins/info
	mv README ../../doc/plugins/info
popd
pushd plugins/listcommands
	mv README ../../doc/plugins/listcommands
popd
pushd plugins/mail_fetch
	mv README ../../doc/plugins/mail_fetch 
popd
pushd plugins/newmail 
	mv HISTORY README ../../doc/plugins/newmail
popd
# pushd plugins/sent_subfolders; mv... eh? NO docs? 
pushd plugins/spamcop 
	mv README ../../doc/plugins/spamcop
popd
pushd plugins/squirrelspell 
	mv INSTALL ../../doc/plugins/squirrelspell
	rm -f doc/index.php 
	mv doc/* ../../doc/plugins/squirrelspell
  	rm -rf doc
popd
pushd plugins/translate 
	mv INSTALL README ../../doc/plugins/translate
popd

# remove the silly make_archive.pl stuff from plugins/. It's supposed to be
# run on sourceforge and shouldn't be in the releases.
rm -f plugins/make_archive.pl
# fix default_pref
echo "left_refresh=300" >> data/default_pref
# fix paths
%{__perl} -pi -e "s|__ATTDIR__|%{attdir}|g" contrib/RPM/cleanup.cron
%{__perl} -pi -e "s|__PREFSDIR__|%{prefsdir}/|g; s|__ATTDIR__|%{attdir}/|g" \
	contrib/RPM/config.php.rh7
mv contrib/RPM/RPM.readme doc/

%build

%install
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT
mkdir -p -m0755 $RPM_BUILD_ROOT%{etcdir}
mkdir -p -m0755 $RPM_BUILD_ROOT%{prefsdir}
mkdir -p -m0755 $RPM_BUILD_ROOT%{attdir}
mkdir -p -m0755 $RPM_BUILD_ROOT%{basedir}
mkdir -p -m0755 $RPM_BUILD_ROOT%{webdir}
mkdir -p -m0755 $RPM_BUILD_ROOT%{crondir}

# install default_pref
install -m 0644 data/default_pref $RPM_BUILD_ROOT%{prefsdir}

# install the config files
mkdir -p -m0755 $RPM_BUILD_ROOT%{basedir}/config
install -m 0644 config/*.php $RPM_BUILD_ROOT%{basedir}/config/
install -m 0644 contrib/RPM/config.php.rh7 $RPM_BUILD_ROOT%{etcdir}/config.php
# symlink
ln -s %{etcdir}/config.php $RPM_BUILD_ROOT%{basedir}/config/config.php
install -m 0755 config/*.pl $RPM_BUILD_ROOT%{basedir}/config/

# install index.php
install -m 0644 index.php $RPM_BUILD_ROOT%{basedir}/
# install functions
mkdir -p -m0755 $RPM_BUILD_ROOT%{basedir}/functions
install -m 0644 functions/* $RPM_BUILD_ROOT%{basedir}/functions/
# install src
mkdir -p -m0755 $RPM_BUILD_ROOT%{basedir}/src
install -m 0644 src/* $RPM_BUILD_ROOT%{basedir}/src/
# install themes
mkdir -p -m0755 $RPM_BUILD_ROOT%{basedir}/themes
install -m 0644 themes/*.php $RPM_BUILD_ROOT%{basedir}/themes/
mkdir -p -m0755 $RPM_BUILD_ROOT%{basedir}/themes/css
install -m 0644 themes/css/*.css $RPM_BUILD_ROOT%{basedir}/themes/css/
# install images
mkdir -p -m0755 $RPM_BUILD_ROOT%{basedir}/images
install -m 0644 images/* $RPM_BUILD_ROOT%{basedir}/images/

# install the plugins
# do a cp -rp, we'll figure out the files later.
cp -rp plugins $RPM_BUILD_ROOT%{basedir}

# install the locales.
# do a cp -rp as well.
cp -rp locale $RPM_BUILD_ROOT%{basedir}

# install help files
# just cp -rp it.
cp -rp help $RPM_BUILD_ROOT%{basedir}

# po will go into the poutils package, so just copy it
cp -rp po $RPM_BUILD_ROOT%{basedir}

# install the cron script
install -m 0755 contrib/RPM/cleanup.cron \
	$RPM_BUILD_ROOT/%{crondir}/squirrelmail-cleanup.cron

# symlink to %{basedir} from %{webdir}.
ln -s %{basedir} $RPM_BUILD_ROOT/%{webdir}/webmail

%post
# fix the hostname
MYHOST=`hostname -f`
for CONFILE in %{etcdir}/config.php*; do
    if [ -f "$CONFILE" ]; then
        if %{__grep} -q "__HOSTNAME__" $CONFILE; then
            %{__perl} -pi -e "s|__HOSTNAME__|$MYHOST|g" $CONFILE
        fi
    fi
done
# check if file_uploads is set to Off and warn if so.
if %{__grep} -qi "file_uploads[[:space:]]*=[[:space:]]*off" \
	%{_sysconfdir}/php.ini; then
	echo "WARNING:"
	echo "I am detecting that your php is set to disallow file uploads."
	echo "This will not allow mail sending in SquirrelMail."
	echo "To fix this problem, set:"
	echo "file_uploads = On"
	echo "in your %{_sysconfdir}/php.ini"
	echo "then restart apache using 'service httpd restart'"
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root)
%config %dir %{etcdir}
%config(noreplace) %{etcdir}/config.php
%doc doc/*
%dir %{basedir}
%dir %{varlibdir}
%dir %{varspooldir}
%{basedir}/config
%{basedir}/functions
%{basedir}/help
%{basedir}/images
%{basedir}/locale
%{basedir}/plugins
%{basedir}/src
%{basedir}/themes
%{basedir}/index.php
%attr(0700, apache, apache) %dir %{prefsdir}
%attr(0700, apache, apache) %dir %{attdir}
%{prefsdir}/default_pref
%{webdir}/webmail
%{crondir}/squirrelmail-cleanup.cron

%files poutils
%defattr(-,root,root)
%{basedir}/po

%changelog
* Thu Jun 20 2002 Konstantin Riabitsev <icon@duke.edu>
  - Incorporating RPM-related files into the core.

* Tue Apr 30 2002 Konstantin Riabitsev <icon@duke.edu>
 Squirrelmail:
  - A complete MagicHTML rewrite since the existing codebase was
    causing too many XSS problems. Hopefully now Nick Cleaton will
    leave us alone. :) Testing credits go to Nick.
  - Fix for cross-site scripting vulnerability (bug #545933)
    Reported by Nick Cleaton.
  - Changing "emtpy" to "purge" for more clarity.
  - Fix for cross-site scripting vulnerability (bug #544658)
    Reported by Nick Cleaton.
  - Fix for incorrect word wrap in Opera (bug #495073)
  - Workaround for older prefs: some of them contain "None" for
    left_refresh (bug #540108)
  - Fix for entities in cc and bcc fields on message display (bug #522493)
  - Fixes for quoted values in the addressbook by David Rees (bug #538389)
  - Fixed src/src problem (bug #538803)
  - Fixed so non-ascii searches no longer fail both when searching
    and when applying filters (bug #520918)
  - Added POP3 Before SMTP option (feature request: #498428)
  - Added a server-side thread sorting option per folder
  - Added a server-side sorting global option
  - Compose in new window size can be set in Display prefs.
  - Logout error system unified.
  - Fix for a "theme passed as cookie" exploit.
  - PostgreSQL is now supported for database backed use
  - Added user option to sort messages by internal date
  - Changed attachment handling now attachments are adressed to 
    unique compose session.
  - Added forward messages as message/rfc822 attachment
  - Fixed handling message/rfc822 attachments
  - Fixed folder list display when special folders have subfolders
  - Added option to auto-append sig before reply/forward text (523853)
  - Fixed subfolders being "orphaned" when renaming parents (498167)
  - Filters can be applied to only new mail.
  - Filters are updated when renaming/deleting folders (512056)
  - Filtering now happens on login (filters plugin)
  - Added option for WIDTH and HEIGHT tags to Org. Logo. (patch #412754)
  - Fixed resume draft bug #513521, #514639
  - Newmail plugin: admin can disable the use of audio (patch #517698)
  - Fixed quoting problem in safe html (patch #516542)
  - SPAM folder no longer special folder (filters plugin)
  - Filtering now happens on folder list refresh (filters plugin)
  - Added checking of input of the folders page
  - Made erronous deleting of folders harder (patch #514208)
  - Made SquirrelMail display \Noselect nodes in Cyrus also made it 
    impossible to try to delete \Noselect nodes. (patch #452178)
  - SquirrelSpell version 0.3.8 -- pretty configuration error reporting
    added by popular demand.
  - Improved the handling of IMAP [PARSE] messages to reduce retrieval error.
  - Fixed small bug in handeling timezone (bug #536149)	
  - MDN message now RFC compatible (bug #537662)
  - Fixed html tables in printer_friendly_bottom.php (patch #542367)
  - Fixed return address of MDN receipts when having multiple identities
    (patch #530139)
 RPM:
  - Updated default config.

* Fri Feb 22 2002 Konstantin Riabitsev <icon@duke.edu>
 Squirrelmail:
  - Multiple mailbox list calls cached.
  - Added 'View unsafe images' link to the bottom of pages which contain
    unsafe images.
  - Fixed 'too many close table tags' and various other issues
    which meant SM output didn't always validate as clean HTML.
  - Added the ability to add special folders through plugins.
  - Added an Always compose in a pop-up window option.
  - Search page update with ability to save searches and search
    all folders at once.
  - Made searching on multiple criteria possible, with thanks to Jason Munro
  - Fixed 'list all' in addressbook (#506624, thanks to Kurt Yoder)
  - Fixed small bugs in db_prefs
  - Allowed SquirrelMail to work from within a frame, eg. not using _top
    this is configureable. (thanks to Simon Dick)
  - Added options to conf.pl to enable automated plugin installation:
    ./conf.pl --install-plugin <pluginname>. This allows plugins to be
    distributed in packages. Conf.pl now also reports when saving fails.
  - Attachment hooks now also allow specification of generic rules like
    text/* which will be used when no specific rule is available.
  - conf.pl can now configure database backed address books and
    preferences.
  - Version 0.3.7 of SquirrelSpell. Fixes a potential privacy
    vulnerability (symlink attack), plus introduces formatting fixes
    and javadoc-style comments.
  - Bugfix in mailfetch reported by Mateusz Mazur
  - Administrator plugin. A web based conf.pl replacement.
  - Removed GLOBALS from conf.pl
  - HTML messages optimization.
  - Added support for requesting read receipts (MDN) and delivery receipts.
  - Added the ability to stop users changing their names and email addresses.
  - Added signature into multiple identities (Stefan Meier 
    <Stefan.Meier@cimsource.com>)
  - Updated user help files to reflect UI chanegs and added functionality.
 RPM:
  - Release for 1.2.5
  - Added a default pref to refresh the left panel after 5 minutes to
    combat the frequent timeout complaints.

* Thu Jan 24 2002 Konstantin Riabitsev <icon@duke.edu>
- Fix for the horrible remote execution bug in squirrelspell. 
  (/me shuts his ears in the door. Bad Dobby!)
- Check whether we need to edit the config.php file before actually
  sed'ing it.
- Release for 1.2.4.

* Wed Jan 23 2002 Konstantin Riabitsev <icon@duke.edu>
- Setting 0755 permissions on the created directories so people
  running the --rebuild with umask 007 don't get a broken install.
- cp'ing the RPM.readme file instead of moving it.
- cp -rp'ing some dirs instead of mv'ing them
- minor bugfixes to %files section.
- organizing docs for the spamcop plugin now as well
- fixes in the default config -- trailing slashes added to the prefsdir
  and attach_dir (squirrelmail's bug, really).

* Mon Jan 21 2002 Konstantin Riabitsev <icon@duke.edu>
- Package for Squirrelmail-1.2.3.
- Accommodation for the themes/css directory.
- Added RPM.readme and a warning message for upgraders
  (I know, I hate myself, too. ;))

* Wed Jan 02 2002 Konstantin Riabitsev <icon@duke.edu>
- Updated paths as per the list discussion. If you are upgrading, please
  move the existing preferences from /var/squirrelmail/prefs to
  /var/lib/squirrelmail/prefs
- A cleanup cron script. Runs daily and cleans up any old temp attachments
  files in the attachment dir (10 days or older).

* Tue Jan 01 2002 Konstantin Riabitsev <icon@duke.edu>
- Package for the 1.2.2 release.

* Fri Dec 28 2001 Konstantin Riabitsev <icon@duke.edu>
- Changed Requires: sendmail to Requires: /usr/sbin/sendmail so people
  with Postfix can install it as well.
- Changed the chowning of the /var/squirrelmail/* to doing it the right
  way, with %attr's.

* Tue Dec 25 2001 Konstantin Riabitsev <icon@duke.edu>
- First spec file build for RH7.
- Locale patches.
- Fixed russian po/mo files (still screws up on occasion! php/gettext is 
  so broken!).
- Default config for RH7 systems.
