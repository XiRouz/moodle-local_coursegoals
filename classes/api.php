<?php

namespace local_coursegoals;

use Exception;

/** API methods for dynamic forms and other actions
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    protected static function resolveRedirectURL($url) {
        if (!empty($url) && $url != "null") { // for whatever reason the default for not specified url is "null"
            $redirecturl = $url;
        } else {
            $redirecturl = false;
        }
        return $redirecturl;
    }

    public static function createGoal($data) {
        $result = false;
        $errors = [];
        try {
            $goal = Goal::create($data);
            $result = !empty($goal);
        } catch (Exception $e) { $errors[] = $e->getMessage(); }

        $redirecturl = self::resolveRedirectURL($data->redirecturl);
        return [$result, $errors, $redirecturl];
    }

    public static function editGoal($data) {
        $result = false;
        $errors = [];
        try {
            $goal = new Goal($data->id);
            $result = $goal->update($data);
        } catch (Exception $e) { $errors[] = $e->getMessage(); }

        $redirecturl = self::resolveRedirectURL($data->redirecturl);
        return [$result, $errors, $redirecturl];
    }

    public static function deleteGoal($data) {
        $result = false;
        $errors = [];
        try {
            $goal = new Goal($data->id);
            $result = $goal->delete();
        } catch (Exception $e) { $errors[] = $e->getMessage(); }

        $redirecturl = self::resolveRedirectURL($data->redirecturl);
        return [$result, $errors, $redirecturl];
    }

    public static function activateGoal($data) {
        $result = false;
        $errors = [];
        try {
            $goal = new Goal($data->id);
            $result = $goal->activate();
        } catch (Exception $e) { $errors[] = $e->getMessage(); }

        $redirecturl = self::resolveRedirectURL($data->redirecturl);
        return [$result, $errors, $redirecturl];
    }

    public static function createTask($data) {
        $result = false;
        $errors = [];
        try {
            $task = Task::create($data);
            $cruleresult = self::handleCompruleCreation($task);
            $result = !empty($task) && $cruleresult;
        } catch (Exception $e) { $errors[] = $e->getMessage(); }

        $redirecturl = self::resolveRedirectURL($data->redirecturl);
        return [$result, $errors, $redirecturl];
    }

    public static function editTask($data) {
        $result = false;
        $errors = [];
        try {
            $task = new Task($data->id);
            $taskresult = $task->update($data);
            $cruleresult = self::handleCompruleUpdate($task);
            $result = $taskresult && $cruleresult;
        } catch (Exception $e) { $errors[] = $e->getMessage(); }

        $redirecturl = self::resolveRedirectURL($data->redirecturl);
        return [$result, $errors, $redirecturl];
    }

    public static function deleteTask($data) {
        $result = false;
        $errors = [];
        try {
            $task = new Task($data->id);
            $cruleresult = self::handleCompruleDeletion($task);
            $taskresult = $task->delete();
            $result = $taskresult && $cruleresult;
        } catch (Exception $e) { $errors[] = $e->getMessage(); }

        $redirecturl = self::resolveRedirectURL($data->redirecturl);
        return [$result, $errors, $redirecturl];
    }

    public static function handleCompruleCreation($task) {
        $comprule = comprule::getCompruleByID($task->compruleid);
        $class = comprule::makeCompruleClassname($comprule);
        $created = false;
        try {
            $crule = new $class();
            $created = $crule::handleCreate($task);
        } catch (Exception $e) {
            debugging($e->getMessage(), DEBUG_DEVELOPER);
            $created = false;
        }
        return $created;
    }

    public static function handleCompruleUpdate($task) {
        $comprule = comprule::getCompruleByID($task->compruleid);
        $class = comprule::makeCompruleClassname($comprule);
        $updated = false;
        try {
            $crule = new $class();
            $updated = $crule::handleUpdate($task);
        } catch (Exception $e) {
            debugging($e->getMessage(), DEBUG_DEVELOPER);
            $updated = false;
        }
        return $updated;
    }

    public static function handleCompruleDeletion($task) {
        $comprule = comprule::getCompruleByID($task->compruleid);
        $class = comprule::makeCompruleClassname($comprule);
        $deleted = false;
        try {
            $crule = new $class();
            $deleted = $crule::handleDelete($task);
        } catch (Exception $e) {
            debugging($e->getMessage(), DEBUG_DEVELOPER);
            $deleted = false;
        }
        return $deleted;
    }
}