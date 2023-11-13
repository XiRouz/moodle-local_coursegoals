<?php

namespace local_coursegoals;

use Exception;
use stdClass;

class Goal extends database_object
{
    const TABLE = 'coursegoals';

    const ACTION_CREATE = 'create_goal';
    const ACTION_EDIT = 'edit_goal';
    const ACTION_DELETE = 'delete_goal';
    const ACTION_ACTIVATE = 'activate_goal';
    const ACTION_PAUSE = 'pause_goal';

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_PAUSED = -1;

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

    public function activate() {
        if ($this->status == self::STATUS_ACTIVE) {
            return false;
        }
        $data = new stdClass();
        $data->status = self::STATUS_ACTIVE;
        return $this->update($data);
    }

    public function pause() {
        if ($this->status != self::STATUS_ACTIVE) {
            return false;
        }
        $data = new stdClass();
        $data->status = self::STATUS_PAUSED;
        return $this->update($data);
    }

    public function isActive() {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * @param bool $asClassObjects if true, function returns Task objects, else DB instances returned
     * @return Task[]|array tasks of goal as array of instances from DB or Task objects
     * @throws \dml_exception
     */
    public function getTasks($asClassObjects = false)
    {
        global $DB;
        $instances = $DB->get_records_select(Task::TABLE, 'coursegoalid = :coursegoalid', ['coursegoalid' => $this->id]);
        if ($asClassObjects) {
            $objects = [];
            foreach ($instances as $instance) {
                $objects[$instance->id] = new \local_coursegoals\Task($instance->id);
            }
            return $objects;
        } else {
            return $instances;
        }
    }

    public function getTasksGroupedInSections() {
        global $DB;
        $whereconditions = [];
        $params['coursegoalid'] = $this->id;
        $whereconditions[] = "cgt.coursegoalid = :coursegoalid";

        $whereclause = !empty($whereconditions) ? "WHERE (" . implode(" AND ", $whereconditions) . ")" : "";
        $sql = "
            SELECT cgt.id as taskid,
                   cgs.id as sectionid
            FROM {coursegoals_task} cgt
            LEFT JOIN {coursegoals_section} cgs ON cgt.sectionid = cgs.id
            JOIN {coursegoals} cg ON cgt.coursegoalid = cg.id
            $whereclause
            ORDER BY cgs.sortorder ASC
        ";
        $results = $DB->get_records_sql($sql, $params);
        $sections = [];
        $nosectiontasks = [];
        foreach ($results as $result) {
            $section_id = $result->sectionid;
            if ($section_id == null) {
                $nosectiontasks[] = new Task($result->taskid);
                continue;
            }
            if (!isset($sections[$section_id])) {
                $sections[$section_id] = new Section($section_id);
            }
            $sections[$section_id]->sectiontasks[] = new Task($result->taskid);
        }
        // putting tasks without section to end of array
        if (!empty($nosectiontasks)) {
            $sections['withoutsection'] = (object)['id'=> '', 'name' => ' ', 'displayedname' => '&#x200b;', 'description' => ''];
            $sections['withoutsection']->sectiontasks = $nosectiontasks;
        }
        return $sections;
    }

    /**
     * @param null|int $courseid
     * @param null|int|array $goalStatus goal status filter, null converts to STATUS_ACTIVE, if no status filter needed - pass empty array
     * @param bool $returnBool
     * @return Goal[]|bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function getGoals($courseid = null, $goalStatus = null, $returnBool = false) {
        global $DB;
        if (is_null($goalStatus)) {
            $goalStatus = Goal::STATUS_ACTIVE;
        }
        $params = [];
        $whereconditions = [];
        if (!empty($courseid)) {
            $params['courseid'] = $courseid;
            $whereconditions[] = "courseid = :courseid";
        }
        if (is_array($goalStatus)) {
            if (!empty($goalStatus)) {
                list($statusval, $statusparams) = $DB->get_in_or_equal($goalStatus, SQL_PARAMS_NAMED);
                $whereconditions[] = "status $statusval";
                $params = array_merge($params, $statusparams);
            }
        } else if (is_numeric($goalStatus)) {
            $whereconditions[] = "status = :status";
            $params['status'] = $goalStatus;
        }
        $whereclause = !empty($whereconditions) ? "WHERE (" . implode(" AND ", $whereconditions) . ")" : "";
        $sql = "
            SELECT cg.id
            FROM {coursegoals} cg
            $whereclause
            ORDER BY cg.id DESC
        ";
        $results = $DB->get_records_sql($sql, $params);
        if (empty($results)) {
            return false;
        } else {
            if ($returnBool) {
                return true;
            }
            $objects = [];
            foreach ($results as $result) {
                $objects[$result->id] = new self($result->id);
            }
            return $objects;
        }
    }

    public static function recalculateTaskCompletionsForUser($userid, $courseid = null) {
        global $DB;
        if (is_null($courseid)) {
            // TODO: make a function or query for all available (by course) goals OR for all users in course. Recalcs for all goals can be quite long
        } else {
            $goals = self::getGoals($courseid);
            foreach ($goals as $goal) {
                $tasks = $goal->getTasks(true);
                foreach ($tasks as $task) {
                    $task->updateTaskRecordForUser($userid);
                }
            }
        }
    }

    public static function userCanManageGoals($context) {
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            return has_capability('local/coursegoals:manage_all_goals', $context);
        } else if ($context->contextlevel == CONTEXT_COURSE) {
            return has_capability('local/coursegoals:manage_goals_in_course', $context);
        }
        return false;
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