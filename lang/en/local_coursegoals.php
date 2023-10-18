<?php

defined('MOODLE_INTERNAL') || die();

/**
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course goals';

// General
$string['coursechoice'] = 'The course goal will be tied to chosen course and its items';
$string['displayname'] = 'Course goals';
$string['tasks'] = 'Tasks';

// Actions
$string['create_goal'] = 'Create goal';
$string['edit_goal'] = 'Edit goal';
$string['delete_goal'] = 'Delete goal';
$string['ays_delete_goal'] = '<b>Are you sure you want to delete this course goal?</b>';
$string['activate_goal'] = 'Activate goal';
$string['activate_goal_explained'] = 'Goal activation leads to showing the goal on course page. Any edits to goal or tasks within it are NOT recommended. Proceed with activation?';
$string['create_task'] = 'Create task';
$string['edit_task'] = 'Edit task';
$string['delete_task'] = 'Delete task';
$string['ays_delete_task'] = '<b>Are you sure you want to delete this task?</b>';

// Statuses
$string['status_active'] = 'Active';
$string['status_inactive'] = 'Inactive';
$string['status_stopped'] = 'Stopped';

// Help strings
$string['formatstring_naming_help'] = 'This field supports format plugins';

// Errors
$string['error:choose_course'] = 'Choose course';

// Capabilities
$string['coursegoals:manage_all_goals'] = 'Manage all course goals';
$string['coursegoals:manage_goals_in_course'] = 'Manage goals in course';

