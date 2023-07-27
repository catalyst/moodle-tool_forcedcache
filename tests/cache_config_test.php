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

namespace tool_forcedcache;

/**
 * Tests for tool_forcedcache_cache_config.
 *
 * @package     tool_forcedcache
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \tool_forcedcache_cache_config
 */
class cache_config_test extends \advanced_testcase {

    /**
     * We need to load the config files outside of the $CFG->dirroot, so it
     * will be copied out as part of these tests.
     *
     * @param  string $source file
     * @return string of the copied file, in a valid config loading location
     */
    public function copy_to_valid_config_location(string $source): string {
        $this->tmpdir = sys_get_temp_dir();
        $dest = $this->tmpdir . DIRECTORY_SEPARATOR . 'tool_forcedcache_cache_config_testcase-' . basename($source);
        copy($source, $dest);
        return realpath($dest);
    }

    public function test_read_config_file_from_invalid_path() {
        global $CFG;
        $this->resetAfterTest(true);

        // Directly create a config.
        $config = new \tool_forcedcache_cache_config();

        // Lets unset anything that may be forced from config.php.
        unset($CFG->tool_forcedcache_config_path);
        unset($CFG->tool_forcedcache_config_array);

        // Setup reflection for private function.
        $method = new \ReflectionMethod($config, 'read_config_file');
        $method->setAccessible(true);

        // First try loading a file in an invalid config path.
        $CFG->tool_forcedcache_config_path = realpath(__DIR__ . '/../config.json');
        $this->expectException(\cache_exception::class);
        $this->expectExceptionMessage(get_string('config_json_path_invalid', 'tool_forcedcache', [
            'path' => $CFG->tool_forcedcache_config_path,
            'dirroot' => $CFG->dirroot
        ]));
        $method->invoke($config);
    }

    public function test_read_valid_config_file() {
        global $CFG;
        $this->resetAfterTest(true);

        // Directly create a config.
        $config = new \tool_forcedcache_cache_config();

        // Lets unset anything that may be forced from config.php.
        unset($CFG->tool_forcedcache_config_path);
        unset($CFG->tool_forcedcache_config_array);

        // Setup reflection for private function.
        $method = new \ReflectionMethod($config, 'read_config_file');
        $method->setAccessible(true);

        // Next use the default json file, but from a valid path.
        $CFG->tool_forcedcache_config_path = $this->copy_to_valid_config_location(__DIR__ . '/../config.json');
        $configarr1 = $method->invoke($config);
        $this->assertEquals(3, count($configarr1));
        $this->assertArrayHasKey('rules', $configarr1);
        $this->assertArrayHasKey('stores', $configarr1);
        $this->assertArrayHasKey('definitionoverrides', $configarr1);
    }

    public function test_read_garbled_config_file() {
        global $CFG;
        $this->resetAfterTest(true);

        // Directly create a config.
        $config = new \tool_forcedcache_cache_config();

        // Lets unset anything that may be forced from config.php.
        unset($CFG->tool_forcedcache_config_path);
        unset($CFG->tool_forcedcache_config_array);

        // Setup reflection for private function.
        $method = new \ReflectionMethod($config, 'read_config_file');
        $method->setAccessible(true);

        // Now lets point to a garbled file.
        $CFG->tool_forcedcache_config_path = $this->copy_to_valid_config_location(__DIR__ . '/../classes/cache_factory.php');
        $this->expectException(\cache_exception::class);
        $this->expectExceptionMessage(get_string('config_json_parse_fail', 'tool_forcedcache'));
        $method->invoke($config);
    }

    public function test_read_non_existent_config_file() {
        global $CFG;
        $this->resetAfterTest(true);

        // Directly create a config.
        $config = new \tool_forcedcache_cache_config();

        // Lets unset anything that may be forced from config.php.
        unset($CFG->tool_forcedcache_config_path);
        unset($CFG->tool_forcedcache_config_array);

        // Setup reflection for private function.
        $method = new \ReflectionMethod($config, 'read_config_file');
        $method->setAccessible(true);

        // Now try a non-existent file.
        $CFG->tool_forcedcache_config_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'forcedcache_cache_config-fake.json';
        $this->expectException(\cache_exception::class);
        $this->expectExceptionMessage(get_string('config_json_missing', 'tool_forcedcache'));
        $method->invoke($config);
    }


