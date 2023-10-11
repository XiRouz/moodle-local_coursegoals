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
        $data->parentelement = '#page-header';
        return $this->render_from_template('local_coursegoals/goals_tab', $data);
    }

    private function prepareGoalsTabData() {


        $data = new stdClass();

        return $data;
    }
}
