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

$storeone = array (
    'input' => array (
        'filetest' => array (
            'type' => 'file',
            'config' => array (
                'path' => '/tmp/hardcode',
                'autocreate' => 1
            )
        )
    ),
    'expected' => array (
        'filetest' =>
            array (
                'name' => 'filetest',
                'plugin' => 'file',
                'configuration' =>
                    array (
                        'path' => '/tmp/hardcode',
                        'autocreate' => 1,
                    ),
                'features' => 30,
                'modes' => 3,
                'mappingsonly' => false,
                'class' => 'cachestore_file',
                'default' => false,
                'lock' => 'cachelock_file_default',
                ),
        'default_application' =>
            array (
                'name' => 'default_application',
                'plugin' => 'file',
                'configuration' =>
                    array (
                    ),
                'features' => 30,
                'modes' => 3,
                'default' => true,
                'class' => 'cachestore_file',
                'lock' => 'cachelock_file_default',
            ),
        'default_session' =>
            array (
                'name' => 'default_session',
                'plugin' => 'session',
                'configuration' =>
                array (
                ),
                'features' => 14,
                'modes' => 2,
                'default' => true,
                'class' => 'cachestore_session',
                'lock' => 'cachelock_file_default',
            ),
        'default_request' =>
            array (
                'name' => 'default_request',
                'plugin' => 'static',
                'configuration' =>
                array (
                ),
                'features' => 31,
                'modes' => 4,
                'default' => true,
                'class' => 'cachestore_static',
                'lock' => 'cachelock_file_default',
            )
    )
);

$storetwo = array (
    'input' => array (
        'filetest' => array (
            'type' => 'file',
            'config' => array (
                'path' => '/tmp/hardcode',
                'autocreate' => 1
            )
        ),
        'filetest2' => array (
            'type' => 'file',
            'config' => array (
                'path' => '/tmp/hardcode2',
                'autocreate' => 1
            )
        )
    ),
    'expected' => array (
        'filetest' =>
            array (
                'name' => 'filetest',
                'plugin' => 'file',
                'configuration' =>
                    array (
                        'path' => '/tmp/hardcode',
                        'autocreate' => 1,
                    ),
                'features' => 30,
                'modes' => 3,
                'mappingsonly' => false,
                'class' => 'cachestore_file',
                'default' => false,
                'lock' => 'cachelock_file_default',
            ),
        'filetest2' =>
            array (
                'name' => 'filetest2',
                'plugin' => 'file',
                'configuration' =>
                    array (
                        'path' => '/tmp/hardcode2',
                        'autocreate' => 1,
                    ),
                'features' => 30,
                'modes' => 3,
                'mappingsonly' => false,
                'class' => 'cachestore_file',
                'default' => false,
                'lock' => 'cachelock_file_default',
            ),
        'default_application' =>
            array (
                'name' => 'default_application',
                'plugin' => 'file',
                'configuration' =>
                    array (
                    ),
                'features' => 30,
                'modes' => 3,
                'default' => true,
                'class' => 'cachestore_file',
                'lock' => 'cachelock_file_default',
            ),
        'default_session' =>
            array (
                'name' => 'default_session',
                'plugin' => 'session',
                'configuration' =>
                array (
                ),
                'features' => 14,
                'modes' => 2,
                'default' => true,
                'class' => 'cachestore_session',
                'lock' => 'cachelock_file_default',
            ),
        'default_request' =>
            array (
                'name' => 'default_request',
                'plugin' => 'static',
                'configuration' =>
                array (
                ),
                'features' => 31,
                'modes' => 4,
                'default' => true,
                'class' => 'cachestore_static',
                'lock' => 'cachelock_file_default',
            )
    )
);

$storezero = array (
    'input' => array (
    ),
    'expected' => array (
        'default_application' =>
            array (
                'name' => 'default_application',
                'plugin' => 'file',
                'configuration' =>
                    array (
                    ),
                'features' => 30,
                'modes' => 3,
                'default' => true,
                'class' => 'cachestore_file',
                'lock' => 'cachelock_file_default',
            ),
        'default_session' =>
            array (
                'name' => 'default_session',
                'plugin' => 'session',
                'configuration' =>
                array (
                ),
                'features' => 14,
                'modes' => 2,
                'default' => true,
                'class' => 'cachestore_session',
                'lock' => 'cachelock_file_default',
            ),
        'default_request' =>
            array (
                'name' => 'default_request',
                'plugin' => 'static',
                'configuration' =>
                array (
                ),
                'features' => 31,
                'modes' => 4,
                'default' => true,
                'class' => 'cachestore_static',
                'lock' => 'cachelock_file_default',
            )
    )
);

$storebadtype = array (
    'input' => array (
        'apcutest' => array (
            'type' => 'faketype',
            'config' => array (
                'prefix' => 'test'
            )
        )
    )
);

$storemissingfield = array (
    'input' => array (
        'apcutest' => array (
            'type' => 'faketype',
        )
    )
);

$storereqsnotmet = array (
    'input' => array (
        'apcutest' => array (
            'type' => 'apcu',
            'config' => array (
                'prefix' => 'test_',
            )
        )
    ),
    'expected' => array (
        'default_application' =>
            array (
                'name' => 'default_application',
                'plugin' => 'file',
                'configuration' =>
                    array (
                    ),
                'features' => 30,
                'modes' => 3,
                'default' => true,
                'class' => 'cachestore_file',
                'lock' => 'cachelock_file_default',
            ),
        'default_session' =>
            array (
                'name' => 'default_session',
                'plugin' => 'session',
                'configuration' =>
                array (
                ),
                'features' => 14,
                'modes' => 2,
                'default' => true,
                'class' => 'cachestore_session',
                'lock' => 'cachelock_file_default',
            ),
        'default_request' =>
            array (
                'name' => 'default_request',
                'plugin' => 'static',
                'configuration' =>
                array (
                ),
                'features' => 31,
                'modes' => 4,
                'default' => true,
                'class' => 'cachestore_static',
                'lock' => 'cachelock_file_default',
            )
    )
);
