##
# $Id$
%define spec_release 1

##
# By default build rhl7 version. Rebuild with 
# rpmbuild --rebuild --define 'rhl7 0' squirrelmail-1.2.x.src.rpm
# to build for rhl8^Htnv. :)
# If you want rhl8^Htnv to build by default, just flip the value to 0 in
# the next line.
%{!?rhl7:%define rhl7 1}
%if %{rhl7}
	%define webserver apache
    %define rpm_release %{spec_release}.7.x
%else
	%define webserver httpd
    %define rpm_release %{spec_release}.8.x
%endif

Summary: SquirrelMail webmail client
Name: squirrelmail
Version: 1.2.9
Release: %{rpm_release}
License: GPL
URL: http://www.squirrelmail.org/
Vendor: squirrelmail.org
Group: Applications/Internet
Source: %{name}-%{version}.tar.gz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Requires: %{webserver}, php >= 4.0.4, perl, tmpwatch >= 2.8, aspell
Requires: /usr/sbin/sendmail
Prereq: %{webserver}, perl
BuildPrereq: perl

%description
SquirrelMail is a standards-based webmail package written in PHP4. It
includes built-in pure PHP support for the IMAP and SMTP protocols, and
all pages render in pure HTML 4.0 (with no Javascript) for maximum
compatibility across browsers.  It has very few requirements and is very
easy to configure and install. SquirrelMail has all the functionality
you would want from an email client, including strong MIME support,
address books, and folder manipulation.

%prep
%setup -q
rm -f plugins/make_archive.pl

# Rearrange the documentation
mv AUTHORS ChangeLog COPYING INSTALL README UPGRADE doc/
mv ReleaseNotes doc/ReleaseNotes.txt
mv themes/README.themes doc/
for f in `find plugins -name "README*" -or -name INSTALL \
		   -or -name CHANGES -or -name HISTORY`; do
    mkdir -p doc/`dirname $f`
    mv $f $_
