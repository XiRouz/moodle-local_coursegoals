<?php

/**
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \local_coursegoals\helper;
use \local_coursegoals\Goal;

function local_coursegoals_before_footer() {

    $html = '';
    try {
        // check if on allowed page
        if (! helper::isOnAllowedPage())
            return '';

        // check if any goals for this course exist
        global $COURSE;
        if (! Goal::getGoalsInCourse($COURSE->id, Goal::STATUS_ACTIVE, true))
            return '';

        // todo: check for available goals (availability API)

        // if passed all checks, render or create\calculate goal task items for user
        global $PAGE;

        if (isloggedin() && !isguestuser()) {
            $output = $PAGE->get_renderer('local_coursegoals');
            $html .= $output->renderGoalsTab();
            $PAGE->requires->js_call_amd('local_coursegoals/coursegoals',
                'initCourseGoalsTab',
                ['#page-header']);
        }

    } catch (Exception $ex) {
        $html = '';
        debugging($ex->getMessage(), DEBUG_DEVELOPER);
    }
    return $html;
}