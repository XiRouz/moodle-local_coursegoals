<?php

namespace local_coursegoals\output;

use plugin_renderer_base;
use stdClass;
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
//        $data->parentelement = '#page-header';
        return $this->render_from_template('local_coursegoals/goals_tab', $data);
    }

    private function prepareGoalsTabData() {
        $data = new stdClass();

        $goal1 = new stdClass();
        $goal1->goalname = "GOALNAME1";
        $task1 = (object)['taskname' => "TASKNAME1"];
        $goal1->tasks[] = $task1;
        $task2 = (object)['taskname' => "TASKNAME2"];
        $goal1->tasks[] = $task2;

        $data->goals[] = $goal1;

        $goal2 = new stdClass();
        $goal2->goalname = "GOALNAME2";
        $othertask = (object)['taskname' => "other"];
        $goal2->tasks[] = $othertask;

        $data->goals[] = $goal2;

        return $data;
    }
}
