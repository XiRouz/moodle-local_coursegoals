<?php

namespace comprules_cmcompleted;

defined('MOODLE_INTERNAL') || die();

use local_coursegoals\Goal;
use local_coursegoals\Task;

class cmcompleted_comprule extends \local_coursegoals\comprule
{
    const COMPLETION_FAIL = -2;
    const COMPLETION_WARNING = -1;
    const COMPLETION_EMPTY = 0;
    const COMPLETION_DONE = 1;

    public static function getName()
    {
        return 'cmcompleted';
    }

    /** Handle and encode parameters from form array to JSON
     * @param $params
     * @return string
     */
    public static function encodeParams($params)
    {
        return json_encode($params);
    }

    /** Handle and decode parameters from JSON to array
     * @param string $jsonparams
     * @return array
     */
    public static function decodeParams($jsonparams)
    {
        return json_decode($jsonparams, true);
    }

    public static function calculateCompletion($userid, $task)
    {
        global $USER;
        $params = self::decodeParams($task->comprule_params);
        $goal = new Goal($task->coursegoalid);
        $courseid = $goal->courseid;

        $cminfo = \cm_info::create((object)['id' => $params['cmid'], 'course' => $courseid]);
        $completion = new \completion_info($cminfo->get_course());
        $completiondata = $completion->get_data($cminfo, false, $userid);


        if ($completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
            return self::COMPLETION_DONE;
        } else if ($completiondata->completionstate == COMPLETION_INCOMPLETE) {
            return self::COMPLETION_EMPTY;
        } else if ($completiondata->completionstate == COMPLETION_COMPLETE_FAIL) {
            return self::COMPLETION_WARNING;
        } else {
//            $gradesinfo = grade_get_grades($courseid, 'mod', $cminfo->modname, $cminfo->instance, $USER->id);
//            if (!empty($gradesinfo) && !empty($gradesinfo->items)) {
//                foreach ($gradesinfo->items as $item) {
//                    if ($item->scaleid !== null) { // scaleid is null for grade items of GRADE_TYPE_NONE
//                        if (!empty($item->gradepass)) {
//                            $data->gradepass = round($item->gradepass, 2);
//                        }
//                        if (!empty($item->grades)) {
//                            $grade = end($item->grades);
//                            $data->yourgrade = round($grade->grade, 2);
//                        } else {
//                            $data->yourgrade = '-';
//                        }
//                    }
//                }
//            }
            return self::COMPLETION_EMPTY;
        }
    }

    /** Get HTML that explains what needs to be done to finish the task with this completion rule
     * @param Task|null $task
     * @return string
     */
    public static function getCompletionConditions($task = null)
    {
        if (!empty($task)) {
            $params = self::decodeParams($task->comprule_params);
            $goal = new Goal($task->coursegoalid);
            $courseid = $goal->courseid;
            $cminfo = \cm_info::create((object)['id' => $params['cmid'], 'course' => $courseid]);
            $link = \html_writer::link($cminfo->get_url(), format_string($cminfo->name));
            return get_string('completioncondition', 'comprules_cmcompleted', $link);
        }
        return '';
    }
}