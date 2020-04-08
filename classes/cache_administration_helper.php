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
 * This class changes the actions that are available to various stores,
 * and changes the layout slightly
 *
 * @package     tool_forcedcache
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



// Manually require locallib as class isn't autoloaded.
require_once(__DIR__.'/../../../../cache/locallib.php');

class tool_forcedcache_cache_administration_helper extends cache_administration_helper {

    /**
     * We don't wish any actions to be allowed on stores.
     *
     * @param string $name the store name.
     * @param array $plugindetails details of the plugin.
     * @return array array of plugin actions.
     */
    public function get_store_plugin_actions($name, array $plugindetails) {
        return array();
    }

    /**
     * The only action allowed for stores is purge.
     *
     * @param string $name The store instance name.
     * @param array $storedetails details of the store instance
     * @return void
     */
    public function get_store_instance_actions($name, array $storedetails) {
        $actions = array();
        if (has_capability('moodle/site:config', context_system::instance())) {
            $baseurl = new moodle_url('/cache/admin.php', array('store' => $name, 'sesskey' => sesskey()));
            $actions[] = array(
                'text' => get_string('purge', 'cache'),
                'url' => new moodle_url($baseurl, array('action' => 'purgestore'))
            );
        }
        return $actions;
    }

    /**
     * The only action allowed for definitions is purge.
     *
     * @param context $context
     * @param array $definitionsummary
     * @return void
     */
    public function get_definition_actions(context $context, array $definitionsummary) {
        $actions = array();
        if (has_capability('moodle/site:config', $context)) {
            $actions[] = array(
                'text' => get_string('purge', 'cache'),
                'url' => new moodle_url('/cache/admin.php', array('action' => 'purgedefinition', 'sesskey' => sesskey()))
            );
        }
        return $actions;
    }

    /**
     * We dont wish any locks to be addable.
     *
     * @return array
     */
    public function get_addable_lock_options() {
        return array();
    }

    public function output_admin_page($storepluginsummaries, $storeinstancesummaries, $definitionsummaries, $defaultmodestores, $locks, $renderer) {
        $context = context_system::instance();

        echo $renderer->store_plugin_summaries($storepluginsummaries);
        echo $renderer->store_instance_summariers($storeinstancesummaries, $storepluginsummaries);
        echo $renderer->definition_summaries($definitionsummaries, $context);
        echo $renderer->lock_summaries($locks);

        echo $this->get_ruleset_output();
    }

    /**
     * This generates the HTML to display the currently active rulesets.
     *
     * @return string HTML to display the currently active rulesets.
     */
    public function get_ruleset_output() {
        global $CFG;

        $html = html_writer::tag('h3', get_string('page_rulesets', 'tool_forcedcache'));

        if (!empty($CFG->tool_forcedcache_config_path)) {
            $path = $CFG->tool_forcedcache_config_path;
        } else {
            $path = __DIR__.'/../config.json';
        }
        // We dont need safety here, if we reach this point,
        // Its already been included and working.
        $config = tool_forcedcache_cache_config::read_config_file($path);

        $applicationtable = $this->generate_mode_table(cache_store::MODE_APPLICATION, $config);
        $sessiontable = $this->generate_mode_table(cache_store::MODE_SESSION, $config);
        $requesttable = $this->generate_mode_table(cache_store::MODE_REQUEST, $config);

        return $html . $applicationtable . $sessiontable . $requesttable;
    }

    private function generate_mode_table($mode, $config) {
        $html = '';

        $rules = $config['rules'];
        // Assign a bitmask to rule keys
        switch ($mode) {
            case cache_store::MODE_APPLICATION:
                $ruletype = 'application';
                break;

            case cache_store::MODE_SESSION:
                $ruletype = 'session';
                break;

            case cache_store::MODE_REQUEST:
                $ruletype = 'request';
                break;
        }

        $table = new html_table();
        $table->id = $mode . '_rule_table';
        //$table->attributes['class'] = 'generaltable table table-bordered'; Might look ugly
        $table->head = array (
            get_string('rule_priority', 'tool_forcedcache'),
            get_string('rule_ruleset', 'tool_forcedcache'),
            get_string('mappings', 'cache')
        );

        $counter = 1;
        foreach ($rules[$ruletype] as $ruleset) {
            if (array_key_exists('conditions', $ruleset)) {
                // Little bit of string mangling.
                $conditions = '';
                foreach ($ruleset['conditions'] as $condition => $value) {
                    $conditions .= $condition . ' = ' . $value . ', ';
                }
                $conditions = rtrim($conditions, ', ');
            } else {
                $conditions = get_string('rule_noconditions', 'tool_forcedcache');
            }

            $table->data[] = array(
                $counter,
                $conditions,
                implode(',', $ruleset['stores']),
            );
            $counter++;
        }

        // Now output a header and the table
        $ruletype = ucwords($ruletype);
        $html .= html_writer::tag('h4', get_string('page_mode', 'tool_forcedcache', $ruletype));
        $html .= html_writer::table($table);

        return $html;
    }
}
