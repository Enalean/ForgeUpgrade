<?php
/**
 * Copyright (c) STMicroelectronics, 2010. All Rights Reserved.
 *
 * Originally written by Manuel Vacelet
 *
 * ForgeUpgrade is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ForgeUpgrade is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ForgeUpgrade. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'src/BucketFilter.php';

Mock::generate('SplFileInfo');

class ForgeUpgrade_BucketFilterTest extends UnitTestCase {
    
    public function testNameCorrect() {
        $file = new MockSplFileInfo($this);
        $file->setReturnValue('getPathname', '201004231055_add_system_event_table.php');

        $filter = new ForgeUpgrade_BucketFilter(new ArrayIterator(array($file)));
        $filter->rewind();
        $this->assertTrue($filter->valid());
    }

    public function testBadNameWrongExtension() {
        $file = new MockSplFileInfo($this);
        $file->setReturnValue('getPathname', '201004231055_add_system_event_table.pl');

        $filter = new ForgeUpgrade_BucketFilter(new ArrayIterator(array($file)));
        $filter->rewind();
        $this->assertFalse($filter->valid());
    }

    public function testBadNameWrongSeparator() {
        $file = new MockSplFileInfo($this);
        $file->setReturnValue('getPathname', '201004231055-add_system_event_table.php');

        $filter = new ForgeUpgrade_BucketFilter(new ArrayIterator(array($file)));
        $filter->rewind();
        $this->assertFalse($filter->valid());
    }

    public function testIncludeWithRightPath() {
        $file = new MockSplFileInfo($this);
        $file->setReturnValue('getPathname', '/toto/src/db/updates/201004231055_add_system_event_table.php');

        $filter = new ForgeUpgrade_BucketFilter(new ArrayIterator(array($file)));
        $filter->addInclude('/db/updates/');

        $filter->rewind();
        $this->assertTrue($filter->valid());
    }

    public function testIncludeWithWrongPath() {
        $file = new MockSplFileInfo($this);
        $file->setReturnValue('getPathname', '/toto/src/etc/updates/201004231055_add_system_event_table.php');

        $filter = new ForgeUpgrade_BucketFilter(new ArrayIterator(array($file)));
        $filter->addInclude('/db/updates/');

        $filter->rewind();
        $this->assertFalse($filter->valid());
    }

    public function testExcludeWithMatchingPath() {
        $file = new MockSplFileInfo($this);
        $file->setReturnValue('getPathname', '/toto/src/etc/updates/201004231055_add_system_event_table.php');

        $filter = new ForgeUpgrade_BucketFilter(new ArrayIterator(array($file)));
        $filter->addExclude('/etc/updates/');

        $filter->rewind();
        $this->assertFalse($filter->valid());
    }

    public function testExcludeWithNonMatchingPath() {
        $file = new MockSplFileInfo($this);
        $file->setReturnValue('getPathname', '/toto/src/db/updates/201004231055_add_system_event_table.php');

        $filter = new ForgeUpgrade_BucketFilter(new ArrayIterator(array($file)));
        $filter->addExclude('/etc/updates/');

        $filter->rewind();
        $this->assertTrue($filter->valid());
    }
}

?>