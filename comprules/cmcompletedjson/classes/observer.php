<?php

namespace comprules_cmcompletedjson;

class observer
{
    public static function user_graded(\core\event\user_graded $event) {
        \local_coursegoals\Goal::recalculateTaskCompletionsForUser($event->get_grade()->userid, $event->courseid);
        return;
        $grade = $event->get_grade();
        $gradeitem = $grade->grade_item;

        $goals = \local_coursegoals\Goal::getGoalsInCourse($event->courseid);
        foreach ($goals as $goal) {
            $tasks = $goal->getTasks(true);
            foreach ($tasks as $task) {
                $params = cmcompleted_comprulejson::decodeParams($task->comprule_params);
            }
        }
    }
}