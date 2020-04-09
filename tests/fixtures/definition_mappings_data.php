<?php defined('MOODLE_INTERNAL') || die();

$definition_match_top_ruleset = array (
    'definition' => array (
        'core/string' =>
        array (
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
        ),
    ),
    'rules' => array (
        'application' => array (
            array (
                'conditions' => array (
                    'canuselocalstore' => true,
                    'name' => 'core/string'
                ),
                'stores' => array(
                    'apcu-test',
                    'file-test'
                )
            ),
            array (
                'stores' => array (
                    'file-test'
                )
            )
        ),
        'session' => array(),
        'request' => array()
    ),
    'expected' => array (
        1 =>
        array (
          'store' => 'apcu-test',
          'definition' => 'core/string',
          'sort' => 2,
        ),
        2 =>
        array (
          'store' => 'file-test',
          'definition' => 'core/string',
          'sort' => 1,
        )
    )
);

$definition_non_match_top_ruleset = array (
    'definition' => array (
        'core/string' =>
        array (
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
        ),
    ),
    'rules' => array (
        'application' => array (
            array (
                'conditions' => array (
                    'canuselocalstore' => true,
                    'name' => 'core/fakename'
                ),
                'stores' => array(
                    'apcu-test',
                    'file-test'
                )
            ),
            array (
                'stores' => array (
                    'file-test'
                )
            )
        ),
        'session' => array(),
        'request' => array()
    ),
    'expected' => array (
        1 =>
        array (
          'store' => 'file-test',
          'definition' => 'core/string',
          'sort' => 1,
        )
    )
);

$definition_bottom_ruleset = array (
    'definition' => array (
        'core/string' =>
        array (
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
        ),
    ),
    'rules' => array (
        'application' => array (
            array (
                'conditions' => array (
                    'canuselocalstore' => true,
                    'name' => 'core/fakename'
                ),
                'stores' => array(
                    'apcu-test',
                    'file-test'
                )
            ),
            array (
                'conditions' => array (
                    'canuselocalstore' => false,
                    'name' => 'core/fakename'
                ),
                'stores' => array(
                    'apcu-test',
                    'file-test'
                )
            ),
            array (
                'stores' => array (
                    'redis-test'
                )
            )
        ),
        'session' => array(),
        'request' => array()
    ),
    'expected' => array (
        1 =>
        array (
          'store' => 'redis-test',
          'definition' => 'core/string',
          'sort' => 1,
        )
    )
);

$definition_no_ruleset = array (
    'definition' => array (
        'core/string' =>
        array (
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
        ),
    ),
    'rules' => array (
        'application' => array (
            array (
                'conditions' => array (
                    'canuselocalstore' => true,
                    'name' => 'core/fakename'
                ),
                'stores' => array(
                    'apcu-test',
                    'file-test'
                )
            ),
            array (
                'conditions' => array (
                    'canuselocalstore' => false,
                    'name' => 'core/fakename'
                ),
                'stores' => array(
                    'apcu-test',
                    'file-test'
                )
            ),
            array (
                'conditions' => array (
                    'canuselocalstore' => false,
                    'name' => 'core/differentfakename'
                ),
                'stores' => array (
                    'redis-test'
                )
            )
        ),
        'session' => array(),
        'request' => array()
    ),
    'expected' => array (
    )
);