done
mv doc/plugins/squirrelspell/doc/README doc/plugins/squirrelspell
rmdir doc/plugins/squirrelspell/doc
mv plugins/squirrelspell/doc/* doc/plugins/squirrelspell
rm -f doc/plugins/squirrelspell/index.php
rmdir plugins/squirrelspell/doc

# Fixup various files
echo "left_refresh=300" >> data/default_pref
for f in contrib/RPM/squirrelmail.cron contrib/RPM/config.php.rh7; do
    perl -pi -e "s|__ATTDIR__|%{_localstatedir}/spool/squirrelmail/attach/|g;"\
	     -e "s|__PREFSDIR__|%{_localstatedir}/lib/squirrelmail/prefs/|g;" $f
done


%install
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT
mkdir -p -m0755 $RPM_BUILD_ROOT%{_sysconfdir}/squirrelmail
mkdir -p -m0755 $RPM_BUILD_ROOT%{_localstatedir}/lib/squirrelmail/prefs
mkdir -p -m0755 $RPM_BUILD_ROOT%{_localstatedir}/spool/squirrelmail/attach
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/squirrelmail
mkdir -p -m0755 $RPM_BUILD_ROOT%{_sysconfdir}/cron.daily

# install default_pref
install -m 0644 data/default_pref \
    $RPM_BUILD_ROOT%{_localstatedir}/lib/squirrelmail/prefs

# install the config files
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/squirrelmail/config
install -m 0644 config/*.php $RPM_BUILD_ROOT%{_datadir}/squirrelmail/config/
install -m 0644 contrib/RPM/config.php.rh7 \
    $RPM_BUILD_ROOT%{_sysconfdir}/squirrelmail/config.php
ln -s ../../../..%{_sysconfdir}/squirrelmail/config.php \
    $RPM_BUILD_ROOT%{_datadir}/squirrelmail/config/config.php
install -m 0755 config/*.pl $RPM_BUILD_ROOT%{_datadir}/squirrelmail/config/

# install index.php
install -m 0644 index.php $RPM_BUILD_ROOT%{_datadir}/squirrelmail/

# install functions
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/squirrelmail/functions
install -m 0644 functions/* $RPM_BUILD_ROOT%{_datadir}/squirrelmail/functions/

# install src
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/squirrelmail/src
install -m 0644 src/* $RPM_BUILD_ROOT%{_datadir}/squirrelmail/src/

# install themes
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/squirrelmail/themes
install -m 0644 themes/*.php $RPM_BUILD_ROOT%{_datadir}/squirrelmail/themes/
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/squirrelmail/themes/css
install -m 0644 themes/css/*.css \
    $RPM_BUILD_ROOT%{_datadir}/squirrelmail/themes/css/

# install images
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/squirrelmail/images
install -m 0644 images/* $RPM_BUILD_ROOT%{_datadir}/squirrelmail/images/

# install the plugins
cp -rp plugins $RPM_BUILD_ROOT%{_datadir}/squirrelmail

# install the locales.
cp -rp locale $RPM_BUILD_ROOT%{_datadir}/squirrelmail

# install help files
cp -rp help $RPM_BUILD_ROOT%{_datadir}/squirrelmail

# install the cron script
install -m 0755 contrib/RPM/squirrelmail.cron \
    $RPM_BUILD_ROOT/%{_sysconfdir}/cron.daily/

%if %{rhl7}
# symlink from /var/www/html/webmail to /usr/share/squirrelmail
mkdir -p -m0755 $RPM_BUILD_ROOT/var/www/html
ln -s %{_datadir}/squirrelmail $RPM_BUILD_ROOT/var/www/html/webmail
%else
# install the config file
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d
install -m 644 contrib/RPM/squirrelmail.conf \
	$RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/
%endif


%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root)
%config %dir %{_sysconfdir}/squirrelmail
%config(noreplace) %{_sysconfdir}/squirrelmail/config.php
%if %{rhl7}
  /var/www/html/webmail
%else
  %config(noreplace) %{_sysconfdir}/httpd/conf.d/*.conf
%endif
%doc doc/*
%dir %{_datadir}/squirrelmail
%dir %{_localstatedir}/lib/squirrelmail
%dir %{_localstatedir}/spool/squirrelmail
%{_datadir}/squirrelmail/config
%{_datadir}/squirrelmail/functions
%{_datadir}/squirrelmail/help
%{_datadir}/squirrelmail/images
%{_datadir}/squirrelmail/locale
%{_datadir}/squirrelmail/plugins
%{_datadir}/squirrelmail/src
%{_datadir}/squirrelmail/themes
%{_datadir}/squirrelmail/index.php
%attr(0700, apache, apache) %dir %{_localstatedir}/lib/squirrelmail/prefs
%attr(0700, apache, apache) %dir %{_localstatedir}/spool/squirrelmail/attach
%{_localstatedir}/lib/squirrelmail/prefs/default_pref
%{_sysconfdir}/cron.daily/squirrelmail.cron

%changelog
* Tue Oct 29 2002 Konstantin Riabitsev <icon@duke.edu> 1.2.9-1
- Upping version number.

* Sat Sep 14 2002 Konstantin Riabitsev <icon@duke.edu> 1.2.8-1
- adopted RH's spec file so we don't duplicate effort. 
- Removed rh'ized splash screen.
- Adding fallbacks for building rhl7 version as well with the same 
  specfile. Makes the spec file not as clean, but hey.
- remove workarounds for #68669 (rh bugzilla), since 1.2.8 works with
  register_globals = Off.
- Hardwiring localhost into the default config file. Makes sense.
- No more such file MIRRORS.
- Adding aspell as one of the req's, since squirrelspell is enabled by
  default
- Added Vendor: line to distinguish ourselves from RH.
- Doing the uglies with the release numbers.

* Tue Aug  6 2002 Preston Brown <pbrown@redhat.com> 1.2.7-4
- replacement splash screen.

* Mon Jul 22 2002 Gary Benson <gbenson@redhat.com> 1.2.7-3
- get rid of long lines in the specfile.
- remove symlink in docroot and use an alias in conf.d instead.
- work with register_globals off (#68669)

* Tue Jul 09 2002 Gary Benson <gbenson@redhat.com> 1.2.7-2
- hardwire the hostname (well, localhost) into the config file (#67635)

* Mon Jun 24 2002 Gary Benson <gbenson@redhat.com> 1.2.7-1
- hardwire the locations into the config file and cron file.
- install squirrelmail-cleanup.cron as squirrelmail.cron.
- make symlinks relative.
- upgrade to 1.2.7.
- more dependency fixes.

* Fri Jun 21 2002 Gary Benson <gbenson@redhat.com>
- summarize the summary, fix deps, and remove some redundant stuff.
- tidy up the %prep section.
- replace directory definitions with standard RHL ones.

* Fri Jun 21 2002 Tim Powers <timp@redhat.com> 1.2.6-3
- automated rebuild

* Wed Jun 19 2002 Preston Brown <pbrown@redhat.com> 1.2.6-2
- adopted Konstantin Riabitsev <icon@duke.edu>'s package for Red Hat
  Linux.  Nice job Konstantin!
