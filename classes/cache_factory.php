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
 * This cache_factory swaps the cache_config and cache_config_writer.
 *
 * @package     tool_forcedcache
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_forcedcache_cache_factory extends cache_factory {

    /**
     * This is a copy of the core class, with the classes swapped out.
     * TODO: Refactor core method to accept class param, and call parent with param.
     *
     * @param bool $writer
     */
    public function create_config_instance($writer = false) {
        global $CFG;

        // The class to use.
        $class = 'tool_forcedcache_cache_config';
        // Are we running tests of some form?
        $testing = (defined('PHPUNIT_TEST') && PHPUNIT_TEST) || defined('BEHAT_SITE_RUNNING');

        // Check if this is a PHPUnit test and redirect to the phpunit config classes if it is.
        if ($testing) {
            require_once($CFG->dirroot.'/cache/locallib.php');
            require_once($CFG->dirroot.'/cache/tests/fixtures/lib.php');
            // We have just a single class for PHP unit tests. We don't care enough about its
            // performance to do otherwise and having a single method allows us to inject things into it
            // while testing.
            $class = 'cache_config_testing';
        }

        if ($writer) {
            require_once($CFG->dirroot.'/cache/locallib.php');
            if (!$testing) {
                $class .= '_writer';
            }
        }

        if (!array_key_exists($class, $this->configs)) {
            // Create a new instance and call it to load it.
            $this->configs[$class] = new $class;
            $this->configs[$class]->load();
        }

        // We need the siteid in order to use the caches, but the siteid
        // is also looked up from the caches so we have a chicken and egg
        // situation in the bootstrap. This first time we configure the
        // caches but disable them so the siteid can warm up correctly.
        if (empty($CFG->siteidentifier)) {
            $this->set_state(self::STATE_STORES_DISABLED);
        } else {
            $this->set_state(self::STATE_READY);
        }

        // Return the instance.
        return $this->configs[$class];
    }

    /**
     * Returns an instance of the tool_forcedcache administration helper,
     * only if forcedcaching config is OK.
     *
     * @return core_cache\administration_helper
     */
    public static function get_administration_display_helper() : core_cache\administration_helper {
        // Check if there was a config error.
        global $SESSION;

        if (is_null(self::$displayhelper)) {
            if (!empty($SESSION->tool_forcedcache_caching_exception)) {
                self::$displayhelper = new core_cache\administration_helper();
            } else {
                self::$displayhelper = new tool_forcedcache_cache_administration_helper();
            }
        }
        // Unset session error so checks are fresh.
        unset($SESSION->tool_forcedcache_caching_exception);
        return self::$displayhelper;
    }

    /**
     * This factory is readonly, so the state can never be updating.
     * This should prevent instances where the configs are dropped
     * as the factory believes it needs to update.
     *
     * @return bool
     */
    public function updating_started() {
        if ($this->state !== self::STATE_READY) {
            return false;
        }
        return true;
    }

    /**
     * Don't care about disabled caching, the writer is just here to provide purging.
     *
     * @return cache_config_writer
     */
    public static function get_disabled_writer(): cache_config_writer {
        $factoryinstance = new tool_forcedcache_cache_factory();
        return $factoryinstance->create_config_instance(true);
    }
}
