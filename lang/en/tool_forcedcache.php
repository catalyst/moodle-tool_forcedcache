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
 * Language strings
 *
 * @package    tool_forcedcache
 * @author     Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright  2022, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Forced Caching';

// Page Strings.
$string['page_status'] = 'Forced caching status';
$string['page_config_ok'] = 'Forced cache config OK';
$string['page_config_broken'] = 'Forced cache config broken';
$string['page_config_broken_details'] = 'The reported error message is: {$a}';
$string['page_mode'] = 'Mode: {$a}';
$string['page_store'] = 'Store: {$a->name} ({$a->type})';
$string['page_rulesets'] = 'Caching Rules';
$string['page_not_active'] = 'Forced cache configuration is NOT active.';
$string['page_active'] = 'Forced cache configuration IS active.';

// Exception Strings.
$string['config_json_parse_fail'] = 'Error parsing JSON to array. JSON syntax may be malformed.';
$string['config_array_parse_fail'] = 'Error parsing configuration array. Array syntax may be malformed.';
$string['config_json_missing'] = 'Error reading JSON file. File may not exist, path is incorrect or path has not been set.';
$string['config_json_path_invalid'] = 'Invalid configuration path. Please ensure the path {$a->path} is outside of the {$a->dirroot} directory.
Please see https://github.com/catalyst/moodle-tool_forcedcache#set-a-path-to-the-json-configuration for further instructions.';
$string['config_path_and_array'] = 'Detected both path to file and config array. Only one can be specified.';
$string['definition_not_found'] = 'Definition not defined for configuration override: {$a}.';
$string['store_missing_fields'] = 'Error reading store {$a}, it may be missing fields or malformed.';
$string['store_bad_type'] = 'Error loading store {$a}. Store may not exist or type is malformed.';
$string['store_not_ready'] = 'Error creating store {$a}, config may be incorrect or missing required fields.';


// Table Strings.
$string['definition_overrides_title'] = 'Definition overrides';
$string['definition_name'] = 'Definition';
$string['definition_overrides'] = 'Overrides';
$string['rule_default_rule'] = 'Default rule';
$string['rule_no_rulesets'] = 'No rulesets are defined for this mode. The default store for this mode is: {$a}.';
$string['rule_priority'] = 'Priority';
$string['rule_ruleset'] = 'Ruleset';
$string['store_config'] = 'Name';
$string['store_value'] = 'Value';

// Check Strings.
$string['checkforcedcache'] = 'Check that forced caching is enabled.';

$string['privacy:metadata'] = 'The forced caching plugin does not store any data for any reason.';
