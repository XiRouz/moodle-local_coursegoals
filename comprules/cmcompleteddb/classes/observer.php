<?php

namespace comprules_cmcompleteddb;

class observer
{
    public static function user_graded(\core\event\user_graded $event) {
        \local_coursegoals\Goal::recalculateTaskCompletionsForUser($event->get_grade()->userid, $event->courseid);
        return;
        $gradeitem = $event->get_grade()->grade_item;

        $goals = \local_coursegoals\Goal::getGoalsInCourse($event->courseid);
        foreach ($goals as $goal) {
            $tasks = $goal->getTasks(true);
            foreach ($tasks as $task) {
                $params = cmcompleteddb_comprule::decodeParams($task->comprule_params);
            }
        }
    }
}