<?php

namespace local_coursegoals\form;

use context;
use moodle_url;
use local_coursegoals\Goal;
use local_coursegoals\api;

class goal_create_form extends \core_form\dynamic_form {

    protected function definition()
    {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHAEXT);

        $courses = \core_course_category::search_courses([], [], ['local/coursegoals:manage_goals_in_course']);
        $coursesOptions = [0 => get_string('choosedots')];
        foreach ($courses as $id => $course) {
            $coursesOptions[$id] = format_string($course->fullname);
        }
        $mform->addElement('select', 'courseid', get_string('course'), $coursesOptions);
        $mform->addHelpButton('courseid', 'coursechoice', 'local_coursegoals');
        $mform->addRule('courseid', null, 'required');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name'));
        $mform->addHelpButton('name', 'formatstring_naming', 'local_coursegoals');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $mform->addElement('textarea', 'description', get_string('description'));
        $mform->setType('description', PARAM_TEXT);
    }

    /**
     * Validate incoming data.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = [];

        if (!isset($data['courseid']) || $data['courseid'] == 0) {
            $errors['courseid'] = get_string('error:choose_course', 'local_coursegoals');
        }

        return $errors;
    }

    public function process_dynamic_submission() {
        $data = $this->get_data();
        list($result, $errors, $redirecturl) = api::createGoal($data);
        return [
            'result' => $result,
            'errors' => $errors,
            'redirecturl' => $redirecturl,
        ];
    }

    /**
     * Load in existing data as form defaults
     *
     * Can be overridden to retrieve existing values from db by entity id and also
     * to preprocess editor and filemanager elements
     */
    public function set_data_for_dynamic_submission(): void {
        $data = [];
        $data['action'] = $this->optional_param('action', null, PARAM_ALPHAEXT);
        $data = (object)$data;
        $this->set_data($data);
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     *
     * Sometimes permission check may depend on the action and/or id of the entity.
     * If necessary, form data is available in $this->_ajaxformdata or
     * by calling $this->optional_param()
     * @throws \moodle_exception
     */
    protected function check_access_for_dynamic_submission(): void {
        $context = $this->get_context_for_dynamic_submission();
        if(! Goal::userCanManageGoals($context)) {
            throw new \moodle_exception('nocapabilitytousethisservice');
        }
    }

    /**
     * Returns context where this form is used
     *
     * If context depends on the form data, it is available in $this->_ajaxformdata or
     * by calling $this->optional_param()
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        $course = $this->optional_param('courseid', null, PARAM_INT);
        if ($course) {
            return \context_course::instance($course);
        } else {
            return \context_system::instance();
        }

    }

    protected function get_page_url_for_dynamic_submission(): moodle_url {
        global $FULLME;
        return new \moodle_url($FULLME);
    }
}