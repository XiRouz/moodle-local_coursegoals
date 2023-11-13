<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\local_coursegoals\event\taskrecords_updaterequested',
        'callback'  => 'coursegoals_observer::taskrecords_updaterequested',
        'priority'  => 10,
        'internal'  => true
    ],
];