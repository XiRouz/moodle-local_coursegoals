<?php

namespace local_coursegoals;

class coursegoals_observer
{
    public static function taskrecords_updaterequested($event) {
        // get task and user from event data
        // $task->updateTaskRecordForUser($userid);

        // maybe foreach in array of users?
        // \local_coursegoals\Goal::recalculateTaskCompletionsForUser();
        $test = 1;
    }
}