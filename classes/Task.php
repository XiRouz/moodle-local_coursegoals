<?php

namespace local_coursegoals;

use Exception;
use stdClass;
use function GuzzleHttp\Promise\task;

class Task extends database_object
{
    const TABLE = 'coursegoals_task';

    const ACTION_CREATE = 'create_task';
    const ACTION_EDIT = 'edit_task';
    const ACTION_DELETE = 'delete_task';

    public int $coursegoalid;
    public int $compruleid;
    public ?int $sectionid;
    public string $name;
    public ?string $description;
    public ?string $comprule_params;
    public int $timecreated;
    public ?int $timemodified;
    public int $usercreated;
    public ?int $usermodified;

    /**
     * @param int $id
     * @throws \dml_exception
     */
    public function __construct(int $id)
    {
        parent::__construct($id);

    }

    public function getTaskRecordForUser($userid = null) {
        if (is_null($userid)) {
            global $USER;
            $userid = $USER->id;
        }

        $taskrecord = \local_coursegoals\TaskRecord::getTaskRecordByUserID($this->id, $userid);
        if (empty($taskrecord)) {
            $completed = $this->calculateCompletionForUser($userid);
            if (is_numeric($completed)) {
                $data = (object)[
                    'taskid' => $this->id,
                    'userid' => $userid,
                    'completed' => $completed,
                ];
                $taskrecord = TaskRecord::create($data);
            }
        }
        return $taskrecord;
    }

    public function updateTaskRecordForUser($userid = null) {
        if (is_null($userid)) {
            global $USER;
            $userid = $USER->id;
        }

        $taskrecord = \local_coursegoals\TaskRecord::getTaskRecordByUserID($this->id, $userid);
        if (!empty($taskrecord)) {
            $completed = $this->calculateCompletionForUser($userid);
            if (is_numeric($completed)) {
                $newdata = (object)[
                    'completed' => $completed,
                ];
                $taskrecord->update($newdata);
            }
        }
    }

    public function calculateCompletionForUser($userid) {
        $goal = new Goal($this->coursegoalid);
        if (! $goal->isActive()) {
            return false;
        }
        $comprule = comprule::getCompruleByID($this->compruleid);
        $class = comprule::makeCompruleClassname($comprule);
        $completed = null;
        try {
            $crule = new $class();
            $completed = $crule->calculateCompletion($userid, $this);
        } catch (Exception $e) {
            debugging($e->getMessage(), DEBUG_DEVELOPER);
        }
        return $completed;
    }

    public static function userCanManageTasks($context) {
        // TODO: maybe make separate capabilities to manage tasks
        return Goal::userCanManageGoals($context);
    }

    public static function find($params) {
        global $DB;
        if (!is_array($params) || empty($params)) {
            return false;
        }

        $id = $DB->get_field(self::get_table_name(), 'id', $params);
        if (!$id) {
            return null;
        }
        return new self($id);
    }

    /**
     * @throws \coding_exception
     */
    public static function create(\stdClass $fields)
    {
        global $USER;
        if (!isset($fields->usercreated)) {
            $fields->usercreated = $USER->id;
        }
        if (!isset($fields->timecreated)) {
            $fields->timecreated = time();
        }

        $instance = parent::_create($fields);
        $fields->id = $instance->id;

        return $instance;
    }

    public function delete(): bool
    {
        $result = $this->_delete();

        return $result;
    }

    public function update(\stdClass $fields): bool
    {
        if (!$this->can_be_updated()) {
            return false;
        }
        global $USER;
        if (!isset($fields->usermodified)) {
            $fields->usermodified = $USER->id;
        }
        if (!isset($fields->timemodified)) {
            $fields->timemodified = time();
        }
        $result = $this->_update($fields);

        return $result;
    }

    /**
     * @return string[]
     */
    protected static function get_create_required_fields():array {
        return [
            'coursegoalid',
            'compruleid',
            'name',
            'timecreated',
            'usercreated',
        ];
    }

    /**
     * @return string[]
     */
    protected static function get_update_prohibited_fields():array {
        return [
            'coursegoalid',
            'timecreated',
            'usercreated',
        ];
    }

    public function can_be_updated() : bool {
        return true;
    }

    /**
     * @return bool
     * @throws \dml_exception
     */
    public function can_be_deleted() : bool
    {
        return true;
    }
}