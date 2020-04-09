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

$definitionmatchtopruleset = array (
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

$definitionnonmatchtopruleset = array (
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

$definitionbottomruleset = array (
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

$definitionnoruleset = array (
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
