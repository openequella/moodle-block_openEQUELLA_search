<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class block_equella_search_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'];

        // visible elements
        $mform->addElement('text', 'searchstring', get_string('search.label', 'block_equella_search'), 'size="48"');

        // hidden optional params
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons(false, get_string('search.button', 'block_equella_search'));
    }
}

