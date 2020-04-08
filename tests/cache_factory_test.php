<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test file for tool_forcedcache_cache_factory.
 *
 * @package     tool_forcedcache
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_forcedcache\tests;

defined('MOODLE_INTERNAL') || die();

class tool_forcedcache_cache_config_testcase extends \advanced_testcase {

    public function test_read_config_file() {
        // Directly create a config.
        $config = new \tool_forcedcache_cache_config();

        // Setup reflection for private function.
        $method = new \ReflectionMethod($config, 'read_config_file');
        $method->setAccessible(true);

        // First use the default json file.
        $path = __DIR__ . '/../config.json';
        $configarr1 = $method->invoke($config, $path);
        $this->assertIsArray($configarr1);
        $this->assertEquals(2, count($configarr1));
        $this->assertArrayHasKey('rules', $configarr1);
        $this->assertArrayHasKey('stores', $configarr1);

        // Now lets point to a garbled file.
        $path = __DIR__ . '/../classes/cache_factory.php';
        $this->expectException(\cache_exception::class);
        $this->expectExceptionMessage(get_string('config_json_parse_fail', 'tool_forcedcache'));
        $configarr2 = $method->invoke($config, $path);
        $this->assertNull($configarr2);

        // Now try a non-existent file.
        $path = __DIR__ . '/fake.json';
        $this->expectException(\cache_exception::class);
        $this->expectExceptionMessage(get_string('config_json_missing', 'tool_forcedcache'));
        $configarr3 = $method->invoke($config, $path);
        $this->assertNull($configarr3);
    }
}
