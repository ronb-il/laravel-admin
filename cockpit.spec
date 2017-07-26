Summary: 		personali web server
Name: 			cockpit
Version: 		@@version@@
Release: 		@@release@@
License: 		Personali Inc.
Requires(pre):		shadow-utils
BuildArch: 		noarch
AutoReqProv: no
#Requires:

%description
personali web server

# ****** rpm build install: move files from BUILD to target directory structure ********
%install
%define _binaries_in_noarch_packages_terminate_build 0
mkdir -p %{buildroot}/opt/personali/www/cockpit-%{version}-%{release}
cp -R %{_builddir}/%{name}-%{version}-%{release}/* %{buildroot}/opt/personali/www/cockpit-%{version}-%{release}

# ****** deploy: copy files on installed instance ********
%files
%defattr(-,personali,apache)
/opt/personali/www/cockpit-%{version}-%{release}

# ****** pre-install: create user if not exists and stop running server ********
%pre
 echo "stop apache..."
 if [ -e /etc/init.d/httpd ]
   then
 	 /etc/init.d/httpd stop
 fi
 getent group personali >/dev/null || groupadd -r personali
 getent passwd personali >/dev/null || \
    useradd -r -g personali -d /home/personali -s /sbin/nologin \
    -c "Useful comment about the purpose of this account" personali
 exit 0

# ****** post-install: create new soft link and start server ********
%post
 echo "setup post install"
 echo "link CockPit:" %{version}-%{release}
 ln -snf /opt/personali/www/cockpit-%{version}-%{release} /opt/personali/www/cockpit
 sudo chmod 777 -R /opt/personali/www/cockpit/public

%preun
 echo "uninstall " %{version}-%{release}
 echo "stop apache"
 if [ -e /etc/init.d/httpd ]; then
 	 /etc/init.d/httpd stop
 fi

%postun
 echo "uninstall links"  %{version}-%{release}
 rm -rf /opt/personali/www/cockpit-%{version}-%{release}
 rm -f /etc/httpd/conf.d/personali.conf