    public function test_generate_store_instance_config() {
        // Directly create a config.
        $config = new \tool_forcedcache_cache_config();

        // Setup reflection for private function.
        $method = new \ReflectionMethod($config, 'generate_store_instance_config');
        $method->setAccessible(true);

        // Read in the fixtures file for data.
        include(__DIR__ . '/fixtures/stores_data.php');

        // First test with 1 store.
        $this->assertEquals($storeone['expected'], $method->invoke($config, $storeone['input']));

        // Now a second store.
        $this->assertEquals($storetwo['expected'], $method->invoke($config, $storetwo['input']));

        // Now test with 0 stores declared and confirm its just the defaults.
        $this->assertEquals($storezero['expected'], $method->invoke($config, $storezero['input']));

        // Now test a store with a bad type.
        $this->expectException(\cache_exception::class);
        $this->expectExceptionMessage(get_string('store_bad_type', 'tool_forcedcache', 'faketype'));
        $storearr1 = $method->invoke($config, $storebadtype['input']);
        $this->assertNull($storearr1);

        // Now test a store with a missing required field.
        $this->expectException(\cache_exception::class);
        $this->expectExceptionMessage(get_string('store_missing_fields', 'tool_forcedcache', 'apcu-test'));
        $storearr1 = $method->invoke($config, $storemissingfields['input']);
        $this->assertNull($storearr1);

        // Now test store with where store isn't ready, don't instantiate (APCu doesn't work from CLI).
        $this->assertEquals($storereqsnotmet['expected'], $method->invoke($config, $storereqsnotmet['input']));
    }

    /**
     * Test if default mappings return as expected.
     */
    public function test_default_mode_mappings() {
        $defaultmodemappings = \tool_forcedcache_cache_config::get_default_mode_mappings();

        // Read in the fixtures file for data.
        include(__DIR__ . '/fixtures/mode_mappings_data.php');

        $this->assertEquals($defaultsexpected, $defaultmodemappings);
    }

    /**
     * Tests output of mode mpapings once rules included in the mix.
     */
    public function test_generated_mode_mappings_for_definitionmatchtopruleset() {
        $config = new \tool_forcedcache_cache_config();
        // Setup reflection for private function.
        $method = new \ReflectionMethod($config, 'generate_mode_mapping');
        $method->setAccessible(true);

        // Read in the fixtures file for data.
        include(__DIR__ . '/fixtures/mode_mappings_data.php');
        include(__DIR__ . '/fixtures/definition_mappings_data.php');

        $expected = $generatedmodemappingagainstdefinitionmatchtoprulesetexpected;
        $rules = $definitionmatchtopruleset['rules'];
        $this->assertEquals($expected, $method->invoke($config, $rules));
    }
    /**
     * Tests output of mode mpapings once rules included in the mix.
     */
    public function test_generated_mode_mappings_for_definitionnoruleset() {
        $config = new \tool_forcedcache_cache_config();
        // Setup reflection for private function.
        $method = new \ReflectionMethod($config, 'generate_mode_mapping');
        $method->setAccessible(true);

        // Read in the fixtures file for data.
        include(__DIR__ . '/fixtures/mode_mappings_data.php');
        include(__DIR__ . '/fixtures/definition_mappings_data.php');

        $rules = $definitionnoruleset['rules'];
        $this->assertEquals($defaultsexpected, $method->invoke($config, $rules));
    }

    public function test_generate_definition_mappings_from_rules() {
        $config = new \tool_forcedcache_cache_config();

        // Setup reflection for private function.
        $method = new \ReflectionMethod($config, 'generate_definition_mappings_from_rules');
        $method->setAccessible(true);

        // Read in the fixtures file for data.
        include(__DIR__ . '/fixtures/definition_mappings_data.php');

        // Test when a condition and the name match.
        $this->assertEquals($definitionmatchtopruleset['expected'],
            $method->invoke($config, $definitionmatchtopruleset['rules'], $definitionmatchtopruleset['definition']));

        // Test when 1 condition fails in a set, fallthrough occurs.
        $this->assertEquals($definitionnonmatchtopruleset['expected'],
            $method->invoke($config, $definitionnonmatchtopruleset['rules'], $definitionnonmatchtopruleset['definition']));

        // Test when failing 2 rulesets, fall through to 3rd.
        $this->assertEquals($definitionbottomruleset['expected'],
            $method->invoke($config, $definitionbottomruleset['rules'], $definitionbottomruleset['definition']));

        // Test when all rulesets fail (no mappings).
        $this->assertEquals($definitionnoruleset['expected'],
            $method->invoke($config, $definitionnoruleset['rules'], $definitionnoruleset['definition']));
    }
}
