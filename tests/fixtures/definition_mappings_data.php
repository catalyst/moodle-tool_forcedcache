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
 * Test fixture data for definition mappings.
 *
 * @package    tool_forcedcache
 * @copyright  2020 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$definitionmatchtopruleset = [
    'definition' => [
        'core/string' =>
         [
        'mode' => 1,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 30,
        'canuselocalstore' => true,
        'component' => 'core',
        'area' => 'string',
        'selectedsharingoption' => 2,
        'userinputsharingkey' => '',
        'sharingoptions' => 15,
        ],
    ],
    'rules' => [
        'application' => [
             [
                'conditions' => [
                    'canuselocalstore' => true,
                    'name' => 'core/string',
                ],
                'stores' => [
                    'apcu-test',
                    'file-test',
                ],
             ],
              [
                'stores' => [
                    'file-test',
                ],
             ],
        ],
        'session' => [],
        'request' => [],
    ],
    'expected' => [
        1 =>
         [
          'store' => 'apcu-test',
          'definition' => 'core/string',
          'sort' => 2,
        ],
        2 =>
         [
          'store' => 'file-test',
          'definition' => 'core/string',
          'sort' => 1,
        ],
    ],
];

$definitionnonmatchtopruleset = [
    'definition' => [
        'core/string' =>
         [
        'mode' => 1,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 30,
        'canuselocalstore' => true,
        'component' => 'core',
        'area' => 'string',
        'selectedsharingoption' => 2,
        'userinputsharingkey' => '',
        'sharingoptions' => 15,
        ],
    ],
    'rules' => [
        'application' => [
             [
                'conditions' => [
                    'canuselocalstore' => true,
                    'name' => 'core/fakename',
                ],
                'stores' => [
                    'apcu-test',
                    'file-test',
                ],
             ],
              [
                'stores' => [
                    'file-test',
                ],
             ],
        ],
        'session' => [],
        'request' => [],
    ],
    'expected' => [
        1 =>
         [
          'store' => 'file-test',
          'definition' => 'core/string',
          'sort' => 1,
        ],
    ],
];

$definitionbottomruleset = [
    'definition' => [
        'core/string' =>
         [
        'mode' => 1,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 30,
        'canuselocalstore' => true,
        'component' => 'core',
        'area' => 'string',
        'selectedsharingoption' => 2,
        'userinputsharingkey' => '',
        'sharingoptions' => 15,
        ],
    ],
    'rules' => [
        'application' => [
             [
                'conditions' => [
                    'canuselocalstore' => true,
                    'name' => 'core/fakename',
                ],
                'stores' => [
                    'apcu-test',
                    'file-test',
                ],
             ],
             [
                'conditions' => [
                    'canuselocalstore' => false,
                    'name' => 'core/fakename',
                ],
                'stores' => [
                    'apcu-test',
                    'file-test',
                ],
             ],
              [
                'stores' => [
                    'redis-test',
                ],
             ],
        ],
        'session' => [],
        'request' => [],
    ],
    'expected' => [
        1 =>
         [
          'store' => 'redis-test',
          'definition' => 'core/string',
          'sort' => 1,
        ],
    ],
];

$definitionnoruleset = [
    'definition' => [
        'core/string' =>
         [
        'mode' => 1,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 30,
        'canuselocalstore' => true,
        'component' => 'core',
        'area' => 'string',
        'selectedsharingoption' => 2,
        'userinputsharingkey' => '',
        'sharingoptions' => 15,
        ],
    ],
    'rules' => [
        'application' => [
             [
                'conditions' => [
                    'canuselocalstore' => true,
                    'name' => 'core/fakename',
                ],
                'stores' => [
                    'apcu-test',
                    'file-test',
                ],
             ],
             [
                'conditions' => [
                    'canuselocalstore' => false,
                    'name' => 'core/fakename',
                ],
                'stores' => [
                    'apcu-test',
                    'file-test',
                ],
             ],
              [
                'conditions' => [
                    'canuselocalstore' => false,
                    'name' => 'core/differentfakename',
                ],
                'stores' => [
                    'redis-test',
                ],
             ],
        ],
        'session' => [],
        'request' => [],
    ],
    'expected' => [
    ],
];
