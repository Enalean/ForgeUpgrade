#!/bin/bash
#
# Copyright (c) Xerox Corporation, Codendi 2001-2009.
# This file is licensed under the GNU General Public License version 2. See the file COPYING. 
#
#      Originally written by Laurent Julliard 2004-2006, Codendi Team, Xerox
#
#  This file is part of the Codendi software and must be placed at the same
#  level as the Codendi, RPMS_Codendi and nonRPMS_Codendi directory when
#  delivered on a CD or by other means
#
#  This script migrates a site running Codendi 4.0 to Codendi 4.2
#


progname=$0
#scriptdir=/mnt/cdrom
if [ -z "$scriptdir" ]; then 
    scriptdir=`dirname $progname`
fi
cd "${scriptdir}";TOP_DIR=`pwd`;cd - > /dev/null # redirect to /dev/null to remove display of folder (RHEL4 only)
RPMS_DIR="${TOP_DIR}/RPMS_Codendi"
nonRPMS_DIR="${TOP_DIR}/nonRPMS_Codendi"
Codendi_DIR="${TOP_DIR}/Codendi"
TODO_FILE=/root/todo_codendi_upgrade_4.2.txt
export INSTALL_DIR="/usr/share/codendi"
BACKUP_INSTALL_DIR="/usr/share/codendi_40"
ETC_DIR="/etc/codendi"
USR_LIB_DIR="/usr/lib/codendi"
VAR_LIB_DIR="/var/lib/codendi"
VAR_TMP_DIR="/var/tmp/codendi_cache"
VAR_LOG_DIR="/var/log/codendi"
BACKUP_DIR="/root/codendi_4.0_backup"

# path to command line tools
GROUPADD='/usr/sbin/groupadd'
GROUPDEL='/usr/sbin/groupdel'
USERADD='/usr/sbin/useradd'
USERDEL='/usr/sbin/userdel'
USERMOD='/usr/sbin/usermod'
MV='/bin/mv'
CP='/bin/cp'
LN='/bin/ln'
LS='/bin/ls'
RM='/bin/rm'
TAR='/bin/tar'
MKDIR='/bin/mkdir'
RPM='/bin/rpm'
CHOWN='/bin/chown'
CHMOD='/bin/chmod'
FIND='/usr/bin/find'
export MYSQL='/usr/bin/mysql'
TOUCH='/bin/touch'
CAT='/bin/cat'
MAKE='/usr/bin/make'
TAIL='/usr/bin/tail'
GREP='/bin/grep'
CHKCONFIG='/sbin/chkconfig'
SERVICE='/sbin/service'
PERL='/usr/bin/perl'
DIFF='/usr/bin/diff'
PHP='/usr/bin/php'

CMD_LIST="GROUPADD GROUDEL USERADD USERDEL USERMOD MV CP LN LS RM TAR \
MKDIR RPM CHOWN CHMOD FIND MYSQL TOUCH CAT MAKE TAIL GREP CHKCONFIG \
SERVICE PERL DIFF"

CHCON='/usr/bin/chcon'
SELINUX_CONTEXT="root:object_r:httpd_sys_content_t";
SELINUX_ENABLED=1
if [ ! -e $CHCON ] || [ ! -e "/etc/selinux/config" ] || `grep -i -q '^SELINUX=disabled' /etc/selinux/config`; then
   # SELinux not installed
   SELINUX_ENABLED=0
fi


# Functions
create_group() {
    # $1: groupname, $2: groupid
    $GROUPDEL "$1" 2>/dev/null
    $GROUPADD -g "$2" "$1"
}

build_dir() {
    # $1: dir path, $2: user, $3: group, $4: permission
    $MKDIR -p "$1" 2>/dev/null; $CHOWN "$2.$3" "$1";$CHMOD "$4" "$1";
}

make_backup() {
    # $1: file name, $2: extension for old file (optional)
    file="$1"
    ext="$2"
    if [ -z $ext ]; then
	ext="nocodendi"
    fi
    backup_file="$1.$ext"
    [ -e "$file" -a ! -e "$backup_file" ] && $CP "$file" "$backup_file"
}

todo() {
    # $1: message to log in the todo file
    echo -e "- $1" >> $TODO_FILE
}

