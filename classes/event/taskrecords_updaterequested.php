<?php

namespace local_coursegoals\event;

defined('MOODLE_INTERNAL') || die();


class taskrecords_updaterequested extends \core\event\base {

    protected function init()
    {
        $this->data['objecttable'] = 'coursegoals_taskrecord';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
}

