<?php

/**
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Upgrade code for the coursegoals local plugin.
 *
 * @param int $oldversion - the version we are upgrading from.
 * @return bool result
 */
function xmldb_local_coursegoals_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    $newversion = 2023110700;
    if ($oldversion < $newversion) {
        $table = new xmldb_table('coursegoals_section');
        $field = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, $newversion, 'local', 'coursegoals');
    }


    return true;
}