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

[$course, $cm] = get_course_and_cm_from_cmid($id, 'coursecertificate');

require_course_login($course, true, $cm);

$outputpage = new \mod_coursecertificate\output\view_page($id, $page, $perpage, $course, $cm);
$output = $PAGE->get_renderer('coursecertificate');
$data = $outputpage->export_for_template($output);
$certificatename = $PAGE->activityrecord->name;

// Redirect to view issue page if 'studentview' (user can not manage but can receive issues) and issue code is set.
if ($data['studentview'] && isset($data['issuecode'])) {
    $issueurl = new \moodle_url('/admin/tool/certificate/view.php', ['code' => $data['issuecode']]);
    redirect($issueurl);
}

$PAGE->set_url('/mod/coursecertificate/view.php', ['id' => $id]);
$PAGE->set_title($course->shortname . ': ' . $certificatename);
$PAGE->set_heading(format_string($course->fullname));

$context = \context_module::instance($id);
$PAGE->set_context($context);
$heading = $output->heading(format_string($certificatename), 2);

if ($CFG->version >= 2022012900) {
    // Moodle 4.0 and above. Heading and completion information are part of activity header present in the theme.
    $PAGE->activityheader->set_attrs([]);
    $heading = '';
} else if ($CFG->version >= 2021050700) {
    // Moodle 3.11. Display completion information under the header.
    $cminfo = cm_info::create($cm);
    $completiondetails = \core_completion\cm_completion_details::get_instance($cminfo, $USER->id);
    $activitydates = \core\activity_dates::get_dates_for_module($cminfo, $USER->id);
    $heading .= $output->activity_information($cminfo, $completiondetails, $activitydates);
}

echo $output->header();
echo $heading;
echo $output->render_from_template('mod_coursecertificate/view_page', $data);
echo $output->footer();
