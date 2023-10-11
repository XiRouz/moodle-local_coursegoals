<?php


require_once('../../config.php');

require_login();

$courseid = optional_param('courseid', null, PARAM_INT);
if ($courseid) {
    require_capability('local/coursegoals:manage_coursegoals', context_course::instance($courseid));
} else {
    require_capability('local/coursegoals:manage_coursegoals', context_system::instance());
}

//global $PAGE, $FULLME, $OUTPUT;
//
//$PAGE->set_title(get_string('theme_planning', 'local_sic'));
//$PAGE->set_heading(get_string('theme_planning', 'local_sic'));
//$PAGE->set_url(new moodle_url($FULLME));
//$PAGE->set_pagelayout('admin');
//
//$context = \context_system::instance();
//$table = \local_sic\table\themeplans_table::create($PAGE, ['context' => $context]);
//
//echo $OUTPUT->header();
//echo $OUTPUT->spacer([], true);
//
//$table->render(true);
//
//echo $OUTPUT->footer();
