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
 * Test file for tool_forcedcache_cache_config.
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

    function test_generate_store_instance_config() {
        // Directly create a config.
        $config = new \tool_forcedcache_cache_config();

        // Setup reflection for private function.
        $method = new \ReflectionMethod($config, 'generate_store_instance_config');
        $method->setAccessible(true);

        // Read in the fixtures file for data.
        include (__DIR__ . '/fixtures/stores_data.php');

        // First test with 1 store.
        $this->assertEquals($store_one['expected'], $method->invoke($config, $store_one['input']));

        // Now a second store.
        $this->assertEquals($store_two['expected'], $method->invoke($config, $store_two['input']));

        // Now test with 0 stores declared and confirm its just the defaults.
        $this->assertEquals($store_zero['expected'], $method->invoke($config, $store_zero['input']));

        // Now test a store with a bad type.
        $this->expectException(\cache_exception::class);
        $this->expectExceptionMessage(get_string('store_bad_type', 'tool_forcedcache', 'faketype'));
        $storearr1 = $method->invoke($config, $store_badtype['input']);
        $this->assertNull($storearr1);

        // Now test a store with a missing required field.
        $this->expectException(\cache_exception::class);
        $this->expectExceptionMessage(get_string('store_missing_fields', 'tool_forcedcache', 'apcu-test'));
        $storearr1 = $method->invoke($config, $store_missingfields['input']);
        $this->assertNull($storearr1);
    }
}
