ForgeUpgrade
============

**ForgeUpgrade** aims to propose a convenient way to perform automated upgrade of a Forge.

See the [spec](https://codendi.org/wiki/index.php?pagename=UpgradeAutomation&group_id=104) on Codendi.org

Features
========
The command line tool now allows to select where to look for migration scripts:
<pre>
$> php migration.php --path=tests/_fixtures/CoreAndPlugins --include="db/updates" check-update
</pre>

This will look for migrations in "tests/_fixtures/CoreAndPlugins" subdirectory
but only in "db/updates" subpath.
It will match:
tests/_fixtures/CoreAndPlugins/src/db/updates/...
tests/_fixtures/CoreAndPlugins/plugins/foobar/db/updates/...

Usage example
=============
<pre>
$> php migration.php
Wed Apr 14 18:01:40 2010,781 [30794] INFO ForgeUpgrade - [Pre Up] Run pre up checks
Wed Apr 14 18:01:40 2010,787 [30794] INFO ForgeUpgrade - [Pre Up] OK : AddTablesForDocmanWatermarking
Wed Apr 14 18:01:40 2010,789 [30794] INFO ForgeUpgrade - [Pre Up] SKIP: AddDateColumnToItem depends on a migration not already applied
Wed Apr 14 18:01:40 2010,790 [30794] INFO ForgeUpgrade - [Pre Up] Global: OK
Wed Apr 14 18:01:40 2010,791 [30794] INFO ForgeUpgrade - [Up] Start running migrations...
Wed Apr 14 18:01:40 2010,791 [30794] INFO ForgeUpgrade - [Up] AddTablesForDocmanWatermarking
Add tables to docman pdf watermarking plugin in order to
allow admins to disable watermarking on selected documents.
Wed Apr 14 18:01:40 2010,792 [30794] INFO ForgeUpgrade - [Up] AddTablesForDocmanWatermarking PreUp OK
Wed Apr 14 18:01:40 2010,792 [30794] INFO ForgeUpgrade - Add table plugin_docmanwatermark_item_excluded
Wed Apr 14 18:01:40 2010,796 [30794] INFO ForgeUpgrade - plugin_docmanwatermark_item_excluded already exists
Wed Apr 14 18:01:40 2010,797 [30794] INFO ForgeUpgrade - Add table plugin_docmanwatermark_item_excluded_log
Wed Apr 14 18:01:40 2010,800 [30794] INFO ForgeUpgrade - plugin_docmanwatermark_item_excluded_log already exists
Wed Apr 14 18:01:40 2010,801 [30794] INFO ForgeUpgrade - [Up] AddTablesForDocmanWatermarking Up OK
Wed Apr 14 18:01:40 2010,807 [30794] INFO ForgeUpgrade - [Up] AddTablesForDocmanWatermarking Done
Wed Apr 14 18:01:40 2010,807 [30794] INFO ForgeUpgrade - [Up] AddDateColumnToItem
Add column to DocmanWatermarking table.
Wed Apr 14 18:01:40 2010,810 [30794] INFO ForgeUpgrade - [Up] AddDateColumnToItem PreUp OK
Wed Apr 14 18:01:40 2010,811 [30794] INFO ForgeUpgrade - [Up] AddDateColumnToItem Up OK
Wed Apr 14 18:01:40 2010,812 [30794] INFO ForgeUpgrade - [Up] AddDateColumnToItem Done
</pre>
