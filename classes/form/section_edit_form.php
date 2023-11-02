<?php

namespace local_coursegoals\form;

use context;
use core\check\performance\debugging;
use Exception;
use local_coursegoals\Goal;
use local_coursegoals\Section;
use moodle_url;
use local_coursegoals\Task;
use local_coursegoals\api;
use local_coursegoals\comprule;
use local_coursegoals\comprule_form;
use local_coursegoals\helper;

class section_edit_form extends \core_form\dynamic_form {

    protected function definition()
    {
        $mform = $this->_form;

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('static', 'sections_explained', '', get_string('sections_explained', 'local_coursegoals'));
        $mform->setType('sections_explained', PARAM_TEXT);

        $mform->addElement('text', 'name', get_string('name'));
        $mform->addHelpButton('name', 'formatstring_naming', 'local_coursegoals');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $mform->addElement('text', 'displayedname', get_string('displayedname', 'local_coursegoals'));
        $mform->addHelpButton('displayedname', 'displayedname', 'local_coursegoals');
        $mform->setType('displayedname', PARAM_TEXT);
        $mform->addRule('displayedname', null, 'required');

        $mform->addElement('textarea', 'description', get_string('description'));
        $mform->addHelpButton('description', 'formatstring_naming', 'local_coursegoals');
        $mform->setType('description', PARAM_TEXT);

        $coursegoalOptions = [0 => get_string('shared', 'local_coursegoals')];
        $goals = Goal::getGoals();
        foreach ($goals as $id => $goal) {
            $coursegoalOptions[$id] = $goal->get_name();
        }
        $mform->addElement('select', 'coursegoalid', get_string('coursegoalid', 'local_coursegoals'), $coursegoalOptions);
        $mform->addHelpButton('coursegoalid', 'coursegoalid', 'local_coursegoals');
        $mform->setType('coursegoalid', PARAM_INT);
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
        return $errors;
    }

    public function process_dynamic_submission() {
        $data = $this->get_data();
        list($result, $errors, $redirecturl) = api::editSection($data);
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
        $id = $this->optional_param('sectionid', null, PARAM_INT);
        if ($id) {
            $section = new Section($id);
            $data['id'] = $section->id;
            $data['coursegoalid'] = $section->coursegoalid;
            $data['name'] = $section->name;
            $data['displayedname'] = $section->displayedname;
            $data['description'] = $section->description;
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