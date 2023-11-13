<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_comprules_cmcompleted_uninstall()
{
    global $DB;
    $comprule_name = 'cmcompleted';

    $DB->delete_records('coursegoals_comprule', ['name' => $comprule_name]);

    return true;
}
