<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\user_graded',
        'callback'  => '\comprules_cmcompleteddb\observer::user_graded',
        'priority'  => 1,
        'internal'  => true
    ],
];
