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
 * Test fixture data for cache stores.
 *
 * @package    tool_forcedcache
 * @copyright  2020 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$storeone = [
    'input' => [
        'filetest' => [
            'type' => 'file',
            'config' => [
                'path' => '/tmp/hardcode',
                'autocreate' => 1,
            ],
        ],
    ],
    'expected' => [
        'filetest' =>
             [
                'name' => 'filetest',
                'plugin' => 'file',
                'configuration' =>
                     [
                        'path' => '/tmp/hardcode',
                        'autocreate' => 1,
                    ],
                'features' => 30,
                'modes' => 3,
                'mappingsonly' => false,
                'class' => 'cachestore_file',
                'default' => false,
                'lock' => 'cachelock_file_default',
                ],
        'default_application' =>
             [
                'name' => 'default_application',
                'plugin' => 'file',
                'configuration' =>
                     [
                    ],
                'features' => 30,
                'modes' => 3,
                'default' => true,
                'class' => 'cachestore_file',
                'lock' => 'cachelock_file_default',
            ],
        'default_session' =>
             [
                'name' => 'default_session',
                'plugin' => 'session',
                'configuration' =>
                 [
                ],
                'features' => 14,
                'modes' => 2,
                'default' => true,
                'class' => 'cachestore_session',
                'lock' => 'cachelock_file_default',
            ],
        'default_request' =>
             [
                'name' => 'default_request',
                'plugin' => 'static',
                'configuration' =>
                 [
                ],
                'features' => 31,
                'modes' => 4,
                'default' => true,
                'class' => 'cachestore_static',
                'lock' => 'cachelock_file_default',
            ],
    ],
];

$storetwo = [
    'input' => [
        'filetest' => [
            'type' => 'file',
            'config' => [
                'path' => '/tmp/hardcode',
                'autocreate' => 1,
            ],
        ],
        'filetest2' => [
            'type' => 'file',
            'config' => [
                'path' => '/tmp/hardcode2',
                'autocreate' => 1,
            ],
        ],
    ],
    'expected' => [
        'filetest' =>
             [
                'name' => 'filetest',
                'plugin' => 'file',
                'configuration' =>
                     [
                        'path' => '/tmp/hardcode',
                        'autocreate' => 1,
                    ],
                'features' => 30,
                'modes' => 3,
                'mappingsonly' => false,
                'class' => 'cachestore_file',
                'default' => false,
                'lock' => 'cachelock_file_default',
            ],
        'filetest2' =>
             [
                'name' => 'filetest2',
                'plugin' => 'file',
                'configuration' =>
                     [
                        'path' => '/tmp/hardcode2',
                        'autocreate' => 1,
                    ],
                'features' => 30,
                'modes' => 3,
                'mappingsonly' => false,
                'class' => 'cachestore_file',
                'default' => false,
                'lock' => 'cachelock_file_default',
            ],
        'default_application' =>
             [
                'name' => 'default_application',
                'plugin' => 'file',
                'configuration' =>
                     [
                    ],
                'features' => 30,
                'modes' => 3,
                'default' => true,
                'class' => 'cachestore_file',
                'lock' => 'cachelock_file_default',
            ],
        'default_session' =>
             [
                'name' => 'default_session',
                'plugin' => 'session',
                'configuration' =>
                 [
                ],
                'features' => 14,
                'modes' => 2,
                'default' => true,
                'class' => 'cachestore_session',
                'lock' => 'cachelock_file_default',
            ],
        'default_request' =>
             [
                'name' => 'default_request',
                'plugin' => 'static',
                'configuration' =>
                 [
                ],
                'features' => 31,
                'modes' => 4,
                'default' => true,
                'class' => 'cachestore_static',
                'lock' => 'cachelock_file_default',
            ],
    ],
];

$storezero = [
    'input' => [
    ],
    'expected' => [
        'default_application' =>
             [
                'name' => 'default_application',
                'plugin' => 'file',
                'configuration' =>
                     [
                    ],
                'features' => 30,
                'modes' => 3,
                'default' => true,
                'class' => 'cachestore_file',
                'lock' => 'cachelock_file_default',
            ],
        'default_session' =>
             [
                'name' => 'default_session',
                'plugin' => 'session',
                'configuration' =>
                 [
                ],
                'features' => 14,
                'modes' => 2,
                'default' => true,
                'class' => 'cachestore_session',
                'lock' => 'cachelock_file_default',
            ],
        'default_request' =>
             [
                'name' => 'default_request',
                'plugin' => 'static',
                'configuration' =>
                 [
                ],
                'features' => 31,
                'modes' => 4,
                'default' => true,
                'class' => 'cachestore_static',
                'lock' => 'cachelock_file_default',
            ],
    ],
];

$storebadtype = [
    'input' => [
        'apcutest' => [
            'type' => 'faketype',
            'config' => [
                'prefix' => 'test',
            ],
        ],
    ],
];

$storemissingfield = [
    'input' => [
        'apcutest' => [
            'type' => 'faketype',
        ],
    ],
];

$storereqsnotmet = [
    'input' => [
        'apcutest' => [
            'type' => 'apcu',
            'config' => [
                'prefix' => 'test_',
            ],
        ],
    ],
    'expected' => [
        'default_application' =>
             [
                'name' => 'default_application',
                'plugin' => 'file',
                'configuration' =>
                     [
                    ],
                'features' => 30,
                'modes' => 3,
                'default' => true,
                'class' => 'cachestore_file',
                'lock' => 'cachelock_file_default',
            ],
        'default_session' =>
             [
                'name' => 'default_session',
                'plugin' => 'session',
                'configuration' =>
                 [
                ],
                'features' => 14,
                'modes' => 2,
                'default' => true,
                'class' => 'cachestore_session',
                'lock' => 'cachelock_file_default',
            ],
        'default_request' =>
             [
                'name' => 'default_request',
                'plugin' => 'static',
                'configuration' =>
                 [
                ],
                'features' => 31,
                'modes' => 4,
                'default' => true,
                'class' => 'cachestore_static',
                'lock' => 'cachelock_file_default',
            ],
    ],
];
