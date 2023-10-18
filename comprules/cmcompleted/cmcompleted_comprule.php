<?php

namespace local_coursegoals\comprules;

defined('MOODLE_INTERNAL') || die();

class cmcompleted_comprule extends comprule
{

    public static function getName()
    {
        return 'cmcompleted';
    }

    public static function calculateCompletion($userid, $params)
    {
        // TODO: Implement calculateCompletion() method.
    }
}