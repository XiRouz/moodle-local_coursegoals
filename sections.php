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

//$params = ['context' => $context, 'courseid' => $course ?? null];
$table = \local_coursegoals\table\sections_table::create($PAGE, []);

echo $OUTPUT->header();
echo $OUTPUT->spacer([], true);

$goals_url = new moodle_url('/local/coursegoals/index.php');
echo html_writer::link($goals_url, get_string('displayname', 'local_coursegoals'), ['class' => 'm-1 btn btn-secondary']);
echo '<br/><br/>';

$table->render(true);

echo $OUTPUT->footer();
