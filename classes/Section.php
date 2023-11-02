<?php

namespace local_coursegoals;

use Exception;
use stdClass;

class Section extends database_object
{
    const TABLE = 'coursegoals_section';

    const ACTION_CREATE = 'create_section';
    const ACTION_EDIT = 'edit_section';
    const ACTION_DELETE = 'delete_section';

    public int $coursegoalid;
    public string $name;
    public string $displayedname;
    public ?string $description;
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
        // TODO: get tasks with current sections and update them with null value in sectionid
        $result = $this->_delete();

        return $result;
    }

    public function update(\stdClass $fields): bool
    {
        if (!$this->can_be_updated()) {
            return false;
        }
//        if ($this->coursegoalid === 0 && !empty($fields->coursegoalid)) {
//            debugging('Cannot change general section to local', DEBUG_DEVELOPER);
//            return false;
//        }
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
            'name',
            'displayedname',
            'timecreated',
            'usercreated',
        ];
    }

    /**
     * @return string[]
     */
    protected static function get_update_prohibited_fields():array {
        return [
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