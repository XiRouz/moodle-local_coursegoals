<?php

defined('MOODLE_INTERNAL') || die();

/**
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course goals';

// General
$string['coursegoalid'] = 'Select linked goal';
$string['compruleid'] = 'Select completion rule';
$string['coursechoice'] = 'The course goal will be tied to chosen course and its items';
$string['displayname'] = 'Course goals';
$string['displayedname'] = 'Displayed name';
$string['shared'] = 'Shared';
$string['task'] = 'Task';
$string['tasks'] = 'Tasks';

// Actions
$string['create_goal'] = 'Create goal';
$string['edit_goal'] = 'Edit goal';
$string['delete_goal'] = 'Delete goal';
$string['ays_delete_goal'] = '<b>Are you sure you want to delete this course goal?</b>';
$string['activate_goal'] = 'Activate goal';
$string['activate_goal_explained'] = 'Goal activation leads to showing the goal on course page. Any edits to goal or tasks within it are NOT recommended. Proceed with activation?';
$string['sections_explained'] = 'Sections act as labels for tasks to group them on view pages.';
$string['create_section'] = 'Create section';
$string['edit_section'] = 'Edit section';
$string['delete_section'] = 'Delete section';
$string['ays_delete_section'] = '<b>Are you sure you want to delete this section? This will UNLINK the section from all tasks where it was used!</b>';
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
$string['coursegoalid_help'] = 'Linking sections to goal is optional. If link exists, these sections will only pop up when creating tasks for particular goal.
If the section is "shared", it will appear for selection in any task.';
$string['displayedname_help'] = 'This field supports format plugins. Displayed name will be used for rendering almost anywhere as a short name.';

// Errors
$string['error:choose_course'] = 'Choose course';
$string['error:choose_comprule'] = 'Choose completion rule';

// Capabilities
$string['coursegoals:manage_all_goals'] = 'Manage all course goals';
$string['coursegoals:manage_goals_in_course'] = 'Manage goals in course';
$string['coursegoals:complete_goals'] = 'Complete goals';

