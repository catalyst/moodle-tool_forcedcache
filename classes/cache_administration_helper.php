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
 * This class changes the actions that are available to various stores, and changes the layout slightly
 *
 * @package     tool_forcedcache
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_forcedcache_cache_administration_helper extends core_cache\administration_helper {

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
    public function get_store_instance_actions(string $name, array $storedetails): array {
        global $OUTPUT;
        $actions = array();
        if (has_capability('moodle/site:config', context_system::instance())) {
            $baseurl = new moodle_url('/cache/admin.php', array('store' => $name, 'sesskey' => sesskey()));
            $actions[] = $OUTPUT->action_link(
                new moodle_url($baseurl, array('action' => 'purgestore')),
                get_string('purge', 'cache')
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
    public function get_definition_actions(context $context, array $definitionsummary): array {
        global $OUTPUT;
        $actions = array();
        if (has_capability('moodle/site:config', $context)) {
            $actions[] = $OUTPUT->action_link(
                new moodle_url('/cache/admin.php', array('action' => 'purgedefinition',
                    'definition' => $definitionsummary['id'], 'sesskey' => sesskey())),
                get_string('purge', 'cache')
            );
        }
        return $actions;
    }

    /**
     * This function performs all of the outputting for the cache admin page,
     * with some custom tweaks for the plugin.
     *
     * @param \core_cache\output\renderer $renderer
     * @return string HTML for the page;
     */
    public function generate_admin_page(\core_cache\output\renderer $renderer): string {
        $context = context_system::instance();
        $html = '';

        $storepluginsummaries = $this->get_store_plugin_summaries();
        $storeinstancesummaries = $this->get_store_instance_summaries();
        $definitionsummaries = $this->get_definition_summaries();
        $locks = $this->get_lock_summaries();

        $html .= $renderer->store_plugin_summaries($storepluginsummaries);
        $html .= $renderer->store_instance_summariers($storeinstancesummaries, $storepluginsummaries);
        $html .= $renderer->definition_summaries($definitionsummaries, $context);
        $html .= $renderer->lock_summaries($locks);
        $html .= $this->get_ruleset_output();

        return $html;
    }

    /**
     * This generates the HTML to display the currently active rulesets.
     *
     * @return string HTML to display the currently active rulesets.
     */
    public function get_ruleset_output(): string {
        global $CFG, $OUTPUT;

        if (!empty($CFG->tool_forcedcache_config_path)) {
            $path = $CFG->tool_forcedcache_config_path;
        } else {
            $path = __DIR__.'/../config.json';
        }
        // We dont need safety here, if we reach this point,
        // Its already been included and working.
        $config = tool_forcedcache_cache_config::read_config_file($path);

        $html = $OUTPUT->heading(get_string('stores', 'cache'), 2);
        foreach ($config['stores'] as $name => $store) {
            $html .= $this->generate_store_table($name, $store);
        }

        $html .= $OUTPUT->heading(get_string('page_rulesets', 'tool_forcedcache'), 2);

        $html .= $this->generate_mode_table(cache_store::MODE_APPLICATION, $config);
        $html .= $this->generate_mode_table(cache_store::MODE_SESSION, $config);
        $html .= $this->generate_mode_table(cache_store::MODE_REQUEST, $config);

        $html .= $this->generate_override_table($config['definitionoverrides']);
        return html_writer::tag('div', $html);
    }

    /**
     * Generates a config table for the selected caching store
     *
     * @param string $name def table name (prefix)
     * @param array $config the config array from the JSON.
     * @return string HTML for the table.
     */
    private function generate_store_table(string $name, array $config): string {
        global $OUTPUT;

        $html = $OUTPUT->heading(get_string('page_store', 'tool_forcedcache', ['name' => $name, 'type' => $config['type']]), 3);

        $table = new html_table();
        $table->id = $name . '_def_table';
        $table->attributes['class'] = 'generaltable table table-bordered table-sm w-auto';
        $table->head = array (
            get_string('store_config', 'tool_forcedcache'),
            get_string('store_value', 'tool_forcedcache'),
        );
        $table->data = [];
        foreach ($config['config'] as $key => $value) {
            $table->data[] = [$key, $value];
        }
        $html .= html_writer::table($table);

        return html_writer::tag('div', $html);
    }

    /**
     * Generates a config table for the definition overrides.
     *
     * @param array $overrides the overrides array from the JSON.
     * @return string HTML for the table.
     */
    private function generate_override_table(array $overrides): string {
        global $OUTPUT;

        if (empty($overrides)) {
            return '';
        }

        $html = $OUTPUT->heading(get_string('definition_overrides_title', 'tool_forcedcache'), 3);

        $table = new html_table();
        $table->id = 'def_override_table';
        $table->attributes['class'] = 'generaltable table table-bordered table-sm w-auto';
        $table->head = array (
            get_string('definition_name', 'tool_forcedcache'),
            get_string('definition_overrides', 'tool_forcedcache'),
        );
        $table->data = [];
        foreach ($overrides as $definition => $items) {
            $itemstring = '';
            foreach ($items as $setting => $value) {
                $itemstring .= "{$setting}: {$value} <br>";
            }

            $table->data[] = [$definition, $itemstring];
        }
        $html .= html_writer::table($table);

        return html_writer::tag('div', $html);
    }

    /**
     * Generates a ruleset table for the selected caching mode.
     *
     * @param int $mode the mode to generate the table for.
     * @param array $config the config array from the JSON.
     * @return string HTML for the table.
     */
    private function generate_mode_table(int $mode, array $config): string {
        global $OUTPUT;
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
        $table->attributes['class'] = 'generaltable table table-bordered table-sm w-auto';
        $table->head = array (
            get_string('rule_priority', 'tool_forcedcache'),
            get_string('rule_ruleset', 'tool_forcedcache'),
            get_string('mappings', 'cache')
        );

        $counter = 1;
        $defaultrulestr = get_string('rule_default_rule', 'tool_forcedcache');
        foreach ($rules[$ruletype] as $ruleset) {
            if (array_key_exists('conditions', $ruleset)) {
                // Little bit of string mangling.
                $conditions = '';
                foreach ($ruleset['conditions'] as $condition => $value) {
                    $conditions .= $condition . ' = ' . $value . ', ';
                }
                $conditions = rtrim($conditions, ', ');
            } else {
                $conditions = $defaultrulestr;
            }

            $table->data[] = array(
                $counter,
                $conditions,
                implode(',', $ruleset['stores']),
            );
            $counter++;
        }

        // Ensure there is always a default rule shown. (Either the broadest rule
        // will be the default, or if no broad rule, it will use the system's
        // default).
        if (empty($table->data) || end($table->data)[1] !== $defaultrulestr) {
            // Append a default entry to the table.
            $defaultmodemappings = tool_forcedcache_cache_config::get_default_mode_mappings();
            $defaultstoreformode = array_filter($defaultmodemappings, function($modemapping) use ($mode) {
                return $modemapping['mode'] === $mode;
            });
            $defaultstore = reset($defaultstoreformode)['store'];
            $conditions = $defaultrulestr;

            $table->data[] = array(
                $counter,
                $conditions,
                implode(',', (array) $defaultstore),
            );
        }

        // Now output a header and the table.
        $formattedruletype = ucwords($ruletype);
        $html .= $OUTPUT->heading(get_string('page_mode', 'tool_forcedcache', $formattedruletype), 3);

        if (count($rules[$ruletype]) === 0) {
            $html .= $OUTPUT->notification(
                get_string('rule_no_rulesets', 'tool_forcedcache', $defaultstore),
                \core\output\notification::NOTIFY_WARNING);
            $html .= html_writer::table($table);
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
    public function perform_cache_actions(string $action, array $forminfo): array {
        // Purge actions will statically reference the core implementation.
        $corehelper = new core_cache\local\administration_display_helper();

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
     * @return core_cache\administration_helper
     */
    public static function instance(): core_cache\administration_helper {
        if (is_null(self::$instance)) {
            self::$instance = new tool_forcedcache_cache_administration_helper();
        }
        return self::$instance;
    }

    /**
     * Gets usage information about the whole cache system.
     *
     * This is a slow function and should only be used on an admin information page.
     *
     * The returned array lists all cache definitions with fields 'cacheid' and 'stores'. For
     * each store, the following fields are available:
     *
     * - name (store name)
     * - class (e.g. cachestore_redis)
     * - supported (true if we have any information)
     * - items (number of items stored)
     * - mean (mean size of item)
     * - sd (standard deviation for item sizes)
     * - margin (margin of error for mean at 95% confidence)
     * - storetotal (total usage for store if known, otherwise null)
     *
     * The storetotal field will be the same for every cache that uses the same store.
     *
     * @param int $samplekeys Number of keys to sample when checking size of large caches
     * @return array Details of cache usage
     */
    public function get_usage(int $samplekeys): array {
        $corehelper = new core_cache\local\administration_display_helper();
        return $corehelper->get_usage($samplekeys);
    }
}
