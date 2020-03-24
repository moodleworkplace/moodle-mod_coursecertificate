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
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @uses FEATURE_BACKUP_MOODLE2
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function coursecertificate_supports(string $feature): ?bool {
    // TODO: Check supports needed.
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
    coursecertificate_set_mainfile($data);

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

    coursecertificate_set_mainfile($data);

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
function coursecertificate_page_type_list(string $pagetype, stdClass $parentcontext, stdClass $currentcontext): array {
    $modulepagetype = [
        'mod-coursecertificate-*' => get_string('page-mod-coursecertificate-x', 'mod_coursecertificate'),
    ];
    return $modulepagetype;
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter  if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 */
function coursecertificate_check_updates_since(cm_info $cm, int $from, array $filter = []): stdClass {
    $updates = course_check_module_updates_since($cm, $from, ['package'], $filter);
    return $updates;
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}.
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @return string[] array of pair file area => human file area name
 */
function coursecertificate_get_file_areas(stdClass $course, stdClass $cm, stdClass $context): array {
    $areas = [];
    $areas['package'] = get_string('areapackage', 'mod_coursecertificate');
    return $areas;
}

/**
 * File browsing support for data module.
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info_stored|null file_info_stored instance or null if not found
 */
function coursecertificate_get_file_info(file_browser $browser, array $areas, stdClass $course,
                                   stdClass $cm, context $context, string $filearea, int $itemid,
                                   string $filepath, string $filename): ?file_info_stored {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'package') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_coursecertificate', 'package', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_coursecertificate', 'package', 0);
            } else {
                // Not found.
                return null;
            }
        }
        return new file_info_stored($browser, $context, $storedfile, $urlbase, $areas[$filearea], false, true, false, false);
    }
    return null;
}

/**
 * Serves the files from the mod_coursecertificate file areas.
 *
 * @param mixed $course course or id of the course
 * @param mixed $cm course module or id of the course module
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function coursecertificate_pluginfile($course, $cm, context $context,
                                string $filearea, array $args, bool $forcedownload, array $options = []): bool {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    $fullpath = '';

    if ($filearea === 'package') {
        $revision = (int)array_shift($args); // Prevents caching problems - ignored here.
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_coursecertificate/package/0/$relativepath";
    }
    if (empty($fullpath)) {
        return false;
    }
    $fs = get_file_storage();
    $file = $fs->get_file_by_hash(sha1($fullpath));
    if (empty($file)) {
        return false;
    }
    send_stored_file($file, null, 0, false, $options);
}

/**
 * Saves draft files as the activity package.
 *
 * @param stdClass $data an object from the form
 */
function coursecertificate_set_mainfile(stdClass $data): void {
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $context = context_module::instance($cmid);

    if (!empty($data->packagefile)) {
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_coursecertificate', 'package');
        file_save_draft_area_files($data->packagefile, $context->id, 'mod_coursecertificate', 'package',
            0, ['subdirs' => 0, 'maxfiles' => 1]);
    }
}