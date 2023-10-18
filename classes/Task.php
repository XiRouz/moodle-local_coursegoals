<?php

namespace local_coursegoals;

use Exception;
use stdClass;

class Task extends database_object
{
    const TABLE = 'coursegoals_task';

    const ACTION_CREATE = 'create_task';
    const ACTION_EDIT = 'edit_task';
    const ACTION_DELETE = 'delete_task';

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_STOPPED = -1;

    public int $coursegoalid;
    public int $compruleid;
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

    public function calculateCompletionForUser($userid) {
        // get comprule
        // calc it inside comprule class
        // update or insert result in taskrecord
    }

    public static function userCanManageTasks($context) {
        // TODO: maybe make separate capabilities to manage tasks
        return Goal::userCanManageGoals($context);
    }

    /**
     * @throws \coding_exception
     */
    public static function create(\stdClass $fields)
    {
        $fields->status = self::STATUS_INACTIVE;

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