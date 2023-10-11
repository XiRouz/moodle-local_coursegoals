<?php

namespace local_coursegoals;

class helper
{
    /** Checks if user is on page, where rendering this block is allowed
     * @return bool
     */
    public static function isOnAllowedPage(): bool {

        if (helper::isCoursePage()
                || helper::isSingleActivityCoursePage()
//                || helper::isFrontpage()
//                || helper::isMyPage()
        ) {
            return true;
        }
        return false;
    }

    /** Checks if user is on a main course page
     * @return int
     */
    public static function isCoursePage(): int {
        global $PAGE, $CFG;
        if ($PAGE->course && $PAGE->url->out_omit_querystring() === $CFG->wwwroot . '/course/view.php') {
            return $PAGE->course->id;
        }
        return 0;
    }

    /** Checks if user is on a main page of single-activity course
     * @return int
     */
    public static function isSingleActivityCoursePage(): int {
        global $PAGE, $CFG;
        if ($PAGE->context->contextlevel == CONTEXT_MODULE && $PAGE->course->format === 'singleactivity' &&
            $PAGE->url->out_omit_querystring() === $CFG->wwwroot . '/mod/' . $PAGE->cm->modname . '/view.php') {
            return $PAGE->course->id;
        }
        return 0;
    }

    /** Checks if user is around main pages of site
     * @return int
     */
    public static function is_mainpages(): int {
        global $PAGE;
        if ($PAGE->course->id == 1) {
            return 1;
        }
        return 0;
    }

    /** Checks if user is on a frontpage
     * @return int
     */
    public static function isFrontpage(): int {
        global $PAGE, $CFG;
        if ($PAGE->url->out_omit_querystring() === $CFG->wwwroot . '/') { // || $PAGE->url->out_omit_querystring() === $CFG->wwwroot
            return 1;
        }
        return 0;
    }

    /** Checks if user is on any of /my/.. pages
     *@return int
     */
    public static function isMyPage(): int {
        global $PAGE, $CFG;
        $curpage = $PAGE->url->out_omit_querystring();
        $mypage = $CFG->wwwroot . '/my';
        if (is_integer(strpos($curpage, $mypage))) {
            return 1;
        }
        return 0;
    }

    public static function courseHasGoals($courseid, $goalStatus = Goal::STATUS_ACTIVE): bool {
        global $DB;

        $whereconditions = [];
        $params['courseid'] = $courseid;
        $whereconditions[] = "courseid = :courseid";
        if (is_array($goalStatus)) {
            list($statusval, $statusparams) = $DB->get_in_or_equal($goalStatus, SQL_PARAMS_NAMED);
            $whereconditions[] = "status $statusval";
            $params = array_merge($params, $statusparams);
        } else if (is_numeric($goalStatus)) {
            $whereconditions[] = "status = :status";
            $params['status'] = $goalStatus;
        }
        $whereclause = !empty($whereconditions) ? "WHERE (" . implode(" AND ", $whereconditions) . ")" : "";
        $sql = "
            SELECT cg.*
            FROM {coursegoals} cg
            $whereclause
            ORDER BY cg.id DESC
        ";
        $result = $DB->get_records_sql($sql, $params);
        if (empty($result)) {
            return false;
        } else {
            return true;
        }
    }

}