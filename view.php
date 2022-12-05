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
 * Prints an instance of mod_coursecertificate.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

global $PAGE, $USER, $CFG;

$id = required_param('id', PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$download = optional_param('download', false, PARAM_BOOL);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'coursecertificate');

require_course_login($course, true, $cm);

$PAGE->set_url('/mod/coursecertificate/view.php', ['id' => $id]);
$PAGE->set_title($course->shortname . ': ' . $PAGE->activityrecord->name);
$PAGE->set_heading(format_string($course->fullname));

$output = $PAGE->get_renderer('coursecertificate');
$outputpage = new \mod_coursecertificate\output\view_page($id, $page, $perpage, $course, $cm);
$data = $outputpage->export_for_template($output);

if (!empty($data['viewurl']) && $download) {
    // When we link to the course module for the student, we link with &download=1 parameter
    // and with target=_blank. In other situations where the links to the view page is displayed
    // (index page, logs, hardcoded links, etc), we need to make sure that the certificate will open in a
    // new tab and user can return to where they came from. In this case we display a button on the page.
    redirect($data['viewurl']);
}

$context = \context_module::instance($id);
$PAGE->set_context($context);

echo $output->header();
echo $output->render_from_template('mod_coursecertificate/view_page', $data);
echo $output->footer();
