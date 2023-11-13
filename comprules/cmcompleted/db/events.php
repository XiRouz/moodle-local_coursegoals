<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\user_graded',
        'callback'  => '\comprules_cmcompleted\observer::user_graded',
        'priority'  => 1,
        'internal'  => true
    ],
    [
        'eventname'   => '\core\event\course_module_completion_updated',
        'callback'    => '\comprules_cmcompleted\observer::course_module_completion_updated',
        'priority'  => 1,
        'internal'  => true
    ],
];
