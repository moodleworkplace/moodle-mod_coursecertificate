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
 * Library of interface functions and constants.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Checks if certificate activity supports a specific feature.
 *
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_SHOW_DESCRIPTION
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_COMPLETION_HAS_RULES
 * @uses FEATURE_MODEDIT_DEFAULT_COMPLETION
 * @uses FEATURE_BACKUP_MOODLE2
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function coursecertificate_supports(string $feature): ?bool {
    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_MODEDIT_DEFAULT_COMPLETION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_coursecertificate into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param stdClass $data An object from the form.
 * @param mod_coursecertificate_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function coursecertificate_add_instance(stdClass $data, mod_coursecertificate_mod_form $mform = null): int {
    global $DB;

    $data->timecreated = time();
    $cmid = $data->coursemodule;

    $data->id = $DB->insert_record('coursecertificate', $data);

    // We need to use context now, so we need to make sure all needed info is already in db.
    $DB->set_field('course_modules', 'instance', $data->id, ['id' => $cmid]);

    return $data->id;
}

/**
 * Updates an instance of the mod_coursecertificate in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param stdClass $data An object from the form in mod_form.php.
 * @param mod_coursecertificate_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function coursecertificate_update_instance(stdClass $data, mod_coursecertificate_mod_form $mform = null): bool {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    return $DB->update_record('coursecertificate', $data);
}

/**
 * Removes an instance of the mod_coursecertificate from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function coursecertificate_delete_instance(int $id): bool {
    global $DB;

    $activity = $DB->get_record('coursecertificate', ['id' => $id]);
    if (!$activity) {
        return false;
    }

    $DB->delete_records('coursecertificate', ['id' => $id]);

    return true;
}

/**
 * Return a list of page types
 *
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array array of page types and it's names
 */
function coursecertificate_page_type_list($pagetype, $parentcontext, $currentcontext): array {
    $modulepagetype = [
        'mod-coursecertificate-*' => get_string('page-mod-coursecertificate-x', 'mod_coursecertificate'),
    ];
    return $modulepagetype;
}

/**
 * Callback for tool_certificate - the fields available for the certificates
 */
function mod_coursecertificate_tool_certificate_fields() {
    global $CFG;

    if (!class_exists('tool_certificate\customfield\issue_handler')) {
        return;
    }

    $handler = tool_certificate\customfield\issue_handler::create();
    if ($CFG->version < 2021050700) {
        // Moodle 3.9-3.10.
        $gradestring = get_string('grade');
    } else {
        // Moodle 3.11 and above.
        $gradestring = get_string('gradenoun');
    }

    // TODO: the only currently supported field types are text/textarea (numeric will fallback to text).
    $handler->ensure_field_exists('courseid', 'numeric',
        get_string('courseinternalid', 'mod_coursecertificate'), false, 1);
    $handler->ensure_field_exists('courseshortname', 'text', get_string('shortnamecourse'),
        true, get_string('previewcourseshortname', 'mod_coursecertificate'));
    $handler->ensure_field_exists('coursefullname', 'text', get_string('fullnamecourse'),
        true, get_string('previewcoursefullname', 'mod_coursecertificate'));
    $handler->ensure_field_exists('courseurl', 'text',
        get_string('courseurl', 'mod_coursecertificate'),
        true, $CFG->wwwroot . '/course/view.php?id=1');
    $handler->ensure_field_exists(
        'coursecompletiondate',
        'text',
        get_string('course') . ': ' . get_string('coursecompletiondate', 'mod_coursecertificate'),
        true,
        get_string('coursecompletiondate', 'mod_coursecertificate')
    );
    $handler->ensure_field_exists(
        'coursegrade',
        'text',
        get_string('course') . ': ' . $gradestring,
        true,
        $gradestring
    );

    // Get the course custom fields (note the only supported field types are text/textarea).
    $coursehandler = \core_course\customfield\course_handler::create();
    foreach ($coursehandler->get_fields() as $field) {
        $handler->ensure_field_exists(
            'coursecustomfield_' . $field->get('shortname'),
            $field->get('type'),
            get_string('course') . ': ' . $field->get_formatted_name(),
            true,
            $field->get_formatted_name()
        );
    }
}
