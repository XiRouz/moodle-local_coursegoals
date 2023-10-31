<?php

namespace comprules_cmcompleted;

use local_coursegoals\form\goal_form;
use local_coursegoals\Goal;
use local_coursegoals\Task;

require_once($CFG->dirroot.'/grade/querylib.php');
require_once($CFG->dirroot.'/lib/grade/constants.php');

defined('MOODLE_INTERNAL') || die();

class cmcompleted_form extends \local_coursegoals\comprule_form
{
    /** Get necessary form elements for completion rule as an array
     * @param $mform
     * @return array
     */
    public static function getFormElementsGroup(&$mform)
    {
        $group = [];
        $goalid = $mform->optional_param('coursegoalid', null, PARAM_INT);
        if (!empty($goalid)) {
            $goal = new Goal($goalid);
        } else {
            $taskid = $mform->optional_param('taskid', null, PARAM_INT);
            if (!empty($taskid)) {
                $task = new Task($taskid);
                $goal = new Goal($task->coursegoalid);
            }
        }
        // maybe taskid from dataid will be helpful to some degree?

        $cmOptions = [0 => get_string('choosedots')] + self::getCMs($goal->courseid);

        $group[] = $mform->createElement('select', 'cmid',
            get_string('cmid', 'comprules_cmcompleted'), $cmOptions);
        // add help button?
        return $group;
    }

    /** Validate parameters of form elements for this form
     * @param $params
     * @return array
     */
    public static function validateParams($params)
    {
        $errors = [];
        if (empty($params['cmid'])) {
            $errors['cmid'] = get_string('error:choose_cm', 'comprules_cmcompleted');
        }
        return $errors;
    }

    public static function getCMs($courseid) {
        $cmOptions = [];
        $cms = \grade_get_gradable_activities($courseid);
        if (!empty($cms)) {
            foreach ($cms as $id => $cm) {
                $cmOptions[$id] = format_string($cm->name);
            }
        }
        asort($cmOptions);
        return $cmOptions;
    }
}