die() {
  # $1: message to prompt before exiting
  echo -e "**ERROR** $1"; exit 1
}

substitute() {
  if [ -f $1 ]; then
    # $1: filename, $2: string to match, $3: replacement string
    # Allow '/' is $3, so we need to double-escape the string
    replacement=`echo $3 | sed "s|/|\\\\\/|g"`
    $PERL -pi -e "s/$2/$replacement/g" $1
  fi
}

# @param $1 table
# @param $2 name of the index
mysql_drop_index() {
    $MYSQL $pass_opt codendi -e "SHOW INDEX FROM $1 WHERE key_name = '$2'" | grep -q $2
    if [ $? -eq 0 ]; then
        $MYSQL $pass_opt codendi -e "ALTER TABLE $1 DROP INDEX $2"
    fi
}

# @param $1 table
# @param $2 name of the index
# @param $3 columns (coma separated)
mysql_add_index() {
    mysql_drop_index "$1" "$2"
    $MYSQL $pass_opt codendi -e "ALTER TABLE $1 ADD INDEX $2($3)"
}
##############################################
# Codendi 4.0 to 4.2 migration
##############################################
echo "Migration script from Codendi 4.0 to Codendi 4.2"
echo "Please Make sure you read migration_from_Codendi_4.0_to_Codendi_4.2.README"
echo "*before* running this script!"
yn="y"
read -p "Continue? [yn]: " yn
if [ "$yn" = "n" ]; then
    echo "Bye now!"
    exit 1
fi

##############################################
# Check that all command line tools we need are available
#
for cmd in `echo ${CMD_LIST}`
do
    [ ! -x ${!cmd} ] && die "Command line tool '${!cmd}' not available. Stopping installation!"
done


##############################################
# Check the machine is running Codendi 4.0
#
OLD_CX_RELEASE='4.0'
yn="y"
$GREP -q "$OLD_CX_RELEASE" /usr/share/codendi/src/www/VERSION
if [ $? -ne 0 ]; then
    $CAT <<EOF
This machine does not have Codendi ${OLD_CX_RELEASE} installed. Executing this
script may cause data loss or corruption.
EOF
read -p "Continue? [yn]: " yn
else
    echo "Found Codendi ${OLD_CX_RELEASE} installed... good!"
fi

if [ "$yn" = "n" ]; then
    echo "Bye now!"
    exit 1
fi

##############################################
# Check we are running on RHEL 5
#
RH_RELEASE="5"
yn="y"
$RPM -q redhat-release-${RH_RELEASE}* 2>/dev/null 1>&2
if [ $? -eq 1 ]; then
  $RPM -q centos-release-${RH_RELEASE}* 2>/dev/null 1>&2
  if [ $? -eq 1 ]; then
    cat <<EOF
This machine is not running RedHat Enterprise Linux ${RH_RELEASE}. Executing this install
script may cause data loss or corruption.
EOF
read -p "Continue? [yn]: " yn
  else
    echo "Running on CentOS ${RH_RELEASE}... good!"
  fi
else
    echo "Running on RedHat Enterprise Linux ${RH_RELEASE}... good!"
fi

if [ "$yn" = "n" ]; then
    echo "Bye now!"
    exit 1
fi


##############################################
# Check Required Stock RedHat RPMs are installed
#

$RPM -q java-1.6.0-openjdk  2>/dev/null 1>&2
if [ $? -eq 1 ]; then
   echo "Java is now supported by RHEL/CentOS with the OpenJDK. If you wish to use it, you can install the package java-1.6.0-openjdk with yum and uninstall the JRE."
   read -p "Continue with current Java configuration? [yn]: " yn
   if [ "$yn" = "n" ]; then
       echo "Bye now!"
       exit 1
   fi
fi

rpms_ok=1
for rpm in nscd php-pear mod_auth_mysql
do
    $RPM -q $rpm  2>/dev/null 1>&2
    if [ $? -eq 1 ]; then
	rpms_ok=0
	missing_rpms="$missing_rpms $rpm"
    fi
done
if [ $rpms_ok -eq 0 ]; then
    msg="The following Redhat Linux RPMs must be installed first:\n"
    msg="${msg}$missing_rpms\n"
    msg="${msg}Get them from your Redhat CDROM or FTP site, install them and re-run the installation script"
    die "$msg"
