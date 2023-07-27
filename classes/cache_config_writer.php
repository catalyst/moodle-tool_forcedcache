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

defined('MOODLE_INTERNAL') || die();
// Manually require locallib as it isn't autoloaded.
require_once(__DIR__.'/../../../../cache/locallib.php');

/**
 * This config_writer is readonly, and provides public access to some protected methods.
 *
 * @package     tool_forcedcache
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_forcedcache_cache_config_writer extends cache_config_writer {

    /**
     * Overriding this means nothing gets Written.
     * This must still work if we fallback to core caching.
     *
     * @return void
     */
    public function config_save() {
    }

    // @codingStandardsIgnoreStart Required as this appears as a useless override, but its not.
    /**
     * This is a public wrapper for a protected function, needed from cache_config.php.
     *
     * @param bool $coreonly
     * @return array array of located definitions.
     */
    public static function locate_definitions($coreonly = false) {
        return parent::locate_definitions($coreonly);
    }
    // @codingStandardsIgnoreEnd

    /**
     * This is a public wrapper for a protected function, needed from cache_config.php.
     *
     * @return array array of default store configurations.
     */
    public static function get_default_stores() {
        $defaults = parent::get_default_stores();
        // Get default stores doesn't append some info to the default caches.
        $defaults['default_application']['class'] = 'cachestore_file';
        $defaults['default_session']['class'] = 'cachestore_session';
        $defaults['default_request']['class'] = 'cachestore_static';
        $defaults['default_application']['lock'] = 'cachelock_file_default';
        $defaults['default_session']['lock'] = 'cachelock_file_default';
        $defaults['default_request']['lock'] = 'cachelock_file_default';

        return $defaults;
    }

    /**
     * Override to use the same loader as the forcedcache cache config.
     *
     * @return array
     */
    protected function include_configuration() {
        $config = new tool_forcedcache_cache_config();
        return $config->include_configuration_wrapper();
    }
}
