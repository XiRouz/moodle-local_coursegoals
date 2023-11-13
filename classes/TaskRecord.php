<?php

namespace local_coursegoals;

use Exception;
use stdClass;

class TaskRecord extends database_object
{
    const TABLE = 'coursegoals_taskrecord';

    const ACTION_CREATE = 'create_taskrecord';
    const ACTION_EDIT = 'edit_taskrecord';
    const ACTION_DELETE = 'delete_taskrecord';

    public int $taskid;
    public int $userid;
    public int $completed;
    public ?int $timemodified;

    /**
     * @param int $id
     * @throws \dml_exception
     */
    public function __construct(int $id)
    {
        parent::__construct($id);

    }

    public static function getTaskRecordByUserID($taskid, $userid) {
        global $DB;
        $instance = $DB->get_record(self::TABLE, ['taskid' => $taskid, 'userid' => $userid]);
        if (!empty($instance)) {
            return new self($instance->id);
        } else {
            return false;
        }
    }

    /**
     * @throws \coding_exception
     */
    public static function create(\stdClass $fields)
    {
        $fields->timemodified = time();

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
            'taskid',
            'userid',
            'completed',
        ];
    }

    /**
     * @return string[]
     */
    protected static function get_update_prohibited_fields():array {
        return [
            'taskid',
            'userid',
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