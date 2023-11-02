<?php

namespace local_coursegoals\table;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->libdir . '/tablelib.php');

use local_coursegoals\comprule;
use Exception;
use table_sql;
use moodle_url;
use html_writer;
use local_coursegoals\Goal;
use local_coursegoals\Task;
use local_coursegoals\Section;

class coursegoals_table extends table_sql
{
    /** @var string Table ID */
    const UNIQUEID = 'coursegoals-table';

    /** @var $PAGE object where the table is rendering */
    protected object $page;

    protected $context;
    protected $courseid = null;

    /** Constructor
     * @param $page
     * @param mixed $params Acceptable parameters: context(required), courseid
     * @throws \coding_exception
     */
    public function __construct($page, $params = null) {
        parent::__construct(self::UNIQUEID);
        $this->page = $page;

        if (empty($params)) {
            throw new \coding_exception("Required parameters missing");
        } else {
            if (empty($params['context'])) {
                throw new \coding_exception("Missing context");
            } else {
                $this->context = $params['context'];
                unset($params['context']);
            }
            if (!empty($params['courseid'])) {
                $this->courseid = $params['courseid'];
            }
//            foreach ($params as $key => $param) {
//                $this->{$key} = $param;
//            }
        }

        $columnheaders = [
            'name' => get_string('name'),
            'coursename' => get_string('course'),
            'status' => get_string('status'),
            'tasks' => get_string('tasks', 'local_coursegoals'),
            'actions' => get_string('actions'),
        ];

        $this->define_columns(array_keys($columnheaders));
        $this->define_headers(array_values($columnheaders));
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(false);
        $this->is_downloadable(false);
    }

    public function col_name($data) {
        return format_string($data->name);
    }
    public function col_coursename($data) {
        return format_string($data->coursename);
    }

    public function col_status($data) {
        switch ($data->status) {
            case Goal::STATUS_INACTIVE:
                return get_string('status_inactive', 'local_coursegoals');
                break;
            case Goal::STATUS_ACTIVE:
                return get_string('status_active', 'local_coursegoals');
                break;
            case Goal::STATUS_STOPPED:
                return get_string('status_stopped', 'local_coursegoals');
                break;
        }
    }

    public function col_actions($data) {
        global $OUTPUT;
        $icons = [];


        if ($data->goal->status != Goal::STATUS_ACTIVE && count($data->goal->getTasks()) > 0) { // TODO: think of activation and deactivation of goals once again
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/play', get_string(Goal::ACTION_ACTIVATE, 'local_coursegoals'),'core'),
                null, [
                    'data-action' => Goal::ACTION_ACTIVATE,
                    'data-id' => $data->id,
                    'data-title' => get_string(Goal::ACTION_ACTIVATE, 'local_coursegoals')
                ]);
        }

