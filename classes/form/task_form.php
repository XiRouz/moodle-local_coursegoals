<?php

namespace local_coursegoals\form;

use context;
use moodle_url;
use local_coursegoals\Task;
use local_coursegoals\Goal;
use local_coursegoals\api;

class task_form extends \core_form\dynamic_form {

    protected function definition()
    {
        $mform = $this->_form;

        $action = $this->optional_param('action', null, PARAM_ALPHAEXT);
        $goalid = $this->optional_param('dataparentid', null, PARAM_INT)
            ?? $this->optional_param('coursegoalid', null, PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        if ($action == Task::ACTION_CREATE || $action == Task::ACTION_EDIT) {
            if ($action == Task::ACTION_CREATE) {
                $mform->addElement('hidden', 'coursegoalid');
                $mform->setType('coursegoalid', PARAM_INT);
            }
            $mform->addElement('text', 'name', get_string('name'));
            $mform->addHelpButton('name', 'formatstring_naming', 'local_coursegoals');
            $mform->setType('name', PARAM_TEXT);
            $mform->addRule('name', null, 'required');

            $mform->addElement('textarea', 'description', get_string('description'));
            $mform->setType('description', PARAM_TEXT);

            // TODO: comprules form elements: selector of comprule and its params

        } else if ($action == Task::ACTION_DELETE) {
            $mform->addElement('static', 'name', get_string('name'));
            $mform->setType('name', PARAM_TEXT);

            $mform->addElement('static', 'description', get_string('description'));
            $mform->setType('description', PARAM_TEXT);

            $mform->addElement('static', 'confirm', '', get_string('ays_'.Task::ACTION_DELETE, 'local_coursegoals'));
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

//        $action = $this->optional_param('action', null, PARAM_ALPHAEXT);
//        if ($action == Goal::ACTION_CREATE && (!isset($data['courseid']) || $data['courseid'] == 0)) {
//            $errors['courseid'] = get_string('error:choose_course', 'local_coursegoals');
//        }

        return $errors;
    }

    public function process_dynamic_submission() {
        $data = $this->get_data();
        switch ($data->action) {
            case Task::ACTION_CREATE:
                list($result, $errors, $redirecturl) = api::createTask($data);
                break;
            case Task::ACTION_EDIT:
                list($result, $errors, $redirecturl) = api::editTask($data);
                break;
            case Task::ACTION_DELETE:
                list($result, $errors, $redirecturl) = api::deleteTask($data);
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
        if ($data['action'] == Task::ACTION_CREATE) {
            $data['coursegoalid'] = $this->optional_param('coursegoalid', null, PARAM_INT);
        }
        $id = $this->optional_param('dataid', null, PARAM_INT);
        if ($id) {
            $task = new Task($id);
            $data['id'] = $task->id;
            // TODO: load comprules defaults for form
            $data['name'] = $task->name;
            $data['description'] = $task->description;
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
        if(! Task::userCanManageTasks($context)) {
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