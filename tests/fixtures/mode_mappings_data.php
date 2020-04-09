<?php defined('MOODLE_INTERNAL') || die();

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
