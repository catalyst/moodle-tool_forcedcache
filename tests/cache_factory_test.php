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
 * Tests for tool_forcedcache_cache_factory.
 *
 * @package     tool_forcedcache
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \tool_forcedcache_cache_factory
 */
final class cache_factory_test extends \advanced_testcase {
    /**
     * Builds an uninitialised tool_forcedcache_cache_factory instance, bypassing its
     * protected constructor, and pre-seeds its config cache with the already loaded
     * PHPUnit test config so create_config_instance() does not attempt to recreate it.
     *
     * @return \tool_forcedcache_cache_factory
     */
    private function create_sandboxed_factory(): \tool_forcedcache_cache_factory {
        $factoryclass = new \ReflectionClass(\tool_forcedcache_cache_factory::class);
        $factory = $factoryclass->newInstanceWithoutConstructor();

        // While PHPUNIT_TEST is defined, create_config_instance() always swaps in the
        // core 'cache_config_testing' fixture rather than tool_forcedcache's own config
        // class. Pre-seed it with the already-loaded instance from the real PHPUnit
        // cache factory singleton so create_config_instance() reuses it rather than
        // attempting (and failing) to build a new one from scratch.
        $realconfig = \cache_factory::instance()->create_config_instance();
        $configsprop = new \ReflectionProperty(\cache_factory::class, 'configs');
        $configsprop->setAccessible(true);
        $configsprop->setValue($factory, ['cache_config_testing' => $realconfig]);

        return $factory;
    }

    /**
     * Reads a protected/private property from an object via reflection.
     *
     * @param object $object
     * @param string $name
     * @return mixed
     */
    private function get_property(object $object, string $name) {
        $property = new \ReflectionProperty($object, $name);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * Sets a protected/private property on an object via reflection.
     *
     * @param object $object
     * @param string $name
     * @param mixed $value
     */
    private function set_property(object $object, string $name, $value): void {
        $property = new \ReflectionProperty($object, $name);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * When the site identifier is not yet known (the chicken and egg bootstrap problem
     * this class handles), create_config_instance() sets the cache stores as disabled for
     * that request. Any definitions/caches created and memoised during that window may
     * freeze an 'unknown' site identifier permanently into their cache key prefix, as key
     * prefixes are only ever generated once per definition instance and then reused.
     *
     * This test confirms that once the site identifier becomes known later in the same
     * request, any definitions/caches memoised while stores were disabled are discarded,
     * so that they are rebuilt (with the correct site identifier) before any real
     * (non-dummy) stores are used.
     */
    public function test_memoised_definitions_are_discarded_when_stores_are_reenabled(): void {
        global $CFG;
        $this->resetAfterTest(true);

        $factory = $this->create_sandboxed_factory();

        // Simulate the bootstrap window: stores disabled because the site identifier
        // was not yet known, with a definition and cache instances memoised in that state.
        $this->set_property($factory, 'state', \cache_factory::STATE_STORES_DISABLED);
        $this->set_property($factory, 'definitions', ['core/config' => (object) ['marker' => 'stale']]);
        $this->set_property($factory, 'cachesfromdefinitions', ['core/config' => (object) ['marker' => 'stale']]);
        $this->set_property($factory, 'cachesfromparams', ['some/params' => (object) ['marker' => 'stale']]);
        $this->set_property($factory, 'stores', ['some_store' => (object) ['marker' => 'stale']]);

        // Now the site identifier becomes known (as it would later in the same request).
        $CFG->siteidentifier = 'realsiteidentifier';

        $factory->create_config_instance();

        $this->assertFalse($factory->stores_disabled(), 'Stores should be re-enabled once the site identifier is known.');
        $this->assertSame([], $this->get_property($factory, 'definitions'));
        $this->assertSame([], $this->get_property($factory, 'cachesfromdefinitions'));
        $this->assertSame([], $this->get_property($factory, 'cachesfromparams'));
        $this->assertSame([], $this->get_property($factory, 'stores'));
    }

    /**
     * When stores are already enabled and the site identifier remains known (the normal,
     * steady-state case), create_config_instance() must NOT discard memoised definitions
     * and cache instances, otherwise caches would be needlessly rebuilt on every call.
     */
    public function test_memoised_definitions_are_kept_when_already_enabled(): void {
        global $CFG;
        $this->resetAfterTest(true);

        $factory = $this->create_sandboxed_factory();

        // Force to the normal, already-ready state.
        $this->set_property($factory, 'state', \cache_factory::STATE_READY);
        $marker = (object) ['marker' => 'kept'];
        $this->set_property($factory, 'definitions', ['core/config' => $marker]);

        $CFG->siteidentifier = 'realsiteidentifier';

        $factory->create_config_instance();

        $this->assertFalse($factory->stores_disabled());
        $this->assertSame(['core/config' => $marker], $this->get_property($factory, 'definitions'));
    }
}
