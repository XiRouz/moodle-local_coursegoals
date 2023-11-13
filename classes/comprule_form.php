<?php

namespace local_coursegoals;

defined('MOODLE_INTERNAL') || die();

abstract class comprule_form
{
    /** Get necessary form elements for completion rule as an array
     * @param $form
     * @return array
     */
    abstract public static function getFormElements(&$form);

    /** Validate parameters of form elements for this form
     * @param $params
     * @return array
     */
    abstract public static function validateParams($params);

    public static function makeCompruleFormClassname($compruleInstance) {
        return "\\comprules_{$compruleInstance->name}\\{$compruleInstance->name}_form";
    }
}