<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\user_graded',
        'callback'  => '\comprules_cmcompleted\observer::user_graded',
        'priority'  => 1,
        'internal'  => true
    ],
];
