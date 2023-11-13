<?php


require_once('../../config.php');

require_login();

$context = context_system::instance();
if (!has_capability('local/coursegoals:manage_all_goals', $context)) {
    $course = required_param('course', PARAM_INT);
    $context = context_course::instance($course);
    require_capability('local/coursegoals:manage_goals_in_course', $context);
}

global $PAGE, $FULLME, $OUTPUT;

$PAGE->set_context($context);
$PAGE->set_title(get_string('displayname', 'local_coursegoals'));
$PAGE->set_heading(get_string('displayname', 'local_coursegoals'));
$PAGE->set_url(new moodle_url($FULLME));

$params = ['context' => $context, 'courseid' => $course ?? null];
$table = \local_coursegoals\table\coursegoals_table::create($PAGE, $params);

echo $OUTPUT->header();
echo $OUTPUT->spacer([], true);

// TODO: create and add navigation nodes?
$sections_url = new moodle_url('/local/coursegoals/sections.php');
echo html_writer::link($sections_url, get_string('sections', 'local_coursegoals'), ['class' => 'm-1 btn btn-secondary']);
echo '<br/><br/>';

$table->render(true);

echo $OUTPUT->footer();
