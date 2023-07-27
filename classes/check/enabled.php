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

namespace tool_forcedcache\check;
use core\check\check;
use core\check\result;

/**
 * Forcedcache enabled check
 *
 * @package     tool_forcedcache
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enabled extends check {
    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('page_status', 'tool_forcedcache');
    }

    /**
     * Getter for a link to page with more information.
     *
     * @return \action_link|null
     */
    public function get_action_link(): ?\action_link {
        $url = new \moodle_url('/admin/tool/forcedcache/index.php');
        return new \action_link($url, get_string('page_status', 'tool_forcedcache'));
    }

    /**
     * Getter for the status of the check.
     *
     * @return result
     */
    public function get_result() : result {
        global $CFG;

        // Create a dummy cache config instance and check for errors in instantiation.
        $dummy = new \tool_forcedcache_cache_config();
        $errors = $dummy->get_inclusion_errors();

        $forcedcacheenabled = isset($CFG->alternative_cache_factory_class)
            && $CFG->alternative_cache_factory_class === 'tool_forcedcache_cache_factory';

        if (!$forcedcacheenabled || !empty($errors)) {
            $status = $forcedcacheenabled ? result::ERROR : result::WARNING;
            $summary = get_string('page_not_active', 'tool_forcedcache');
        } else {
            $status = result::OK;
            $summary = get_string('page_active', 'tool_forcedcache');
        }

        return new result($status, $summary);
    }
}
