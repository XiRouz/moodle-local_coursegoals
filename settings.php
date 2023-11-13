<?php

/**
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \local_coursegoals\helper;

global $DB, $USER;

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_coursegoals', get_string('pluginname', 'local_coursegoals'));

    $url = new moodle_url('/local/coursegoals/index.php');
    $settings->add(new admin_setting_configempty('local_coursegoals/index_page',
        get_string('config:index_page', 'local_coursegoals'), html_writer::link($url, $url->out())));

    $settings->add(new admin_setting_configcheckbox('local_coursegoals/enable_viewtab',
        get_string('config:enable_viewtab', 'local_coursegoals'),
        get_string('config:enable_viewtab_descr', 'local_coursegoals'), 1));

    $headerOptions = [
        helper::COURSE_PAGE_HEADER_400 => get_string('course_header', 'local_coursegoals'),
        helper::COURSE_CONTENT_START_400 => get_string('after_course_navigation', 'local_coursegoals'),
    ];
    // TODO: get default depending on moodle version
    $default = helper::COURSE_PAGE_HEADER_400;
    $settings->add(new admin_setting_configselect('local_coursegoals/tab_render_header',
        get_string('config:tab_render_header', 'local_coursegoals'),
        get_string('config:tab_render_header_descr', 'local_coursegoals'), $default, $headerOptions));

    $ADMIN->add('localplugins', $settings);

}
