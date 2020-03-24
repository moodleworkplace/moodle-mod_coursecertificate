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
 * Prints an instance of mod_coursecertificate.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->libdir.'/completionlib.php');

$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'coursecertificate');

require_login($course, true, $cm);

$moduleinstance = $DB->get_record('coursecertificate', ['id' => $cm->instance], '*', MUST_EXIST);

$context = context_module::instance($cm->id);

$event = \mod_coursecertificate\event\course_module_viewed::create([
    'objectid' => $moduleinstance->id,
    'context' => $context
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('coursecertificate', $moduleinstance);
$event->trigger();

// Completion.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// TODO: Generate certificate issues report.
//$table = new \mod_coursecertificate\certificate_issues_table($userid, $download);
//$table->define_baseurl($pageurl);

//if ($table->is_downloading()) {
//    $table->download();
//    exit();
//}

$PAGE->set_url('/mod/certificate/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

// TODO: Add certificate content here.
$certificate = $DB->get_record('coursecertificate', array('id' => $cm->instance), '*', MUST_EXIST);
print_object($certificate);

$templateissues = \tool_certificate\certificate::get_issues_for_template($certificate->template, 0, 100);
print_object($templateissues);

// Print report.
//$table->out($perpage, false);

echo $OUTPUT->footer();