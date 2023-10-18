<?php

namespace local_coursegoals\comprules;

defined('MOODLE_INTERNAL') || die();

abstract class comprule
{
    abstract public static function getName();

    abstract public static function calculateCompletion($userid, $params);
}