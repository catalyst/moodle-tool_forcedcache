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
 * Plugin status page.
 *
 * @package    tool_forcedcache
 * @copyright  2020 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('tool_forcedcache_status');

$PAGE->set_title(get_string('page_status', 'tool_forcedcache'));
$PAGE->set_heading(get_string('page_status', 'tool_forcedcache'));

echo $OUTPUT->header();

// Create a dummy cache config instance and check for errors in instantiation.
$dummy = new tool_forcedcache_cache_config();
$errors = $dummy->get_inclusion_errors();


if (empty($CFG->alternative_cache_factory_class) ||
    $CFG->alternative_cache_factory_class !== 'tool_forcedcache_cache_factory' ||
    !empty($errors)) {
    echo $OUTPUT->notification(get_string('page_not_active', 'tool_forcedcache'), \core\output\notification::NOTIFY_ERROR);
} else {
    echo $OUTPUT->notification(get_string('page_active', 'tool_forcedcache'), \core\output\notification::NOTIFY_SUCCESS);
}

if (!empty($errors)) {
    echo html_writer::tag('h3', get_string('page_config_broken', 'tool_forcedcache'));
    $error = html_writer::tag('pre', $errors);
    echo html_writer::tag('p', get_string('page_config_broken_details', 'tool_forcedcache', $error));
} else {
    echo $OUTPUT->notification(get_string('page_config_ok', 'tool_forcedcache'), \core\output\notification::NOTIFY_SUCCESS);
}

if (empty($errors)) {
    $adminhelper = new tool_forcedcache_cache_administration_helper();
    echo $adminhelper->get_ruleset_output();
}

echo $OUTPUT->footer();