fi
echo "All requested RedHat RPMS installed... good!"

##############################################
# Ask for domain name and other installation parameters
#
sys_default_domain=`grep ServerName /etc/httpd/conf/httpd.conf | grep -v '#' | head -1 | cut -d " " -f 2 ;`
if [ -z $sys_default_domain ]; then
  read -p "Codeindi Domain name: " sys_default_domain
fi
sys_ip_address=`grep NameVirtualHost /etc/httpd/conf/httpd.conf | grep -v '#' | cut -d " " -f 2 | cut -d ":" -f 1`
if [ -z $sys_ip_address ]; then
  read -p "Codendi Server IP address: " sys_ip_address
fi


###############################################################################
echo "Updating Packages"

# -> JPGraph
$RPM -e jpgraph jpgraphs-docs 2>/dev/null
echo "Installing JPGraph RPM for Codendi...."
cd "${RPMS_DIR}/jpgraph"
newest_rpm=`$LS -1  -I old -I TRANS.TBL | $TAIL -1`
$RPM -Uvh ${newest_rpm}/jpgraph-3*noarch.rpm
$RPM -Uvh ${newest_rpm}/jpgraph-docs-3*noarch.rpm


##############################################
# Stop some services before upgrading
#
echo "Stopping crond, httpd, sendmail, mailman and smb ..."
$SERVICE openfire stop
$SERVICE crond stop
$SERVICE httpd stop
$SERVICE mysqld stop
$SERVICE sendmail stop
$SERVICE mailman stop
$SERVICE smb stop


##############################################
# Install the Codendi software 
#
echo "Installing the Codendi software..."
$MV /usr/share/codendi $BACKUP_INSTALL_DIR
$MKDIR $INSTALL_DIR;
cd $INSTALL_DIR
$TAR xfz "${Codendi_DIR}/codendi*.tgz"
$CHOWN -R codendiadm.codendiadm $INSTALL_DIR

echo "Setting up fileperms on installed files and directory"
$FIND $INSTALL_DIR -type f -exec $CHMOD u+rw,g+rw,o-w+r {} \;
$FIND $INSTALL_DIR -type d -exec $CHMOD 775 {} \;

#
# Install New dist files
#

for f in /etc/httpd/conf/httpd.conf /etc/httpd/conf.d/codendi_aliases.conf \
 /etc/httpd/conf.d/php.conf /etc/httpd/conf.d/subversion.conf \
 /etc/libnss-mysql.cfg  /etc/libnss-mysql-root.cfg /etc/httpd/conf.d/auth_mysql.conf; do
    yn="0"
    fn=`basename $f`
    [ -f "$f" ] && read -p "$f already exist. Overwrite? [y|n]:" yn

    if [ "$yn" = "y" ]; then
	$CP -f $f $f.orig
    fi

    if [ "$yn" != "n" ]; then
	$CP -f $INSTALL_DIR/src/etc/$fn.dist $f
    fi

    $CHOWN codendiadm.codendiadm $f
    $CHMOD 640 $f
done

# replace string patterns in httpd.conf
substitute '/etc/httpd/conf/httpd.conf' '%sys_default_domain%' "$sys_default_domain"
substitute '/etc/httpd/conf/httpd.conf' '%sys_ip_address%' "$sys_ip_address"
# replace string patterns in codendi_aliases.inc
substitute '/etc/httpd/conf.d/codendi_aliases.conf' '%sys_default_domain%' "$sys_default_domain" 


###############################################################################
echo "Updating local.inc"

#...


##############################################
# Analyze site-content 
#
echo "Analysing your site-content (in $ETC_DIR/site-content/)..."

#Only in etc => removed
removed=`$DIFF -q -r \
 $ETC_DIR/site-content/ \
 $INSTALL_DIR/site-content/        \
 | grep -v '.svn'  \
 | sed             \
 -e "s|^Only in $ETC_DIR/site-content/\([^:]*\): \(.*\)|@\1/\2|g" \
 -e "/^[^@]/ d"  \
 -e "s/@//g"     \
 -e '/^$/ d'`
if [ "$removed" != "" ]; then
  echo "The following files do not exist in the site-content of Codendi:"
  echo "$removed"
fi

