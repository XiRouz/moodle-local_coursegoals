<?php

namespace comprules_cmcompleted;

class observer
{
    public static function user_graded(\core\event\user_graded $event) {
        $grade = $event->get_grade();
        $gradeitem = $grade->grade_item;
//        if (!$cm = get_coursemodule_from_instance($gradeitem->itemmodule, $gradeitem->iteminstance, $gradeitem->courseid)) {
//
//        }
        $cm = get_fast_modinfo($gradeitem->courseid)->instances[$gradeitem->itemmodule][$gradeitem->iteminstance];
        if (!empty($cm)) {
            $tasks = cmcompleted_comprule::getTasksByCmid($cm->id);
            foreach ($tasks as $task) {
                $task->updateTaskRecordForUser($grade->userid);
            }
        } else {
            // recalculate all tasks in course if we can't point out CM from event   **shrug**
            \local_coursegoals\Goal::recalculateTaskCompletionsForUser($event->get_grade()->userid, $event->courseid);
        }
    }
}