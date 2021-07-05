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
 * All the steps to restore mod_coursecertificate are defined here.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * CLass defines the structure step to restore one mod_coursecertificate activity.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_coursecertificate_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     */
    protected function define_structure(): array {
        $paths = [];
        $paths[] = new restore_path_element('coursecertificate', '/activity/coursecertificate');

        // Check if we want the issues as well.
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('tool_certificate_issue', '/activity/coursecertificate/issues/issue');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the element restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_coursecertificate(array $data): void {
        global $DB;
        $data = (object) $data;
        $data->course = $this->get_courseid();
        // Insert the record.
        $newitemid = $DB->insert_record('coursecertificate', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Handles restoring a tool_certificate issue.
     *
     * @param stdClass $data Parsed element data.
     */
    protected function process_tool_certificate_issue($data) {
        global $DB;

        if (!$DB->get_manager()->table_exists('tool_certificate_issues')) {
            throw new \dml_exception('tool_certificate_issues table does not exists');
        }
        if (!$DB->get_manager()->table_exists('tool_certificate_templates')) {
            throw new \dml_exception('tool_certificate_templates table does not exists');
        }
        $data = (object) $data;

        $codefound = $DB->record_exists('tool_certificate_issues', ['code' => $data->code]);
        $templatefound = $DB->record_exists('tool_certificate_templates', ['id' => $data->templateid]);

        // TODO WP-1997 For now, we only restore issues if is same site, template exists and same issue code does not exist.
        if ($this->task->is_samesite() && $templatefound && !$codefound) {
            $oldid = $data->id;
            $data->courseid = $this->get_courseid();
            $data->userid = $this->get_mappingid('user', $data->userid);
            $newitemid = $DB->insert_record('tool_certificate_issues', $data);
            $this->set_mapping('tool_certificate_issue', $oldid, $newitemid, true, $this->task->get_old_system_contextid());
        }
    }

    /**
     * Defines post-execution actions.
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_coursecertificate', 'intro', null);
        $this->add_related_files('tool_certificate', 'issues', 'tool_certificate_issue', $this->task->get_old_system_contextid());
    }
}
