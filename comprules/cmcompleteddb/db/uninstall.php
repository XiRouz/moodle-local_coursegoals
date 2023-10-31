<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_comprules_cmcompleteddb_uninstall()
{
    global $DB;
    $comprule_name = 'cmcompleteddb';

    $DB->delete_records('coursegoals_comprule', ['name' => $comprule_name]);

    return true;
}
