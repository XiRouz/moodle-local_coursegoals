<?php

namespace local_coursegoals;

defined('MOODLE_INTERNAL') || die();

abstract class comprule
{
    /** Constant that defines if completion rule subplugin is active */
    const STATUS_ACTIVE = 1;

    /** Constant that defines if completion rule subplugin is inactive */
    const STATUS_INACTIVE = 0;

    /** Name getter
     * @return string
     */
    abstract public static function getName();

    /** Get HTML that explains what needs to be done to finish the task with this completion rule
     * @param Task|null $task
     * @return string
     */
    abstract public static function getCompletionConditions($task = null);

    /** Handle and encode parameters from form array
     * @param $params
     * @return string
     */
    abstract public static function encodeParams($params);

    /** Handle and decode parameters to array
     * @param string $params
     * @return array
     */
    abstract public static function decodeParams($params);

    /** Calculates completion of task
     * @param $userid
     * @param $task
     * @return int
     */
    abstract public static function calculateCompletion($userid, $task);

    /** Optional code that handles creation of task with some completion rule
     * @param Task $task
     * @return bool
     */
    abstract public static function handleCreate($task);

    /** Optional code that handles updating of task with some completion rule
     * @param Task $task
     * @return bool
     */
    abstract public static function handleUpdate($task);

    /** Optional code that handles deletion of task with some completion rule
     * @param Task $task
     * @return bool
     */
    abstract public static function handleDelete($task);

    /* ======================================= */

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

    /** Gets a comprule instance from DB by name
     */
    public static function getCompruleByName($comprulename) {
        global $DB;
        return $DB->get_record('coursegoals_comprule', ['name' => $comprulename], 'id, name');
    }

    public static function makeCompruleClassname($compruleInstance) {
        return "\\comprules_{$compruleInstance->name}\\{$compruleInstance->name}_comprule";
    }

}