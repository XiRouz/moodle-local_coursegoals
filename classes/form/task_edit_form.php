<?php

namespace local_coursegoals\form;

use context;
use core\check\performance\debugging;
use Exception;
use moodle_url;
use local_coursegoals\Task;
use local_coursegoals\api;
use local_coursegoals\comprule;
use local_coursegoals\comprule_form;

class task_edit_form extends \core_form\dynamic_form {

    protected function definition()
    {
        $mform = $this->_form;

        $action = $this->optional_param('action', null, PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);


        $mform->addElement('text', 'name', get_string('name'));
        $mform->addHelpButton('name', 'formatstring_naming', 'local_coursegoals');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $mform->addElement('textarea', 'description', get_string('description'));
        $mform->addHelpButton('description', 'formatstring_naming', 'local_coursegoals');
        $mform->setType('description', PARAM_TEXT);

        $comprules = comprule::getComprules();
        $comprulesOptions = [0 => get_string('choosedots')];
        foreach ($comprules as $id => $rule) {
            $comprulesOptions[$id] = get_string('pluginname', "comprules_{$rule->name}");
        }
        $mform->addElement('select', 'compruleid', get_string('compruleid', 'local_coursegoals'), $comprulesOptions);
//           $mform->addHelpButton('compruleid', 'compruleid', 'local_coursegoals');
        $mform->setType('compruleid', PARAM_INT);
        $mform->setDefault('compruleid', 0);
        foreach ($comprules as $id => $comprule) {
            $class = comprule_form::makeCompruleFormClassname($comprule);
            try {
                $ruleFormObj = new $class();
                $mformgroup = $ruleFormObj::getFormElementsGroup($mform);
                if (!empty($mformgroup)) {
                    $mform->addGroup($mformgroup, "crgr_{$comprule->name}", 'CRule parameters', /*null, false*/);
                    $mform->setType("crgr_{$comprule->name}", PARAM_RAW);
                    $mform->hideIf("crgr_{$comprule->name}", 'compruleid', 'neq', $id);
                }
            } catch (Exception $e) {
                debugging($e->getMessage(), DEBUG_DEVELOPER);
            }
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

        if ((!isset($data['compruleid']) || $data['compruleid'] == 0)) {
            $errors['compruleid'] = get_string('error:choose_comprule', 'local_coursegoals');
        }

        if (isset($data['compruleid']) && $data['compruleid'] != 0) {
            $comprule = comprule::getCompruleByID($data['compruleid']);
            $class = comprule_form::makeCompruleFormClassname($comprule);
            try {
                $ruleFormObj = new $class();
                $ruleErrors = $ruleFormObj::validateParams($data["crgr_{$comprule->name}"]);
                if (!empty($ruleErrors)) {
                    $errors["crgr_{$comprule->name}"] = $ruleErrors;
                }
            } catch (Exception $e) {
                debugging($e->getMessage(), DEBUG_DEVELOPER);
            }
        }

        return $errors;
    }

    public function process_dynamic_submission() {
        $data = $this->get_data();
        $comprule = comprule::getCompruleByID($data->compruleid);
        $crgr_name = 'crgr_'.$comprule->name;
        $class = comprule::makeCompruleClassname($comprule);
        try {
            $crule = new $class();
            $data->comprule_params = $crule::encodeParams($data->$crgr_name);
        } catch (Exception $e) {
            debugging($e->getMessage(), DEBUG_DEVELOPER);
        }

        list($result, $errors, $redirecturl) = api::editTask($data);
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
        $id = $this->optional_param('taskid', null, PARAM_INT);
        if ($id) {
            $task = new Task($id);
            $data['id'] = $task->id;
            $data['name'] = $task->name;
            $data['description'] = $task->description;
            $data['coursegoalid'] = $task->coursegoalid;
            $data['compruleid'] = $task->compruleid;
            $comprule = comprule::getCompruleByID($task->compruleid);
            $class = comprule::makeCompruleClassname($comprule);
            try {
                $crule = new $class();
                $decodedParams = $crule::decodeParams($task->comprule_params);
                if (!empty($decodedParams)) {
                    $data["crgr_{$comprule->name}"] = $decodedParams;
                }
            } catch (Exception $e) {
                debugging($e->getMessage(), DEBUG_DEVELOPER);
            }
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