<?php

namespace local_coursegoals;

defined('MOODLE_INTERNAL') || die();

abstract class comprule
{
    /** Name getter
     * @return string
     */
    abstract public static function getName();

    /** Get HTML that explains what needs to be done to finish the task with this completion rule
     * @param Task|null $task
     * @return string
     */
    abstract public static function getCompletionConditions($task = null);

    /** Handle and encode parameters from form array to JSON
     * @param $params
     * @return string
     */
    abstract public static function encodeParams($params);

    /** Handle and decode parameters from JSON to array
     * @param string $jsonparams
     * @return array
     */
    abstract public static function decodeParams($jsonparams);

    /** Calculates completion of task
     * @param $userid
     * @param $task
     * @return int
     */
    abstract public static function calculateCompletion($userid, $task);

    /** Gets all available comprule ids and names from DB table
     * @return array
     */
    public static function getComprules() {
        global $DB;
        return $DB->get_records('coursegoals_comprule', null, '', 'id, name');
    }

    /** Gets a comprule instance from DB by ID
     */
    public static function getCompruleByID($compruleid) {
        global $DB;
        return $DB->get_record('coursegoals_comprule', ['id' => $compruleid], 'id, name');
    }

    public static function makeCompruleClassname($compruleInstance) {
        return "\\comprules_{$compruleInstance->name}\\{$compruleInstance->name}_comprule";
    }

}