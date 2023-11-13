<?php

namespace comprules_cmcompleted;

class observer
{
    public static function user_graded(\core\event\user_graded $event) {
        // old func, this observer should be deleted
        return;
        $grade = $event->get_grade();
        $gradeitem = $grade->grade_item;
        $modinfo = get_fast_modinfo($gradeitem->courseid);
        if (!empty($modinfo) && $gradeitem->itemmodule != null) {
            $cm = $modinfo->instances[$gradeitem->itemmodule][$gradeitem->iteminstance];
            if (!empty($cm)) {
                $tasks = cmcompleted_comprule::getTasksByCmid($cm->id);
                foreach ($tasks as $task) {
                    $task->updateTaskRecordForUser($grade->userid);
                }
            }
        } else {
            // recalculate all tasks in course if we can't point out CM from event   **shrug**
            \local_coursegoals\Goal::recalculateTaskCompletionsForUser($event->get_grade()->userid, $event->courseid);
        }
    }

    public static function course_module_completion_updated(\core\event\course_module_completion_updated $event) {
        $courseid = $event->courseid;
        $cmid = $event->contextinstanceid;
        $userid = $event->relateduserid;

        if (!empty($userid)) {
            if (!empty($cmid)) {
                $tasks = cmcompleted_comprule::getTasksByCmid($cmid);
                foreach ($tasks as $task) {
                    $task->updateTaskRecordForUser($userid);
                }
            } else if (!empty($courseid)) {
                // recalculate all tasks in course if we can't point out CM from event?   **shrug**
//                \local_coursegoals\Goal::recalculateTaskCompletionsForUser($userid, $courseid);
            }
        }
    }
}