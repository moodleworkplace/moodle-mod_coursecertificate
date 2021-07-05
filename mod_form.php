<?php
// This file is part of the mod_coursecertificate plugin for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The main mod_coursecertificate configuration form.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition(): void {
        global $CFG, $OUTPUT;

        $mform = $this->_form;
        $hasissues = $this->has_issues();
        $canmanagetemplates = \tool_certificate\permission::can_manage_anywhere();

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Adding the template selector.
        if ($hasissues) {
            // If coursecertificate has issues, just add the current template to the selector.
            $templates = $this->get_current_template();
        } else {
            // Get all available templates for the user.
            $templates = $this->get_template_select();
        }
        $templateoptions = ['' => get_string('chooseatemplate', 'coursecertificate')] + $templates;
        $manageurl = new \moodle_url('/admin/tool/certificate/manage_templates.php');
        $elements = [$mform->createElement('select', 'template', get_string('template', 'coursecertificate'), $templateoptions)];
        // Adding "Manage templates" link if user has capabilities to manage templates.
        if ($canmanagetemplates && !empty($templates)) {
            $elements[] = $mform->createElement('static', 'managetemplates', '',
                $OUTPUT->action_link($manageurl, get_string('managetemplates', 'coursecertificate')));
        }
        $mform->addGroup($elements, 'template_group', get_string('template', 'coursecertificate'),
            \html_writer::div('', 'w-100'), false);

        if (empty($templates)) {
            // Adding warning text if there are not templates available.
            if ($canmanagetemplates) {
                $warningstr = get_string('notemplateswarningwithlink', 'coursecertificate', $manageurl->out());
            } else {
                $warningstr = get_string('notemplateswarning', 'coursecertificate');
            }
            $html = html_writer::tag('div', $warningstr, ['class' => 'alert alert-warning']);
            $mform->addElement('static', 'notemplateswarning', '', $html);
        } else {
            $warningstr = get_string('selecttemplatewarning', 'mod_coursecertificate');
            $html = html_writer::tag('div', $warningstr, ['class' => 'alert alert-warning']);
            $mform->addElement('static', 'selecttemplatewarning', '', $html);
        }
        if (!$hasissues) {
            $rules = [];
            $rules['template'][] = [null, 'required', null, 'client'];
            $mform->addGroupRule('template_group', $rules);
        }
        // If Certificate has issues it's not possible to change the template.
        $mform->addElement('hidden', 'hasissues', $hasissues);
        $mform->setType('hasissues', PARAM_TEXT);
        $mform->disabledIf('template', 'hasissues', 'eq', 1);

        // Adding the expirydate selector.
        $selectdatestr = get_string('selectdate', 'coursecertificate');
        $neverstr = get_string('never');
        $expirydatestr = get_string('expirydate', 'coursecertificate');
        $expirydateoptions = [
            0 => $neverstr,
            1 => $selectdatestr,
        ];
        $group = [];
        $expirydatetype = $mform->createElement('select', 'expirydatetype', '', $expirydateoptions,
            ['class' => 'calendar-fix-selector-width']);
        $group[] =& $expirydatetype;
        $expirydate = $mform->createElement('date_selector', 'expires', '');
        $group[] =& $expirydate;
        $mform->addGroup($group, 'expirydategroup', $expirydatestr, ' ', false);
        $mform->hideIf('expires', 'expirydatetype', 'noteq', 1);
        $mform->disabledIf('expires', 'expirydatetype', 'noteq', 1);

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Enforce validation rules here
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array
     **/
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }

    /**
     * Enforce defaults here.
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues) {
        if (isset($defaultvalues['expires']) && ($defaultvalues['expires'] != 0)) {
            $defaultvalues['expirydatetype'] = 1;
        }
    }

    /**
     * Allows modules to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data passed by reference
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        $data->expires = $data->expirydatetype == 0 ? 0 : $data->expires;
    }

    /**
     * Gets the current coursecertificate template for the template selector.
     *
     * @return array
     */
    private function get_current_template(): array {
        global $DB;
        $templates = [];
        if ($instance = $this->get_instance()) {
            $sql = "SELECT ct.id, ct.name
                    FROM {tool_certificate_templates} ct
                    JOIN {coursecertificate} c
                    ON c.template = ct.id
                    AND c.id = :instance";
            if ($record = $DB->get_record_sql($sql, ['instance' => $instance], IGNORE_MISSING)) {
                $templates[$record->id] = format_string($record->name);
            }
        }
        return $templates;
    }

    /**
     * Gets array options of available templates for the user for the template selector.
     *
     * @return array
     */
    private function get_template_select(): array {
        $context = context_course::instance($this->current->course);
        $templates = [];
        if (!empty($records = \tool_certificate\permission::get_visible_templates($context))) {
            foreach ($records as $record) {
                $templates[$record->id] = format_string($record->name);
            }
        }
        return $templates;
    }

    /**
     * Returns "1" if course certificate has been issued.
     *
     * @return string
     * @uses \tool_certificate\certificate
     */
    private function has_issues(): string {
        global $DB;

        if ($instance = $this->get_instance()) {
            $certificate = $certificate = $DB->get_record('coursecertificate', ['id' => $instance], '*', MUST_EXIST);
            $courseissues = \tool_certificate\certificate::count_issues_for_course($certificate->template, $certificate->course,
                'mod_coursecertificate', null, null);
            if ($courseissues > 0) {
                return  "1";
            }
        }
        return "0";
    }
}
