<?php

namespace local_coursegoals;

class helper
{
    const FEATURE_CUSTOMVIEW = 'customview';
    const FEATURE_CUSTOMTASKDETAILS = 'customtaskdetails';


    // course page header - '#page-header'
    // course content div -
    const COURSE_PAGE_HEADER_400 = '#page-header';
    const COURSE_CONTENT_START_400 = '.course-content';

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
     * @return int
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

    public static function resolveAppendOrder($selector) {
        switch($selector) {
            case helper::COURSE_PAGE_HEADER_400:
                $order = 'last';
                break;
            case helper::COURSE_CONTENT_START_400:
                $order = 'first';
                break;
            default:
                $order = 'first';
                break;
        }
        return $order;
    }

    public static function canViewGoalsInCourse($courseid) {
        // TODO: maybe reconsider this restriction
        $context = \context_course::instance($courseid);
        return has_capability('local/coursegoals:complete_goals', $context);
    }

}