        if (Goal::userCanManageGoals($this->context) && $data->goal->can_be_updated()) {
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/edit', get_string('edit'),'core'),
                null, [
                    'data-action' => Goal::ACTION_EDIT,
                    'data-id' => $data->id,
                    'data-title' => get_string(Goal::ACTION_EDIT, 'local_coursegoals')
                ]);
        }

        if (Goal::userCanManageGoals($this->context) && $data->goal->can_be_deleted()) {
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/delete', get_string('delete'),'core'),
                null, [
                    'data-action' => Goal::ACTION_DELETE,
                    'data-id' => $data->id,
                    'data-title' => get_string(Goal::ACTION_DELETE, 'local_coursegoals')
                ]);
        }

        $this->setupGoalActionsModals($data->id);

        return \html_writer::span(implode('', $icons),'nowrap');
    }

    public function col_tasks($data) {
        $html = '';
        $tasks = $data->goal->getTasks(true);
        foreach ($tasks as $task) {
            $crule = null;
            $comprule = comprule::getCompruleByID($task->compruleid);
            $class = comprule::makeCompruleClassname($comprule);
            try {
                $crule = new $class();
            } catch (Exception $e) {
                debugging($e->getMessage(), DEBUG_DEVELOPER);
            }
            $section = null;
            if (!empty($task->sectionid)) {
                $section = new Section($task->sectionid);
            }
            $implodearr = [];
            $implodearr[] = '<b>'.$task->get_name().'</b>';
            $implodearr[] = $this->getTaskActions($task);
            $label = implode('&ensp;', $implodearr);

            $info = [];
            $info[] = get_string('description').': '.
                $task->description ? format_string($task->description) : get_string('empty');
            $info[] = get_string('section', 'local_coursegoals').': '.
                (!empty($section)) ? $section->get_displayedname() : get_string('empty');
            if (!empty($crule)) {
                $ruleConditions = $crule->getCompletionConditions($task);
                if (!empty($ruleConditions)) {
                    $info[] = $ruleConditions;
                }
            }

            $html .= html_writer::start_tag('details', ['class' => 'taskinfo', 'open' => 'true']);
            $html .= html_writer::tag('summary', "{$label}");
            $html .= html_writer::alist($info);
            $html .= html_writer::end_tag('details');
            $this->setupTaskActionsModals($task->id);
        }

        $html .= $this->addNewTaskLink($data->id);
        $this->setupAddNewTaskModal($data->id);
        return $html;
    }

    public function get_sql_sort() {
        $columns = $this->get_sort_columns();
        if (count($columns) == 0) {
            $columns['courseid'] = SORT_DESC;
            return self::construct_order_by($columns);
        }
        return parent::get_sql_sort();
    }

    /**
     * @param $sort
     * @param $pagestart
     * @param $pagesize
     * @param bool $count
     * @return array|int
     * @throws \dml_exception
     */
    protected function get_data($sort, $pagestart, $pagesize, bool $count = false) {
        global $DB;

        $whereconditions = [];
        $params = [];
        if (!empty($this->courseid)) {
            $params['courseid'] = $this->courseid;
            $whereconditions[] = "cg.courseid = :courseid";
        }

        $whereclause = !empty($whereconditions) ? 'WHERE (' . implode(' AND ', $whereconditions) . ')' : "";
        $result = false;
        if ($count) {
//            $countsql = "
//            ";
//            $result = $DB->count_records_sql($countsql, $params);
        } else {
            $sql = "
                SELECT 
                    cg.id,
                    cg.name,
                    cg.courseid,
                    cg.status,
                    c.fullname as coursename
                FROM {coursegoals} cg
                JOIN {course} c ON cg.courseid = c.id
                {$whereclause} 
        ";
            if (!empty($sort)) {
                $sql .= " ORDER BY " . $sort;
            }
            $result = $DB->get_records_sql($sql, $params, $pagestart, $pagesize);
        }

        return $result;

    }

    /**
     * Query the database for results to display in the table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     * @throws dml_exception
     * @throws coding_exception
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $PAGE, $DB;

        $sort = $this->get_sql_sort();
        $page_start = $this->get_page_start();
        $page_size = $this->get_page_size();

        $total = $this->get_data($sort, $page_start, $page_size, true);
        $this->pagesize($pagesize, $total);
        $page_size = $this->get_page_size();

        $this->rawdata = $this->get_data($sort, $page_start, $page_size);

        if (!$this->rawdata) {
            return;
        }

        foreach ($this->rawdata as &$row) {
            $row->goal = new Goal($row->id);
        }

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    public static function create($page, $params = null) {
        $table = new self($page, $params);
        $url_params = [];
        if (!empty($params['courseid'])) {
            $url_params['course'] = $params['courseid'];
        }
        $base_url = new moodle_url('/local/coursegoals/index.php', $url_params);
        $table->define_baseurl($base_url);
        return $table;
    }

    public function renderControls() {
        global $OUTPUT;
        $html = '';
        $controls = '';

        if (Goal::userCanManageGoals($this->context)) {
            $controls .= html_writer::tag('button', get_string(Goal::ACTION_CREATE, 'local_coursegoals'), [
                'class' => 'm-1 btn btn-primary',
                'data-action' => Goal::ACTION_CREATE,
            ]);
            $setupModals = [];
            $setupModals[] = [
                'elementSelector' => '[data-action="'.Goal::ACTION_CREATE.'"]',
                'formClass' => \local_coursegoals\form\goal_form::class,
            ];
            $this->page->requires->js_call_amd('local_coursegoals/coursegoals', 'setupModals', [
                $setupModals
            ]);
        }
        // TODO: maybe make a different capability for creating sections
        if (Goal::userCanManageGoals($this->context)) {
//            $this->page->requires->js_call_amd('local_coursegoals/coursegoals', 'setupSectionModalForm', [
//                'elementSelector' => '[data-action="'.Section::ACTION_EDIT.'"][data-sectionid="'.$rowid.'"]',
//                'formClass' => \local_coursegoals\form\task_edit_form::class,
//            ]);

            $controls .= html_writer::tag('button', get_string(Section::ACTION_CREATE, 'local_coursegoals'), [
                'class' => 'm-1 btn btn-primary',
                'data-action' => Section::ACTION_CREATE,
            ]);
            $this->page->requires->js_call_amd('local_coursegoals/coursegoals', 'setupSectionModalForm', [
                'elementSelector' => '[data-action="'.Section::ACTION_CREATE.'"]',
                'formClass' => \local_coursegoals\form\section_create_form::class,
            ]);
        }

        $html .= html_writer::div($controls, 'controls'); // end controls
        echo $html;
    }

    public function render($withControls = true) {
        if ($withControls) {
            $this->renderControls();
        }
        $this->renderWrapperStart();
        $this->out(0, true);
        $this->renderWrapperEnd();
    }

    /**
     * Returns wrapper ID that is used for table div.
     */
    public static function getWrapperID() {
        return 'wrapper_'.self::UNIQUEID;
    }

    /**
     * Rendering HTML div start that will contain the table. Used to dynamically update the table later on.
     */
    protected function renderWrapperStart() {
        echo html_writer::start_div('', ['id' => self::getWrapperID()]);
    }

    /**
     * Rendering HTML div that will contain the table. Used to dynamically update the table later on.
     */
    protected function renderWrapperEnd() {
        echo html_writer::end_div();
    }


    /**
     * @param Task $task
     * @return string
     * @throws \coding_exception
     */
    public function getTaskActions($task) {
        global $OUTPUT;
        $icons = [];

        if (Task::userCanManageTasks($this->context) && $task->can_be_updated()) {
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/edit', get_string('edit'),'core'),
                null, [
                    'data-action' => Task::ACTION_EDIT,
                    'data-taskid' => $task->id,
                    'data-title' => get_string(Task::ACTION_EDIT, 'local_coursegoals')
                ]);
        }

        if (Task::userCanManageTasks($this->context) && $task->can_be_deleted()) {
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/delete', get_string('delete'),'core'),
                null, [
                    'data-action' => Task::ACTION_DELETE,
                    'data-taskid' => $task->id,
                    'data-title' => get_string(Task::ACTION_DELETE, 'local_coursegoals')
                ]);
        }

        return \html_writer::span(implode('', $icons),'nowrap');
    }

    public function addNewTaskLink($goalid) {
        $link = html_writer::link('#',
            '&#10133; '. get_string(Task::ACTION_CREATE, 'local_coursegoals'), [
                'data-action' => Task::ACTION_CREATE,
                'data-coursegoalid' => $goalid,
                'data-title' => get_string(Task::ACTION_CREATE, 'local_coursegoals')
            ]);
        return $link;
    }

    protected function setupGoalActionsModals($rowid) {
        $setupModals = [];
        $setupModals[] = [
            'elementSelector' => '[data-action="'.Goal::ACTION_ACTIVATE.'"][data-id="'.$rowid.'"]',
            'formClass' => \local_coursegoals\form\goal_form::class,
        ];
        $setupModals[] = [
            'elementSelector' => '[data-action="'.Goal::ACTION_EDIT.'"][data-id="'.$rowid.'"]',
            'formClass' => \local_coursegoals\form\goal_form::class,
        ];
        $setupModals[] = [
            'elementSelector' => '[data-action="'.Goal::ACTION_DELETE.'"][data-id="'.$rowid.'"]',
            'formClass' => \local_coursegoals\form\goal_form::class,
        ];
        $this->page->requires->js_call_amd('local_coursegoals/coursegoals', 'setupModals', [$setupModals]);
    }

    protected function setupTaskActionsModals($rowid) {
        $this->page->requires->js_call_amd('local_coursegoals/coursegoals', 'setupTaskModalForm', [
            'elementSelector' => '[data-action="'.Task::ACTION_EDIT.'"][data-taskid="'.$rowid.'"]',
            'formClass' => \local_coursegoals\form\task_edit_form::class,
        ]);

        $this->page->requires->js_call_amd('local_coursegoals/coursegoals', 'setupTaskModalForm', [
            'elementSelector' => '[data-action="'.Task::ACTION_DELETE.'"][data-taskid="'.$rowid.'"]',
            'formClass' => \local_coursegoals\form\task_delete_form::class,
        ]);
    }

    protected function setupAddNewTaskModal($goalid) {
        $this->page->requires->js_call_amd('local_coursegoals/coursegoals', 'setupTaskModalForm', [
            'elementSelector' => '[data-action="'.Task::ACTION_CREATE.'"][data-coursegoalid="'.$goalid.'"]',
            'formClass' => \local_coursegoals\form\task_create_form::class,
        ]);
    }

}