Summary: ForgeUpgrade helps developer to upgrades their application data
Name: forgeupgrade
Version: @@VERSION@@
Release: 1%{?dist}
BuildArch: noarch
License: LGPG
Group: Development/Tools
URL: http://github.com/vaceletm/ForgeUpgrade
Source0: %{name}-%{version}.tar.gz
BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root

%description
ForgeUpgrade is an application data (files or databases) upgrade automation
tool. It is very close to Ruby on Rails 'migration' system.


%prep
%setup -q

%build
# Nothing to build

%install
rm -rf $RPM_BUILD_ROOT

# Application
%{__install} -m 755 -d $RPM_BUILD_ROOT/%{_datadir}/%{name}
%{__cp} forgeupgrade.php $RPM_BUILD_ROOT/%{_datadir}/%{name}
%{__cp} generator.php $RPM_BUILD_ROOT/%{_datadir}/%{name}
%{__cp} -r src $RPM_BUILD_ROOT/%{_datadir}/%{name}
%{__cp} -r lib $RPM_BUILD_ROOT/%{_datadir}/%{name}
%{__cp} -r db $RPM_BUILD_ROOT/%{_datadir}/%{name}

# Should be delivered from Codendi itself
%{__install} -m 755 -d $RPM_BUILD_ROOT/%{_datadir}/%{name}/dbdriver
%{__cp} examples/CustomDrivers/ForgeUpgrade_Db_Driver_Codendi.php $RPM_BUILD_ROOT/%{_datadir}/%{name}/dbdriver

# Binaries
%{__install} -m 755 -d $RPM_BUILD_ROOT/%{_libdir}/%{name}/bin
%{__install} -m 755 %{name} $RPM_BUILD_ROOT/%{_libdir}/%{name}/bin


%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root,-)
%doc CHANGES COPYING README.markdown VERSION
%{_datadir}/%{name}
%dir %{_libdir}/%{name}
%dir %{_libdir}/%{name}/bin
%attr(755,root,root) %{_libdir}/%{name}/bin/%{name}


%changelog
* Tue Jun 15 2010 Manuel VACELET <manuel.vacelet@st.com> - 1.0-1
- Initial build.