#Differ => modified
one_has_been_found=0
for i in `$DIFF -q -r \
            $ETC_DIR/site-content/ \
            $INSTALL_DIR/site-content/        \
            | grep -v '.svn'  \
            | sed             \
            -e "s|^Files $ETC_DIR/site-content/\(.*\) and $INSTALL_DIR/site-content/\(.*\) differ|@\1|g" \
            -e "/^[^@]/ d"  \
            -e "s/@//g"     \
            -e '/^$/ d'` 
do
   if [ $one_has_been_found -eq 0 ]; then
      echo "  The following files differ from the site-content of Codendi:"
      one_has_been_found=1
   fi
   echo "    $i"
done

if [ $one_has_been_found -eq 1 ]; then
   echo "  Please check those files"
fi

echo "Analysis done."


##############################################
# Database Structure and initvalues upgrade
#
echo "Updating the database..."

$SERVICE mysqld start
sleep 5



pass_opt=""
# See if MySQL root account is password protected
mysqlshow 2>&1 | grep password
while [ $? -eq 0 ]; do
    read -s -p "Existing DB is password protected. What is the Mysql root password?: " old_passwd
    echo
    mysqlshow --password=$old_passwd 2>&1 | grep password
done
[ "X$old_passwd" != "X" ] && pass_opt="--password=$old_passwd"


echo "Starting DB update for Codendi 4.2 This might take a few minutes."

echo "- remove legacy tables"
$CAT <<EOF | $MYSQL $pass_opt codendi
DROP TABLE frs_status IF EXISTS
EOF

echo "- New widget: image"
$CAT <<EOF | $MYSQL $pass_opt codendi
CREATE TABLE IF NOT EXISTS widget_image (
  id int(11) unsigned NOT NULL auto_increment PRIMARY KEY,
  owner_id int(11) unsigned NOT NULL default '0',
  owner_type varchar(1) NOT NULL default 'u',
  title varchar(255) NOT NULL,
  url TEXT NOT NULL,
  KEY (owner_id, owner_type)
);
EOF

mysql_add_index 'priority_plugin_hook' 'idx_plugin_id' 'plugin_id'
mysql_add_index 'plugin' 'idx_available' 'available'

TODO: Migrer graphontrackers (report -> renderer, widgets, new fields api)
TODO: add the field severity on all reports
TODO: table user: has_avatar TINYINT(1) NOT NULL DEFAULT 0,

###############################################################################
# Run 'analyse' on all MySQL DB
echo "Analyzing and optimizing MySQL databases (this might take a few minutes)"
mysqlcheck -Aaos $pass_opt


##############################################
# Restarting some services
#
echo "Starting services..."
$SERVICE crond start
$SERVICE httpd start
$SERVICE sendmail start
$SERVICE mailman start




#
# Re-copy files that have been modified
#
cd $INSTALL_DIR/src/utils/cvs1
$CP log_accum /usr/lib/codendi/bin
$CP commit_prep /usr/lib/codendi/bin
cd /usr/lib/codendi/bin
$CHOWN codendiadm.codendiadm log_accum commit_prep
$CHMOD 755 log_accum commit_prep cvssh cvssh-restricted
$CHMOD u+s log_accum   # sets the uid bit (-rwsr-xr-x)

cd $INSTALL_DIR/src/utils/svn
$CP commit-email.pl codendi_svn_pre_commit.php /usr/lib/codendi/bin
cd /usr/lib/codendi/bin
$CHOWN codendiadm.codendiadm commit-email.pl codendi_svn_pre_commit.php
$CHMOD 755 commit-email.pl codendi_svn_pre_commit.php


##############################################
# Generate Documentation
#
echo "Generating the Codendi Manuals. This will take a few minutes."
su -c "$INSTALL_DIR/src/utils/generate_doc.sh -f" - codendiadm 2> /dev/null &
su -c "$INSTALL_DIR/src/utils/generate_programmer_doc.sh -f" - codendiadm 2> /dev/null &
su -c "$INSTALL_DIR/src/utils/generate_cli_package.sh -f" - codendiadm 2> /dev/null &
$CHOWN -R codendiadm.codendiadm $INSTALL_DIR/documentation
$CHOWN -R codendiadm.codendiadm $INSTALL_DIR/downloads


# End of it
echo "=============================================="
echo "Migration completed succesfully!"
echo

exit 1;
