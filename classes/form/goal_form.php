<?php

namespace local_coursegoals\form;

use context;
use moodle_url;
use local_coursegoals\Goal;
use local_coursegoals\api;

class goal_form extends \core_form\dynamic_form {

    protected function definition()
    {
        // TODO: make separate forms for all goal actions

        $mform = $this->_form;

        $action = $this->optional_param('action', null, PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHAEXT);

        if ($action == Goal::ACTION_CREATE || $action == Goal::ACTION_EDIT) {
            if ($action == Goal::ACTION_CREATE) {
                $courses = \core_course_category::search_courses([], [], ['local/coursegoals:manage_goals_in_course']);
                $coursesOptions = [0 => get_string('choosedots')];
                foreach ($courses as $id => $course) {
                    $coursesOptions[$id] = format_string($course->fullname);
                }
                $mform->addElement('select', 'courseid', get_string('course'), $coursesOptions);
                $mform->addHelpButton('courseid', 'coursechoice', 'local_coursegoals');
                $mform->addRule('courseid', null, 'required');
                $mform->setType('courseid', PARAM_INT);
            }

            $mform->addElement('text', 'name', get_string('name'));
            $mform->addHelpButton('name', 'formatstring_naming', 'local_coursegoals');
            $mform->setType('name', PARAM_TEXT);
            $mform->addRule('name', null, 'required');

            $mform->addElement('textarea', 'description', get_string('description'));
            $mform->setType('description', PARAM_TEXT);

            // TODO: availability
            // TODO: onfinish

        } else if ($action == Goal::ACTION_DELETE) {
            $mform->addElement('static', 'name', get_string('name'));
            $mform->setType('name', PARAM_TEXT);

            $mform->addElement('static', 'description', get_string('description'));
            $mform->setType('description', PARAM_TEXT);

            $mform->addElement('static', 'confirm', '', get_string('ays_'.Goal::ACTION_DELETE, 'local_coursegoals'));
            $mform->setType('confirm', PARAM_TEXT);

        } else if ($action == Goal::ACTION_ACTIVATE) {
            $mform->addElement('static', 'name', get_string('name'));
            $mform->setType('name', PARAM_TEXT);

            $mform->addElement('static', 'description', get_string('description'));
            $mform->setType('description', PARAM_TEXT);

            $mform->addElement('static', 'confirm', '', get_string(Goal::ACTION_ACTIVATE.'_explained', 'local_coursegoals'));
            $mform->setType('confirm', PARAM_TEXT);

        }
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

        $action = $this->optional_param('action', null, PARAM_ALPHAEXT);
        if ($action == Goal::ACTION_CREATE && (!isset($data['courseid']) || $data['courseid'] == 0)) {
            $errors['courseid'] = get_string('error:choose_course', 'local_coursegoals');
        }

        return $errors;
    }

    public function process_dynamic_submission() {
        $data = $this->get_data();
        // TODO: try to rework this universality, it may cause trouble
        switch ($data->action) {
            case Goal::ACTION_CREATE:
                list($result, $errors, $redirecturl) = api::createGoal($data);
                break;
            case Goal::ACTION_EDIT:
                list($result, $errors, $redirecturl) = api::editGoal($data);
                break;
            case Goal::ACTION_DELETE:
                list($result, $errors, $redirecturl) = api::deleteGoal($data);
                break;
            case Goal::ACTION_ACTIVATE:
                list($result, $errors, $redirecturl) = api::activateGoal($data);
                break;
        }
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
        $id = $this->optional_param('dataid', null, PARAM_INT);
        if ($id) {
            $goal = new Goal($id);
            $data['id'] = $goal->id;
            $data['courseid'] = $goal->courseid;
            $data['name'] = $goal->name;
            $data['description'] = $goal->description;
        }

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