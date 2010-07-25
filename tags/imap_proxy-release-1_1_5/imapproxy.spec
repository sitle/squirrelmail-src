#This is imapproxy rpm spec file

%define ver	1.1.5
%define rel	1
%define prefix	/usr/local

Summary:	Imapproxy Daemon
Name:		up-imapproxy
Version:	%ver
Release:	%rel
Copyright:	University of Pittsburgh
Group:		Networking/Daemons
Source0:	ftp://ftp.pitt.edu/users/d/g/dgm/up-imapproxy-%{ver}.tar.gz
Source1:	imapproxy.conf
Source2:	imapproxy.init
Url:		ftp://ftp.pitt.edu/users/d/g/dgm
Packager:	Devrim SERAL <devrim@gazi.edu.tr>
BuildRoot:	/var/tmp/imapproxy-%{ver}-root

%description
This is a connection caching imapproxy daemon for proxied imap connections

%prep
%setup 

%build
chmod 755 ./configure


./configure --with-prefix=%{prefix}  
make OPT_FLAGS="$RPM_OPT_FLAGS" 

%install
rm -rf $RPM_BUILD_ROOT

install -d $RPM_BUILD_ROOT/etc
install -d $RPM_BUILD_ROOT/etc/init.d
install -d $RPM_BUILD_ROOT/%{prefix}/sbin

make prefix=$RPM_BUILD_ROOT%{prefix} rpm_prefix=$RPM_BUILD_ROOT  rpm-install

%clean
rm -rf $RPM_BUILD_ROOT

%pre
if [ -f /etc/imapproxy.conf ]; then
	cp -a /etc/imapproxy.conf /etc/imapproxy.conf.old
fi


%post
/sbin/chkconfig --add imapproxy

%preun
/sbin/chkconfig --del imapproxy 

%files
%defattr(-, root, root)
%config /etc/imapproxy.conf
%doc README ChangeLog
%attr(750,root,root) 		/etc/init.d/imapproxy
%attr(750,root,root) 		%{prefix}/sbin/in.imapproxyd
%attr(750,root,root) 		%{prefix}/sbin/pimpstat

%changelog
* Tue Mar 18 2003 Devrim SERAL<devrim@gazi.edu.tr>
- Created imapproxy.spec file
