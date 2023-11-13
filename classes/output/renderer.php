<?php

namespace local_coursegoals\output;

use plugin_renderer_base;
use stdClass;
use Exception;
use local_coursegoals\Goal;
use local_coursegoals\Section;
use local_coursegoals\comprule;
use local_coursegoals\helper;

/**
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class renderer extends plugin_renderer_base {

    public function renderGoalsTab() {
        $data = self::prepareGoalsTabData();
        if (empty($data)) {
            return '';
        }
        return $this->render_from_template('local_coursegoals/goals_tab', $data);
    }

    private function prepareGoalsTabData() {
        global $COURSE, $USER, $PAGE, $OUTPUT;
        $data = new stdClass();

        $goals = Goal::getGoals($COURSE->id);
        
        foreach ($goals as $goal) {
            $goalrow = (object)[
                'goalname' => $goal->get_name(),
                'goaldescription' => format_string($goal->description),
            ];

            $sections = $goal->getTasksGroupedInSections();
            if (count($sections) == 1 && isset($sections['withoutsection'])) {
                $tasks = $sections['withoutsection']->sectiontasks;
                foreach ($tasks as $task) {
                    $taskrow = $this->prepareTaskRow($task);
                    $goalrow->tasks[] = $taskrow;
                }
            } else {
                foreach ($sections as $section) {
                    $displayedname = $section instanceof Section ? $section->get_displayedname() : $section->displayedname;
                    $sectionrow = (object)[
                        'sectionrefid' => $section->id,
                        'displayedname' => $displayedname,
                    ];
                    foreach ($section->sectiontasks as $task) {
                        $taskrow = $this->prepareTaskRow($task);
                        $sectionrow->sectiontasks[] = $taskrow;
                    }
                    $goalrow->sections[] = $sectionrow;
                }
            }
            $data->goals[] = $goalrow;
        }

        return $data;
    }

    public function renderTaskDetails($task) {
        $data = new stdClass();
        $data->taskname = $task->get_name();
        if (!empty($task->description)) {
            $data->taskdescription = format_string($task->description);
        }
        return $this->render_from_template('local_coursegoals/task_details', $data);
    }

    public function renderCompletion($task, $taskrecord) {
        global $OUTPUT;
        // $checkmark = '&#9989;';
        // $redcross = '&#10060;';
        $alt = $task->get_name();
        $pix_id = $taskrecord->completed ? 'cb-complete' : 'cb-empty';
        return $OUTPUT->pix_icon($pix_id, $alt, 'local_coursegoals', ['class' => 'mr-0']);
    }

    public function makeCompruleTaskrendererClassname($compruleInstance) {
        return "\\comprules_{$compruleInstance->name}\\output\\renderer";
    }

    private function prepareTaskRow ($task) {
        global $PAGE, $USER;
        $taskrow = (object)['taskrefid' => $task->id];
        $taskDetailsOutput = '';
        $comprule = comprule::getCompruleByID($task->compruleid);
        $cruleRenderer = $PAGE->get_renderer("comprules_{$comprule->name}");
        if (empty($cruleRenderer)) {
            $cruleRenderer = $PAGE->get_renderer("local_coursegoals");
        }

        $taskrecord = $task->getTaskRecordForUser($USER->id);
        if (!empty($taskrecord)) {
            $taskrow->taskcompletedoutput = $cruleRenderer->renderCompletion($task, $taskrecord);
        }
        if (plugin_supports('comprules', $comprule->name, helper::FEATURE_CUSTOMTASKDETAILS)) {
            try {
                $taskDetailsOutput = $cruleRenderer->renderTaskDetails($task);
            } catch (Exception $e) {
                debugging($e->getMessage(), DEBUG_DEVELOPER);
                $taskDetailsOutput = '';
            }
        }
        if (!empty($taskDetailsOutput)) {
            $taskrow->taskdetailshtml = $taskDetailsOutput;
        } else {
            $taskrow->taskdetailshtml = $this->renderTaskDetails($task);
        }

        return $taskrow;
    }
}
