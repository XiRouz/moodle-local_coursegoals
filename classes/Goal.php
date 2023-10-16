<?php

namespace local_coursegoals;

use Exception;

class Goal extends database_object
{
    const TABLE = 'coursegoals';

    const ACTION_CREATE = 'create_goal';
    const ACTION_EDIT = 'edit_goal';
    const ACTION_DELETE = 'delete_goal';

    const STATUS_INITIAL = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_STOPPED = -1;

    public int $courseid;
    public int $status;
    public string $name;
    public ?string $description;
    public ?string $availability;
    public ?string $onfinish;
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

    public static function userCanManageGoals($context) {
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            return has_capability('local/coursegoals:manage_all_goals', $context);
        } else if ($context->contextlevel == CONTEXT_COURSE) {
            return has_capability('local/coursegoals:manage_goals_in_course', $context);
        }
        return false;
    }

    public static function getTasksByGoalID($goalid) {
        global $DB;
        return $DB->get_records_select('coursegoals_task', 'coursegoalid = :coursegoalid', ['coursegoalid' => $goalid]); // TODO: to Task::TABLE
    }

    /**
     * @throws \coding_exception
     */
    public static function create(\stdClass $fields)
    {
        $fields->status = self::STATUS_INITIAL;

        global $USER;
        if (!isset($fields->usercreated)) {
            $fields->usercreated = $USER;
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
            $fields->usermodified = $USER;
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
            'courseid',
            'status',
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
            'courseid',
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