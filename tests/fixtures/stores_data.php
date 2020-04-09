<?php defined('MOODLE_INTERNAL') || die();

$store_one = array (
    'input' => array (
        'apcu-test' => array (
            'type' => 'apcu',
            'config' => array (
                'prefix' => 'test'
            )
        )
    ),
    'expected' => array (
        'apcu-test' =>
            array (
                'name' => 'apcu-test',
                'plugin' => 'apcu',
                'configuration' =>
                    array (
                    'prefix' => 'test',
                ),
                'features' => 4,
                'modes' => 3,
                'mappingsonly' => false,
                'class' => 'cachestore_apcu',
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

$store_two = array (
    'input' => array (
        'apcu-test' => array (
            'type' => 'apcu',
            'config' => array (
                'prefix' => 'test'
            )
        ),
        'file-test' => array (
            'type' => 'file',
            'config' => array (
                'path' => '/tmp/hardcode',
                'autocreate' => 1
            )
        )
    ),
    'expected' => array (
        'apcu-test' =>
            array (
                'name' => 'apcu-test',
                'plugin' => 'apcu',
                'configuration' =>
                    array (
                        'prefix' => 'test',
                ),
                'features' => 4,
                'modes' => 3,
                'mappingsonly' => false,
                'class' => 'cachestore_apcu',
                'default' => false,
                'lock' => 'cachelock_file_default',
            ),
        'file-test' =>
            array (
                'name' => 'file-test',
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

$store_zero = array (
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

$store_badtype = array (
    'input' => array (
        'apcu-test' => array (
            'type' => 'faketype',
            'config' => array (
                'prefix' => 'test'
            )
        )
    )
);

$store_missingfield = array (
    'input' => array (
        'apcu-test' => array (
            'type' => 'faketype',
        )
    )
);
