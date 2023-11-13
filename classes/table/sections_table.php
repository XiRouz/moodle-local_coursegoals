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
use local_coursegoals\Section;

class sections_table extends table_sql
{
    /** @var string Table ID */
    const UNIQUEID = 'coursegoals-table';

    /** @var $PAGE object where the table is rendering */
    protected object $page;

    protected $context;

    /** Constructor
     * @param $page
     * @param mixed $params Acceptable parameters: context(required), courseid
     * @throws \coding_exception
     */
    public function __construct($page, $params = null) {
        parent::__construct(self::UNIQUEID);
        $this->page = $page;

        // TODO: remove hardcode
        $this->context = \context_system::instance();

//        if (empty($params)) {
//            throw new \coding_exception("Required parameters missing");
//        } else {
//            if (empty($params['context'])) {
//                throw new \coding_exception("Missing context");
//            } else {
//                $this->context = $params['context'];
//                unset($params['context']);
//            }
//            if (!empty($params['courseid'])) {
//                $this->courseid = $params['courseid'];
//            }
//            foreach ($params as $key => $param) {
//                $this->{$key} = $param;
//            }
//        }

        $columnheaders = [
            'name' => get_string('name'),
            'coursegoalid' => get_string('section_coursegoalid', 'local_coursegoals'),
            'sortorder' => get_string('sortorder', 'local_coursegoals'),
            'displayedname' => get_string('displayedname', 'local_coursegoals'),
            'description' => get_string('description'),
            'actions' => get_string('actions'),
        ];

        $this->define_columns(array_keys($columnheaders));
        $this->define_headers(array_values($columnheaders));
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(false);
        $this->is_downloadable(false);
    }

    public function col_coursegoalid($data) {
        if (!empty($data->coursegoalid)) {
            $section = new Section($data->coursegoalid);
            return $section->get_displayedname();
        } else if ($data->coursegoalid == strval(Section::SHARED_SECTION_CGID)) {
            return get_string('shared', 'local_coursegoals');
        }
        return '-';
    }

    public function col_name($data) {
        return format_string($data->name);
    }

    public function col_displayedname($data) {
        return format_string($data->displayedname);
    }

    public function col_description($data) {
        return format_string($data->description);
    }

    public function col_actions($data) {
        global $OUTPUT;
        $icons = [];

        // TODO: separate capability for section management?
        if (Goal::userCanManageGoals($this->context) && $data->section->can_be_updated()) {
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/edit', get_string('edit'),'core'),
                null, [
                    'data-action' => Section::ACTION_EDIT,
                    'data-sectionid' => $data->id,
                    'data-title' => get_string(Section::ACTION_EDIT, 'local_coursegoals')
                ]);
        }

        if (Goal::userCanManageGoals($this->context) && $data->section->can_be_deleted()) {
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/delete', get_string('delete'),'core'),
                null, [
                    'data-action' => Section::ACTION_DELETE,
                    'data-sectionid' => $data->id,
                    'data-title' => get_string(Section::ACTION_DELETE, 'local_coursegoals')
                ]);
        }

        $this->setupSectionActionsModals($data->id);

        return \html_writer::span(implode('', $icons),'nowrap');
    }

    public function get_sql_sort() {
        $columns = $this->get_sort_columns();
        if (count($columns) == 0) {
            $columns['sortorder'] = SORT_ASC;
            return self::construct_order_by($columns);
        }
        return parent::get_sql_sort();
    }

    protected function get_data($sort, $pagestart, $pagesize, bool $count = false) {
        global $DB;

        $whereconditions = [];
        $params = [];
//        if (!empty($this->courseid)) {
//            $params['courseid'] = $this->courseid;
//            $whereconditions[] = "cg.courseid = :courseid";
//        }

        $whereclause = !empty($whereconditions) ? 'WHERE (' . implode(' AND ', $whereconditions) . ')' : "";
        $result = false;
        if ($count) {
//            $countsql = "
//            ";
//            $result = $DB->count_records_sql($countsql, $params);
        } else {
            $sql = "
                SELECT 
                    cgs.id,
                    cgs.coursegoalid,
                    cgs.name,
                    cgs.displayedname,
                    cgs.description,
                    cgs.sortorder
                FROM {coursegoals_section} cgs
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
            $row->section = new Section($row->id);
        }

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    public static function create($page, $params = null, $base_url = null) {
        $table = new self($page, $params);
        $url_params = [];
//        if (!empty($params['courseid'])) {
//            $url_params['course'] = $params['courseid'];
//        }
        if (is_null($base_url)) {
            $base_url = new moodle_url('/local/coursegoals/sections.php', $url_params);
        }
        $table->define_baseurl($base_url);
        return $table;
    }

    public function renderControls() {
        global $OUTPUT;
        $html = '';
        $controls = '';

        // TODO: maybe make a different capability for creating sections
        if (Goal::userCanManageGoals($this->context)) {
            $controls .= html_writer::tag('button', get_string(Section::ACTION_CREATE, 'local_coursegoals'), [
                'class' => 'm-1 btn btn-primary',
                'data-action' => Section::ACTION_CREATE,
            ]);
            $this->setupAddNewSectionModal();
        }
        $html .= html_writer::div($controls, 'controls');
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

    protected function setupAddNewSectionModal() {
        $this->page->requires->js_call_amd('local_coursegoals/coursegoals', 'setupSectionModalForm', [
            'elementSelector' => '[data-action="'.Section::ACTION_CREATE.'"]',
            'formClass' => \local_coursegoals\form\section_create_form::class,
        ]);
    }

    protected function setupSectionActionsModals($rowid) {
        $this->page->requires->js_call_amd('local_coursegoals/coursegoals', 'setupSectionModalForm', [
            'elementSelector' => '[data-action="'.Section::ACTION_EDIT.'"][data-sectionid="'.$rowid.'"]',
            'formClass' => \local_coursegoals\form\section_edit_form::class,
        ]);

        $this->page->requires->js_call_amd('local_coursegoals/coursegoals', 'setupSectionModalForm', [
            'elementSelector' => '[data-action="'.Section::ACTION_DELETE.'"][data-sectionid="'.$rowid.'"]',
            'formClass' => \local_coursegoals\form\section_edit_form::class,
        ]);
    }
}