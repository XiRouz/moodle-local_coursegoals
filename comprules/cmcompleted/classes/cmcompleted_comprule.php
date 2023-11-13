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

    /** Handle and encode parameters from form array
     * @param $params
     * @return string
     */
    public static function encodeParams($params)
    {
        return $params['cmid'];
    }

    /** Handle and decode parameters to array
     * @param string $params
     * @return array
     */
    public static function decodeParams($params)
    {
        return ['cmid' => $params];
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
        return self::convertCompletionFromcompData($completiondata);
    }

    public static function convertCompletionFromCompData($completiondata) {
        if ($completiondata->completionstate == COMPLETION_COMPLETE
            || $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
            return self::COMPLETION_DONE;
        } else if ($completiondata->completionstate == COMPLETION_INCOMPLETE) {
            return self::COMPLETION_EMPTY;
        } else if ($completiondata->completionstate == COMPLETION_COMPLETE_FAIL) {
            return self::COMPLETION_WARNING;
        } else {
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

    public static function handleCreate($task)
    {
        return true;
    }

    public static function handleUpdate($task)
    {
        return true;
    }

    public static function handleDelete($task)
    {
        return true;
    }

    /* =========== OWN FUNCTIONS ========= */

    /**
     * @param $cmid
     * @param $active
     * @return Task[]
     */
    public static function getTasksByCmid($cmid, $active = true) {
        global $DB;
        $params = ['cmid' => $cmid];
        $whereconditions = [];
        $whereconditions[] = "cgt.comprule_params = :cmid";
        if ($active) {
            $params['status'] = \local_coursegoals\Goal::STATUS_ACTIVE;
            $whereconditions[] = "cg.status = :status";
        }

        $whereclause = !empty($whereconditions) ? 'WHERE (' . implode(' AND ', $whereconditions) . ')' : "";
        $sql = "
        SELECT cgt.*
        FROM {".\local_coursegoals\Task::TABLE."} cgt
        JOIN {".\local_coursegoals\Goal::TABLE."} cg ON cg.id = cgt.coursegoalid
        $whereclause
        ";
        $instances = $DB->get_records_sql($sql, $params);
        $objects = [];
        foreach ($instances as $instance) {
            $objects[$instance->id] = new Task($instance->id);
        }
        return $objects;
    }

}