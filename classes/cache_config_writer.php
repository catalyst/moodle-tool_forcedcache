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

// Manually require locallib as it isn't autoloaded.
require_once(__DIR__.'/../../../../cache/locallib.php');

class tool_forcedcache_cache_config_writer extends cache_config_writer {
    //public function add_store_instance() {}

    /**
     * Overriding this means nothing gets Written.
     * This must still work if we fallback to core caching.
     */
    public function config_save() {
        global $CFG;
        if (!empty($CFG->tool_forcedcache_config_broken) &&$CFG->tool_forcedcache_config_broken) {
            parent::config_save();
        }
    }

    // This is a public wrapper for a protected function, needed from cache_config.php.
    public static function locate_definitions($coreonly = false) {
        return parent::locate_definitions($coreonly);
    }

    // This is a public wrapper for a protected function, needed from cache_config.php.
    public static function get_default_stores() {
        return parent::get_default_stores();
    }


    // TODO These should all be overridden with null/parent calls,
    // Which should have marginal performance improvements when these get called.

    //public function delete_lock_instance($name) {}

    //public function delete_store_instance($name) {}

    //public function edit_store_instance($name, $plugin, $configuration) {
    //    return true;
    //}

    //public function set_definition_mappings($definition, $mappings) {}

    //public function set_definition_sharing($definition, $mappings) {}

    // PROBABLY A DECENT PLACE TO PUT RULESET CONFIG
    //public function set_mode_mappings(array $modemappings) {
    //    return true;
    //}

    //public static function update_default_config_stores() {}

    //public static function update_definitions($coreonly = false) {}

    //public static function update_site_identifier($siteidentifier)

    //public function add_lock_instance() {}

    //public static function create_default_configuration($forcesave = false) {}

}
