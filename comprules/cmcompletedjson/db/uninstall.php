<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_comprules_cmcompletedjson_uninstall()
{
    global $DB;
    $comprule_name = 'cmcompletedjson';

    $DB->delete_records('coursegoals_comprule', ['name' => $comprule_name]);

    return true;
}
