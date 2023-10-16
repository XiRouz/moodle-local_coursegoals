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
$PAGE->set_pagelayout('course');
//$PAGE->set_pagetype('course-view-' . $course->format); //TODO

$params = ['context' => $context, 'courseid' => $course ?? null];
$table = \local_coursegoals\table\coursegoals_table::create($PAGE, $params);

echo $OUTPUT->header();
echo $OUTPUT->spacer([], true);

$table->render(true);

echo $OUTPUT->footer();
