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

$defaultsexpected = array(
    array(
        'mode' => cache_store::MODE_APPLICATION,
        'store' => 'default_application',
        'sort' => -1
    ),
    array(
        'mode' => cache_store::MODE_SESSION,
        'store' => 'default_session',
        'sort' => -1
    ),
    array(
        'mode' => cache_store::MODE_REQUEST,
        'store' => 'default_request',
        'sort' => -1
    )
);

$generatedmodemappingagainstdefinitionmatchtoprulesetexpected = array(
    array(
        'mode' => cache_store::MODE_APPLICATION,
        'store' => 'file-test',
        'sort' => -1
    ),
    array(
        'mode' => cache_store::MODE_SESSION,
        'store' => 'default_session',
        'sort' => -1
    ),
    array(
        'mode' => cache_store::MODE_REQUEST,
        'store' => 'default_request',
        'sort' => -1
    )
);
