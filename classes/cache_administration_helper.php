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

defined('MOODLE_INTERNAL') || die();
// Manually require locallib as class isn't autoloaded.
require_once(__DIR__.'/../../../../cache/locallib.php');

class tool_forcedcache_cache_administration_helper extends cache_administration_helper {

    /**
     * Empty constructor so cache_helper::__construct isn't called.
     */
    public function __construct() {
        // Nothing to do here.
    }

    /**
     * The only action allowed for stores is purge.
     *
     * @param string $name The store instance name.
     * @param array $storedetails details of the store instance.
     * @return array array of store instance actions.
     */
    public function get_store_instance_actions(string $name, array $storedetails) : array {
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
     * @param context $context the context for the definition.
     * @param array $definitionsummary summary of definition.
     * @return array array of definition actions.
     */
    public function get_definition_actions(context $context, array $definitionsummary) : array {
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
     * This an alternate output method from the cache_renderer.
     * It is identical but removes the select from the end of the HTML
     *
     * @param array $locks the array of configured locks.
     * @return string the HTML for the lock summaries
     */
    public function lock_summaries(array $locks) : string {
        global $OUTPUT;

        $table = new html_table();
        $table->colclasses = array(
            'name',
            'type',
            'default',
            'uses',
            'actions'
        );
        $table->rowclasses = array(
            'lock_name',
            'lock_type',
            'lock_default',
            'lock_uses',
            'lock_actions',
        );
        $table->head = array(
            get_string('lockname', 'cache'),
            get_string('locktype', 'cache'),
            get_string('lockdefault', 'cache'),
            get_string('lockuses', 'cache'),
            get_string('actions', 'cache')
        );
        $table->data = array();
        $tick = $OUTPUT->pix_icon('i/valid', '');
        foreach ($locks as $lock) {
            $actions = array();
            if ($lock['uses'] === 0 && !$lock['default']) {
                $url = new moodle_url('/cache/admin.php', array('lock' => $lock['name'],
                    'action' => 'deletelock', 'sesskey' => sesskey()));
                $actions[] = html_writer::link($url, get_string('delete', 'cache'));
            }
            $table->data[] = new html_table_row(array(
                new html_table_cell($lock['name']),
                new html_table_cell($lock['type']),
                new html_table_cell($lock['default'] ? $tick : ''),
                new html_table_cell($lock['uses']),
                new html_table_cell(join(' ', $actions))
            ));
        }

        $html = html_writer::start_tag('div', array('id' => 'core-cache-lock-summary'));
        $html .= $OUTPUT->heading(get_string('locksummary', 'cache'), 3);
        $html .= html_writer::table($table) . '<br>';
        $html .= html_writer::end_div();
        return $html;
    }

    /**
     * This function performs all of the outputting for the cache admin page,
     * with some custom tweaks for the plugin.
     *
     * @param core_cache_renderer $renderer
     * @return string HTML for the page;
     */
    public function generate_admin_page(core_cache_renderer $renderer) : string {
        $context = context_system::instance();
        $html = '';

        $storepluginsummaries = $this->get_store_plugin_summaries();
        $storeinstancesummaries = $this->get_store_instance_summaries();
        $definitionsummaries = $this->get_definition_summaries();
        $locks = $this->get_lock_summaries();

        $html .= $renderer->store_plugin_summaries($storepluginsummaries);
        $html .= $renderer->store_instance_summariers($storeinstancesummaries, $storepluginsummaries);
        $html .= $renderer->definition_summaries($definitionsummaries, $context);
        $html .= $this->lock_summaries($locks);
        $html .= $this->get_ruleset_output();

        return $html;
    }

    /**
     * This generates the HTML to display the currently active rulesets.
     *
     * @return string HTML to display the currently active rulesets.
     */
    public function get_ruleset_output() : string {
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

    /**
     * Generates a ruleset table for the selected caching mode.
     *
     * @param integer $mode the mode to generate the table for.
     * @param array $config the config array from the JSON.
     * @return string HTML for the table.
     */
    private function generate_mode_table(int $mode, array $config) : string {
        $html = '';

        $rules = $config['rules'];
        // Assign a bitmask to rule keys.
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
        $table->attributes['class'] = 'generaltable table table-bordered';
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

        // Now output a header and the table.
        $formattedruletype = ucwords($ruletype);
        $html .= html_writer::tag('h4', get_string('page_mode', 'tool_forcedcache', $formattedruletype));

        if (count($rules[$ruletype]) === 0) {
            $html .= html_writer::tag('h5', get_string('rule_no_rulesets', 'tool_forcedcache'));
        } else {
            $html .= html_writer::table($table);
        }
        return html_writer::tag('p', $html);
    }

    /**
     * This function processes the actions available on the cache_admin page.
     * The only allowed actions are purges and rescans, as the config is read-only.
     * forminfo is required for compatability with parent function signature.
     *
     * @param string $action the action to perform
     * @param array $forminfo empty array to be passed through function
     * @return array empty array
     */
    public function perform_cache_actions(string $action, array $forminfo) : array {
        // Purge actions will statically reference the core implementation.
        $corehelper = new cache_administration_display_helper;

        switch ($action) {
            case 'rescandefinitions':
                $corehelper->action_rescan_definition();
                break;

            case 'purgedefinition':
                $corehelper->action_purgedefinition();
                break;

            case 'purgestore':
            case 'purge':
                $corehelper->action_purge();
                break;
        }

        return $forminfo;
    }

    /**
     * Gets an instance of the custom administration helper.
     * This shouldn't be called directly, use cache_administration_helper::instance()
     * This is used by the plugin status page to get some renderer functionality.
     *
     * @return cache_administration_helper
     */
    public static function instance() : cache_administration_helper {
        if (is_null(self::$instance)) {
            self::$instance = new tool_forcedcache_cache_administration_helper();
        }
        return self::$instance;
    }
}
