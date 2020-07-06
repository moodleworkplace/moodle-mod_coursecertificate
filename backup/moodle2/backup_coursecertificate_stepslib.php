<?php
// This file is part of Moodle - http://moodle.org/
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
 * Define the complete structure for backup, with file and id annotations.
 *
 * @package     mod_coursecertificate
 * @category    backup
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * THe class defines the complete structure for backup, with file and id annotations.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_coursecertificate_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the resulting xml file.
     *
     * @return backup_nested_element The structure wrapped by the common 'activity' element.
     */
    protected function define_structure() {
        global $DB;

        // Replace with the attributes and final elements that the element will handle.
        $attributes = ['id'];
        $finalelements = ['name', 'timecreated', 'timemodified', 'intro',
            'introformat', 'template', 'automaticsend', 'expires'];


        $root = new backup_nested_element('coursecertificate', $attributes, $finalelements);

        // The issues.
        $issues = new backup_nested_element('issues');
        $issue = new backup_nested_element('issue', ['id'],
            ['userid', 'templateid', 'code', 'emailed', 'timecreated', 'expires', 'data', 'component', 'courseid']);

        // Build the tree.
        $root->add_child($issues);
        $issues->add_child($issue);

        // Define the source tables for the elements.
        $root->set_source_table('coursecertificate', ['id' => backup::VAR_ACTIVITYID]);

        // If we are including user info then save the issues.
        if ($this->get_setting_value('userinfo')) {
            if (!$DB->get_manager()->table_exists('tool_certificate_issues')) {
                throw new \dml_exception('tool_certificate_issues table does not exists');
            }
            $issue->set_source_table('tool_certificate_issues', ['courseid' => backup::VAR_COURSEID]);
        }

        // Define file annotations.
        $root->annotate_files('mod_coursecertificate', 'intro', null); // This file area hasn't itemid.

        return $this->prepare_activity_structure($root);
    }
}