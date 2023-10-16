<?php

namespace local_coursegoals\table;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->libdir . '/tablelib.php');

use table_sql;
use moodle_url;
use html_writer;
use local_coursegoals\Goal;

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

    public function col_tasks($data) {
        $tpc = new Chapter($data->id, true);
        $html = '';
        foreach ($tpc->themes as $theme) {
            $implodearr = [];
            $implodearr[] = '<b>'.$theme->get_name().'</b> | <small>'.$theme->getShortName().'</small>';
            $implodearr[] = $this->getThemeActions($theme);
            $label = implode('&ensp;', $implodearr);

            $info = [];
            switch ($theme->default_grade_mode) {
                case Theme::GRADE_MODE_MANUAL:
                    $gradeModeString = get_string('grade_mode_manual', 'local_sic');
                    break;
                case Theme::GRADE_MODE_FROMMOD:
                    $gradeModeString = get_string('grade_mode_frommod', 'local_sic');
                    break;
                case Theme::GRADE_MODE_TEACHER_CHOICE:
                    $gradeModeString = get_string('grade_mode_teacher_choice', 'local_sic');
                    break;
            }
            $info[] = $gradeModeString;
            $cm = $theme->getAssignedCM();
            if ($cm) {
                $info[] = \html_writer::link($cm->get_url(), format_string($cm->name));
            }
            $info[] = get_string('grading', 'local_sic').': 
                ['.round($theme->min_grade, 2).';'.round($theme->max_grade, 2).']';

            $html .= html_writer::start_tag('details', ['class' => 'themeinfo', 'open' => 'true']);
            $html .= html_writer::tag('summary', "{$label}");
            $html .= html_writer::alist($info);
            $html .= html_writer::end_tag('details');
            $this->setupThemeActionsModals($theme->id);
        }

        $html .= $this->addNewThemeLink($data->id);
        $this->setupAddNewThemeModal($data->id);
        return $html;
    }

    public function col_actions($data) {
        global $OUTPUT;
        $icons = [];

        if (has_capability('local/sic:manage_themeplans', $this->context)
                && $data->tpc->can_be_updated()) {
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/edit', get_string('edit'),'core'),
                null, [
                    'data-action' => Chapter::ACTION_EDIT,
                    'data-id' => $data->id,
                    'data-parentid' => $data->tpc->themeplan_id,
                    'data-title' => get_string('edit_themeplan_chapter', 'local_sic')
                ]);
        }

        if (has_capability('local/sic:manage_themeplans', $this->context)
                && $data->tpc->can_be_deleted()) {
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/delete', get_string('delete'),'core'),
                null, [
                    'data-action' => Chapter::ACTION_DELETE,
                    'data-id' => $data->id,
                    'data-parentid' => $data->tpc->themeplan_id,
                    'data-title' => get_string('delete_themeplan_chapter', 'local_sic')
                ]);
        }

        $this->setupChapterActionsModals($data->id);

        return \html_writer::span(implode('', $icons),'nowrap');
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
        $params['courseid'] = $this->courseid;
        $whereconditions[] = "cg.courseid = :courseid";

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

//        foreach ($this->rawdata as &$row) {
//
//        }

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

    /**
     * @param Theme $theme
     * @return string
     * @throws \coding_exception
     */
    public function getThemeActions($theme) {
        global $OUTPUT;
        $icons = [];

        if (has_capability('local/sic:manage_themeplans', $this->context)
                && $theme->can_be_updated()) {
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/edit', get_string('edit'),'core'),
                null, [
                    'data-action' => Theme::ACTION_EDIT,
                    'data-id' => $theme->id,
                    'data-parentid' => $theme->item_idnumber,
                    'data-title' => get_string(Theme::ACTION_EDIT, 'local_sic')
                ]);
        }

        if (has_capability('local/sic:manage_themeplans', $this->context)
                && $theme->can_be_deleted()) {
            $icons[] = $OUTPUT->action_icon('#',
                new \pix_icon('t/delete', get_string('delete'),'core'),
                null, [
                    'data-action' => Theme::ACTION_DELETE,
                    'data-id' => $theme->id,
                    'data-parentid' => $theme->item_idnumber,
                    'data-title' => get_string(Theme::ACTION_DELETE, 'local_sic')
                ]);
        }

        return \html_writer::span(implode('', $icons),'nowrap');
    }

    public function addNewThemeLink($chapterid) {
        $link = html_writer::link('#',
            '&#10133; '. get_string(Theme::ACTION_CREATE, 'local_sic'), [
            'data-action' => Theme::ACTION_CREATE,
            'data-parentid' => $chapterid,
            'data-title' => get_string(Theme::ACTION_CREATE, 'local_sic')
        ]);
//        $this->setupAddNewThemeModal($chapterid);
        return $link;
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

    protected function setupThemeActionsModals($rowid) {
        $setupModals = [];
        $setupModals[] = [
            'elementSelector' => '[data-action="'.Theme::ACTION_EDIT.'"][data-id="'.$rowid.'"]',
            'formClass' => \local_sic\form\themeplan_theme_form::class,
        ];
        $setupModals[] = [
            'elementSelector' => '[data-action="'.Theme::ACTION_DELETE.'"][data-id="'.$rowid.'"]',
            'formClass' => \local_sic\form\themeplan_theme_form::class,
        ];
        $this->page->requires->js_call_amd('local_sic/themeplan', 'setupModals', [
            $setupModals
        ]);
    }

    protected function setupChapterActionsModals($rowid) {
        $setupModals = [];
        $setupModals[] = [
            'elementSelector' => '[data-action="'.Chapter::ACTION_EDIT.'"][data-id="'.$rowid.'"]',
            'formClass' => \local_sic\form\themeplan_chapter_form::class,
        ];
        $setupModals[] = [
            'elementSelector' => '[data-action="'.Chapter::ACTION_DELETE.'"][data-id="'.$rowid.'"]',
            'formClass' => \local_sic\form\themeplan_chapter_form::class,
        ];
        $this->page->requires->js_call_amd('local_sic/themeplan', 'setupModals', [
            $setupModals
        ]);
    }

    protected function setupAddNewThemeModal($chapterid) {
        $setupModals = [];
        $setupModals[] = [
            'elementSelector' => '[data-action="'.Theme::ACTION_CREATE.'"][data-parentid="'.$chapterid.'"]',
            'formClass' => \local_sic\form\themeplan_theme_form::class,
        ];
        $this->page->requires->js_call_amd('local_sic/themeplan', 'setupModals', [
            $setupModals
        ]);
    }

}