<?php

namespace comprules_cmcompleted\output;

defined('MOODLE_INTERNAL') || die();

use comprules_cmcompleted\cmcompleted_comprule;
use stdClass;
use \local_coursegoals\Goal;
use \local_coursegoals\Task;

class renderer extends \local_coursegoals\output\renderer
{
    public function renderTaskDetails($task)
    {
        global $USER;
        $data = new stdClass();
        $data->taskname = $task->get_name();
        if (!empty($task->description)) {
            $data->taskdescription = format_string($task->description);
        }
        $params = cmcompleted_comprule::decodeParams($task->comprule_params);
        $goal = new Goal($task->coursegoalid);
        $courseid = $goal->courseid;
        $cminfo = \cm_info::create((object)['id' => $params['cmid'], 'course' => $courseid]);
        if ($cminfo) {
            $data->cmlink = $cminfo->get_url();
            $data->cmname = format_string($cminfo->name);
            $gradesinfo = grade_get_grades($courseid, 'mod', $cminfo->modname, $cminfo->instance, $USER->id);
            if (!empty($gradesinfo) && !empty($gradesinfo->items)) {
                foreach ($gradesinfo->items as $item) {
                    if ($item->scaleid !== null) { // scaleid is null for grade items of GRADE_TYPE_NONE
                        if (!empty($item->gradepass)) { // TODO: setting in task whether to show gradepass or not
                            $data->gradepass = round($item->gradepass, 2);
                        }
                        if (!empty($item->grades)) {
                            $grade = end($item->grades);
                            $data->yourgrade = round($grade->grade, 2);
                        }
                        if (empty($data->yourgrade)) {
                            $data->yourgrade = get_string('notapplicable', 'comprules_cmcompleted');
                        }
                    }
                }
            }
            if (!isset($data->yourgrade)) {
                $completion = new \completion_info($cminfo->get_course());
                $completiondata = $completion->get_data($cminfo, false, $USER->id);
                $completionResult = cmcompleted_comprule::convertCompletionFromCompData($completiondata);
                $checkmark = '&#9989;';
                $redcross = '&#10060;';
                $data->completed = $completionResult > 0 ? $checkmark : $redcross;
            }
            return $this->render_from_template('comprules_cmcompleted/task_details', $data);
        } else {
            return parent::renderTaskDetails($task);
        }
    }

    public function renderCompletion($task, $taskrecord) {
        global $OUTPUT;
        $alt = $task->get_name();
        $pluginname = 'local_coursegoals';
        switch ($taskrecord->completed) {
            case cmcompleted_comprule::COMPLETION_EMPTY:
                $pix_id = 'cb-empty';
                break;
            case cmcompleted_comprule::COMPLETION_DONE:
                $pix_id = 'cb-complete';
                break;
            case cmcompleted_comprule::COMPLETION_WARNING:
                $pix_id = 'cb-warning';
                break;
            case cmcompleted_comprule::COMPLETION_FAIL:
                $pix_id = 'cb-fail';
                break;
        }
        return $OUTPUT->pix_icon($pix_id, $alt, $pluginname, ['class' => 'mr-0']);
    }
}