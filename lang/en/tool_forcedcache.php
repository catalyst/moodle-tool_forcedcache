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

$string['pluginname'] = 'Forced Caching';

// Page Strings.
$string['page_status'] = 'Forced caching status';
$string['page_config_ok'] = 'Forced cache config OK';
$string['page_config_broken'] = 'Forced cache config broken';
$string['page_config_broken_details'] = 'The reported error message is: {$a}';
$string['page_mode'] = 'Mode: {$a}';
$string['page_rulesets'] = 'Caching Rules';
$string['page_not_active'] = 'Forced cache configuration is NOT active.';
$string['page_active'] = 'Forced cache configuration IS active.';

// Exception Strings.
$string['config_json_parse_fail'] = 'Error parsing JSON to array. JSON syntax may be malformed.';
$string['config_json_missing'] = 'Error reading specified JSON file. File may not exist, or path is incorrect.';
$string['store_missing_fields'] = 'Error reading store {$a}, it may be missing fields or malformed.';
$string['store_bad_type'] = 'Error loading store {$a}. Store may not exist or type is malformed.';

// Table Strings.
$string['rule_priority'] = 'Priority';
$string['rule_ruleset'] = 'Ruleset';
$string['rule_noconditions'] = 'No conditions set';
$string['rule_no_rulesets'] = 'No rulesets are defined for this mode.';
