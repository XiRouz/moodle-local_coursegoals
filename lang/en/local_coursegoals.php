<?php

defined('MOODLE_INTERNAL') || die();

/**
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course goals';

// General
$string['compruleid'] = 'Select completion rule';
$string['coursechoice'] = 'The course goal will be tied to chosen course and its items';
$string['crule_params'] = 'CRule parameters';
$string['displayname'] = 'Course goals';
$string['displayedname'] = 'Displayed name';
$string['hide_tasks_info'] = 'Hide tasks info';
$string['shared'] = 'Shared';
$string['section'] = 'Section';
$string['section_coursegoalid'] = 'Linked goal';
$string['section_coursegoalid_select'] = 'Select linked goal';
$string['select_sectionid'] = 'Section';
$string['sections'] = 'Sections';
$string['sortorder'] = 'Sort order';
$string['show_tasks_info'] = 'Show tasks info';
$string['task'] = 'Task';
$string['tasks'] = 'Tasks';
$string['withoutsection'] = 'Without section';

// Actions
$string['create_goal'] = 'Create goal';
$string['edit_goal'] = 'Edit goal';
$string['delete_goal'] = 'Delete goal';
$string['ays_delete_goal'] = '<b>Are you sure you want to delete this course goal?</b>';
$string['activate_goal'] = 'Activate goal';
$string['activate_goal_explained'] = 'Goal activation leads to showing the goal on course page. Any edits to goal or tasks within it are NOT recommended. Proceed with activation?';
$string['pause_goal'] = 'Pause goal';
$string['pause_goal_explained'] = 'Pausing goal means that task records for users won\'t be updated if some task requires completion calculation. Are you sure?';
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
$string['status_paused'] = 'Paused';

// Help strings
$string['displayedname_help'] = 'This field supports format plugins. Displayed name will be used for rendering almost anywhere as a short name.';
$string['formatstring_naming_help'] = 'This field supports format plugins';
$string['section_coursegoalid_select_help'] = 'Linking sections to goal is optional. If link exists, these sections will only pop up when creating tasks for particular goal.
If the section is "shared", it will appear for selection in any task.';
$string['sections_explained_help'] = 'Sections act as labels for tasks to group them on view pages.';
$string['sortorder_help'] = 'Sortorder is a number for ordering sections. The smaller the number - the sooner the section will render.';

// Errors
$string['error:choose_course'] = 'Choose course';
$string['error:choose_comprule'] = 'Choose completion rule';

// Config
$string['config:enable_viewtab'] = 'Enable view tab';
$string['config:enable_viewtab_descr'] = 'Enables a tab on course page that displays goals to users.';
$string['config:tab_render_header'] = 'Render header';
$string['config:tab_render_header_descr'] = 'Choose an option after which header goals tab will be rendered.';
$string['config:index_page'] = 'Course goals index page';

// Capabilities
$string['coursegoals:manage_all_goals'] = 'Manage all course goals';
$string['coursegoals:manage_goals_in_course'] = 'Manage goals in course';
$string['coursegoals:complete_goals'] = 'Complete goals';

// Other
$string['course_header'] = 'Course header';
$string['after_course_navigation'] = 'After course navigation (content start)';

