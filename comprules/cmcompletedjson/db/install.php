<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_comprules_cmcompletedjson_install()
{
    global $DB;
    $comprule_name = 'cmcompletedjson';

    if (!$DB->get_record('coursegoals_comprule', ['name' => $comprule_name])) {

        $new_comprule = new \stdClass();
        $new_comprule->name = $comprule_name;

        $DB->insert_record('coursegoals_comprule', $new_comprule);
    }

    return true;
}
