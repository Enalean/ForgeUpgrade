ifeq ($(strip $(RPM_TMP)),)
	RPM_TMP=~/rpmbuild
endif

BASE_DIR=$(shell pwd)
version=$(shell LANG=C cat $(BASE_DIR)/VERSION)

PKG_NAME=forgeupgrade

rpmprep:
	mkdir -p $(RPM_TMP)
	cd $(RPM_TMP) && mkdir -p BUILD RPMS SOURCES SPECS SRPMS TMP
	echo "%_topdir $(RPM_TMP)" > ~/.rpmmacros
	echo '%_tmppath %{_topdir}/TMP' >> ~/.rpmmacros
	echo '%_buildroot %{_tmppath}/%{name}-root' >> ~/.rpmmacros
	echo '%_sysconfdir /etc' >> ~/.rpmmacros

tarball:
	cd $(BASE_DIR) && find . -type f -or -type l | egrep -v '^./tests' | egrep -v '^./.git' | cpio -pdumB --quiet $(RPM_TMP)/SOURCES/$(PKG_NAME)-$(version)
	cd $(RPM_TMP)/SOURCES && tar czf $(PKG_NAME)-$(version).tar.gz $(PKG_NAME)-$(version)

forgeupgrade: rpmprep tarball
	cat forgeupgrade.spec |\
		sed -e 's/@@VERSION@@/$(version)/g' \
		> $(RPM_TMP)/SPECS/forgeupgrade.spec
	rpmbuild -bb $(RPM_TMP)/SPECS/forgeupgrade.spec

all: forgeupgrade
